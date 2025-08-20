<?php
declare(strict_types=1);

namespace App\Form\Common\Users;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Form\ConfirmTransitionTrait;
use Cake\Form\Schema;

/**
 * 会員フォーム
 */
abstract class UserForm extends AppForm
{
    use CommonFormTrait;
    use ConfirmTransitionTrait;
    use UserFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildUserSchema($schema);

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        if (!isset($data['users']) || !is_array($data['users'])) {
            $data['users'] = [];
        }
        $data['users'] += $this->getUserParameter();

        $result = parent::validate($data);
        if (!$this->validateUser($this->getData())) {
            $result = false;
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = $this->buildUserFieldValueOptions();

        return $fieldValueOptions;
    }
}
