<?php
declare(strict_types=1);

namespace App\Model\InputType;

use App\Model\Entity\FormItem;
use App\Model\Entity\FormPatternDisplayType;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\CsvFormat\CsvFormatTrait;
use Cake\Core\Exception\CakeException;
use Cake\Core\InstanceConfigTrait;
use Cake\Database\TypeFactory;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;

/**
 * InputTypeItem abstract class.
 */
abstract class AbstractInputTypeItem
{
    use CommonDataTrait;
    use CsvFormatTrait;
    use InstanceConfigTrait;
    use LocatorAwareTrait;

    /**
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     * @var int|null
     */
    protected $formType = null;

    /**
     * @var \App\Model\Entity\FormItem|null
     */
    protected $formItem = null;

    /**
     * @var bool|null
     */
    protected $isAdmin = null;

    /**
     * @var string|null
     */
    protected $tableName = null;

    /**
     * @var string|null
     */
    protected $columnName = null;

    /**
     * @var mixed|null
     */
    protected $columnType = null;

    /**
     * @var array|null
     */
    protected $displayType = null;

    /**
     * @var \App\Model\Entity\Reservation|null
     */
    protected $reservationEntity = null;

    /**
     * Constructor.
     *
     * @param int $formType フォームタイプ
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @param bool $isAdmin 管理者側フラグ
     */
    public function __construct(int $formType, FormItem $formItem, bool $isAdmin)
    {
        $this->formType = $formType;
        $this->formItem = $formItem;
        $this->isAdmin = $isAdmin;

        $this->initialize();
    }

    /**
     * 初期化処理
     *
     * @return void
     */
    protected function initialize()
    {
    }

    /**
     * フォーム種別を取得
     *
     * @return int
     */
    public function getFormType()
    {
        if (!isset($this->formType)) {
            throw new CakeException();
        }

        return $this->formType;
    }

    /**
     * フォーム項目を取得
     *
     * @return \App\Model\Entity\FormItem フォーム項目
     */
    public function getFormItem()
    {
        if (!isset($this->formItem)) {
            throw new CakeException();
        }

        return $this->formItem;
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
     * テーブル名を取得
     *
     * @return string|null テーブル名
     */
    public function getTableName()
    {
        if (is_null($this->tableName)) {
            return '';
        }

        return $this->tableName;
    }

    /**
     * カラム名を取得
     *
     * @return string カラム名
     */
    public function getColumnName()
    {
        if (is_null($this->columnName)) {
            return '';
        }

        return $this->columnName;
    }

    /**
     * テーブルのエイリアスを取得
     *
     * @return string|null エイリアス
     */
    public function getTableAlias()
    {
        $tableName = $this->getTableName();
        if (!isset($tableName)) {
            return null;
        }

        $split = preg_split('/_/', $tableName);
        if (!is_array($split)) {
            throw new CakeException();
        }

        $tableAlias = implode('', Hash::map($split, '{*}', function ($value) {
            return ucfirst($value);
        }));

        return $tableAlias;
    }

    /**
     * 入力項目のキー名を取得
     *
     * @return array|string|null キー名
     */
    public function getFieldsetInputKey()
    {
        $tableName = $this->getTableName();
        if (!isset($tableName)) {
            return null;
        }
        $columnName = $this->getColumnName();
        if (empty($columnName)) {
            return null;
        }

        $fieldsetInputKey = $tableName . '.' . $columnName;

        return $fieldsetInputKey;
    }

    /**
     * 表示タイプを設定
     *
     * @param \App\Model\Entity\FormPatternDisplayType $formPatternDisplayType フォームパターン表示タイプ
     * @return void
     */
    public function settingDisplayType(FormPatternDisplayType $formPatternDisplayType)
    {
        $this->displayType = $formPatternDisplayType->checkDisplayType(
            $this->getFormType(),
            (array)$this->getConfig() + [
                'adminFlg' => $this->isAdmin(),
            ]
        );
    }

    /**
     * 項目の表示可否を判定
     *
     * @return bool 判定結果
     */
    public function canDisplay()
    {
        if (!isset($this->displayType['canDisplay'])) {
            return false;
        }

        return $this->displayType['canDisplay'];
    }

    /**
     * 詳細の表示可否を判定
     *
     * @param string|null $value 値
     * @return bool 判定結果
     */
    public function canDisplayDetail($value)
    {
        if (!isset($this->displayType['canDisplay']) || !$this->displayType['canDisplay']) {
            return false;
        }
        if (isset($this->displayType['hideEmpty']) && $this->displayType['hideEmpty'] && ((string)$value) === '') {
            return false;
        }

        return true;
    }

    /**
     * 詳細の表示内容を取得
     *
     * @param array|null $options オプション引数
     * @return mixed 表示内容
     */
    public function getDetailValue(?array $options = null)
    {
        return null;
    }

    /**
     * 詳細の説明文の表示可否を判定
     *
     * @param array|null $options オプション引数
     * @return bool 判定結果
     */
    public function canDisplayDetailDescription($options)
    {
        $mode = Hash::get((array)$options, 'mode');
        if ($mode !== 'add' && $mode !== 'edit') {
            return false;
        }

        if (isset($this->displayType['hideDescription']) && $this->displayType['hideDescription']) {
            return false;
        }

        return true;
    }

    /**
     * PHPのデータ形式をDBのデータ形式へ変換
     *
     * @param mixed $value 値
     * @return mixed 値
     */
    public function valueToDatabase($value)
    {
        if (isset($this->columnType)) {
            $value = TypeFactory::build($this->columnType)->toDatabase($value, $this->getConnection()->getDriver());
        }

        return $value;
    }

    /**
     * DBのデータ形式をPHPのデータ形式へ変換
     *
     * @param mixed $value 値
     * @return mixed 値
     */
    public function valueToPHP($value)
    {
        if (isset($this->columnType)) {
            $value = TypeFactory::build($this->columnType)->toPHP($value, $this->getConnection()->getDriver());
        }

        return $value;
    }

    /**
     * フォーム項目詳細を取得
     *
     * @param int $index インデックス
     * @return array|\Cake\Datasource\EntityInterface|null フォーム項目詳細
     */
    public function getFormItemDetails(?int $index = null)
    {
        $formItemDetails = $this->getFormItem()->get('form_item_details');
        if (!isset($formItemDetails)) {
            return null;
        }

        if (!is_array($formItemDetails)) {
            $formItemDetails = [$formItemDetails];
        }
        $formItemDetails = array_values($formItemDetails);

        if (isset($index)) {
            return Hash::get($formItemDetails, (string)$index);
        }

        return $formItemDetails;
    }

    /**
     * 非会員の表示を判定
     *
     * @return bool 判定結果
     */
    protected function isGuestUserDisplayType()
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->getTableLocator()->get('UserAuthorities');

        $userAuthorityId = $this->getConfig('userAuthorityId');
        if (((string)$userAuthorityId) === ((string)$userAuthoritiesTable->getGuestAuthority()->get('id'))) {
            return true;
        }

        return false;
    }

    /**
     * DB接続を取得
     *
     * @return \Cake\Database\Connection DB接続
     */
    protected function getConnection()
    {
        return $this->getTableLocator()->get('FormItems')->getConnection();
    }

    /**
     * ドライバ固有のSQLを生成するビルダーを取得
     *
     * @return \App\Utility\Database\AbstractBuilder ビルダー
     */
    protected function driverExpression()
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        return $formItemsTable->driverExpression();
    }

    /**
     * 付加文言(前部)を取得
     *
     * @param int $index インデックス
     * @return string 付加文言
     */
    public function getFrontWord($index = 0)
    {
        $formItemDetail = $this->getFormItemDetails($index);
        if (!($formItemDetail instanceof EntityInterface)) {
            return '';
        }
        if (!$formItemDetail->has('front_word')) {
            return '';
        }

        return $formItemDetail->get('front_word');
    }

    /**
     * 付加文言(後部)を取得
     *
     * @param int $index インデックス
     * @return string 付加文言
     */
    public function getBackWord($index = 0)
    {
        $formItemDetail = $this->getFormItemDetails($index);
        if (!($formItemDetail instanceof EntityInterface)) {
            return '';
        }
        if (!$formItemDetail->has('back_word')) {
            return '';
        }

        return $formItemDetail->get('back_word');
    }

    /**
     * アプリ側での表示内容を取得
     *
     * @param array|null $options オプション引数
     * @return mixed 表示内容
     */
    public function getAppValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * 予約エンティティを取得
     *
     * @return \App\Model\Entity\Reservation|null エンティティ
     */
    public function getReservationEntity()
    {
        return $this->reservationEntity;
    }

    /**
     * 予約エンティティを設定
     *
     * @param \App\Model\Entity\Reservation $reservation 予約エンティティ
     * @return void
     */
    public function setReservationEntity($reservation): void
    {
        $this->reservationEntity = $reservation;
    }
}
