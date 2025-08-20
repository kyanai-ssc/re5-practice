<?php
declare(strict_types=1);

namespace App\Form\User\Reservations;

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
     * 利用規約(予約)を取得
     *
     * @return string|null
     */
    public function getReservationTerms()
    {
        /** @var \App\Model\Table\TermsTable $termsTable */
        $termsTable = $this->getTableLocator()->get('Terms');

        /** @var string|null $terms */
        $terms = $termsTable->getData(Term::TYPE_TERMS_RESERVE);

        return $terms;
    }

    /**
     * 特定商取引法(予約)を取得
     *
     * @return string|null
     */
    public function getReservationSctl()
    {
        /** @var \App\Model\Table\TermsTable $termsTable */
        $termsTable = $this->getTableLocator()->get('Terms');

        /** @var string|null $sctl */
        $sctl = $termsTable->getData(Term::TYPE_SCTL_RESERVE);

        return $sctl;
    }

    /**
     * 特定商取引法(予約)の表示を判定
     *
     * @return bool
     */
    public function requiredReservationSctl()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        return $siteSettingsTable->getData()->isUseFlgOn('reservation_sctl_flg');
    }

    /**
     * 利用規約(予約)のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema
     */
    protected function buildReservationTermsSchema(Schema $schema)
    {
        $schema
            ->addField('reservation_terms', 'string');

        return $schema;
    }

    /**
     * 利用規約(予約)のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return void
     */
    public function buildReservationTermsValidator(Validator $validator)
    {
        $validator
            ->requirePresence('reservation_terms', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('reservation_terms', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('reservation_terms', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', [Configure::readOrFail('Master.common.flg.on')]],
                    'last' => true,
                    'message' => __(Message::ERROR_RESERVATION_TERMS),
                ],
            ]);
    }
}
