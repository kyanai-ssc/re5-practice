<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\EventHolidays\SearchForm;
use App\Locale\Message;
use App\Model\Entity\AdminOperationalLog;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * EventHolidays Controller
 */
class EventHolidaysController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'delete',
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
            'controller' => 'EventHolidays',
            'action' => 'list',
        ], 301);
    }

    /**
     * List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('eventHolidays.list.search');
            $this->getRequest()->getSession()->delete('eventHolidays.list.checked');
        }

        if ($this->SearchInput->checkSearchButtonClick()) {
            $this->getRequest()->getSession()->delete('eventHolidays.list.checked');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('eventHolidays.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('eventHolidays.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $eventHolidays = $this->Pagination->paginate($this->fetchTable('EventHolidays'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('eventHolidays.list.search', $searchData);

        // ビュー変数
        $this->set([
            'eventHolidays' => $eventHolidays,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'checkList' => $this->getRequest()->getSession()->read('eventHolidays.list.checked'),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\EventHolidaysTable $eventHolidaysTable */
        $eventHolidaysTable = $this->fetchTable('EventHolidays');

        $id = null;
        $eventHolidays = $eventHolidaysTable->find('edit', [
            'eventId' => $id,
        ])->toArray();

        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('EventHolidays', 'event_holidays');
            $inputs = (array)$this->getRequest()->getData('event_holidays', []);
            $eventHolidays = $eventHolidaysTable->patchEntities($eventHolidays, $inputs, [
                'associated' => [
                    'EventHolidayExcludeDates' => [],
                    'EventHolidayWeeks' => [],
                ],
            ]);

            //操作ログ保存
            $eventHolidaysTable->getBehavior('AdminOperationLog')->setConfig([
                'saveOperation' => true,
                'afterSave' => false,
            ]);
            if (
                $eventHolidaysTable->saveMany(
                    $eventHolidays,
                    [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                    'exceptionIds' => AdminOperationalLog::EXCEPTION_ID_EVENT_HOLIDAYS,
                    ]
                ) !== false
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'eventHolidaysFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'EventHolidays',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                // エラーメッセージ
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'eventHolidaysErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $eventHolidaysTable->formatDefault($eventHolidays);
        }

        // ビュー変数
        $this->set([
            'eventHolidays' => $eventHolidays,
            'eventData' => $eventHolidaysTable->getEventData($id),
            'valueOptions' => $eventHolidaysTable->getFieldValueOptions(),
        ]);
    }
}
