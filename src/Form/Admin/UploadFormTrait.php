<?php
declare(strict_types=1);

namespace App\Form\Admin;

use App\Locale\Message;
use App\Model\ImportableTableInterface;
use App\Utility\FileUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * アップロード
 */
trait UploadFormTrait
{
    /**
     * アップロード用のスキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema スキーマ
     */
    protected function buildUploadSchema(Schema $schema)
    {
        $schema
            ->addField('file', 'string');

        return $schema;
    }

    /**
     * アップロード用のバリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return \Cake\Validation\Validator バリデータ
     */
    protected function buildUploadValidator(Validator $validator, array $options = [])
    {
        $validator
            ->requirePresence('file', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyFile('file', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('file', [
                'upload' => [
                    'rule' => ['uploadError', false],
                    'last' => true,
                    'message' => __(Message::ERROR_UPLOAD),
                ],
                'size' => [
                    'rule' => [
                        'fileSize',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        $this->getFieldValueOptions('maxFileSize'),
                    ],
                    'last' => true,
                    'message' => __(
                        Message::ERROR_FILE_SIZE,
                        FileUtility::getFileSize($this->getFieldValueOptions('maxFileSize'), 'mb')
                    ),
                ],
                'isCsv' => [
                    'rule' => ['extension', ['csv']],
                    'last' => true,
                    'message' => __(Message::ERROR_FILE_TYPE),
                ],
                'type' => [
                    'rule' => ['mimeType', $this->getFieldValueOptions('fileType')],
                    'last' => true,
                    'message' => __(Message::ERROR_FILE_TYPE),
                ],
            ]);

        return $validator;
    }

    /**
     * アップロード用の値リストを生成
     *
     * @return array 値リスト
     */
    protected function buildUploadFieldValueOptions()
    {
        $fieldValueOptions = [
            'fileType' => Configure::readOrFail('Setting.csv.import.applyExt'),
            'maxFileSize' => Configure::readOrFail('Setting.csv.import.maxSize'),
        ];

        return $fieldValueOptions;
    }

    /**
     * インポートの実行中チェック
     *
     * @param \Cake\ORM\Table $table テーブル
     * @return bool
     */
    protected function checkRunningImport($table)
    {
        if (!($table instanceof ImportableTableInterface)) {
            throw new CakeException();
        }

        $result = true;
        if (!$table->tryLockForImport()) {
            $this->setErrors([
                'check' => __(Message::ERROR_RUNNING_IMPORT),
            ]);
            $result = false;
        }

        return $result;
    }
}
