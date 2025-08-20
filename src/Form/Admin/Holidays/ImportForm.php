<?php
declare(strict_types=1);

namespace App\Form\Admin\Holidays;

use App\Form\Admin\ImportFormInterface;
use App\Form\Admin\ImportFormTrait;
use App\Form\AppForm;
use App\Locale\Message;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * インポートフォーム
 */
class ImportForm extends AppForm implements ImportFormInterface
{
    use ImportFormTrait;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        return $schema->addField('date', 'string');
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('date', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyDate('date', __(Message::ERROR_NOT_EMPTY), false);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function createCsvHeader(): array
    {
        $csvHeader[] = Configure::readOrFail('Setting.csv.download.holiday.header.date');

        return $csvHeader;
    }

    /**
     * @inheritDoc
     */
    protected function formatCsvData(array $data): array
    {
        $result['date'] = array_shift($data);

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function createEntity(array $data): ?EntityInterface
    {
        $holidaysTable = $this->getTableLocator()->get('Holidays');
        $entity = $holidaysTable->newEntity($data);

        // CSVアップロードでのみ必須チェックが必要なため，別途実施
        if (!$this->execute($data)) {
            $entity->setErrors($this->getErrors());
        }

        if (CustomValidation::date($data['date'], 'ymd')) {
            if ($holidaysTable->exists(['date' => $data['date']])) {
                $entity->setError('date', __(Message::ERROR_ALREADY_SAVE));
            }
        }

        return $entity;
    }

    /**
     * @inheritDoc
     */
    protected function formatErrors(array $errors): array
    {
        $message = [];
        foreach ($errors as $value) {
            $subject = Hash::get((array)$this->csvHeader, '0');
            $body = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $value);
            $message[] = $subject . Configure::readOrFail('Setting.csv.import.error.separator') . $body;
        }

        return $message;
    }
}
