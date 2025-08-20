<?php
declare(strict_types=1);

namespace App\Form\User\User;

use App\Locale\Message;
use App\Model\Entity\Term;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * Terms trait
 */
trait TermsTrait
{
    /**
     * 利用規約(会員)を取得
     *
     * @return string|null
     */
    public function getUserTerms()
    {
        /** @var \App\Model\Table\TermsTable $termsTable */
        $termsTable = $this->getTableLocator()->get('Terms');

        /** @var string|null $terms */
        $terms = $termsTable->getData(Term::TYPE_TERMS_USER);

        return $terms;
    }

    /**
     * 利用規約(会員)のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema
     */
    protected function buildUserTermsSchema(Schema $schema)
    {
        $schema
            ->addField('user_terms', 'string');

        return $schema;
    }

    /**
     * 利用規約(会員)のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return void
     */
    public function buildUserTermsValidator(Validator $validator)
    {
        $validator
            ->requirePresence('user_terms', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('user_terms', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('user_terms', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [Configure::readOrFail('Master.common.flg.on')]],
                    'last' => true,
                    'message' => __(Message::ERROR_USER_TERMS),
                ],
            ]);
    }
}
