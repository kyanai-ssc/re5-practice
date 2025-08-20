<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Admin\DeleteManyFormTrait;
use App\Form\AppForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 一括削除フォーム
 */
class DeleteManyForm extends AppForm
{
    use DeleteManyFormTrait;

    /**
     * @var bool|null
     */
    protected $hasVideoMeeting = null;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema = $this->buildDeleteManySchema($schema);
        $schema
            ->addField('waiting_cancellation', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator = $this->buildDeleteManyValidator($validator);
        $validator
            ->requirePresence('waiting_cancellation', false)
            ->allowEmptyString('waiting_cancellation')
            ->add('waiting_cancellation', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => ['inList', Configure::readOrFail('Master.common.flg')],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        return $validator;
    }

    /**
     * ビデオ会議連携の削除可能チェック
     *
     * @return bool
     */
    public function canDeleteVideoMeeting()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');
        /** @var \App\Model\Table\ReservationsTable $reservationsTable */
        $reservationsTable = $this->getTableLocator()->get('Reservations');

        if (!$systemSettingsTable->getData()->canCoordinateVideoMeeting()) {
            $this->hasVideoMeeting = false;

            return true;
        }

        $this->hasVideoMeeting = $reservationsTable->find('hasVideoMeeting', $this->deleteChecked)->count() > 0;
        if (!$this->hasVideoMeeting) {
            return true;
        }

        $count = $reservationsTable->find('checkReservations', $this->deleteChecked)->count();
        if ($count > Configure::readOrFail('Setting.videoMeeting.apiLimit')) {
            return false;
        }

        return true;
    }

    /**
     * ビデオ会議連携の存在チェック
     *
     * @return bool
     */
    public function hasVideoMeeting()
    {
        if (!isset($this->hasVideoMeeting)) {
            throw new CakeException();
        }

        return $this->hasVideoMeeting;
    }
}
