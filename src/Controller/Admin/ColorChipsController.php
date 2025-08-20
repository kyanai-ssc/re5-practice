<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AdminAppController;
use App\Locale\Message;

/**
 * ColorChips Controller
 */
class ColorChipsController extends AdminAppController
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
            'controller' => 'ColorChips',
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
        /** @var \App\Model\Table\ColorChipsTable $colorChipsTable */
        $colorChipsTable = $this->fetchTable('ColorChips');

        // エンティティー生成
        $colorChips = $colorChipsTable->find('colorChip')->toArray();

        if ($this->getRequest()->is('post')) {
            // 入力値取得
            $this->RequestFilter->setRebalanceInputs('ColorChips', 'colorChips');
            $colorChipInputs = (array)$this->getRequest()->getData();

            $colorChips = $colorChipsTable->patchEntities(
                $colorChips,
                $colorChipsTable->addSortNo($colorChipInputs['colorChips'])
            );

            $colorChips = $colorChipsTable->sortEntities($colorChips, 'sort_no', true);

            if (
                $colorChipsTable->saveMany($colorChips, [
                'saveOperation' => $this->getRequest()->getAttribute('params'),
                ])
            ) {
                $this->Flash->set((string)__(Message::UPDATE_SUCCESS), [
                    'key' => 'colorChipsFinish',
                    'element' => 'success',
                ]);

                return $this->redirect([
                    'prefix' => 'Admin',
                    'controller' => 'ColorChips',
                    'action' => 'edit',
                ]);
            } else {
                $this->Flash->set((string)__(Message::INVALID_INPUT), [
                    'key' => 'colorChipsErrors',
                    'element' => 'error',
                ]);
            }
        }

        // ビュー変数
        $this->set([
            'colorChips' => $colorChips,
            'valueOptions' => $colorChipsTable->getFieldValueOptions(),
        ]);
    }
}
