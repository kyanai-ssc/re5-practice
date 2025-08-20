<?php
declare(strict_types=1);

namespace App\Form\Admin\ReceptionStatuses;

use App\Locale\Message;
use App\Utility\CommonData\CommonDataTrait;

/**
 * 予約検索
 */
trait SearchFormTrait
{
    use CommonDataTrait;

    /**
     * 予約検索用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema スキーマ
     */
    protected function buildReservationSearchSchema($schema)
    {
        $schema
            ->addField('qr_code', 'string')
            ->addField('label_id', 'string');

        return $schema;
    }

    /**
     * 予約検索用のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return \Cake\Validation\Validator バリデータ
     */
    protected function buildReceptionStatusesSearchValidator($validator, $options = [])
    {
        $validator
            ->requirePresence('qr_code', false, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('qr_code');

        $validator
            ->requirePresence('label_id', false, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('label_id');

        /** @var \App\Model\Table\LabelsTable $labelTable */
        $labelTable = $this->getTableLocator()->get('Labels');
        $validator = $labelTable->addValidateLabelId($validator);

        return $validator;
    }

    /**
     * 予約検索用のデフォルト値を生成
     *
     * @param string $pageType 仕様ぺージ
     * @return array デフォルト値
     */
    protected function buildReservationSearchDefaultFieldValues($pageType = 'receptionStatuses')
    {
        $defaultValues = [
            'sort' => 'Reservations.id',
        ];

        $schema = $this->getSchema();
        if ($pageType === 'receptionStatuses') {
            if ($schema->field('label_id')) {
                $defaultValues['label_id'] = $this->commonData()->getAdminLoginLabel();
            }
        }

        return $defaultValues;
    }
}
