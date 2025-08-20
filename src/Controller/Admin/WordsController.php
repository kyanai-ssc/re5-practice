<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use Cake\Http\Exception\NotFoundException;

/**
 * Words Controller
 */
class WordsController extends AdminAppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect([
            'prefix' => 'Admin',
            'controller' => 'Words',
            'action' => 'wordEdit',
        ], 301);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function wordEdit()
    {
        /** @var \App\Model\Table\WordsTable $wordsTable */
        $wordsTable = $this->fetchTable('Words');

        // エンティティー生成
        $words = $wordsTable->getEntitiesWord();

        if ($this->getRequest()->is('post')) {
            // 入力値取得
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $wordsTable->patchEntities($words, $wordInputs['words']);

            if (
                $wordsTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'saveKey' => 'name',
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'wordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'wordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'wordsErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function errorWordEdit()
    {
        /** @var \App\Model\Table\WordsTable $wordsTable */
        $wordsTable = $this->fetchTable('Words');

        // エンティティー生成
        $words = $wordsTable->getEntitiesError();

        if ($this->getRequest()->is('post')) {
            // 入力値取得
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $wordsTable->patchEntities($words, $wordInputs['words']);

            if (
                $wordsTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'saveKey' => 'name',
                ])
            ) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'errorWordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'errorWordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'errorWordsErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function statusWordEdit()
    {
        /** @var \App\Model\Table\ReservationStatusesTable $reservationStatusesTable */
        $reservationStatusesTable = $this->fetchTable('ReservationStatuses');

        // エンティティー生成
        $words = $reservationStatusesTable->find('wordEdit')->toArray();

        if ($this->getRequest()->is('post')) {
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $reservationStatusesTable->patchEntities(
                $words,
                $reservationStatusesTable->addSortNo($wordInputs['words'])
            );
            $words = $reservationStatusesTable->sortEntities($words);

            if (
                $reservationStatusesTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'saveKey' => 'default_name',
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'statusWordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'statusWordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'statusWordsErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function prefWordEdit()
    {
        /** @var \App\Model\Table\PrefecturesTable $prefecturesTable */
        $prefecturesTable = $this->fetchTable('Prefectures');

        // エンティティー生成
        $words = $prefecturesTable->find('wordEdit')->toArray();

        if ($this->getRequest()->is('post')) {
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $prefecturesTable->patchEntities(
                $words,
                $prefecturesTable->addSortNo($wordInputs['words'])
            );
            $words = $prefecturesTable->sortEntities($words);

            if (
                $prefecturesTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'saveKey' => 'default_name',
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'prefWordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'prefWordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'prefWordsErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }

    /**
     * PaymentMethodWordEdit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function paymentMethodWordEdit()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentMethodsTable $paymentMethodsTable */
        $paymentMethodsTable = $this->fetchTable('PaymentMethods');

        if (!$systemSettingsTable->getData()->usePayment()) {
            throw new NotFoundException();
        }

        // エンティティー生成
        $words = $paymentMethodsTable->find('wordEdit')->toArray();

        if ($this->getRequest()->is('post')) {
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $paymentMethodsTable->patchEntities(
                $words,
                $paymentMethodsTable->addSortNo($wordInputs['words'])
            );
            $words = $paymentMethodsTable->sortEntities($words);

            if (
                $paymentMethodsTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'paymentMethodWordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'paymentMethodWordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'paymentMethodWordsErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }

    /**
     * paymentStatusWordEdit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function paymentStatusWordEdit()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->fetchTable('SystemSettings');
        /** @var \App\Model\Table\PaymentStatusesTable $paymentStatusesTable */
        $paymentStatusesTable = $this->fetchTable('PaymentStatuses');

        if (!$systemSettingsTable->getData()->usePayment()) {
            throw new NotFoundException();
        }

        // エンティティー生成
        $words = $paymentStatusesTable->find('wordEdit')->toArray();

        if ($this->getRequest()->is('post')) {
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $paymentStatusesTable->patchEntities(
                $words,
                $paymentStatusesTable->addSortNo($wordInputs['words'])
            );
            $words = $paymentStatusesTable->sortEntities($words);

            if (
                $paymentStatusesTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'paymentStatusWordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'paymentStatusWordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'paymentStatusWordsFinishErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function receptionStatusWordEdit()
    {
        /** @var \App\Model\Table\ReceptionStatusesTable $receptionStatusesTable */
        $receptionStatusesTable = $this->fetchTable('ReceptionStatuses');

        // エンティティー生成
        $words = $receptionStatusesTable->find('wordEdit')->toArray();

        if ($this->getRequest()->is('post')) {
            $wordInputs = (array)$this->getRequest()->getData();
            $words = $receptionStatusesTable->patchEntities(
                $words,
                $receptionStatusesTable->addSortNo($wordInputs['words'])
            );
            $words = $receptionStatusesTable->sortEntities($words);

            if (
                $receptionStatusesTable->saveMany($words, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                'saveKey' => 'default_name',
                ])
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'statusWordsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'Words',
                    'action' => 'receptionStatusWordEdit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'statusWordsErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'words' => $words,
        ]);
    }
}
