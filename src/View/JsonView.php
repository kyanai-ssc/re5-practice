<?php
declare(strict_types=1);

namespace App\View;

use Cake\View\JsonView as CakeJsonView;

/**
 * Json View
 */
class JsonView extends CakeJsonView
{
    use ViewTrait;

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadCommonViewHelper();
    }
}
