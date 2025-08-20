<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\MailDelivery;
use App\Utility\Csv\CsvWriter;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\I18n\FrozenDate;
use Cake\Mailer\MailerAwareTrait;
use Throwable;

/**
 * BillingCsvCommand class.
 */
class BillingCsvCommand extends Command
{
    use MailerAwareTrait;

    public const MAIL_FROM = 'billings@';
    public const MAIL_SUBJECT = '[RE5] 月額課金CSV %DATE%';

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->setTranslate();
    }

    /**
     * @inheritDoc
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $this->addClientOption($parser);
        $parser->addOption('directory', [
            'name' => 'directory',
        ]);
        $parser->addOption('file', [
            'name' => 'file',
        ]);
        $parser->addOption('server', [
            'name' => 'server',
        ]);
        $parser->addOption('sendTo', [
            'name' => 'sendTo',
        ]);

        return $parser;
    }

    /**
     * @inheritDoc
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $this->outputStartMessage();

        $sendTo = (string)$args->getOption('sendTo');

        $directory = (string)$args->getOption('directory');
        if (
            $sendTo === ''
            && ($directory === '' || !is_dir($directory) || !is_writable($directory))
        ) {
            throw new CakeException('Invalid directory.');
        }

        $file = (string)$args->getOption('file');
        if ($sendTo === '' && $file === '') {
            $file = sprintf(
                '%s_%s.csv',
                $this->commonData()->getNowDateTime()->format('Ym'),
                Configure::readOrFail('Client.host')
            );
        }

        $server = (string)$args->getOption('server');
        if ($server === '') {
            $server = Configure::readOrFail('Client.host');
        }

        /** @var \App\Model\Table\MailDeliveriesTable $mailDeliveriesTable */
        $mailDeliveriesTable = $this->getTableLocator()->get('MailDeliveries');
        $mailDeliveryHistories = $mailDeliveriesTable->getAssociation('MailDeliveryHistories')->getTarget();
        $query = $mailDeliveryHistories->find();

        $nowDate = new FrozenDate();
        $from = new FrozenDate($nowDate->subMonths(1)->format('Y-m-d'));
        $from = $from->setDateTime($from->year, $from->month, $from->startOfMonth()->day, 0, 0);

        $to = new FrozenDate();
        $to = $to->setDateTime($to->year, $to->month, $to->startOfMonth()->day, 0, 0);
        $subQuery = $mailDeliveriesTable->find('all')->select('id')->where([
            'send_status' => MailDelivery::SEND_STATUS_SENT,
            'send_date >= ' => $from,
            'send_date < ' => $to,
        ]);

        $query->select([
            'id',
        ])->where(
            [
                'mail_delivery_id IN ' => $subQuery,
            ]
        );
        $query->enableHydration(false);
        $count = $query->count();

        if ($sendTo === '') {
            // プランがエクスパンド（ベーシック）、エクスパンド（カスタマイズ）以外の場合（引数の送信先が空の場合）、CSV出力
            $this->createCsvRow($directory . DS . $file, $server, $count);
        } else {
            // プランがエクスパンド（ベーシック）、エクスパンド（カスタマイズ）の場合、CSV出力ではなくメールを送る
            $mails = explode(',', $sendTo);
            $fromMail = static::MAIL_FROM . $server;
            $subject = str_replace(
                '%DATE%',
                $this->commonData()->getNowDateTime()->format('Y/m'),
                static::MAIL_SUBJECT
            );
            $csvContents = [
                'server' => $server,
                'domain' => Configure::readOrFail('Client.host'),
                'users' => '',
                'galaySize' => '',
                'mailCount' => $count,
            ];
            $contents = implode(',', $csvContents);

            /** @var \App\Mailer\DefaultMailer $mailer */
            $mailer = $this->getMailer('default');
            $mailer->setProfile('default');
            $mailer->setDomain(Configure::readOrFail('Client.host'));
            $mailer->getMessage()->setTransferEncoding('base64');
            $mailer->viewBuilder()->setTemplate('default');

            $mailer->setTo($mails);
            $mailer->setFrom($fromMail);
            $mailer->setSubject($subject);
            $mailer->setViewVars([
                'content' => $contents,
            ]);
            $mailer->setReturnPath($fromMail);
            $mailer->send();
        }

        $this->outputEndMessage();
    }

    /**
     * 課金集計用のCSV1行を生成
     *
     * @param string $filePath ファイルパス
     * @param string $server サーバー
     * @param int $count メール配信数の集計値
     * @return void
     */
    public function createCsvRow(string $filePath, string $server, int $count): void
    {
        $csvWriter = new CsvWriter([
            'internalEncoding' => 'UTF-8',
            'fileEncoding' => 'UTF-8',
            'fileBom' => false,
            'fileLinefeed' => "\r\n",
            'filePath' => $filePath,
        ]);

        try {
            $csvWriter->open();
            $csvWriter->append([
                'server' => $server,
                'domain' => Configure::readOrFail('Client.host'),
                'users' => '',
                'galaySize' => '',
                'mailCount' => $count,
            ]);
            $csvWriter->close();
        } catch (Throwable $e) {
            $csvWriter->close(true);
            throw $e;
        }
    }
}
