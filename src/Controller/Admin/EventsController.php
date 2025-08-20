<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\Events\EventsForm;
use App\Form\Admin\Events\PlanSearchForm;
use App\Form\Admin\Events\SearchForm;
use App\Form\Admin\Events\TogetherEditForm;
use App\Form\Admin\Reservations\CalendarForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Exception\NotFoundException;

/**
 * Events Controller
 */
class EventsController extends AdminAppController
{
    public const TOKEN_TOGETHER_EDIT = 'token_together_edit';
    public const TOGETHER_EDIT_MAX = 100;

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'delete',
            'download',
            'sample',
            'planDownload',
        ]);

        return $response;
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Events',
            'action' => 'list',
        ], 301);
    }

    /** List method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('events.list.search');
            $this->getRequest()->getSession()->delete('events.list.checked');
        }

        if ($this->SearchInput->checkSearchButtonClick()) {
            $this->getRequest()->getSession()->delete('events.list.checked');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('events.list.search')
        );

        $searchInputs = $labelsTable->setSearchLabelID($searchInputs);
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('events.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $events = $this->Pagination->paginate($this->fetchTable('Events'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                    'contain' => ['EventWeeks', 'Labels'],
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('events.list.search', $searchData);

        // ビュー変数
        $this->set([
            'events' => $events,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'formType' => $labelsTable->setAjaxForm(Configure::readOrFail('Master.label.type.other')),
            'check' => $this->SearchInput->getListCheckInfo(
                $searchForm->getFieldValueOptions(),
                $this->getRequest()->getSession()->read('events.list.checked')
            ),
        ]);
    }

    /**
     * Add method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function add($id = null)
    {
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        // タグ情報
        $tagLists = $tagGroupsTable->getTagsList();

        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('Events', [
                'event_plans',
                'event_stock_marks',
                'event_remarks',
                'event_holidays',
                'event_stock_settings',
            ], ['together' => true]);

            // 入力値取得
            $eventsInputs = $this->getRequest()->getData();
            // 入力チェック
            $event = $eventsTable->newEntity($eventsInputs, [
                'associated' => [
                    'EventWeeks',
                    'EventTags',
                    'EventPlans',
                    'EventStockMarks',
                    'EventImages',
                    'EventRemarks',
                    'EventHolidays',
                    'EventHolidays.EventHolidayWeeks',
                    'EventHolidays.EventHolidayExcludeDates',
                    'EventStockSettings',
                    'EventStockSettings.EventStockSettingWeeks',
                    'EventStockSettings.EventStockSettingExcludeDates',
                    'EventSmartLocks',
                ],
            ]);

            if ($eventsTable->save($event, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'eventsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Events',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'eventsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            if ($id !== null) {
                // 入力チェック
                $event = $eventsTable->get($id, [
                    'finder' => 'all',
                    'contain' => [
                        'EventWeeks',
                        'EventTags',
                        'EventStockMarks',
                        'EventImages',
                        'EventRemarks',
                        'EventPlans',
                        'EventHolidays' => ['EventHolidayWeeks', 'EventHolidayExcludeDates'],
                        'EventStockSettings' => ['EventStockSettingWeeks', 'EventStockSettingExcludeDates'],
                        'EventSmartLocks',
                    ],
                ]);
                $eventsTable->formatDefault($event, $tagLists);
                $event->unset('id');
            } else {
                // 入力値取得
                $eventsInputs = $eventsTable->getDefaultFieldValues();
                // エンティティ生成
                $event = $eventsTable->newEntity($eventsInputs, [
                    'validate' => false,
                    'associated' => [
                        'EventWeeks' => ['validate' => false],
                        'EventTags' => ['validate' => false],
                        'EventPlans' => ['validate' => false],
                        'EventStockMarks' => ['validate' => false],
                        'EventImages' => ['validate' => false],
                        'EventRemarks' => ['validate' => false],
                        'EventHolidays' => [
                            'validate' => false,
                            'EventHolidayWeeks' => ['validate' => false],
                            'EventHolidayExcludeDates' => ['validate' => false],
                        ],
                        'EventStockSettings' => [
                            'validate' => false,
                            'EventStockSettingWeeks' => ['validate' => false],
                            'EventStockSettingExcludeDates' => ['validate' => false],
                        ],
                        'EventSmartLocks' => ['validate' => false],
                    ],
                ]);
            }
        }

        $reserve['future'] = false;
        $reserve['all'] = false;

        // ビュー変数
        $this->set([
            'event' => $event,
            'reserve' => $reserve,
            'tagLists' => $tagLists,
            'valueOptions' => $eventsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * Copy method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function copy($id = null)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        // エンティティー生成
        $event = $this->fetchTable('Events')->get($id, [
            'finder' => 'all',
            'contain' => [
                'EventWeeks',
                'EventTags',
                'EventStockMarks',
                'EventImages',
                'EventRemarks',
                'EventPlans',
                'EventHolidays' => ['EventHolidayWeeks', 'EventHolidayExcludeDates'],
                'EventStockSettings' => ['EventStockSettingWeeks', 'EventStockSettingExcludeDates'],
                'EventSmartLocks',
            ],
        ]);

        // コピー可否チェック
        if (!$labelsTable->isAdminUsableLabel($event->get('label_id'))) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        $this->add($id);

        $this->render('add');
    }

    /**
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        // エンティティー生成
        $event = $eventsTable->get($id, [
            'finder' => 'all',
            'contain' => [
                'EventWeeks',
                'EventTags',
                'EventStockMarks',
                'EventImages',
                'EventRemarks',
                'EventPlans',
                'EventHolidays' => ['EventHolidayWeeks', 'EventHolidayExcludeDates'],
                'EventStockSettings' => ['EventStockSettingWeeks', 'EventStockSettingExcludeDates'],
                'EventSmartLocks',
            ],
        ]);

        // 編集可否チェック
        if (!$labelsTable->isAdminUsableLabel($event->label_id)) {
            throw new BadRequestException(Message::ERROR_ILLEGAL_TRANSITION);
        }

        // タグ情報
        $tagLists = $tagGroupsTable->getTagsList();

        $eventsTable->setEditAbled($event->get('id'));

        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('Events', [
                'event_plans',
                'event_stock_marks',
                'event_remarks',
                'event_holidays',
                'event_stock_settings',
            ]);
            // 入力値取得
            $eventsInputs = $this->getRequest()->getData();

            $eventsTable->patchEntity($event, (array)$eventsInputs, [
                'associated' => [
                    'EventWeeks',
                    'EventTags',
                    'EventPlans',
                    'EventStockMarks',
                    'EventImages',
                    'EventRemarks',
                    'EventHolidays',
                    'EventHolidays.EventHolidayWeeks',
                    'EventHolidays.EventHolidayExcludeDates',
                    'EventStockSettings',
                    'EventStockSettings.EventStockSettingWeeks',
                    'EventStockSettings.EventStockSettingExcludeDates',
                    'EventSmartLocks',
                ],
            ]);

            if ($eventsTable->save($event, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'eventsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Events',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                // 完了メッセージ
                $errorMessage = __(Message::INVALID_INPUT);
                if (!empty($event->getError('stock_error'))) {
                    $stockError = $event->getError('stock_error');
                    $errorMessage = reset($stockError);
                }
                $this->Flash->set($errorMessage, [
                    'key' => 'eventsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $eventsTable->formatDefault($event, $tagLists);
        }
        // ビュー変数
        $this->set([
            'event' => $event,
            'reserve' => $eventsTable->getEditAbled(),
            'timePlan' => $event->time_plan,
            'tagLists' => $tagLists,
            'valueOptions' => $eventsTable->getFieldValueOptions(),
        ]);
    }

    /**
     * まとめて編集
     *
     * @return \Cake\Http\Response|null|void
     */
    public function togetherEdit()
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\TagGroupsTable $tagGroupsTable */
        $tagGroupsTable = $this->fetchTable('TagGroups');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $checked = $this->getRequest()->getSession()->read('events.list.checked');

        $eventList = $eventsTable->getTogetherEditEvents(
            $checked,
            $this->getRequest()->getSession()->read('events.list.search')
        );

        if (empty($eventList)) {
            throw new BadRequestException(Message::ERROR_ONE_OR_MORE_SELECT);
        } elseif (count($eventList) > static::TOGETHER_EDIT_MAX) {
            throw new BadRequestException(Message::TOGETHER_OPERATION_MAX);
        }

        // 編集可否チェック
        foreach ($eventList as $event) {
            if (!$labelsTable->isAdminUsableLabel($event['label_id'])) {
                throw new NotFoundException();
            }
        }

        $eventsTable->getBehavior('AdminOperationLog')->setConfig([
            'afterSave' => false,
        ]);

        // タグ情報
        $tagLists = $tagGroupsTable->getTagsList();

        $togetherEditForm = new TogetherEditForm();
        if ($this->getRequest()->is('post')) {
            $this->RequestFilter->setRebalanceInputs('Events', [
                'event_plans',
                'event_stock_marks',
                'event_remarks',
                'event_holidays',
                'event_stock_settings',
            ], ['together' => true]);

            // 入力値取得
            $eventsInputs = $this->getRequest()->getData();

            // ワンタイムトークンチェック
            if (!$this->TokenValidation->validate(static::TOKEN_TOGETHER_EDIT)) {
                $this->Flash->set((string)__(Message::ERROR_ILLEGAL_TRANSITION), [
                    'key' => 'eventsTokenError',
                    'element' => 'error',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Events',
                    'action' => 'togetherEdit',
                ]);
            }

            // 入力チェック
            if ($togetherEditForm->execute((array)$this->getRequest()->getData())) {
                $entities = $eventsTable->patchEntities(
                    $eventList,
                    $togetherEditForm->setTogetherEntities($eventList, (array)$eventsInputs),
                    [
                        'validate' => false,
                        'associated' => [
                            'EventTags' => ['validate' => false],
                            'EventStockMarks' => ['validate' => false],
                            'EventImages' => ['validate' => false],
                            'EventRemarks' => ['validate' => false],
                            'EventSmartLocks' => ['validate' => false],
                        ],
                        'together' => true,
                    ]
                );

                if (
                    $eventsTable->saveMany(
                        $entities,
                        [
                        'saveOperation' => $this->getRequest()->getAttribute('params'),
                        'afterSave' => false,
                        'together' => true,
                        ]
                    )
                ) {
                    $this->getRequest()->getSession()->delete('events.list.checked');

                    // 完了メッセージ
                    $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                        'key' => 'eventsFinish',
                        'element' => 'success',
                    ]);

                    return $this->redirect([
                        'prefix' => 'Admin',
                        'controller' => 'Events',
                        'action' => 'list',
                        '?' => Configure::read('Setting.searchInput.searchQuery'),
                    ]);
                } else {
                    $this->Flash->set((string)__(Message::INVALID_INPUT), [
                        'key' => 'eventsErrors',
                        'element' => 'error',
                    ]);
                }
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'eventsErrors',
                    'element' => 'error',
                ]);
                $this->setRequestData((array)$eventsInputs);
            }
        } else {
            $eventsInputs = $eventsTable->getDefaultFieldValues();
        }

        // ビュー変数
        $this->set([
            'token' => $this->TokenValidation->generate(self::TOKEN_TOGETHER_EDIT),
            'tokenName' => $this->TokenValidation->getParameterName(),
            'togetherEditForm' => $togetherEditForm,
            'eventsInputs' => $eventsInputs,
            'eventList' => $eventList,
            'isChangeCharge' => $eventsTable->isChangeCharge($eventList),
            'fields' => $togetherEditForm->getSchema()->fields(),
            'valueOptions' => $togetherEditForm->getFieldValueOptions(),
            'tagLists' => $tagLists,
        ]);
    }

    /**
     * アップロードフォーマットサンプルダウンロード
     *
     * @return \Cake\Http\Response|null|void
     */
    public function sample()
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $fileName = Configure::readOrFail('Setting.csv.import.event.sample.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $filePath = $eventsTable->createSampleCsv();

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.import.event.sample.type'),
            $filePath,
            true
        );
    }

    /**
     * Delete method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function delete($id = null)
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        // エンティティー生成
        $event = $eventsTable->get($id, [
            'finder' => 'all',
            'contain' => [
                'EventWeeks',
                'EventTags',
                'EventStockMarks',
                'EventImages',
                'EventRemarks',
                'EventPlans',
                'EventHolidays' => ['EventHolidayWeeks', 'EventHolidayExcludeDates'],
                'EventStockSettings' => ['EventStockSettingWeeks', 'EventStockSettingExcludeDates'],
                'EventSmartLocks',
            ],
        ]);

        // 削除可否チェック
        if (!$labelsTable->isAdminUsableLabel($event->get('label_id')) || !$event->canDelete()) {
            throw new NotFoundException();
        }

        $eventsTable->deleteOrFail($event, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'eventsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Events',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * Download method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function download()
    {
        $this->getRequest()->allowMethod('post');

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $searchData = $this->getRequest()->getSession()->read('events.list.search');
        if (!isset($searchData)) {
            throw new BadRequestException();
        }

        if (!is_array($searchData)) {
            $searchData = [];
        }

        // 管理者に紐づかないカテゴリーで検索された場合は強制的に担当カテゴリで検索する(マスター管理者を除く)
        $searchData = $labelsTable->setSearchLabelID($searchData);

        $fileName = Configure::readOrFail('Setting.csv.download.event.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $callback = $eventsTable->createCsv($searchData);

        return $this->FileDownload->setStreamDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.download.event.type'),
            $callback
        );
    }

    /**
     * プレビュー（時間）
     *
     * @return \Cake\Http\Response|null|void
     */
    public function previewUsage()
    {
        $eventForm = new EventsForm();

        $checkResult = false;
        $eventInputs = $this->getRequest()->getQueryParams();
        $setDate = $this->commonData()->getNowDateTime()->addDays(1)->format('Y/m/d');
        $eventInputs = $eventForm->replacePreviewData((array)$eventInputs);

        if ($eventForm->execute((array)$eventInputs)) {
            $checkResult = true;

            $eventEntity = $this->fetchTable('Events')->newEntity((array)$eventInputs, ['validate' => false]);
            $eventEntity->set('id', -1);
            $eventEntities[] = $eventEntity;

            // プレビュー用カレンダー生成
            $calendarForm = new CalendarForm();
            $eventCalendar = $calendarForm->createEventCalendarInstance([
                'calendar_type' => \App\Model\Entity\Event::CALENDAR_TYPE_TIME_1DAY,
            ]);
            if (!is_null($eventCalendar)) {
                $eventCalendar->setEvents($eventEntities);
                $eventCalendar->setDate($setDate);
                $eventCalendar->createCalendar();
            }

            // ビュー変数
            $this->set([
                'checkResult' => $checkResult,
                'eventEntity' => $eventEntity,
                'eventForm' => $eventForm,
                'event' => $eventForm->getData(),
                'calendar' => $calendarForm->getEventCalendar(),
                'calendarForm' => $calendarForm,
                'valueOptions' => $calendarForm->getFieldValueOptions(),
                'selectCalendar' => false,
            ]);
        } else {
            $this->Flash->set((string)__(Message::INVALID_INPUT), [
                'key' => 'eventsPreviewErrors',
                'element' => 'error',
            ]);

            $this->set([
                'checkResult' => $checkResult,
                'eventForm' => $eventForm,
            ]);
        }
    }

    /**
     * プラン一覧
     *
     * @return \Cake\Http\Response|null|void
     */
    public function eventPlanList()
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        if (!$loginData->isSystemAdmin()) {
            throw new NotFoundException();
        }

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('events.plan.list.search');
        }

        // 入力値取得
        $searchForm = new PlanSearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('events.plan.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('events.plan.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $eventPlans = $this->Pagination->paginate($this->fetchTable('EventPlans'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('events.plan.list.search', $searchData);

        // ビュー変数
        $this->set([
            'eventPlans' => $eventPlans,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
            'formType' => $labelsTable->setAjaxForm(Configure::readOrFail('Master.label.type.other')),
        ]);
    }

    /**
     * プラン一覧Download method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function planDownload()
    {
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        /** @var \App\Model\Entity\Admin $loginData */
        $loginData = $this->commonData()->getAdminLoginData();

        if (!$loginData->isSystemAdmin()) {
            throw new NotFoundException();
        }

        $this->getRequest()->allowMethod('post');

        $searchData = $this->getRequest()->getSession()->read('events.plan.list.search');

        if (!is_array($searchData)) {
            $searchData = [];
        }

        $fileName = Configure::readOrFail('Setting.csv.download.eventPlan.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $filePath = $eventsTable->createPlanCsv($searchData);

        return $this->FileDownload->setDownloadResponse(
            $fileName,
            Configure::readOrFail('Setting.csv.download.eventPlan.type'),
            $filePath,
            true
        );
    }
}
