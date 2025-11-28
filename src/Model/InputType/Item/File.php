<?php
declare(strict_types=1);

namespace App\Model\InputType\Item;

use App\Locale\Message;
use App\Model\InputType\AbstractInputTypeItem;
use App\Model\InputType\Item\Type\AdditionTypeInterface;
use App\Model\InputType\Item\Type\AdditionTypeTrait;
use App\Model\InputType\Item\Type\CsvOutputInterface;
use App\Model\InputType\Item\Type\CsvOutputTrait;
use App\Model\InputType\Item\Type\FileUploadTrait;
use App\Model\InputType\Item\Type\InputInterface;
use App\Model\InputType\Item\Type\InputTrait;
use App\Model\InputType\Item\Type\MailOutputInterface;
use App\Model\InputType\Item\Type\MailOutputTrait;
use App\Model\InputType\Item\Type\SearchDisplayInterface;
use App\Model\InputType\Item\Type\SearchDisplayTrait;
use App\Utility\ArrayUtility;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * File class.
 */
class File extends AbstractInputTypeItem implements
    AdditionTypeInterface,
    CsvOutputInterface,
    SearchDisplayInterface,
    InputInterface,
    MailOutputInterface,
    Type\FileUploadInterface
{
    use AdditionTypeTrait;
    use CsvOutputTrait;
    use FileUploadTrait;
    use InputTrait;
    use MailOutputTrait;
    use SearchDisplayTrait;

    /**
     * @inheritDoc
     */
    public function buildFieldsetValidator(Validator $validator)
    {
        $isRequired = $this->getFormItem()->isRequiredItem() && !$this->isAdmin();

        $validator
            ->requirePresence($this->getFieldsetColumn(), $isRequired, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString($this->getFieldsetColumn(), __(Message::ERROR_NOT_EMPTY_SELECT), !$isRequired)
            ->add($this->getFieldsetColumn(), [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function getDetailValue(?array $options = null)
    {
        $values = $this->getAdditionValue($options);
        if (!isset($values)) {
            return null;
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchQuery(Query $query, array $inputs)
    {
        $values = Hash::get($inputs, $this->getSearchInputKey());

        /** @var \App\Model\Table\ReservationAdditionsTable $reservationAdditionsTable */
        $reservationAdditionsTable = $this->getTableLocator()->get('ReservationAdditions');

        if (!empty($values)) {
            $on = ArrayUtility::inArray(
                Configure::readOrFail('Master.common.flg.on'),
                (array)$values
            );
            $off = ArrayUtility::inArray(
                Configure::readOrFail('Master.common.flg.off'),
                (array)$values
            );
            if ($on && $off) {
                return $query;
            }

            $additionQuery = $reservationAdditionsTable->find();
            $additionQuery->select(['reservation_id']);
            $additionQuery->where([
                'ReservationAdditions.form_item_id' => $this->getFormItem()->get('id'),
            ]);

            if ($on) {
                $query->where([
                    'Reservations.id IN' => $additionQuery,
                ]);
            } elseif ($off) {
                $query->where([
                    'Reservations.id NOT IN' => $additionQuery,
                ]);
            }
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public function filterInputs(array $inputs)//: array
    {
        if (!$this->canInput()) {
            return $inputs;
        }

        return $inputs;
    }

    /**
     * @inheritDoc
     */
    public function buildSearchValidator(Validator $validator)
    {
        $validator
            ->requirePresence($this->getSearchInputKey(), false)
            ->allowEmptyString($this->getSearchInputKey())
            ->add($this->getSearchInputKey(), [
                'isArray' => [
                    'rule' => ['isArray'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'multiple' => [
                    'rule' => ['multiple', [
                        'in' => array_keys($this->getValueOptions()),
                    ]],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    public function valueToDatabase($value)
    {
        return $value;
    }

    /**
     * ファイル添付におけるAjaxControllerのURLを取得
     *
     * @return array URL
     */
    public function getUploadAjaxUrl()
    {
        $reservationId = null;
        $reservation = $this->getReservationEntity();
        if (isset($reservation)) {
            $reservationId = $reservation->get('id');
        }

        if ($this->isAdmin()) {
            $url = [
                'prefix' => 'Admin/Ajax',
                'controller' => 'Reservations',
                'action' => 'uploadFile',
                '?' => [
                    'id' => $reservationId,
                ],
            ];
        } else {
            $url = [
                'prefix' => 'User/Ajax',
                'controller' => 'Reservations',
                'action' => 'uploadFile',
                '?' => [
                    'id' => $reservationId,
                ],
            ];
        }

        return $url;
    }

    /**
     * ファイルの削除URLを取得
     *
     * @return array URL
     */
    public function getDeleteAjaxUrl()
    {
        $reservationId = null;
        $reservation = $this->getReservationEntity();
        if (isset($reservation)) {
            $reservationId = $reservation->get('id');
        }

        if ($this->isAdmin()) {
            if ($this->hasTempFile()) {
                $url = [
                    'prefix' => 'Admin/Ajax',
                    'controller' => 'Reservations',
                    'action' => 'deleteTmpFile',
                    '?' => [
                        'form_item_id' => $this->getFormItem()->get('id'),
                        'id' => $reservationId ?? null,
                    ],
                ];
            } else {
                $url = [
                    'prefix' => 'Admin/Ajax',
                    'controller' => 'Reservations',
                    'action' => 'deleteSavedFile',
                    '?' => [
                        'id' => $reservationId,
                        'form_item_id' => $this->getFormItem()->get('id'),
                    ],
                ];
            }
        } else {
            if ($this->hasTempFile()) {
                $url = [
                    'prefix' => 'User/Ajax',
                    'controller' => 'Reservations',
                    'action' => 'deleteTmpFile',
                    '?' => [
                        'form_item_id' => $this->getFormItem()->get('id'),
                        'id' => $reservationId ?? null,
                    ],
                ];
            } else {
                $url = [
                    'prefix' => 'User/Ajax',
                    'controller' => 'Reservations',
                    'action' => 'deleteSavedFile',
                    '?' => [
                        'id' => $reservationId,
                        'form_item_id' => $this->getFormItem()->get('id'),
                    ],
                ];
            }
        }

        return $url;
    }

    /**
     * 一時ファイルの存在判定
     *
     * @return bool
     */
    public function hasTempFile()
    {
        if (!array_filter($this->fileSession) || isset($this->fileSession['delete'])) {
            return false;
        }

        return true;
    }

    /**
     * 保存済みファイルの存在判定
     *
     * @return bool
     */
    public function hasSavedFile()
    {
        $reservation = $this->getReservationEntity();
        if (!isset($reservation)) {
            return false;
        }

        $oldAdditionValues = $reservation->get('reservation_additions');

        if ($oldAdditionValues) {
            $formItemId = $this->getFormItem()->get('id');
            foreach ($oldAdditionValues as $key => $entity) {
                if ($entity->get('form_item_id') === $formItemId) {
                    if ($entity->get('value')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * 保存済みファイルのURLを取得
     *
     * @return array URL
     */
    public function getSavedFileUrl()
    {
        if (!$this->hasSavedFile()) {
            throw new CakeException();
        }

        $reservationId = null;
        $reservation = $this->getReservationEntity();
        if (isset($reservation)) {
            $reservationId = $reservation->get('id');
        }

        if ($this->isAdmin()) {
            $url = [
                'prefix' => 'Admin',
                'controller' => 'AttachmentFile',
                'action' => 'downloadSavedFile',
                '?' => [
                    'id' => $reservationId,
                    'form_item_id' => $this->getFormItem()->get('id'),
                ],
            ];
        } else {
            $url = [
                'prefix' => 'User',
                'controller' => 'AttachmentFile',
                'action' => 'downloadSavedFile',
                '?' => [
                    'id' => $reservationId,
                    'form_item_id' => $this->getFormItem()->get('id'),
                ],
            ];
        }

        return $url;
    }

    /**
     * 一時ファイルのURLを取得
     *
     * @return array URL
     */
    public function getTempFileUrl()
    {
        $url = [];
        $formItemId = '';
        if (!$this->hasTempFile()) {
            throw new CakeException();
        }

        $reservationId = null;
        $reservation = $this->getReservationEntity();
        if (isset($reservation)) {
            $reservationId = $reservation->get('id');
        }

        if ($this->formItem !== null) {
            $formItemId = $this->formItem->get('id');
        }

        if ($this->isAdmin()) {
            $url = [
                'prefix' => 'Admin',
                'controller' => 'AttachmentFile',
                'action' => 'downloadFile',
                '?' => [
                    'file' => $this->fileSession[$formItemId],
                    'id' => $reservationId,
                ],
            ];
        } else {
            $url = [
                'prefix' => 'User',
                'controller' => 'AttachmentFile',
                'action' => 'downloadFile',
                '?' => [
                    'file' => $this->fileSession[$formItemId],
                    'id' => $reservationId,
                ],
            ];
        }

        return $url;
    }

    /**
     * 添付ファイルの名称を取得
     *
     * @return string|null
     */
    public function getAttachedFileName()
    {
        $name = null;
        $formItemId = '';

        if ($this->formItem !== null) {
            $formItemId = $this->formItem->get('id');
        }
        if ($this->hasTempFile()) {
            $name = $this->fileSession[$formItemId]['original_file_name'];
        } elseif ($this->hasSavedFile()) {
            $reservation = $this->getReservationEntity();
            if ($reservation !== null) {
                $oldAdditionValues = $reservation->get('reservation_additions');
                $formItemId = $this->getFormItem()->get('id');
                foreach ($oldAdditionValues as $key => $entity) {
                    if ($entity->get('form_item_id') === $formItemId) {
                        if ($entity->get('value')) {
                            $name = $entity->get('value');
                        }
                    }
                }
            }
        }

        return $name;
    }

    /**
     * 削除判定セッションを削除
     *
     * @return void
     */
    public function deleteTmpFileSession()
    {
        $formItemId = '';
        if ($this->formItem !== null) {
            $formItemId = $this->formItem->get('id');
        }
        unset($this->fileSession[(string)$formItemId]);
    }

    /**
     * 削除の判定
     *
     * @return bool
     */
    public function isDeleting()
    {
        if (!isset($this->fileSession['delete'])) {
            return false;
        }

        return true;
    }

    /**
     * ファイルセッションに削除判定を追加
     *
     * @return void
     */
    public function setDeleteFileSession()
    {
        $formItemId = '';
        if ($this->formItem !== null) {
            $formItemId = $this->formItem->get('id');
        }
        if ($formItemId) {
            $this->fileSession['delete'] = [$formItemId];
        }
    }

    /**
     * @inheritDoc
     */
    public function getCsvOutputValue(?array $options = null)
    {
        return $this->getDetailValue($options);
    }

    /**
     * @inheritDoc
     */
    public function getMailReplaceToken()
    {
        return $this->getAdditionMailReplaceToken();
    }

    /**
     * @inheritDoc
     */
    public function getMailOutputValue(array $data, ?array $options = null)
    {
        return $this->getDetailValue($data);
    }

    /**
     * 選択肢を取得
     *
     * @return array 選択肢
     */
    public function getValueOptions()
    {
        $options = [];
        foreach (Configure::readOrFail('Master.attachedFile.search') as $key => $value) {
            $options[Configure::readOrFail('Master.common.flg.' . $key)] = $value;
        }

        return $options;
    }
}
