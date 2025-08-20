<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Form\Admin\PaymentErrors\SearchForm;
use App\Locale\Message;
use App\Utility\ArrayUtility;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * PaymentErrors Controller
 */
class PaymentErrorsController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function beforeFilter(EventInterface $event)
    {
        $response = parent::beforeFilter($event);

        $this->FormProtection->setConfig('unlockedActions', [
            'unlock',
        ]);

        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');

        if (!$systemSettingsTable->getData()->usePayment()) {
            throw new NotFoundException();
        }

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
            'controller' => 'PaymentErrors',
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
            $this->getRequest()->getSession()->delete('paymentErrors.list.search');
        }

        // 入力値取得
        $searchForm = new SearchForm();
        $searchInputs = $this->SearchInput->getCondition(
            $searchForm->getDefaultFieldValues(),
            $this->getRequest()->getSession()->read('paymentErrors.list.search')
        );
        $this->setRequestData($searchInputs);

        // 入力チェック
        if ($searchForm->execute($searchInputs)) {
            $searchData = $searchForm->getData();
        } else {
            $searchData = $this->SearchInput->getFallbackCondition(
                $searchForm->getDefaultFieldValues(),
                $this->getRequest()->getSession()->read('paymentErrors.list.search')
            );
        }
        $this->setRequestQuery($this->SearchInput->getPaginatorQuery($searchData));

        // データ取得
        $paymentErrors = $this->Pagination->paginate($this->fetchTable('PaymentErrors'), [
            'finder' => [
                'searchList' => [
                    'inputs' => $searchData,
                ],
            ],
            'maxLimit' => ArrayUtility::arrayMax(array_keys($searchForm->getFieldValueOptions('limit'))),
        ]);

        // セッション保持
        $this->getRequest()->getSession()->write('paymentErrors.list.search', $searchData);

        // ビュー変数
        $this->set([
            'paymentErrors' => $paymentErrors,
            'searchForm' => $searchForm,
            'valueOptions' => $searchForm->getFieldValueOptions(),
        ]);
    }

    /**
     * unlock method
     *
     * @param int $id ID
     * @return \Cake\Http\Response|null|void
     */
    public function unlock($id = null)
    {
        /** @var \App\Model\Table\PaymentErrorsTable $paymentErrorsTable */
        $paymentErrorsTable = $this->fetchTable('PaymentErrors');

        $this->getRequest()->allowMethod('post');

        $paymentError = $paymentErrorsTable->get($id, [
            'finder' => 'all',
        ]);

        if (!$paymentError->isLock()) {
            throw new NotFoundException();
        }

        if ($this->getRequest()->is('post')) {
            $paymentError->unlockPayment();

            if (
                $paymentErrorsTable->save(
                    $paymentError,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'paymentErrorsFinish',
                    'element' => 'success',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'paymentErrorsErrors',
                    'element' => 'error',
                ]);
            }
        }

        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'PaymentErrors',
            'action' => 'list',
        ]);
    }
}
