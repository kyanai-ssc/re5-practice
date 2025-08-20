<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxReservationsTrait;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\Events\SearchForm;
use App\Form\Admin\Events\UploadForm;
use App\Locale\Message;
use Cake\Event\EventInterface;

/**
 * Events Controller
 *
 * @property \App\Controller\Component\ImportComponent $Import
 */
class EventsController extends AdminAppController
{
    use AjaxReservationsTrait;
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
        $this->loadComponent('Import', [
            'model' => 'Events',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'saveCheck',
            'updatePublic',
            'previewUsage',
            'import',
        ]);

        return $response;
    }

    /**
     * 一覧のチェック情報を保持
     *
     * @return void
     */
    public function saveCheck()
    {
        $checkResult = false;

        $checked = $this->getRequest()->getSession()->read('events.list.checked');

        // 入力値取得
        $searchForm = new SearchForm();

        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('events.list.search')
        );

        // 管理者の担当カテゴリがある場合はそれを選択した扱いとする
        $adminLoginLabelId = $this->commonData()->getAdminLoginLabel();
        if ($adminLoginLabelId !== null) {
            $searchInputs['label_id'] = $adminLoginLabelId;
        }

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $saveData = $this->SearchInput->getCheckedIds($checked);

            // セッション保持
            $this->getRequest()->getSession()->write('events.list.checked', $saveData);

            $checkResult = true;
        }

        $this->set('checkResult', $checkResult);
    }

    /**
     * 一覧の公開設定を更新
     *
     * @return \Cake\Http\Response|null|void
     */
    public function updatePublic()
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\EventsTable $eventsTable */
        $eventsTable = $this->fetchTable('Events');

        $eventInputs = (array)$this->getRequest()->getData();
        // 編集可否チェック
        if (!$eventsTable->validatePrimaryKey($eventInputs['id'])) {
            return $this->sendError(__(Message::ERROR_ILLEGAL_TRANSITION));
        }

        // 入力チェック
        $event = $eventsTable->get($eventInputs['id'], [
            'finder' => 'all',
        ]);

        if (!$labelsTable->isAdminUsableLabel($event->get('label_id'))) {
            return $this->sendError(__(Message::ERROR_ILLEGAL_TRANSITION));
        }

        $eventsTable->patchEntity($event, $eventInputs, ['validate' => 'updatePublic', 'fields' => ['public_flg']]);

        if (
            $eventsTable->save(
                $event,
                [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'validate' => false,
                'checkRules' => false,
                'updatePublic' => true,
                ]
            )
        ) {
            // ビュー変数
            $this->set([
                'event' => $event,
                'valueOptions' => $eventsTable->getFieldValueOptions(),
                'checkResult' => true,
            ]);
        } else {
            return $this->sendError(__(Message::INVALID_INPUT));
        }
    }

    /**
     * Import method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function import()
    {
        $this->Import->importData(new UploadForm(), false);
    }

    /**
     * Send error method
     *
     * @param string $message メッセージ
     * @return \Cake\Http\Response|null|void
     */
    protected function sendError($message)
    {
        $this->autoRender = false;
        $this->response->withType('json');

        $json = json_encode([
            'checkResult' => false,
            'message' => $message,
        ]);

        if ($json === false) {
            return $this->response->withType('application/json');
        }

        return $this->response
            ->withType('application/json')
            ->withStringBody($json);
    }
}
