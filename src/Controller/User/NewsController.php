<?php
declare(strict_types=1);

namespace App\Controller\User;

use App\Controller\UserAppController;
use App\Form\User\News\SearchForm;
use App\Utility\ArrayUtility;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

/**
 * Index Controller
 */
class NewsController extends UserAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        $this->isLoginRequire([
            'list',
            'detail',
        ]);
    }

    /**
     * list method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function list()
    {
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
        $newsList = $this->Pagination->paginate($this->fetchTable('News'), [
            'finder' => [
                'publicList' => [
                    'inputs' => $searchData,
                ],
            ],
            'limit' => $searchForm::PAGE_LIMIT,
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('news.list.search', $searchData);

        // ビュー変数
        $this->enableLoginRedirectBack();
        $this->set([
            'newsList' => $newsList,
            'searchForm' => $searchForm,
        ]);
    }

    /**
     * list method
     *
     * @param int $id id
     * @return \Cake\Http\Response|null|void
     */
    public function detail($id = null)
    {
        // エンティティー生成
        $news = $this->fetchTable('News')->get($id, [
            'finder' => 'detail',
        ]);

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('news.list.search')
        );

        $page = Hash::get($searchInputs, 'page');

        // ビュー変数
        $this->enableLoginRedirectBack();
        $this->set([
            'news' => $news,
            'page' => $page,
        ]);
    }
}
