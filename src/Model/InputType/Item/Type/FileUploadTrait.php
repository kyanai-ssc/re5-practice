<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * FileUpload trait.
 */
trait FileUploadTrait
{
    /**
     * @var array
     */
    protected $fileSession = [];

    /**
     * @var \App\Model\Entity\FormItem
     */
    protected $formItem;

    /**
     * @inheritDoc
     */
    public function setFileSession(?array $fileSession): void
    {
        $formItemId = '';
        if ($this->formItem !== null) {
            $formItemId = $this->formItem->get('id');
        }
        if ($formItemId && isset($fileSession[$formItemId])) {
            $this->fileSession[$formItemId] = $fileSession[$formItemId];
        }
    }
}
