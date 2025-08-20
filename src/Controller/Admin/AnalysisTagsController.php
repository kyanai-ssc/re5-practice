<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;
use Cake\Utility\Hash;

/**
 * AnalysisTags Controller
 */
class AnalysisTagsController extends AdminAppController
{
    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        /** @var \App\Model\Table\AnalysisTagsTable $analysisTagsTable */
        $analysisTagsTable = $this->fetchTable('AnalysisTags');

        $analysisTagsTable->checkPlan();
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
            'controller' => 'AnalysisTags',
            'action' => 'edit',
        ], 301);
    }

    /**
     * Edit method
     *
     * @return \Cake\Http\Response|null|void
     */
    public function edit()
    {
        /** @var \App\Model\Table\AnalysisTagsTable $analysisTagsTable */
        $analysisTagsTable = $this->fetchTable('AnalysisTags');

        // エンティティー生成
        $analysisTags = $analysisTagsTable->find('AnalysisTags')->toArray();

        if ($this->getRequest()->is('post')) {
            $inputs = (array)$this->getRequest()->getData();
            $analysisTagsTable->patchEntities($analysisTags, Hash::get($inputs, 'analysisTags', []));

            if (
                $analysisTagsTable->saveMany(
                    $analysisTags,
                    ['saveOperation' => $this->getRequest()->getAttribute('params')]
                )
            ) {
                // 完了メッセージ
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'analysisTagsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'AnalysisTags',
                    'action' => 'edit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'analysisTagsErrors',
                    'element' => 'error',
                ]);
            }
        }
        // ビュー変数
        $this->set([
            'analysisTags' => $analysisTags,
            'valueOptions' => $analysisTagsTable->getFieldValueOptions(),
        ]);
    }
}
