<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\News\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * News Controller
 */
class NewsController extends AdminAppController
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
            'controller' => 'News',
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
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->fetchTable('UserAuthorities');

        // セッション初期化
        if (!$this->SearchInput->shouldKeepCondition()) {
            $this->getRequest()->getSession()->delete('news.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('news.list.search')
        );

        // 会員権限情報
        $userAuthorityLists = $userAuthoritiesTable->getSelectList();
        $searchForm->addFieldValueOptions(['userAuthorityId' => $userAuthorityLists]);

        $searchInputs = $labelsTable->setSearchLabelID($searchInputs);
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('news.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $news = $this->Pagination->paginate($this->fetchTable('News'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('news.list.search', $searchData);

        // ビュー変数
        $this->set([
            'newsList' => $news,
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
        /** @var \App\Model\Table\NewsTable $newsTable */
        $newsTable = $this->fetchTable('News');

        if ($this->getRequest()->is('post')) {
            $news = $newsTable->newEntity($this->getRequest()->getData());

            if ($newsTable->save($news, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::CREATE_SUCCESS), [
                    'key' => 'newsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'News',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'newsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            // エンティティ生成
            $news = $newsTable->newEntity([], [
                'validate' => false,
                'associated' => [
                    'NewsAuthorities' => [
                        'validate' => false,
                    ],
                ],
            ]);
        }

        // ビュー変数
        $this->set([
            'news' => $news,
            'valueOptions' => $newsTable->getFieldValueOptions(),
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
        /** @var \App\Model\Table\NewsTable $newsTable */
        $newsTable = $this->fetchTable('News');

        // エンティティー生成
        $news = $newsTable->get($id, [
            'finder' => 'edit',
        ]);

        if ($this->getRequest()->is('post')) {
            $newsTable->patchEntity($news, (array)$this->getRequest()->getData(), [
                'associated' => [
                    'NewsAuthorities' => [],
                ],
            ]);

            if ($newsTable->save($news, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'newsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'News',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'newsErrors',
                    'element' => 'error',
                ]);
            }
        } else {
            $newsTable->formatDefault($news);
        }

        // ビュー変数
        $this->set([
            'news' => $news,
            'valueOptions' => $newsTable->getFieldValueOptions(),
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

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->fetchTable('Labels');

        // エンティティー生成
        $news = $this->fetchTable('News')->get($id, [
            'finder' => 'delete',
        ]);

        if (!$labelsTable->isAdminUsableLabel($news->get('label_id'))) {
            throw new NotFoundException();
        }

        $this->fetchTable('News')->deleteOrFail($news, [
            'saveOperation' => $this->getRequest()->getAttribute('params'),
        ]);

        // 完了メッセージ
        $this->Flash->set((string)__(Message::DELETE_SUCCESS), [
            'key' => 'newsFinish',
            'element' => 'success',
        ]);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'News',
            'action' => 'list',
            '?' => Configure::read('Setting.searchInput.searchQuery'),
        ]);
    }

    /**
     * Preview method
     *
     * @param int $id ID
     * @return \Cake\Http\Response
     */
    public function preview($id = null)
    {
        /** @var \App\Model\Table\NewsTable $newsTable */
        $newsTable = $this->fetchTable('News');
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->fetchTable('UserAuthorities');

        if ($this->getRequest()->is('post')) {
            $preview['title'] = $this->getRequest()->getData('preview_title');
            $preview['contents'] = $this->getRequest()->getData('preview_contents');
            $preview['public_from'] = $this->getRequest()->getData('preview_public_from');
            $news = $newsTable->newEntity($preview);
        } else {
            // エンティティー生成
            $news = $newsTable->get($id, [
                'finder' => 'detail',
                'isAdmin' => true,
            ]);
        }

        //プレビュー用に権限をセット
        $entity = $userAuthoritiesTable->newEntity([]);
        $this->commonData()->setUserAuthority($entity);
        $news->previewOn();

        // ビュー変数
        $this->set([
            'news' => $news,
            'preview' => true,
            'page' => null,
        ]);

        return $this->render(null, 'user/default');
    }

    /**
     * Setting method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function setting($id = null)
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->fetchTable('SiteSettings');

        $siteSetting = $siteSettingsTable->get($siteSettingsTable->getId($id), [
            'finder' => 'newsSetting',
        ]);

        $siteSetting->setNewsSettingAccess();
        if ($this->getRequest()->is('post')) {
            $siteSettingsTable->patchEntity($siteSetting, (array)$this->getRequest()->getData(), [
                'validate' => 'newsSetting',
            ]);

            if (
                $siteSettingsTable->save($siteSetting, [
                'save' => 'newsSetting',
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::NEWS_SETTING_UPDATE_SUCCESS), [
                    'key' => 'newsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'News',
                    'action' => 'list',
                    '?' => Configure::read('Setting.searchInput.searchQuery'),
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'newsSettingErrors',
                    'element' => 'error',
                ]);
            }
        }

        $this->set('siteSetting', $siteSetting);
        $this->set('valueOptions', $siteSettingsTable->getFieldValueOptions());
    }
}
