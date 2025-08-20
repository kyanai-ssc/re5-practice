<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Form\Admin\UploadFormTrait;
use App\Form\AppForm;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * アップロードフォーム
 */
class UploadForm extends AppForm
{
    use UploadFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildUploadSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = $this->buildUploadFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $this->buildUploadValidator($validator);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = parent::validate($data);
        if (!$this->checkRunningImport($this->getTableLocator()->get('Users'))) {
            $result = false;
        }

        return $result;
    }
}
