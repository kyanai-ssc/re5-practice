<?php
declare(strict_types=1);

namespace App\View;

use Cake\Core\Configure;

/**
 * View trait.
 */
trait ViewTrait
{
    /**
     * 共通のビューヘルパーをロード
     *
     * @return void
     */
    protected function loadCommonViewHelper()
    {
        $this->addHelper('Form', [
            'errorClass' => 'warning',
            'templates' => 'form-templates',
            'autoSetCustomValidity' => false,
        ]);
        $this->addHelper('Paginator', [
            'className' => 'CustomPaginator',
            'options' => [
                'url' => Configure::readOrFail('Setting.pagination.url'),
            ],
            'templates' => 'paginator-templates',
        ]);

        $this->addHelper('Breadcrumbs', [
            'templates' => 'breadcrumbs-templates',
        ]);

        $this->addHelper('Template', ['prefix' => $this->getRequest()->getParam('prefix')]);

        $this->addHelper('Token');
        $this->addHelper('Tr');
    }
}
