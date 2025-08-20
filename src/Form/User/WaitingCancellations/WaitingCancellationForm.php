<?php
declare(strict_types=1);

namespace App\Form\User\WaitingCancellations;

use App\Form\AppForm;
use Cake\Form\Schema;
use Cake\Utility\Hash;

/**
 * キャンセル待ち通知フォーム
 */
class WaitingCancellationForm extends AppForm
{
    /**
     * @var array
     */
    protected $parameter = null;

    /**
     * @var \App\Model\Entity\Event
     */
    protected $eventEntity = null;

    /**
     * @var \App\Model\Entity\WaitingCancellation|null
     */
    protected $waitingCancellationEntity = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('user_id', 'string')
            ->addField('event_id', 'string')
            ->addField('usage_timestamp', 'string')
            ->addField('mail', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $result = parent::validate($data);

        $this->createEntity($this->getData());
        if (!empty($this->getErrors())) {
            $result = false;
        }

        return $result;
    }

    /**
     * パラメータを取得
     *
     * @param string|null $key キー
     * @return mixed
     */
    public function getParameter(?string $key = null)
    {
        if (!isset($key)) {
            return $this->parameter;
        }

        return Hash::get($this->parameter, $key);
    }

    /**
     * パラメータを設定
     *
     * @param array $parameter パラメータ
     * @return void
     */
    public function setParameter(array $parameter)
    {
        $this->parameter = $parameter;
    }

    /**
     * パラメータを検証
     *
     * @return bool
     */
    public function validateParameter()
    {
        /** @var \App\Model\Table\WaitingCancellationsTable $waitingCancellationsTable */
        $waitingCancellationsTable = $this->getTableLocator()->get('WaitingCancellations');

        $parametersData = $waitingCancellationsTable->getParametersData($this->getParameter());
        if (isset($parametersData['errors'])) {
            $this->setErrors($parametersData['errors']);

            return false;
        }

        $this->setParameter($parametersData['parameters']);
        $this->eventEntity = $parametersData['event'];

        return true;
    }

    /**
     * 予約枠を取得
     *
     * @return \App\Model\Entity\Event
     */
    public function getEventEntity()
    {
        return $this->eventEntity;
    }

    /**
     * キャンセル待ち通知を取得
     *
     * @return \App\Model\Entity\WaitingCancellation|null
     */
    public function getWaitingCancellationEntity()
    {
        return $this->waitingCancellationEntity;
    }

    /**
     * エンティティを生成
     *
     * @param array $data データ
     * @return void
     */
    protected function createEntity($data)
    {
        /** @var \App\Model\Table\WaitingCancellationsTable $waitingCancellationsTable */
        $waitingCancellationsTable = $this->getTableLocator()->get('WaitingCancellations');

        $inputs = $this->getParameter() + $data;
        $this->waitingCancellationEntity = $waitingCancellationsTable->newEntity($inputs);
        if ($this->waitingCancellationEntity->hasErrors()) {
            $this->setErrors($this->waitingCancellationEntity->getErrors());
        }
    }
}
