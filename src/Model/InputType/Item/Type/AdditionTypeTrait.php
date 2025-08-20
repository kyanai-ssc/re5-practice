<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use App\Model\Entity\FormGroup;
use App\Utility\Filter\AbstractFilter;
use App\Validation\AbstractInputCheck;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;

/**
 * SelectType trait.
 */
trait AdditionTypeTrait
{
    /**
     * @var int|null
     */
    protected $searchBindNumber = null;

    /**
     * テーブル名を取得
     *
     * @return string|null テーブル名
     */
    public function getTableName()
    {
        $tableName = null;
        if ((string)$this->getFormType() === ((string)FormGroup::FORM_TYPE_USER)) {
            $tableName = 'user_additions';
        }
        if ((string)$this->getFormType() === ((string)FormGroup::FORM_TYPE_RESERVATION)) {
            $tableName = 'reservation_additions';
        }

        return $tableName;
    }

    /**
     * カラム名を取得
     *
     * @return string|null カラム名
     */
    public function getColumnName()
    {
        return 'value';
    }

    /**
     * 入力項目のキー名を取得
     *
     * @return string|null キー名
     */
    public function getFieldsetInputKey()
    {
        $table = null;
        if ((string)$this->getFormType() === ((string)FormGroup::FORM_TYPE_USER)) {
            $table = 'users';
        }
        if ((string)$this->getFormType() === ((string)FormGroup::FORM_TYPE_RESERVATION)) {
            $table = 'reservations';
        }

        return $table . '.addition_values.' . $this->getFieldsetColumn();
    }

    /**
     * 入力項目のカラム名を取得
     *
     * @return string カラム名
     */
    public function getFieldsetColumn()
    {
        return 'item_' . $this->getFormItem()->get('id');
    }

    /**
     * 追加項目の検索クエリを取得
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param mixed $where WHERE
     * @return \Cake\Database\Expression\QueryExpression SQL式
     */
    protected function getAdditionSearchExpression($query, $where)
    {
        $queryExpression = $query->newExpr();
        if ((string)$this->getFormType() === (string)FormGroup::FORM_TYPE_USER) {
            /** @var \App\Model\Table\UserAdditionsTable $userAdditionsTable */
            $userAdditionsTable = $this->getTableLocator()->get('UserAdditions');

            $additionQuery = $userAdditionsTable->find();
            $additionQuery->select(['user_id']);
            $additionQuery->where([
                'form_item_id' => $this->getFormItem()->get('id'),
            ]);
            $additionQuery->where($where);
            $queryExpression->in('Users.id', $additionQuery);
        } elseif ((string)$this->getFormType() === (string)FormGroup::FORM_TYPE_RESERVATION) {
            /** @var \App\Model\Table\ReservationAdditionsTable $reservationAdditionsTable */
            $reservationAdditionsTable = $this->getTableLocator()->get('ReservationAdditions');

            $additionQuery = $reservationAdditionsTable->find();
            $additionQuery->select(['reservation_id']);
            $additionQuery->where([
                'form_item_id' => $this->getFormItem()->get('id'),
            ]);
            $additionQuery->where($where);
            $queryExpression->in('Reservations.id', $additionQuery);
        } else {
            throw new CakeException();
        }

        return $queryExpression;
    }

    /**
     * 追加項目の未入力検索を生成
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param array $inputs 入力値
     * @return \Cake\Database\Expression\QueryExpression|null SQL式
     */
    protected function buildAdditionEmptySearchQuery($query, $inputs)
    {
        if (!($this instanceof SearchDisplayInterface)) {
            throw new CakeException();
        }

        $queryExpression = null;
        $value = Hash::get($inputs, $this->getSearchInputKey() . '.empty');
        if (isset($value) && ((string)$value) === (string)Configure::readOrFail('Master.common.flg.on')) {
            if ((string)$this->getFormType() === (string)FormGroup::FORM_TYPE_USER) {
                /** @var \App\Model\Table\UserAdditionsTable $userAdditionsTable */
                $userAdditionsTable = $this->getTableLocator()->get('UserAdditions');

                $additionQuery = $userAdditionsTable->find();
                $additionQuery->where([
                    'UserAdditions.user_id = Users.id',
                ]);
            } elseif ((string)$this->getFormType() === (string)FormGroup::FORM_TYPE_RESERVATION) {
                /** @var \App\Model\Table\ReservationAdditionsTable $reservationAdditionsTable */
                $reservationAdditionsTable = $this->getTableLocator()->get('ReservationAdditions');

                $additionQuery = $reservationAdditionsTable->find();
                $additionQuery->where([
                    'ReservationAdditions.reservation_id = Reservations.id',
                ]);
            } else {
                throw new CakeException();
            }

            $additionQuery->select(['id']);
            $additionQuery->where([
                'form_item_id' => $this->getFormItem()->get('id'),
                'value IS NOT NULL',
            ]);

            $queryExpression = $query->newExpr();
            $queryExpression->notExists($additionQuery);
        }

        return $queryExpression;
    }

    /**
     * 追加項目のIN検索を生成
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param array $inputs 入力値
     * @return \Cake\Database\Expression\QueryExpression|null SQL式
     */
    protected function buildAdditionInSearchQuery($query, $inputs)
    {
        if (!($this instanceof SearchDisplayInterface)) {
            throw new CakeException();
        }

        $queryExpression = null;
        $value = Hash::get($inputs, $this->getSearchInputKey() . '.value');
        if (is_scalar($value) && ((string)$value !== '') || is_array($value) && !empty($value)) {
            $queryExpression = $this->getAdditionSearchExpression(
                $query,
                function ($expression) use ($value) {
                    $expression->in('value', (array)$value);

                    return $expression;
                }
            );
        }

        return $queryExpression;
    }

    /**
     * 追加項目のJSON配列検索を生成
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param array $inputs 入力値
     * @return \Cake\Database\Expression\QueryExpression|null SQL式
     */
    protected function buildAdditionArraySearchQuery($query, $inputs)
    {
        if (!($this instanceof SearchDisplayInterface)) {
            throw new CakeException();
        }

        $queryExpression = null;
        $values = Hash::get($inputs, $this->getSearchInputKey() . '.value');
        if (is_scalar($values) && ((string)$values !== '') || is_array($values) && !empty($values)) {
            $where = [];
            foreach ((array)$values as $value) {
                $where[] = $this->driverExpression()->jsonArrayContains('value', $value);
            }
            $queryExpression = $this->getAdditionSearchExpression($query, ['OR' => $where]);
        }

        return $queryExpression;
    }

    /**
     * 追加項目のLIKE検索を生成
     *
     * @param \Cake\ORM\Query $query クエリ
     * @param array $inputs 入力値
     * @return \Cake\Database\Expression\QueryExpression|null SQL式
     */
    protected function buildAdditionLikeSearchQuery($query, $inputs)
    {
        if (!($this instanceof SearchDisplayInterface)) {
            throw new CakeException();
        }

        $queryExpression = null;
        $value = Hash::get($inputs, $this->getSearchInputKey() . '.value');
        if (isset($value) && $value !== '') {
            $queryExpression = $this->getAdditionSearchExpression(
                $query,
                function ($expression) use ($value) {
                    $expression->like('value', '%' . $this->driverExpression()->escapeLike($value) . '%');

                    return $expression;
                }
            );
        }

        return $queryExpression;
    }

    /**
     * 追加項目のCSVの入力値を生成
     *
     * @param mixed $data データ
     * @return array 入力値
     */
    protected function formatAdditionCsvInputData($data)
    {
        $result = [];
        foreach ((array)$this->getFieldsetInputKey() as $key) {
            $result = Hash::insert($result, $key, $data);
        }

        return $result;
    }

    /**
     * CSVのエラーメッセージを取得
     *
     * @param array $errors エラー
     * @return array エラーメッセージ
     */
    protected function getAdditionCsvErrorMessages($errors)
    {
        return Hash::get($errors, 'addition_values.' . $this->getFieldsetColumn(), []);
    }

    /**
     * メールの置換文字を取得
     *
     * @return string 置換文字
     */
    protected function getAdditionMailReplaceToken()
    {
        $prefix = null;
        if ((string)$this->getFormType() === ((string)FormGroup::FORM_TYPE_USER)) {
            $prefix = 'user';
        }
        if ((string)$this->getFormType() === ((string)FormGroup::FORM_TYPE_RESERVATION)) {
            $prefix = 'reserve';
        }

        return $prefix . '_value_' . $this->getFormItem()->get('id');
    }

    /**
     * 入力変換を適用
     *
     * @param array $inputs 入力値
     * @param string $key キー
     * @param array|\Cake\Datasource\EntityInterface|null $formItemDetail フォーム項目詳細
     * @return array 適用後の入力値
     */
    protected function applyInputTranslate($inputs, $key, $formItemDetail)
    {
        if (!$formItemDetail instanceof \Cake\Datasource\EntityInterface) {
            return $inputs;
        }

        if (!$formItemDetail->has('text_input_translate')) {
            return $inputs;
        }

        $data = Hash::get($inputs, $key);
        if (!isset($data)) {
            return $inputs;
        }

        $className = Configure::readOrFail(
            'Master.form.textInputTranslateClass.' . $formItemDetail->get('text_input_translate')
        );
        $classPath = '\\App\\Utility\\Filter\\' . $className;
        $filter = new $classPath();
        if (!($filter instanceof AbstractFilter)) {
            throw new CakeException();
        }

        $inputs = Hash::insert($inputs, $key, $filter->filter($data));

        return $inputs;
    }

    /**
     * 入力チェックを追加
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param string $key キー
     * @param \Cake\Datasource\EntityInterface $formItemDetail フォーム項目詳細
     * @return \Cake\Validation\Validator
     */
    protected function addInputCheckValidation($validator, $key, $formItemDetail)
    {
        if (!$formItemDetail->has('text_input_check')) {
            return $validator;
        }

        $validator->add($key, [
            'inputCheck' => [
                'rule' => function ($value) use ($formItemDetail) {
                    $className = Configure::readOrFail(
                        'Master.form.textInputCheckClass.' . $formItemDetail->get('text_input_check')
                    );
                    $classPath = '\\App\\Validation\\InputCheck\\' . $className;
                    $validation = new $classPath();
                    if (!($validation instanceof AbstractInputCheck)) {
                        throw new CakeException();
                    }

                    if (!$validation->validate($value)) {
                        return $validation->getError();
                    }

                    return true;
                },
                'last' => true,
            ],
        ]);

        return $validator;
    }

    /**
     * 追加項目の値を取得
     *
     * @param array|null $options オプション引数
     * @return mixed 値
     */
    protected function getAdditionValue($options = null)
    {
        $keys = [
            FormGroup::FORM_TYPE_USER => 'user',
            FormGroup::FORM_TYPE_RESERVATION => 'reservation',
        ];

        if (is_null($options)) {
            return null;
        }

        $data = Hash::get($options, $keys[$this->getFormType()]);
        if (!isset($data)) {
            return null;
        }

        $additionValues = null;
        if (is_array($data)) {
            $additionValues = Hash::get($data, 'addition_values');
        }
        if ($data instanceof EntityInterface) {
            $additionValues = $data->get('addition_values');
        }
        if (!isset($additionValues)) {
            return null;
        }

        return Hash::get($additionValues, $this->getFieldsetColumn());
    }

    /**
     * 検索時のbindで利用する名称を取得
     *
     * @return string
     */
    protected function getNextSearchBindName()
    {
        if (!isset($this->searchBindNumber)) {
            $this->searchBindNumber = 0;
        }
        $this->searchBindNumber += 1;

        $bindName = sprintf(':additionSearch_%s_%s', $this->getFormItem()->get('id'), $this->searchBindNumber);

        return $bindName;
    }
}
