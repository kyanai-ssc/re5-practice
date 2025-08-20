<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Form\Admin\DeleteManyFormTrait;
use App\Form\AppForm;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 一括削除フォーム
 */
class DeleteManyForm extends AppForm
{
    use DeleteManyFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildDeleteManySchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildDeleteManyValidator($validator);

        return $validator;
    }
}
