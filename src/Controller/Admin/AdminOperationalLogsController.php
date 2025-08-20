<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\AdminOperationalLogs\SearchForm;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * AdminOperationalLogs Controller
 */
class AdminOperationalLogsController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'download',
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
            'controller' => 'AdminOperationalLogs',
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
            $this->getRequest()->getSession()->delete('adminOperationalLogs.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('adminOperationalLogs.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('adminOperationalLogs.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $adminOperationalLogs = $this->Pagination->paginate($this->fetchTable('AdminOperationalLogs'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('adminOperationalLogs.list.search', $searchData);

        // ビュー変数
        $this->set([
            'adminOperationalLogs' => $adminOperationalLogs,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Download method
     *
     * @return \Cake\Http\Response
     */
    public function download()
    {
        /** @var \App\Model\Table\AdminOperationalLogsTable $adminOperationalLogsTable */
        $adminOperationalLogsTable = $this->fetchTable('AdminOperationalLogs');

        $this->getRequest()->allowMethod('post');

        // 検索条件取得
        $searchInputs = $this->getRequest()->getSession()->read('adminOperationalLogs.list.search');

        // CSVファイル出力
        $fileName = Configure::readOrFail('Setting.csv.download.adminOperationalLog.name');
        $fileName = $this->FileDownload->getDlFileName($fileName);
        $fileType = Configure::readOrFail('Setting.csv.download.reservation.type');
        $callback = $adminOperationalLogsTable->createCsv($searchInputs);

        return $this->FileDownload->setStreamDownloadResponse($fileName, $fileType, $callback);
    }
}
