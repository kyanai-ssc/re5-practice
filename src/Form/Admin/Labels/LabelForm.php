<?php
declare(strict_types=1);

namespace App\Form\Admin\Labels;

use App\Form\AppForm;
use App\Form\Common\Labels\LabelFormTrait;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * カテゴリーフォーム
 */
class LabelForm extends AppForm
{
    use LabelFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildLabelSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $this->buildLabelValidator($validator);

        return $validator;
    }
}
