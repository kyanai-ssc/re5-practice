<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\FormItem;
use App\Model\Entity\FormPattern;
use App\Model\Entity\FormPatternDisplayType;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * FormPatternDisplayTypes Model
 *
 * @method \App\Model\Entity\FormPatternDisplayType newEmptyEntity()
 * @method \App\Model\Entity\FormPatternDisplayType newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType get($primaryKey, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\FormPatternDisplayType[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class FormPatternDisplayTypesTable extends AppTable
{
    /**
     * フォームタイプ
     *
     * @var array
     */
    public $formItems = [];

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('FormPatterns', [
            'foreignKey' => 'form_pattern_id',
            'joinType' => 'INNER',
        ]);
        $this->belongsTo('FormItems', [
            'foreignKey' => 'form_item_id',
            'joinType' => 'INNER',
            'sort' => [
                'FormItems.sort_no' => 'ASC',
                'FormItems.id' => 'ASC',
            ],
        ]);
    }

    /**
     * フォームの項目をセット
     *
     * @param array $formItems フォーム項目
     * @return void
     */
    public function setFormItems($formItems)
    {
        $this->formItems = $formItems;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('display_type', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('display_type', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('display_type', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        $this,
                        'displayInList',
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);
        $validator
            ->add('app_display_flg', [
                'inList' => [
                    'rule' => [
                        'inList',
                        FormPatternDisplayType::APP_DISPLAY_FLG_LIST,
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * 表示タイプの検証
     *
     * @param mixed $value 値
     * @param array $context context
     * @return bool
     */
    public function displayInList($value, $context)
    {
        if (!isset($this->formItems[$context['data']['form_item_id']])) {
            return false;
        }

        $form = $this->formItems[$context['data']['form_item_id']];

        $onlyDisplayType = (array)Configure::read('Master.formPattern.inList.displayOnlyItemType');
        $onlyAdminType = (array)Configure::read('Master.formPattern.inList.onlyAdminItemType');
        $displayAndOnlyAdminType
            = (array)Configure::read('Master.formPattern.inList.displayAndOnlyAdminItemType');

        if ((string)$form['default_flg'] === (string)FormPattern::DEFAULT_FLG_ON) {
            $inList = array_keys($this->getFieldValueOptions('displayTypeNoAdminEdit'));

            if (Hash::get($onlyDisplayType, $form['input_type'], false)) {
                $inList = array_keys($this->getFieldValueOptions('displayTypeOnly'));
            } elseif ((string)$form['input_type'] === (string)FormItem::INPUT_TYPE_RESERVATION_NUMBER) {
                $inList = array_keys($this->getFieldValueOptions('displayTypeNumber'));
            } elseif (Hash::get($onlyAdminType, $form['input_type'], false)) {
                $inList = array_keys($this->getFieldValueOptions('displayTypeOnlyAdmin'));
            } elseif (Hash::get($displayAndOnlyAdminType, $form['input_type'], false)) {
                $inList = array_keys($this->getFieldValueOptions('displayAndOnlyAdmin'));
            }
        } else {
            $inList = array_keys($this->getFieldValueOptions('displayTypeAddition'));
        }

        return Validation::inList($value, $inList);
    }

    /**
     * フォーム生成時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findFormCreating(Query $query, array $options)
    {
        $query->select([
            'id',
            'form_pattern_id',
            'form_item_id',
            'display_type',
            'app_display_flg',
        ]);

        $userAuthorityId = (array)Hash::get($options, 'inputs.user_authority_id', []);
        if (count($userAuthorityId) > 0) {
            $userAuthorityQuery = $this->getTableLocator()->get('UserAuthorities')->find();
            $userAuthorityQuery->select(['form_pattern_id']);
            $userAuthorityQuery->where(['UserAuthorities.id IN' => $userAuthorityId]);
            $query->where(['FormPatternDisplayTypes.form_pattern_id IN' => $userAuthorityQuery]);
        }

        $eventId = (array)Hash::get($options, 'inputs.event_id', []);
        if (count($eventId) > 0) {
            $eventQuery = $this->getTableLocator()->get('Events')->find();
            $eventQuery->select(['form_pattern_id']);
            $eventQuery->where(['Events.id IN' => $eventId]);
            $query->where(['FormPatternDisplayTypes.form_pattern_id IN' => $eventQuery]);
        }

        $query->formatResults(function ($formPatternDisplayTypes) {
            return $formPatternDisplayTypes->indexBy('form_item_id');
        });

        return $query;
    }

    /**
     * データ未登録時のデフォルトデータを生成
     *
     * @param int $formItemId フォーム項目ID
     * @return \App\Model\Entity\FormPatternDisplayType
     */
    public function createDefaultData(int $formItemId)
    {
        $entity = $this->newEntity([
            'form_item_id' => $formItemId,
            'display_type' => FormPatternDisplayType::DISPLAY_TYPE_HIDE,
        ]);

        return $entity;
    }

    /**
     * フォーム種別を指定して削除を行う
     *
     * @param int $formType フォーム種別
     * @param array $excludeFormItemId 除外するフォーム項目ID
     * @return void
     */
    public function deleteByFormType(int $formType, ?array $excludeFormItemId = null)
    {
        $formItemsQuery = $this->getAssociation('FormItems')->find();
        $formItemsQuery->select(['FormItems.id']);
        $formItemsQuery->matching('FormGroups', function ($formGroupsQuery) use ($formType) {
            $formGroupsQuery->where(['FormGroups.form_type' => $formType]);

            return $formGroupsQuery;
        });

        $where = [
            'FormPatternDisplayTypes.form_item_id IN' => $formItemsQuery,
        ];
        if (!empty($excludeFormItemId)) {
            $where['FormPatternDisplayTypes.form_item_id NOT IN'] = (array)$excludeFormItemId;
        }
        $this->deleteAll($where);
    }
}
