<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Form\Admin\ImportFormInterface;
use App\Form\Admin\ImportFormTrait;
use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\FormGroup;
use App\Model\Table\FormItemsTable;
use App\Utility\SmartLock\SmartLockLinkage;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Utility\Hash;

/**
 * インポートフォーム
 */
class ImportForm extends AppForm implements ImportFormInterface
{
    use ImportFormTrait;

    /**
     * @var array
     */
    protected $headerColumns = null;

    /**
     * @var array
     */
    protected $csvItems = null;

    /**
     * @inheritDoc
     */
    protected function initialize()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $columns = Configure::readOrFail('Setting.csv.import.user.header');
        // スマートロック設定（アケルン）を利用しない場合はAkerunユーザーIDを削除
        $smartLock = new SmartLockLinkage();
        if (!$smartLock->useAkerun()) {
            unset($columns[FormItemsTable::CSV_COLUMN_AKERUN_USER_ID]);
        }

        $this->headerColumns = $columns;
        $this->csvItems = $formItemsTable->generateCsvItems(
            'input',
            array_keys($this->headerColumns),
            FormGroup::FORM_TYPE_USER
        );
    }

    /**
     * @inheritDoc
     */
    protected function createCsvHeader(): array
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $csvHeader = $formItemsTable->generateCsvHeader($this->csvItems, $this->headerColumns);

        return $csvHeader;
    }

    /**
     * @inheritDoc
     */
    protected function formatCsvData(array $data): array
    {
        $result = [];
        $columns = Configure::readOrFail('Setting.csv.import.user.column');
        foreach ($this->csvItems as $key => $csvItem) {
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $result = Hash::merge($result, $this->formatItemData($data, $key, $item, $columns));
                }
            } else {
                $result = Hash::merge($result, $this->formatItemData($data, $key, $csvItem, $columns));
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function createEntity(array $data): ?EntityInterface
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $userForm = new UserForm();
        if (Hash::get($data, 'users.id', '') !== '') {
            if (!$usersTable->validatePrimaryKey($data['users']['id'])) {
                $this->setErrors([
                    'id' => [
                        Message::ERROR_NUMBER => __(Message::ERROR_NUMBER),
                    ],
                ]);

                return null;
            }

            $exists = true;
            try {
                $userForm->setUserEntity($usersTable->get($data['users']['id'], [
                    'finder' => 'edit',
                ]));
            } catch (RecordNotFoundException $e) {
                $exists = false;
            }
            if (!$exists || !$userForm->getUserEntity()->canEdit()) {
                $this->setErrors([
                    'id' => [
                        Message::ERROR_NOT_EXISTS => __(Message::ERROR_NOT_EXISTS),
                    ],
                ]);

                return null;
            }
        }
        $userForm->setUserParameter([
            'user_authority_id' => $data['users']['user_authority_id'],
        ]);
        if (!$userForm->validateUserParameter()) {
            $errors = $userForm->getErrors();
            if (isset($errors['users'])) {
                $this->setErrors($errors['users']);
            }

            return null;
        }
        $userForm->execute($data);

        $this->isInvalidData = $this->isInvalidDataForFormItem([$userForm->getUserFormGroups()]);

        return $userForm->getUserEntity();
    }

    /**
     * @inheritDoc
     */
    protected function formatErrors(array $errors): array
    {
        $result = [];
        $columns = Configure::readOrFail('Setting.csv.import.user.column');
        foreach ($this->csvItems as $key => $csvItem) {
            if (is_array($csvItem)) {
                foreach ($csvItem as $item) {
                    $result = Hash::merge($result, $this->formatItemErrors($errors, $key, $item, $columns));
                }
            } else {
                $result = Hash::merge($result, $this->formatItemErrors($errors, $key, $csvItem, $columns));
            }
        }

        if (isset($errors['user_error'])) {
            $result = Hash::merge($result, $errors['user_error']);
        }

        return $result;
    }
}
