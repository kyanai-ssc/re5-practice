<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\AppForm;
use App\Locale\Message;
use App\Model\Entity\FormItem;
use App\Model\Entity\Reservation;
use App\Model\InputType\Item\Type as ItemType;
use App\Model\InputType\Item\Type\FileUploadTrait;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Utility\Hash;
use Cake\Validation\Validation;
use Cake\Validation\Validator;

/**
 * ファイルアップロードフォーム
 */
class FileUploadForm extends AppForm
{
    use FileUploadTrait;
    use FileTrait;

    /**
     * @var \App\Model\Entity\FormItem|null
     */
    protected $formItem = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('file', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'fileType' => Configure::readOrFail('Setting.file.extension'),
            'maxFileSize' => Configure::readOrFail('Setting.file.maxFileSize'),
        ];

        return $fieldValueOptions;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('file', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyFile('file', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('file', [
                'upload' => [
                    'rule' => ['uploadError'],
                    'last' => true,
                    'message' => __(Message::ERROR_UPLOAD),
                ],
                'extension' => [
                    'rule' => ['extension', Configure::readOrFail('Setting.file.extension')],
                    'last' => true,
                    'message' => __(Message::ERROR_FILE_TYPE),
                ],
                'fileSize' => [
                    'rule' => [
                        'fileSize',
                        Validation::COMPARE_LESS_OR_EQUAL,
                        Configure::readOrFail('Setting.file.maxFileSize') * 1024 * 1024,
                    ],
                    'last' => true,
                    'message' => __(
                        Message::ERROR_FILE_SIZE,
                        Configure::readOrFail('Setting.file.maxFileSize')
                    ),
                ],
                'mimeType' => [
                    'rule' => ['mimeType', Configure::readOrFail('Setting.file.mimeType')],
                    'last' => true,
                    'message' => __(Message::ERROR_FILE_TYPE),
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

    /**
     * フォーム項目を取得
     *
     * @return \App\Model\Entity\FormItem
     */
    public function getFormItem(): FormItem
    {
        if (!isset($this->formItem)) {
            throw new CakeException();
        }

        return $this->formItem;
    }

    /**
     * フォーム項目を設定
     *
     * @param \App\Model\Entity\FormItem $formItem フォーム項目
     * @return void
     */
    public function setFormItem(FormItem $formItem): void
    {
        $this->formItem = $formItem;
    }

    /**
     * ファイルのセッションデータを設定
     *
     * @param array|null $fileSession セッションデータ
     * @return void
     */
    public function setFileSession(?array $fileSession): void
    {
        $inputTypeItem = $this->getFormItem()->getInputTypeItem();
        if (!($inputTypeItem instanceof ItemType\FileUploadInterface)) {
            throw new CakeException();
        }
        $inputTypeItem->setFileSession($fileSession);
    }

    /**
     * 予約のエンティティーを設定
     *
     * @param \App\Model\Entity\Reservation|null $reservation エンティティー
     * @return void
     */
    public function setReservationEntity(?Reservation $reservation)
    {
        if (isset($reservation)) {
            $this->getFormItem()->getInputTypeItem()->setReservationEntity($reservation);
        }
    }
}
