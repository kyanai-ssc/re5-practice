<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use App\Utility\SmartLock\Akerun;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * SmartLocks Controller
 */
class SmartLocksController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        // アケルン連携関連の処理は未ログインでも接続可能 かつ 設定関連のチェックも行わない
        $callbackMethods = ['akerunCallback', 'akerunCallbackFinish'];
        $this->Authentication->addUnauthenticatedActions($callbackMethods);
        if (!in_array($this->getRequest()->getParam('action'), $callbackMethods)) {
            $smartLock = new SmartLockLinkage();
            if (!$smartLock->useAkerun()) {
                throw new NotFoundException();
            }
        }

        return $response;
    }

    /**
     * アケルン連携
     *
     * @return \Cake\Http\Response|null
     * @throws \Exception
     */
    public function akerunCallback(): ?\Cake\Http\Response
    {
        $code = $this->request->getQuery('code');
        $akerun = new Akerun();
        $akerun->saveAccessToken($code);

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'SmartLocks',
            'action' => 'akerunCallbackFinish',
        ]);
    }

    /**
     * アケルン連携完了画面
     *
     * @return void
     */
    public function akerunCallbackFinish(): void
    {
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
            'controller' => 'SmartLocks',
            'action' => 'akerun',
        ], 301);
    }

    /**
     * akerun method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function akerun()
    {
        /** @var \App\Model\Table\SmartLocksTable $smartLocksTable */
        $smartLocksTable = $this->fetchTable('SmartLocks');

        // エンティティー生成
        $akerun = $smartLocksTable->getData();

        if (!$akerun instanceof EntityInterface) {
            throw new CakeException();
        }

        if ($this->getRequest()->is('post')) {
            $akerun = $smartLocksTable->patchEntity($akerun, (array)$this->getRequest()->getData());

            if ($smartLocksTable->save($akerun, ['saveOperation' => $this->getRequest()->getAttribute('params')])) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'akerunFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'SmartLocks',
                    'action' => 'akerun',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'akerunErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'akerun' => $akerun,
            'valueOptions' => $smartLocksTable->getFieldValueOptions(),
        ]);
    }
}
