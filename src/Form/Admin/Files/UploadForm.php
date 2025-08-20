<?php
declare(strict_types=1);

namespace App\Form\Admin\Files;

use App\Form\AppForm;
use App\Locale\Message;
use App\Utility\FileUtility;
use App\Validation\CustomValidation;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * アップロードフォーム
 */
class UploadForm extends AppForm
{
    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('id', 'integer')
            ->addField('index', 'integer')
            ->addField('file', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'fileType' => Configure::readOrFail('Master.file.applyExt'),
            'maxFileSize' => Configure::readOrFail('Master.file.maxSize'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('id', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('id')
            ->add('id', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT),
                ],
            ]);

        $validator
            ->requirePresence('index', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('index', __(Message::ERROR_NOT_EMPTY), false)
            ->add('index', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'integer' => [
                    'rule' => ['integer', CustomValidation::BIGINT_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_OVER_DIGIT),
                ],
            ]);

        $validator
            ->requirePresence('file', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyFile('file', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('file', [
                'upload' => [
                    'rule' => ['uploadError'],
                    'last' => true,
                    'message' => __(Message::ERROR_UPLOAD),
                ],
                'type' => [
                    'rule' => ['uploadedFile', ['types' => $this->getFieldValueOptions('fileType')]],
                    'last' => true,
                    'message' => __(Message::ERROR_FILE_TYPE),
                ],
                'size' => [
                    'rule' => ['uploadedFile', ['maxSize' => $this->getFieldValueOptions('maxFileSize')]],
                    'last' => true,
                    'message' => __(
                        Message::ERROR_FILE_SIZE,
                        FileUtility::getFileSize($this->getFieldValueOptions('maxFileSize'), 'mb')
                    ),
                ],
            ]);

        return $validator;
    }

    /**
     * エラーメッセージを1次元化
     *
     * @return array
     */
    public function getErrors(): array
    {
        return Hash::flatten(parent::getErrors());
    }

    /**
     * トークンのバリデーターを追加
     *
     * @param string $tokenName トークン名
     * @return void
     */
    public function addTokenValidator(string $tokenName)
    {
        $validator = $this->getValidator();

        $validator
            ->requirePresence($tokenName, true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString($tokenName, __(Message::ERROR_NOT_EMPTY), false)
            ->add($tokenName, [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'last' => true,
                ],
                'alnum' => [
                    'rule' => ['alnum'],
                    'message' => __(Message::ERROR_ALPHA_NUMERIC),
                    'last' => true,
                ],
            ]);

        $this->setValidator('default', $validator);
    }
}
