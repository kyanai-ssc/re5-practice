<?php
declare(strict_types=1);

namespace App\Form\Admin\Users;

use App\Locale\Message;
use App\Model\Entity\AdminListItem;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Kuchen\Validation\Validation\Validator as KuchenValidator;

/**
 * 会員検索
 */
trait SearchFormTrait
{
    /**
     * 会員検索用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @param array $searchItems 検索項目
     * @return \Cake\Form\Schema スキーマ
     */
    protected function buildUserSearchSchema($schema, $searchItems)
    {
        foreach (Hash::get($searchItems, (string)FormGroup::FORM_TYPE_USER, []) as $item) {
            if ($item instanceof FormItem) {
                $inputTypeItem = $item->getInputTypeItem();
                if (!($inputTypeItem instanceof SearchDisplayInterface)) {
                    throw new CakeException();
                }
                $schema->addField($inputTypeItem->getSearchInputKey(), 'string');
            } else {
                $schema->addField(
                    Configure::readOrFail('Master.adminSearchItems.itemsSearchInputKey.' . $item),
                    'string'
                );
            }
        }

        return $schema;
    }

    /**
     * 会員検索用のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return \Cake\Validation\Validator バリデータ
     */
    protected function buildUserSearchValidator($validator, $options = [])
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $searchInputKey = Configure::readOrFail('Master.adminSearchItems.itemsSearchInputKey');

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_USER_ID], false)
            ->allowEmptyString($searchInputKey[AdminSearchItem::ITEM_USER_ID])
            ->add($searchInputKey[AdminSearchItem::ITEM_USER_ID], [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'naturalNumber' => [
                    'rule' => ['naturalNumber'],
                    'last' => true,
                    'message' => __(Message::ERROR_NATURAL_NUMBER),
                ],
                'lessThanOrEqual' => [
                    'rule' => ['comparison', CustomValidation::COMPARE_LESS_OR_EQUAL, CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT, CustomValidation::BIGINT_MAX),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_GUEST_FLG], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_GUEST_FLG])
            ->add($searchInputKey[AdminSearchItem::ITEM_GUEST_FLG], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('guestFlg')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_WITHDRAWAL_FLG], false)
            ->allowEmptyArray($searchInputKey[AdminSearchItem::ITEM_WITHDRAWAL_FLG])
            ->add($searchInputKey[AdminSearchItem::ITEM_WITHDRAWAL_FLG], [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getFieldValueOptions('withdrawalFlg')),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $userInsTimestampValidator = new KuchenValidator();
        $validator
            ->requirePresence($searchInputKey[AdminSearchItem::ITEM_USER_INS_TIMESTAMP], false)
            ->allowEmptyString($searchInputKey[AdminSearchItem::ITEM_USER_INS_TIMESTAMP])
            ->addNested($searchInputKey[AdminSearchItem::ITEM_USER_INS_TIMESTAMP], $userInsTimestampValidator);

        $userInsTimestampValidator
            ->requirePresence('from', false)
            ->allowEmptyDateTime('from')
            ->add('from', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
            ]);

        $userInsTimestampValidator
            ->requirePresence('to', false)
            ->allowEmptyDateTime('to')
            ->add('to', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'date' => [
                    'rule' => ['date', 'ymd'],
                    'last' => true,
                    'message' => __(Message::ERROR_DATE),
                ],
                'compareFields' => [
                    'rule' => ['compareDateTimeFields', 'from', Validation::COMPARE_GREATER_OR_EQUAL],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_FROM_DATE),
                    'on' => function () use ($userInsTimestampValidator) {
                        return $userInsTimestampValidator->isValid('from');
                    },
                ],
            ]);

        foreach ($formItemsTable->getSearchableFormItems(FormGroup::FORM_TYPE_USER) as $formItems) {
            foreach ($formItems as $formItem) {
                $validator = $formItem->getInputTypeItem()->buildSearchValidator($validator);
            }
        }

        return $validator;
    }

    /**
     * 会員検索用の値リストを生成
     *
     * @return array 値リスト
     */
    protected function buildUserSearchFieldValueOptions()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $sortKey = Configure::readOrFail('Master.adminListItems.sortKey');
        $sort = [
            $sortKey[AdminListItem::ITEM_USER_ID],
            $sortKey[AdminListItem::ITEM_GUEST_FLG],
            $sortKey[AdminListItem::ITEM_WITHDRAWAL_FLG],
            $sortKey[AdminListItem::ITEM_USER_INS_TIMESTAMP],
        ];

        foreach ($formItemsTable->getListDisplayableFormItems(FormGroup::FORM_TYPE_USER) as $formItems) {
            foreach ($formItems as $formItem) {
                if ($formItem->getInputTypeItem()->canSort()) {
                    $sort[] = $formItem->getInputTypeItem()->getSortKey();
                }
            }
        }

        $fieldValueOptions = [
            'guestFlg' => Configure::readOrFail('Master.user.guestFlg'),
            'withdrawalFlg' => Configure::readOrFail('Master.user.withdrawalFlg'),
            'sort' => Hash::combine($sort, '{*}'),
        ];

        return $fieldValueOptions;
    }

    /**
     * 会員検索用のデフォルト値を生成
     *
     * @return array デフォルト値
     */
    protected function buildUserSearchDefaultFieldValues()
    {
        $defaultValues = [
            'sort' => Configure::readOrFail('Master.adminListItems.sortKey.' . AdminListItem::ITEM_USER_ID),
        ];

        return $defaultValues;
    }
}
