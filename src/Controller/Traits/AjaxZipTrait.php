<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use Cake\Http\Exception\BadRequestException;
use Cake\Utility\Hash;

/**
 * AjaxZip trait.
 */
trait AjaxZipTrait
{
    /**
     * Search action
     *
     * @param \App\Form\Common\Zip\SearchForm $searchForm 会員フォーム
     * @return void
     */
    protected function searchAction($searchForm)
    {
        $this->getRequest()->allowMethod('post');

        $searchInputs = $this->getRequest()->getData();
        if (!$searchForm->execute((array)$searchInputs)) {
            throw new BadRequestException($searchForm->getErrorText());
        }

        $result = $searchForm->searchAddress(Hash::get($searchForm->getData(), 'zip'));
        if (isset($result['error'])) {
            throw new BadRequestException($result['error']);
        }

        $this->set([
            'result' => $result,
        ]);
    }
}
