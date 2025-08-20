<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Locale\Message;
use App\Model\InputType\Item\Type\CalendarOutputInterface;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * CalendarForm trait.
 */
trait CalendarFormTrait
{
    /**
     * カレンダーのスキーマを追加
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return void
     */
    protected function addAdminCalendarSchema(Schema $schema)
    {
        $schema
            ->addField('user_id', 'string')
            ->addField('display_item', 'string')
            ->addField('display_all_time', 'string');
    }

    /**
     * カレンダーのバリデータを追加
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return void
     */
    protected function addAdminCalendarValidator(Validator $validator)
    {
        $validator
            ->requirePresence('user_id', false)
            ->allowEmptyString('user_id')
            ->add('user_id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_NUMBER),
                ],
            ]);

        $validator
            ->requirePresence('display_item', false)
            ->allowEmptyString('display_item')
            ->add('display_item', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', array_keys($this->getFieldValueOptions('displayItem'))],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('display_all_time', false)
            ->allowEmptyString('display_all_time')
            ->add('display_all_time', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', Configure::readOrFail('Master.common.flg')],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);
    }

    /**
     * カレンダーの値リストを生成
     *
     * @return array
     */
    protected function buildAdminCalendarFieldValueOptions()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $displayItem = [];
        foreach ($formItemsTable->getFormItems() as $formItem) {
            if ($formItem->getInputTypeItem() instanceof CalendarOutputInterface) {
                $displayItem[$formItem->get('id')] = $formItem->get('name');
            }
        }

        $fieldValueOptions = [
            'displayItem' => $displayItem,
        ];

        return $fieldValueOptions;
    }
}
