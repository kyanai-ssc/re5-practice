<?php
declare(strict_types=1);

namespace App\Controller\Admin\Ajax;

use App\Controller\AdminAppController;
use App\Controller\Traits\AjaxTrait;
use App\Form\Admin\BounceMails\SearchForm;
use Cake\Event\EventInterface;

/**
 * BounceMails Controller
 */
class BounceMailsController extends AdminAppController
{
    use AjaxTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Ajax');
    }

    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'saveCheck',
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

        $checked = $this->getRequest()->getSession()->read('bounceMails.list.checked');

        // 入力値取得
        $searchForm = new SearchForm();

        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('bounceMails.list.search')
        );

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $saveData = $this->SearchInput->getCheckedIds($checked);

            // セッション保持
            $this->getRequest()->getSession()->write('bounceMails.list.checked', $saveData);

            $checkResult = true;
        }

        $this->set('checkResult', $checkResult);
    }
}
