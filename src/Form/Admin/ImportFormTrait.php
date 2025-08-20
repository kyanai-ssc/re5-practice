<?php
declare(strict_types=1);

namespace App\Form\Admin;

use App\Model\Entity\FormItem;
use App\Model\InputType\Item\Type\CsvInputInterface;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\Table\FormItemsTable;
use App\Utility\CsvFormat\CsvFormatTrait;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Utility\Hash;

/**
 * ImportForm trait.
 */
trait ImportFormTrait
{
    use CsvFormatTrait;

    /**
     * @var array|null
     */
    protected $csvHeader = null;

    /**
     * @var array|null
     */
    protected $csvColumn = null;

    /**
     * @var array
     */
    protected $csvData = null;

    /**
     * @var \Cake\Datasource\EntityInterface|null
     */
    protected $entity = null;

    /**
     * @var bool|null
     */
    protected $isNewEntity = null;

    /**
     * @var bool
     */
    protected $isInvalidData = false;

    /**
     * CSVのヘッダーを設定
     *
     * @param array $header ヘッダー
     * @return void
     */
    public function setCsvHeader(array $header)
    {
        $csvHeader = $this->createCsvHeader();

        $pattern = '/^\\[(-?[0-9]+)\\].*$/';

        $headerIndex = [];
        foreach ($header as $index => $data) {
            if (preg_match($pattern, $data) === 1) {
                $key = preg_replace($pattern, '$1', $data);
                if (!is_scalar($key)) {
                    throw new CakeException();
                }
                $headerIndex[$key] = $index;
            }
        }

        $csvColumn = [];
        foreach ($csvHeader as $column => $data) {
            if (preg_match($pattern, $data) === 1) {
                $key = preg_replace($pattern, '$1', $data);
                if (!is_scalar($key)) {
                    throw new CakeException();
                }
                if (isset($headerIndex[$key])) {
                    $csvColumn[$headerIndex[$key]] = $column;
                }
            }
        }

        $this->csvHeader = $csvHeader;
        $this->csvColumn = $csvColumn;
    }

    /**
     * CSVのヘッダをチェック
     *
     * @return bool
     */
    public function checkCsvHeader()
    {
        if (!isset($this->csvHeader) || !isset($this->csvColumn)) {
            return false;
        }
        if (count($this->csvHeader) !== count($this->csvColumn)) {
            return false;
        }

        return true;
    }

    /**
     * CSVのデータを設定
     *
     * @param array $data データ
     * @return void
     */
    public function setCsvData(array $data)
    {
        $csvData = [];
        foreach ((array)$this->csvColumn as $index => $column) {
            $csvData[$column] = null;
            if (isset($data[$index]) && ((string)$data[$index]) !== '') {
                $csvData[$column] = $data[$index];
            }
        }
        $csvData = $this->formatCsvData($csvData);

        $this->csvData = $csvData;
    }

    /**
     * エンティティを取得
     *
     * @return \Cake\Datasource\EntityInterface|null
     */
    public function getEntity()
    {
        if (!isset($this->entity)) {
            $this->entity = $this->createEntity($this->csvData);
            if (isset($this->entity)) {
                $this->isNewEntity = $this->entity->isNew();
            }
        }

        return $this->entity;
    }

    /**
     * エンティティの新規作成を判定
     *
     * @return bool|null
     */
    public function isNewEntity()
    {
        return $this->isNewEntity;
    }

    /**
     * エラーメッセージを取得
     *
     * @return array
     */
    public function getMessages()
    {
        $errors = $this->getErrors();
        $entity = $this->getEntity();
        if (isset($entity)) {
            $errors = Hash::merge($errors, $entity->getErrors());
        }
        $messages = $this->formatErrors($errors);

        return $messages;
    }

    /**
     * 登録後に出力するメッセージを取得
     *
     * @return array
     */
    public function getInfoMessages()
    {
        return [];
    }

    /**
     * 項目のデータをフォーマット
     *
     * @param array $data データ
     * @param int|string $key キー
     * @param \Cake\Datasource\EntityInterface|int $item 項目
     * @param array $columns 固定項目のカラム
     * @return mixed
     */
    protected function formatItemData($data, $key, $item, $columns)
    {
        $result = [];
        if ($item instanceof FormItem) {
            $inputTypeItem = $item->getInputTypeItem();
            if (!($inputTypeItem instanceof CsvInputInterface)) {
                throw new CakeException();
            }
            $additions = [
                FormItemsTable::CSV_COLUMN_USER_ADDITION => true,
                FormItemsTable::CSV_COLUMN_RESERVATION_ADDITION => true,
            ];
            $column = (string)$key;
            if (isset($additions[$key])) {
                $column = $key . '_' . $item->get('id');
            }
            $value = Hash::get($data, $column);
            $result = $inputTypeItem->formatCsvInputData($value);
        } else {
            $value = Hash::get($data, (string)$key);
            if (((string)$key) === ((string)FormItemsTable::CSV_COLUMN_EVENT_PLANS)) {
                $planValues = [];
                foreach ($this->csvFormat()->inputsForMultiple($value) as $planValue) {
                    $planValues[] = $this->csvFormat()->inputForId($planValue);
                }
                $value = $planValues;
            } elseif (
                ((string)$key) === ((string)FormItemsTable::CSV_COLUMN_RESERVATION_STATUS_ID)
                || ((string)$key) === ((string)FormItemsTable::CSV_COLUMN_PAYMENT_METHOD)
                || ((string)$key) === ((string)FormItemsTable::CSV_COLUMN_PAYMENT_STATUS)
                || ((string)$key) === ((string)FormItemsTable::CSV_COLUMN_RECEPTION_STATUS_ID)
            ) {
                $value = $this->csvFormat()->inputForId($value);
            }
            if (isset($value)) {
                $result = Hash::insert($result, Hash::get($columns, (string)$key), $value);
            }
        }

        return $result;
    }

    /**
     * 関連データのエラーメッセージを生成
     *
     * @param string $subject カラム名
     * @param array $error エラーメッセージ
     * @param array $asHeader アソシエーションヘッダー
     * @return array
     */
    protected function formatErrorMessageForAssociation(string $subject, array $error, array $asHeader = [])
    {
        $associationBody = [];

        foreach ($error as $asLine => $asMessage) {
            $associationMessages = [];
            foreach ($asMessage as $asKey => $asValue) {
                if (!empty($asHeader)) {
                    $asSubject = $asHeader[$asKey];
                } else {
                    $asSubject = '';
                }
                $asBody = implode(Configure::readOrFail('Setting.csv.import.error.delimiter'), $asValue);
                $separator = Configure::readOrFail('Setting.csv.import.error.separator');
                $associationMessages[] = $asSubject . $separator . $asBody;
            }
            $associationBody[$asLine + 1] = implode(
                Configure::readOrFail('Setting.csv.import.error.delimiter'),
                $associationMessages
            );
        }

        return $associationBody;
    }

    /**
     * 項目のエラーをフォーマット
     *
     * @param array $errors エラー
     * @param int|string $key キー
     * @param \Cake\Datasource\EntityInterface|int $item 項目
     * @param array $columns 固定項目のカラム
     * @return array
     */
    protected function formatItemErrors($errors, $key, $item, $columns)
    {
        $result = [];
        if ($item instanceof FormItem) {
            $inputTypeItem = $item->getInputTypeItem();
            if (!($inputTypeItem instanceof CsvInputInterface)) {
                throw new CakeException();
            }
            $result = (array)$inputTypeItem->formatCsvErrors($errors);
        } else {
            $errorKey = preg_split('/\\./', Hash::get($columns, (string)$key));
            if (!is_array($errorKey)) {
                throw new CakeException();
            }
            $errorKey = (string)array_pop($errorKey);
            $messages = Hash::get($errors, $errorKey);
            if (!empty($messages)) {
                $name = Hash::get((array)$this->csvHeader, (string)$key);
                $separator = Configure::readOrFail('Setting.csv.import.error.separator');
                $delimiter = Configure::readOrFail('Setting.csv.import.error.delimiter');
                $result[] = $name . $separator . implode($delimiter, Hash::flatten($messages));
            }
        }

        return $result;
    }

    /**
     * 文字コード等の不正なデータ判定
     *
     * @return bool
     */
    public function isInvalidData()
    {
        return $this->isInvalidData;
    }

    /**
     * 文字コード等の不正なデータ判定（フォーム項目）
     *
     * @param array $forms 項目データ
     * @return bool
     */
    protected function isInvalidDataForFormItem($forms)
    {
        foreach ($forms as $formGroups) {
            foreach ($formGroups as $formGroup) {
                foreach ((array)$formGroup->get('form_items') as $formItem) {
                    $inputTypeItem = $formItem->getInputTypeItem();
                    if ($inputTypeItem instanceof InputInterface) {
                        if ($inputTypeItem->isInvalidData()) {
                            return true;
                        }
                    }
                }
            }
        }

        return false;
    }

    /**
     * CSVのヘッダを生成
     *
     * @return array
     */
    abstract protected function createCsvHeader(): array;

    /**
     * CSVのデータを生成
     *
     * @param array $data データ
     * @return array
     */
    abstract protected function formatCsvData(array $data): array;

    /**
     * エンティティを生成
     *
     * @param array $data データ
     * @return array
     */
    abstract protected function createEntity(array $data): ?EntityInterface;

    /**
     * エラーメッセージを生成
     *
     * @param array $errors メッセージ
     * @return array
     */
    abstract protected function formatErrors(array $errors): array;
}
