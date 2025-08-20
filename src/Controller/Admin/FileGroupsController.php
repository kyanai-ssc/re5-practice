<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\FileGroups\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;

/**
 * FileGroups Controller
 *
 * @property \App\Controller\Component\FileUploadComponent $FileUpload
 */
class FileGroupsController extends AdminAppController
{
    /**
     * @var array
     */
    protected $selectFile = [];

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->loadComponent('FileUpload');

        $this->FormProtection->setConfig('unlockedActions', [
            'delete',
        ]);

        // ファイル選択ボタン押下時用
        $fileSelect = [];
        $noNavi = false;
        if ($this->getRequest()->getQuery('selectFile') == true) {
            $fileSelect = ['selectFile' => true];
            $noNavi = true;
        }

        $this->set('noNavi', $noNavi);
        $this->set('selectFile', $fileSelect);

        $this->selectFile = $fileSelect;

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
            'controller' => 'FileGroups',
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
            $this->getRequest()->getSession()->delete('fileGroups.list.search');
            $this->getRequest()->getSession()->delete('fileGroups.list.select');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('fileGroups.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('fileGroups.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $files = $this->Pagination->paginate($this->fetchTable('Files'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('fileGroups.list.search', $searchData);

        // ビュー変数
        $this->set([
            'files' => $files,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function add()
    {
        /** @var \App\Model\Table\FileGroupsTable $fileGroupsTable */
        $fileGroupsTable = $this->fetchTable('FileGroups');

        $uploadFiles = [];

        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            $uploadToken = $this->TokenValidation->getToken($this->FileUpload->getTokenName());
            $uploadFiles = $this->FileUpload->get('fileGroups.add.files', []);
            $this->RequestFilter->setRebalanceInputs('FileGroups', 'files');

            $fileGroup = $fileGroupsTable->newEntity($this->getRequest()->getData());

            if (
                $fileGroupsTable->save(
                    $fileGroup,
                    [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                    'upload' => $uploadFiles,
                    ]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'fileGroupsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'FileGroups',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery') + $this->selectFile,
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'fileGroupsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $uploadToken = $this->TokenValidation->generate($this->FileUpload->getTokenName());
            $this->FileUpload->delete('fileGroups.add.files');

            // エンティティ生成
            $fileGroup = $fileGroupsTable->newEntity($fileGroupsTable->getDefaultFieldValues(), [
                'validate' => false,
                'associated' => [
                    'Files' => [
                        'validate' => false,
                    ],
                ],
            ]);
        }

        // ビュー変数
        $this->set([
            'token' => $uploadToken,
            'tokenName' => $this->TokenValidation->getParameterName(),
            'fileGroup' => $fileGroup,
            'valueOptions' => $fileGroupsTable->getFieldValueOptions(),
            'uploadFiles' => $uploadFiles,
        ]);
    }

    /**
     * Edit method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function edit($id = null)
    {
        /** @var \App\Model\Table\FileGroupsTable $fileGroupsTable */
        $fileGroupsTable = $this->fetchTable('FileGroups');

        $fileGroup = $fileGroupsTable->get($id, [
            'finder' => 'edit',
        ]);

        $uploadFiles = [];
        // HTTPメソッドチェック
        if ($this->getRequest()->is('post')) {
            $uploadToken = $this->TokenValidation->getToken($this->FileUpload->getTokenName());
            $uploadFiles = $this->FileUpload->get('fileGroups.edit.' . $id . 'files', []);
            $this->RequestFilter->setRebalanceInputs('FileGroups', 'files');

            $fileGroup = $fileGroupsTable->patchEntity($fileGroup, (array)$this->getRequest()->getData());

            if (
                $fileGroupsTable->save(
                    $fileGroup,
                    [
                    'saveOperation' => $this->getRequest()->getAttribute('params'),
                    'upload' => $uploadFiles,
                    ]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'fileGroupsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'FileGroups',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'fileGroupsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $uploadToken = $this->TokenValidation->generate($this->FileUpload->getTokenName());
            $this->FileUpload->delete('fileGroups.edit.' . $id . 'files');
            $uploadFiles = $this->FileUpload->buildDefaultValue(
                $fileGroup->get('files'),
                'fileGroups.edit.' . $id . 'files'
            );
        }

        // ビュー変数
        $this->set([
            'token' => $uploadToken,
            'tokenName' => $this->TokenValidation->getParameterName(),
            'fileGroup' => $fileGroup,
            'valueOptions' => $fileGroupsTable->getFieldValueOptions(),
            'uploadFiles' => $uploadFiles,
        ]);
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

        // ファイルを取得
        $files = $this->fetchTable('Files')->get($id, [
            'finder' => 'delete',
        ]);

        // ファイルグループを取得
        $fileGroup = $this->fetchTable('FileGroups')->get($files->get('file_group_id'), [
            'finder' => 'delete',
        ]);

        //ファイルが1件の場合はグループ毎削除
        if (count($fileGroup->get('files')) > 1) {
            $this->fetchTable('Files')->deleteOrFail($files, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'fileDelete' => true,
            ]);
        } else {
            $this->fetchTable('FileGroups')->deleteOrFail($fileGroup, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
            ]);
        }

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'fileGroupsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'FileGroups',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }
}
