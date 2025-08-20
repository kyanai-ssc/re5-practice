<?php
declare(strict_types=1);

namespace App\Form\Admin\Reservations;

use App\Form\Common\Reservations\CancelForm as CommonCancelForm;
use App\Locale\Message;
use Cake\Core\Configure;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * キャンセルフォーム
 */
class CancelForm extends CommonCancelForm
{
    /**
     * @var bool
     */
    protected $adminFlg = true;

    /**
     * @inheritDoc
     */
    protected function _buildSchema(Schema $schema): Schema
    {
        $schema
            ->addField('mail_check', 'string');

        return $schema;
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('mail_check', false)
            ->allowEmptyString('mail_check')
            ->add('mail_check', [
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
}
