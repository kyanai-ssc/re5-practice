<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * Holidays Controller
 */
class HolidaysController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'download',
            'sample',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Holidays',
            'action' => 'edit',
        ], 301);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\HolidaysTable $holidaysTable */
        $holidaysTable = $this->fetchTable('Holidays');

        $month = $holidaysTable->setMonth($this->getRequest()->getQuery('month'));

        // エンティティー生成
        $holidays = $holidaysTable->getEntities($month);

        $calender = DateTimeUtility::getDaysOfMonth($month->format('Y/m/d'));

        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('Holidays', 'holidays');
            // 入力値取得
            $holidayInputs = $holidaysTable->filterInputDate((array)$this->getRequest()->getData());
            $holidays = $holidaysTable->patchEntities($holidays, $holidayInputs['holidays']);

            if (
                $holidaysTable->saveMany(
                    $holidays,
                    [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                    'saveKey' => 'date',
                    'month' => $month,
                    ]
                ) !== false
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'holidaysFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Holidays',
                    'action' => 'edit',
                    '?' => [
                        'month' => $month->format('Y/m/d'),
                    ],
                ]);
            } else {
                $flashErrors = $holidaysTable->setHolidayError($holidayInputs, $holidays, $calender);
                foreach ($flashErrors as $errors) {
                    $this->Flash->set((string)$errors, [
                        'key' => 'holidaysErrors',
                        'element' => 'error',
                    ]);
                }
            }
        }

        // ビュー変数
        $this->set([
            'holidays' => $holidays,
            'valueOptions' => $holidaysTable->getFieldValueOptions(),
            'calender' => $calender,
            'month' => $month,
            'checkDate' => $holidaysTable->getInputDates($holidays),
            'holidayList' => $holidaysTable->getList3years($month),
        ]);
    }

    /**
     * Download method
     *
     * @return \Cake\Http\Response
     */
    public function sample()
    {
        /** @var \App\Model\Table\HolidaysTable $holidaysTable */
        $holidaysTable = $this->fetchTable('Holidays');

        // CSVファイル出力
        $fileName = Configure::readOrFail('Setting.csv.import.holidays.sample.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $filePath = $holidaysTable->createSampleCsv();

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.import.holidays.sample.type'),
            $filePath,
            true
        );
    }

    /**
     * Download method
     *
     * @return \Cake\Http\Response
     */
    public function download()
    {
        // HTTPメソッドチェック
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\HolidaysTable $holidaysTable */
        $holidaysTable = $this->fetchTable('Holidays');

        // CSVファイル出力
        $fileName = Configure::readOrFail('Setting.csv.download.holiday.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $fileType = Configure::readOrFail('Setting.csv.download.holiday.type');
        $filePath = $holidaysTable->createCsv();

        return $this->FileDownload->setDownloadResponse($fileName, $fileType, $filePath, true);
    }
}
