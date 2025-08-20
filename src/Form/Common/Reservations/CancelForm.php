<?php
declare(strict_types=1);

namespace App\Form\Common\Reservations;

use App\Form\AppForm;
use App\Form\Common\CommonFormTrait;
use App\Model\Entity\Reservation;

/**
 * キャンセルフォーム
 */
abstract class CancelForm extends AppForm
{
    use CommonFormTrait;

    /**
     * @var \App\Model\Entity\Reservation
     */
    protected $entity = null;

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = parent::validate($data);
        if (!$this->entity->canCancel()) {
            $result = false;
        }

        return $result;
    }

    /**
     * エンティティーを取得
     *
     * @return \App\Model\Entity\Reservation|null エンティティー
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * エンティティーを設定
     *
     * @param \App\Model\Entity\Reservation $entity エンティティー
     * @return void
     */
    public function setEntity(Reservation $entity)
    {
        $this->entity = $entity;
    }
}
