<?php

declare(strict_types=1);

namespace Kuchen\Validation\Test\Mock;

use Cake\Form\Schema;
use Kuchen\Validation\Form\Form;

/**
 * Class MockForm
 */
class MockForm extends Form
{
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
    public function createValidator($name): \Cake\Validation\Validator
    {
        $validator = parent::createValidator($name);

        // CakePHP 3.6.0: Validator が空だと deprecated
        $validator
            ->add('field1', [])
            ->add('field2', []);

        return $validator;
    }
}
