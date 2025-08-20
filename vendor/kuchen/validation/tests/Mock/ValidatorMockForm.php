<?php

declare(strict_types=1);

namespace Kuchen\Validation\Test\Mock;

use Cake\Form\Schema;
use Kuchen\Validation\Form\Form;
use Kuchen\Validation\Validation\Validator;

/**
 * Class MockForm
 */
class ValidatorMockForm extends Form
{
    /**
     * @var Validator
     */
    public $validated;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema // phpcs:ignore
    {
        $schema
            ->addField('field1', [])
            ->addField('field2', []);

        return parent::_buildSchema($schema);
    }

    /**
     * @inheritDoc
     */
    protected function createValidator($name): \Cake\Validation\Validator
    {
        $validator = parent::createValidator($name);

        $validator
            ->add('field1', [
                'maxLength' => [
                    'rule' => ['maxLength', 1],
                ],
            ])
            ->notEmptyString('field2');

        return $validator;
    }
}
