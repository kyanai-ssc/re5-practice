<?php
declare(strict_types=1);

namespace App\Utility\CsvFormat;

use App\Utility\DateTimeUtility;
use App\Utility\StringUtility;
use Cake\Core\InstanceConfigTrait;
use Cake\Datasource\EntityInterface;

/**
 * CsvFormatter class.
 */
class CsvFormatter
{
    use InstanceConfigTrait;

    /**
     * Default config
     *
     * @var array
     */
    protected $_defaultConfig = [
        'idFormat' => '[%ID%] %NAME%',
        'dateFormat' => 'Y/m/d',
        'timeFormat' => 'H:i',
        'timestampFormat' => 'Y/m/d H:i',
        'timestampFormatFull' => 'Y/m/d H:i:s',
        'multipleSeparator' => '|',
        'multipleSeparatorReplace' => '｜',
        'hasManySeparator' => "\n",
    ];

    /**
     * Constructor.
     *
     * @param array $options オプション
     */
    public function __construct(array $options = [])
    {
        $this->setConfig($options);
    }

    /**
     * CSVヘッダをフォーマット
     *
     * @param string $headerTemplate ヘッダテンプレート
     * @param string|\Cake\Datasource\EntityInterface $item 項目
     * @return string ヘッダ
     */
    public function csvHeader(string $headerTemplate, $item): string
    {
        if ($item instanceof EntityInterface) {
            $search = [
                '/%FORM_ITEM_ID%/',
                '/%FORM_ITEM_NAME%/',
            ];
            $replacement = [
                $item->get('id'),
                StringUtility::pregReplaceQuote($item->get('name')),
            ];
            $header = (string)preg_replace($search, $replacement, $headerTemplate);
        } else {
            $search = [
                '/%CSV_COLUMN_ID%/',
            ];
            $replacement = [
                StringUtility::pregReplaceQuote($item),
            ];
            $header = (string)preg_replace($search, $replacement, $headerTemplate);
        }

        return $header;
    }

    /**
     * IDのCSVデータをフォーマット
     *
     * @param int|string $id ID
     * @param string $name 名称
     * @return string データ
     */
    public function csvForId($id, string $name): string
    {
        $search = [
            '/%ID%/',
            '/%NAME%/',
        ];
        $replacement = [
            StringUtility::pregReplaceQuote((string)$id),
            StringUtility::pregReplaceQuote($name),
        ];
        $data = preg_replace(
            $search,
            $replacement,
            $this->getConfig('idFormat')
        );
        if (!is_string($data)) {
            return '';
        }

        return $data;
    }

    /**
     * 複数選択のIDのCSVデータをフォーマット
     *
     * @param array $dataList 値リスト
     * @param array $keys 設定値
     * @return string データ
     */
    public function csvForMultipleId(array $dataList, array $keys)
    {
        $multiple = [];
        foreach ($dataList as $data) {
            $multiple[] = $this->csvForId($data, $keys[$data]);
        }

        $value = $this->csvForMultiple($multiple);

        return $value;
    }

    /**
     * 複数選択のIDのCSVデータをフォーマット
     *
     * @param array $dataList 値リスト
     * @param array $keys 設定値
     * @return string データ
     */
    public function csvForHasManyId(array $dataList, array $keys): string
    {
        $multiple = [];
        foreach ($dataList as $data) {
            $multiple[] = $this->csvForId($data, $keys[$data]);
        }

        $value = $this->csvForHasMany($multiple);

        return $value;
    }

    /**
     * CSVデータを入力値にフォーマット
     *
     * @param string|null $data ID
     * @return string|null データ
     */
    public function inputForId(?string $data): ?string
    {
        if (((string)$data) === '') {
            return null;
        }

        preg_match('/^\[(\d+)\]/u', (string)$data, $matches);
        if (isset($matches[1])) {
            $id = $matches[1];
        } else {
            $id = $data;
        }

        return $id;
    }

    /**
     * 日付のCSVデータをフォーマット
     *
     * @param string|\DateTimeInterface $dateTime 日付
     * @return string|null データ
     */
    public function csvForDate($dateTime): ?string
    {
        $dateTime = DateTimeUtility::convertToDateObject($dateTime);
        if (!isset($dateTime)) {
            return null;
        }

        $data = $dateTime->format($this->getConfig('dateFormat'));

        return $data;
    }

    /**
     * 時間のCSVデータをフォーマット
     *
     * @param string|\DateTimeInterface $dateTime 日付
     * @return string|null データ
     */
    public function csvForTime($dateTime): ?string
    {
        $dateTime = DateTimeUtility::convertToTimeObject($dateTime);
        if (!isset($dateTime)) {
            return null;
        }

        $data = $dateTime->format($this->getConfig('timeFormat'));

        return $data;
    }

    /**
     * 日時のCSVデータをフォーマット
     *
     * @param string|\DateTimeInterface $dateTime 日付
     * @param string $format フォーマット
     * @return string|null データ
     */
    public function csvForTimestamp($dateTime, string $format = 'timestampFormat'): ?string
    {
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (!isset($dateTime)) {
            return null;
        }

        $data = $dateTime->format($this->getConfig($format));

        return $data;
    }

    /**
     * 複数項目のCSVデータをフォーマット
     *
     * @param array $values 項目の値
     * @param bool $nl 改行を含んだ行
     * @return string データ
     */
    public function csvForMultiple(array $values, bool $nl = false): string
    {
        $delimiter = $this->getConfig('multipleSeparator');
        $data = implode($delimiter, $values);

        if ($nl) {
            $data = $data . $delimiter;
        }

        return $data;
    }

    /**
     * 複数項目の文字列を配列にフォーマット
     *
     * @param string|null $values 項目の値
     * @return array データ
     */
    public function inputsForMultiple(?string $values): array
    {
        if (((string)$values) === '') {
            return [];
        }

        $delimiter = $this->getConfig('multipleSeparator');
        $data = explode($delimiter, (string)$values);

        return $data;
    }

    /**
     * 複数項目で利用する入力値の区切り文字を置換
     *
     * @param string|null $data 入力値
     * @return string|null 置換結果
     */
    public function replaceSeparetorForMultiple(?string $data): ?string
    {
        if (!isset($data)) {
            return null;
        }

        $delimiter = $this->getConfig('multipleSeparator');
        $replacement = $this->getConfig('multipleSeparatorReplace');
        $result = preg_replace('/' . preg_quote($delimiter, '/') . '/u', $replacement, $data);

        return $result;
    }

    /**
     * 複数行のデータのCSVデータをフォーマット
     *
     * @param array $values 項目の値
     * @return string データ
     */
    public function csvForHasMany(array $values): string
    {
        $data = implode($this->getConfig('hasManySeparator'), $values);

        return $data;
    }

    /**
     * 改行文字列を複数行データにフォーマット
     *
     * @param string|null $values 項目の値
     * @param int $nl 改行を含んだ行
     * @return array データ
     */
    public function inputsForHasMany(?string $values, int $nl = 0): array
    {
        if (((string)$values) === '') {
            return [];
        }

        $data = [];
        $delimiter = $this->getConfig('hasManySeparator');

        if ($nl > 0) {
            $multiDelimiter = $this->getConfig('multipleSeparator');
            $multiData = array_chunk(explode($multiDelimiter, (string)$values), $nl);
            foreach ($multiData as $key => $val) {
                $oneData = ltrim(implode($multiDelimiter, $val));
                if (!empty($oneData)) {
                    $data[$key] = $oneData;
                }
            }
        } else {
            $data = explode($delimiter, (string)$values);
        }

        return $data;
    }
}
