<?php
declare(strict_types=1);

namespace App\Model\InputType;

use App\Utility\CommonData\CommonDataTrait;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Validation\Validator;

/**
 * InputTypeManager abstract class.
 */
abstract class AbstractInputTypeManager
{
    use CommonDataTrait;
    use LocatorAwareTrait;

    public const ITEM_DETAIL_FRONT_WORD_MAX = 100;
    public const ITEM_DETAIL_BACK_WORD_MAX = 100;
    public const ITEM_DETAIL_TEXT_LOWER_LIMIT_MIN = 0;
    public const ITEM_DETAIL_TEXT_LOWER_LIMIT_MAX = 10000000;
    public const ITEM_DETAIL_TEXT_UPPER_LIMIT_MIN = 0;
    public const ITEM_DETAIL_TEXT_UPPER_LIMIT_MAX = 10000000;
    public const ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MIN = 0;
    public const ITEM_DETAIL_DATE_UPPER_LIMIT_RELATIVE_MAX = 100;

    /**
     * @var int|null
     */
    protected $inputType = null;

    /**
     * @var string|null
     */
    protected $inputTypeName = null;

    /**
     * @var bool|null
     */
    protected $isAdmin = null;

    /**
     * @var array
     */
    protected $canCreate = null;

    /**
     * @var bool
     */
    protected $canSelectRequired = false;

    /**
     * @var bool
     */
    protected $required = false;

    /**
     * @var bool
     */
    protected $canReservationDisplay = false;

    /**
     * @var bool
     */
    protected $hasFormItemDetails = false;

    /**
     * @var int|null
     */
    protected $formItemDetailNumber = null;

    /**
     * @var array|null
     */
    protected $formItemDetailColumns = null;

    /**
     * @var bool
     */
    protected $hasFormItemChoices = false;

    /**
     * @var bool
     */
    protected $hasFormItemOptionGroups = false;

    /**
     * @var bool
     */
    protected $hasFormItemDetailTab = false;

    /**
     * Constructor.
     *
     * @param int $inputType 入力タイプ
     * @param bool $isAdmin 管理者側フラグ
     */
    public function __construct(int $inputType, bool $isAdmin)
    {
        $className = namespaceSplit(static::class);

        $this->inputType = $inputType;
        $this->inputTypeName = $className[1];
        $this->isAdmin = $isAdmin;
    }

    /**
     * 入力タイプを取得
     *
     * @return int 入力タイプ
     */
    public function getInputType()
    {
        if (!isset($this->inputType)) {
            throw new CakeException();
        }

        return $this->inputType;
    }

    /**
     * 入力タイプ名を取得
     *
     * @return string 入力タイプ名
     */
    public function getInputTypeName()
    {
        if (!isset($this->inputTypeName)) {
            throw new CakeException();
        }

        return $this->inputTypeName;
    }

    /**
     * 管理者側フラグを取得
     *
     * @return bool 管理者側フラグ
     */
    public function isAdmin()
    {
        if (!isset($this->isAdmin)) {
            throw new CakeException();
        }

        return $this->isAdmin;
    }

    /**
     * 項目の作成可否を判定
     *
     * @param int $formType フォーム種別
     * @return bool 判定結果
     */
    public function canCreate(int $formType)
    {
        return isset($this->canCreate[$formType]) ? (bool)$this->canCreate[$formType] : false;
    }

    /**
     * 必須固定を判定
     *
     * @return bool 判定結果
     */
    public function required()
    {
        return $this->required;
    }

    /**
     * 必須任意の選択可否
     *
     * @return bool 選択可否
     */
    public function canSelectRequired()
    {
        return $this->canSelectRequired;
    }

    /**
     * 予約画面表示可否を判定
     *
     * @return bool 判定結果
     */
    public function canReservationDisplay()
    {
        return $this->canReservationDisplay;
    }

    /**
     * フォーム項目詳細の有無を判定
     *
     * @return bool 判定結果
     */
    public function hasFormItemDetails()
    {
        return $this->hasFormItemDetails;
    }

    /**
     * フォーム項目詳細の数を取得
     *
     * @return int|null フォーム項目詳細の数
     */
    public function getFormItemDetailNumber()
    {
        return $this->formItemDetailNumber;
    }

    /**
     * フォーム項目詳細のカラム一覧を取得
     *
     * @return array|null カラム一覧
     */
    public function getFormItemDetailColumns()
    {
        return $this->formItemDetailColumns;
    }

    /**
     * フォーム項目選択肢の有無を判定
     *
     * @return bool 判定結果
     */
    public function hasFormItemChoices()
    {
        return $this->hasFormItemChoices;
    }

    /**
     * フォーム項目オプショングループの有無を判定
     *
     * @return bool 判定結果
     */
    public function hasFormItemOptionGroups()
    {
        return $this->hasFormItemOptionGroups;
    }

    /**
     * 詳細設定タブの有無を判定
     *
     * @return bool 判定結果
     */
    public function hasFormItemDetailTab()
    {
        return $this->hasFormItemDetailTab;
    }

    /**
     * フォーム項目詳細のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @return \Cake\Validation\Validator
     */
    public function validationFormItemDetail(Validator $validator)
    {
        return $validator;
    }

    /**
     * 入力項目を生成
     *
     * @param int $formType フォームタイプ
     * @param \Cake\Datasource\EntityInterface $formItem フォーム項目
     * @return \App\Model\InputType\AbstractInputTypeItem
     */
    public function createItem(int $formType, EntityInterface $formItem)
    {
        $class = '\\App\\Model\\InputType\\Item\\' . $this->getInputTypeName();
        $instance = new $class($formType, $formItem, $this->isAdmin());
        if (!($instance instanceof AbstractInputTypeItem)) {
            throw new CakeException();
        }

        return $instance;
    }
}
