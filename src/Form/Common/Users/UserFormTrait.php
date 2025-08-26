<?php
declare(strict_types=1);

namespace App\Form\Common\Users;

use App\Model\Entity\FormItem;
use App\Model\Entity\User;
use App\Model\InputType\Item\Type\InputInterface;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;

/**
 * UserForm trait.
 */
trait UserFormTrait
{
    /**
     * @var array
     */
    protected $userParameter = null;

    /**
     * @var array|null
     */
    protected $userParameterErrors = null;

    /**
     * @var \App\Model\Entity\User|null
     */
    protected $userEntity = null;

    /**
     * @var array|null
     */
    protected $userFormGroups = null;

    /**
     * 会員のパラメータを取得
     *
     * @param string|null $key キー
     * @return mixed パラメータ
     */
    public function getUserParameter(?string $key = null)
    {
        if (!isset($key)) {
            return $this->userParameter;
        }

        return Hash::get($this->userParameter, $key);
    }

    /**
     * 会員のパラメータを設定
     *
     * @param array|string|null $userParameter パラメータ
     * @return void
     */
    public function setUserParameter($userParameter)
    {
        if (is_array($userParameter)) {
            $this->userParameter = $userParameter;
        } else {
            $this->userParameter = [];
        }
    }

    /**
     * 会員のパラメータを検証
     *
     * @return bool 検証結果
     */
    public function validateUserParameter()
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $parametersData = $usersTable->getUserParametersData($this->getUserParameter(), $this->userEntity);
        if (isset($parametersData['errors'])) {
            $this->setErrors($parametersData['errors']);

            return false;
        }

        $this->setUserParameter($parametersData['parameters']);
        if (isset($parametersData['displayErrors'])) {
            $this->userParameterErrors = $parametersData['displayErrors'];
        }

        return true;
    }

    /**
     * 会員のエンティティーを初期化
     *
     * @param  array|string|null $data データ
     * @return void
     */
    public function initializeUserEntity($data = null)
    {
        $this->createUserEntity((array)$data, [
            'validate' => false,
        ]);
    }

    /**
     * 会員のエンティティーを取得
     *
     * @return \App\Model\Entity\User エンティティー
     */
    public function getUserEntity()
    {
        if (!isset($this->userEntity)) {
            throw new CakeException();
        }

        return $this->userEntity;
    }

    /**
     * 会員のエンティティーを設定
     *
     * @param \App\Model\Entity\User $userEntity エンティティー
     * @return void
     */
    public function setUserEntity(User $userEntity)
    {
        $this->userEntity = $userEntity;
    }

    /**
     * 会員のフォームグループを取得
     *
     * @return array フォームグループ
     */
    public function getUserFormGroups()
    {
        if (!isset($this->userFormGroups)) {
            if (!empty($this->getErrors())) {
                return [];
            }

            /** @var \App\Model\Table\UsersTable $usersTable */
            $usersTable = $this->getTableLocator()->get('Users');

            $userId = null;
            if (isset($this->userEntity)) {
                $userId = $this->userEntity->get('id');
            }
            $this->userFormGroups = $usersTable->getUserFormGroups((int)$this->getUserParameter('user_authority_id'), [
                'userId' => $userId,
                'isConfirm' => $this->isConfirm(),
            ]);
        }

        return $this->userFormGroups;
    }

    /**
     * アプリケーションルールのエラーを取得
     *
     * @return string|null
     */
    public function getRulesError()
    {
        if (isset($this->userEntity)) {
            $userErrors = $this->userEntity->getErrors();
            if (isset($userErrors['user_error'])) {
                return reset($userErrors['user_error']);
            }
        }

        return null;
    }

    /**
     * 会員フォーム用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema
     */
    protected function buildUserSchema(Schema $schema)
    {
        foreach ($this->getUserFormGroups() as $formGroup) {
            foreach ((array)$formGroup->get('form_items') as $formItem) {
                /** @var \App\Model\InputType\AbstractInputTypeItem $inputTypeItem */
                $inputTypeItem = $formItem->getInputTypeItem();

                if ($inputTypeItem instanceof InputInterface && $inputTypeItem->canInput()) {
                    foreach ((array)$inputTypeItem->getFieldsetInputKey() as $fieldsetInputKey) {
                        if ($formItem->get('input_type') === FormItem::INPUT_TYPE_EXPIRATION_DATE) {
                            $schema->addField($fieldsetInputKey . '_from', 'string');
                            $schema->addField($fieldsetInputKey . '_to', 'string');
                        } else {
                            $schema->addField($fieldsetInputKey, 'string');
                        }
                    }
                }
            }
        }

        return $schema;
    }

    /**
     * 会員フォーム用の値リストを生成
     *
     * @return array
     */
    protected function buildUserFieldValueOptions()
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $fieldValueOptions = $usersTable->getFieldValueOptions();

        return $fieldValueOptions;
    }

    /**
     * 会員の入力値をフィルタリング
     *
     * @param array $inputs 入力値
     * @return array
     */
    protected function filterUserInputs($inputs)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $data = $inputs;
        if (isset($data['users']) && is_array($data['users'])) {
            $data['users'] = $usersTable->filterFormItemInputs($data['users'], $this->getUserFormGroups());
        }

        return $data;
    }

    /**
     * 会員のバリデーション
     *
     * @param array $data データ
     * @return bool
     */
    protected function validateUser(array $data)
    {
        $data = $this->filterUserInputs($data);
        $this->setData($data);

        $options = [];
        if (!$this->isConfirm()) {
            $options['checkRules'] = true;
        }

        $this->createUserEntity($data, $options);
        if (!empty($this->userParameterErrors) && isset($this->userEntity)) {
            $this->userEntity->setErrors(
                Hash::merge($this->userEntity->getErrors(), Hash::get($this->userParameterErrors, 'users'))
            );
            $this->setErrors(Hash::merge($this->getErrors(), $this->userParameterErrors));
        }
        if (!empty($this->getErrors())) {
            return false;
        }

        return true;
    }

    /**
     * 会員のエンティティを生成
     *
     * @param array $data データ
     * @param array|null $options オプション
     * @return void
     */
    protected function createUserEntity($data, $options = null)
    {
        /** @var \App\Model\Table\UsersTable $usersTable */
        $usersTable = $this->getTableLocator()->get('Users');

        $userInputs = [];
        if (isset($data['users']) && is_array($data['users'])) {
            $userInputs = $data['users'];
        }

        if (
            !isset($userInputs['addition_values'][Configure::read(
                'Setting.formItemAdditionValues.repeatReservationFlg'
            )])
        ) {
            $userInputs['addition_values'][Configure::read(
                'Setting.formItemAdditionValues.repeatReservationFlg'
            )] = Configure::read('Master.common.flg.off');
        }

        $entityOptions = array_merge([
            'associated' => [],
            'otherOptions' => [
                'formGroups' => $this->getUserFormGroups(),
                'parameters' => $this->getUserParameter(),
                'isAdmin' => $this->isAdmin(),
                'isConfirm' => $this->isConfirm(),
                'password' => Hash::get($userInputs, 'password'),
            ],
        ], (array)$options);

        if (!isset($this->userEntity)) {
            $this->setUserEntity($usersTable->newEntity($userInputs, $entityOptions));
        } else {
            $usersTable->patchEntity($this->userEntity, $userInputs, $entityOptions);
        }

        $entityErrors = $this->getUserEntity()->getErrors();
        if (empty($entityErrors)) {
            $userData = $this->getUserEntity()->toArray();
            unset($userData['user_additions']);
            $this->setData(array_merge($this->getData(), ['users' => $userData]));
        } else {
            $this->setErrors(Hash::merge($this->getErrors(), ['users' => $entityErrors]));
        }
    }
}
