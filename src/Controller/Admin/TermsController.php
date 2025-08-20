<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;

/**
 * Terms Controller
 */
class TermsController extends AdminAppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        return $this->redirect(
            [
                'prefix' => 'Admin',
                'controller' => 'Terms',
                'action' => 'edit',
            ],
            301
        );
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\TermsTable $termsTable */
        $termsTable = $this->fetchTable('Terms');

        // エンティティー生成
        $terms = $termsTable->find('terms')->toArray();

        if ($this->getRequest()->is('post')) {
            // 入力値取得
            $termInputs = (array)$this->getRequest()->getData();
            $terms = $termsTable->patchEntities($terms, $termInputs['terms']);

            if (
                $termsTable->saveMany(
                    $terms,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set(
                    (string)__(Message::UPDATE_SUCCESS),
                    [
                        'key' => 'termsFinish',
                        'element' => 'success',
                    ]
                );

                return $this->redirect(
                    [
                        'prefix' => 'Admin',
                        'controller' => 'Terms',
                        'action' => 'edit',
                    ]
                );
            } else {
                $this->Flash->set(
                    (string)__(Message::INVALID_INPUT),
                    [
                        'key' => 'termsErrors',
                        'element' => 'error',
                    ]
                );
            }
        }

        // ビュー変数
        $this->set(
            [
                'terms' => $terms,
                'valueOptions' => $termsTable->getFieldValueOptions(),
            ]
        );
    }
}
