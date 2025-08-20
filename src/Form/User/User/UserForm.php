<?php
declare(strict_types=1);

namespace App\Form\User\User;

use App\Form\Common\Users\UserForm as CommonUserForm;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * 会員フォーム
 */
class UserForm extends CommonUserForm
{
    use TermsTrait;

    /**
     * @var bool
     */
    protected $adminFlg = false;

    /**
     * @var array|null
     */
    protected $optinData = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = parent::_buildSchema($schema);
        if ($this->requiredUserTerms()) {
            $schema = $this->buildUserTermsSchema($schema);
        }

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        parent::validationDefault($validator);

        if ($this->requiredUserTerms()) {
            $this->buildUserTermsValidator($validator);
        }

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function createUserEntity($data, $options = null)
    {
        if (isset($this->optinData)) {
            $data = Hash::merge($data, $this->optinData);
        }

        parent::createUserEntity($data, $options);
    }

    /**
     * オプトインデータを設定
     *
     * @param array $optinData オプトインデータ
     * @return void
     */
    public function setOptinData(array $optinData)
    {
        $this->optinData = $optinData;
    }

    /**
     * 利用規約(会員)の表示を判定
     *
     * @return bool
     */
    public function requiredUserTerms()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        if (
            !$siteSettingsTable->getData()->isUseFlgOn('user_terms_flg')
            || (isset($this->userEntity) && !$this->userEntity->isNew())
        ) {
            return false;
        }

        if ($this->isConfirm()) {
            return false;
        }

        return true;
    }
}
