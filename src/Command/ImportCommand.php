<?php
declare(strict_types=1);

namespace App\Command;

use App\Exception\SmartLockException;
use App\Form\Admin\ImportFormInterface;
use App\Locale\Message;
use App\Model\ImportableTableInterface;
use App\Utility\Csv\CsvReader;
use App\Utility\FileUtility;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Mailer\MailerAwareTrait;
use Throwable;

/**
 * Class MailCommand
 *
 * @package App\Command
 * @property \App\Model\Table\EventsTable $Events
 * @property \App\Model\Table\ReservationsTable $Reservations
 * @property \App\Model\Table\UsersTable $Users
 * @example Import [className] [filename] [adminId] --client=[clientName]
 */
class ImportCommand extends Command
{
    use MailerAwareTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $isWin = strpos(PHP_OS, 'WIN') === 0;
        if ($isWin) {
            setlocale(LC_CTYPE, 'C');
        }

        parent::initialize();

        $this->setTranslate();
    }

    /**
     * @inheritDoc
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);

        $parser->addArgument('model', [
            'help' => 'Import Model',
            'require' => true,
            'choices' => Configure::readOrFail('Setting.csv.import.model'),
        ]);

        $parser->addArgument('file', [
            'help' => 'Import FileName',
            'require' => true,
        ]);

        $parser->addArgument('adminId', [
            'help' => 'Import AdminId',
            'require' => true,
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        /** @var \App\Model\Table\AdminsTable $adminsTable */
        $adminsTable = $this->getTableLocator()->get('Admins');
        /** @var \App\Model\Table\AdminMailsTable $adminMailsTable */
        $adminMailsTable = $this->getTableLocator()->get('AdminMails');
        /** @var \App\Model\Table\AdminOperationalLogsTable $adminOperationalLogsTable */
        $adminOperationalLogsTable = $this->getTableLocator()->get('AdminOperationalLogs');

        $adminId = $args->getArgument('adminId');
        if (!$adminsTable->validatePrimaryKey($adminId)) {
            $this->errorExit('Import Batch AdminIdError');
        }

        try {
            $this->commonData()->setAdminLoginData($adminsTable->get($adminId, [
                'finder' => 'login',
            ]));
        } catch (RecordNotFoundException $e) {
            $this->errorExit('Import Batch AdminIdError');
        }

        $modelName = (string)$args->getArgument('model');
        if ($modelName === '') {
            $this->errorExit('Import Batch ModelError');
        }

        $adminMails = $adminMailsTable->find('mails', [
            'adminId' => $adminId,
        ])->all()->combine('id', 'mail')->toArray();

        $ids = [];
        $successInfo = [
            'create' => 0,
            'modify' => 0,
            'error' => 0,
            'errorMessage' => [],
            'errorLimit' => 0,
            'validHeader' => true,
            'invalidRow' => 0,
        ];

        try {
            $table = $this->getTableLocator()->get($modelName);
            if (!($table instanceof ImportableTableInterface)) {
                throw new CakeException();
            }

            $formClass = '\\App\\Form\\Admin\\' . $modelName . '\\ImportForm';
            /** @var \App\Form\AppForm $importForm */
            $importForm = new $formClass();
            if (!($importForm instanceof ImportFormInterface)) {
                throw new CakeException();
            }

            $table->getLockForImport();

            $csvReader = new CsvReader([
                'internalEncoding' => 'UTF-8',
                'fileEncoding' => 'UTF-8',
                'fileBom' => true,
                'filePath' => TMP_UPLOAD_IMPORT . DS . $args->getArgument('file'),
            ]);
            $csvReader->open();

            $header = $csvReader->read();
            if (isset($header)) {
                $importForm->setCsvHeader($header);
            }
            $validHeader = $importForm->checkCsvHeader();
            $successInfo['validHeader'] = $validHeader;

            $saveOptions = [
                'method' => 'import',
            ];

            // CSV取り込み
            if ($validHeader) {
                while (true) {
                    // 1行取得
                    $line = $csvReader->read();
                    if (!isset($line)) {
                        break;
                    }

                    // Entityの作成（new OR patch）
                    $form = clone $importForm;
                    $form->setCsvData($line);
                    $entity = $form->getEntity();

                    if ($form->isInvalidData()) {
                        $successInfo['invalidRow'] = $csvReader->getRowCount();
                        break;
                    }

                    if (isset($entity) && $table->save($entity, $saveOptions)) {
                        if ($form->isNewEntity()) {
                            $successInfo['create']++;
                        } else {
                            $successInfo['modify']++;
                        }
                        $ids[] = $entity->get('id');

                        $messages = $form->getInfoMessages();
                        if (!empty($messages)) {
                            $successInfo['errorMessage'][$csvReader->getRowCount()] = $messages;
                        }
                    } else {
                        $successInfo['error']++;
                        $successInfo['errorMessage'][$csvReader->getRowCount()] = $form->getMessages();

                        if ($successInfo['error'] >= Configure::readOrFail('Setting.csv.import.errorLimit')) {
                            $successInfo['errorLimit'] = $csvReader->getRowCount();
                            break;
                        }
                    }
                }
            }

            $table->releaseLockForImport();

            $adminOperationalLogsTable->saveImportLog($modelName, $ids, $successInfo['create'], $successInfo['modify']);

            // 管理者にメールを送信（アドレスが登録されている場合のみ）
            if (!empty($adminMails)) {
                $this->getMailer('Admin')->send('uploadFinishMail', [
                    $modelName,
                    $adminMails,
                    $successInfo,
                ]);
            }
        } catch (Throwable $e) {
            $adminOperationalLogsTable->saveImportLog($modelName, $ids, $successInfo['create'], $successInfo['modify']);
            if (!empty($adminMails)) {
                if (isset($csvReader)) {
                    $successInfo['invalidRow'] = $csvReader->getRowCount();
                    if ($e instanceof SmartLockException) {
                        if ($e->getMessage() !== '') {
                            $successInfo['errorMessage'][$csvReader->getRowCount()][] = __($e->getMessage());
                        } else {
                            $successInfo['errorMessage'][$csvReader->getRowCount()][]
                                = __(Message::ERROR_REGISTRATION_SMART_LOCK);
                        }
                    }
                }
                $this->getMailer('Admin')->send('uploadErrorMail', [
                    $modelName,
                    $adminMails,
                    $successInfo,
                ]);
            }

            throw $e;
        }

        $csvReader->close();
        FileUtility::deleteFile($csvReader->getConfig('filePath'));

        $this->outputEndMessage();
    }
}
