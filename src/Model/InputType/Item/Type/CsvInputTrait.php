<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

use Cake\Core\Configure;
use Cake\Utility\Hash;

/**
 * CsvInput trait.
 */
trait CsvInputTrait
{
    /**
     * CSVのエラーメッセージをフォーマット
     *
     * @param array $errors エラー
     * @return array エラーメッセージ
     */
    public function formatCsvErrors(array $errors)
    {
        $messages = $this->getCsvErrorMessages($errors);
        if (empty($messages)) {
            return [];
        }

        $name = '[' . $this->getFormItem()->get('id') . '] ' . $this->getFormItem()->get('name');
        $separator = Configure::readOrFail('Setting.csv.import.error.separator');
        $delimiter = Configure::readOrFail('Setting.csv.import.error.delimiter');
        $result[] = $name . $separator . implode($delimiter, Hash::flatten($messages));

        return $result;
    }

    /**
     * CSVのエラーメッセージを取得
     *
     * @param array $errors エラー
     * @return array エラーメッセージ
     */
    abstract protected function getCsvErrorMessages(array $errors): array;
}
