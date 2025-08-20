<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Inquiry;
use App\Validation\MailValidation;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Mailer\MailerAwareTrait;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * Inquiries Model
 *
 * @method \App\Model\Entity\Inquiry newEmptyEntity()
 * @method \App\Model\Entity\Inquiry newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\Inquiry[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Inquiry get($primaryKey, $options = [])
 * @method \App\Model\Entity\Inquiry findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\Inquiry patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\Inquiry[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Inquiry|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Inquiry saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\Inquiry[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Inquiry[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\Inquiry[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\Inquiry[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class InquiriesTable extends AppTable
{
    use MailerAwareTrait;

    public const NAME_MAX = 100;
    public const CONTENTS_MAX = 50000;
    public const MAIL_MAX = 254;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }

    /**
     * beforeSave callback.
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved
     * @param \ArrayObject $options The options for the query
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, $options)
    {
        $entity->set('user_id', Hash::get($options, 'userId', null));
        $entity->set('mail_send_flg', Inquiry::MAIL_SEND_FLG_ON);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        if (!$this->commonData()->existsUserLoginData()) {
            $validator->requirePresence('name', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('name', __(Message::ERROR_NOT_EMPTY), false)
                ->add('name', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'maxLength' => [
                        'rule' => ['maxLength', static::NAME_MAX],
                        'last' => true,
                        'message' => __(Message::ERROR_MAX_LENGTH, static::NAME_MAX),
                    ],
                ]);

            $validator->requirePresence('phone_number', true, __(Message::ERROR_NOT_EMPTY))
                ->allowEmptyString('phone_number')
                ->add('phone_number', [
                    'isScalar' => [
                        'rule' => ['isScalar'],
                        'last' => true,
                        'message' => __(Message::ERROR_INVALID_VALUE),
                    ],
                    'phoneNumberWithHyphen' => [
                        'rule' => ['phoneNumberWithHyphen'],
                        'last' => true,
                        'message' => __(Message::ERROR_PHONE_NUMBER_HYPHEN),
                    ],
                ]);
        }

        $validator
            ->requirePresence('mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('mail', MailValidation::getMailValidator());

        $validator
            ->requirePresence('mail_confirm', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('mail_confirm', __(Message::ERROR_NOT_EMPTY), false)
            ->add('mail_confirm', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'message' => __(Message::ERROR_INVALID_VALUE),
                    'last' => true,
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::MAIL_MAX],
                    'message' => __(Message::ERROR_MAX_LENGTH, static::MAIL_MAX),
                    'last' => true,
                ],
                'compareWith' => [
                    'rule' => ['compareWith', 'mail'],
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_SAME),
                ],
            ]);

        $validator->requirePresence('contents', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('contents', __(Message::ERROR_NOT_EMPTY), false)
            ->add('contents', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::CONTENTS_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, static::CONTENTS_MAX),
                ],
            ]);

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function buildDefaultFieldValues()
    {
        $default = [];
        if ($this->commonData()->existsUserLoginData()) {
            /** @var \Cake\Datasource\EntityInterface $user */
            $user = $this->commonData()->getUserLoginData();
            $default['mail'] = $user->get('mail');
            $default['mail_confirm'] = $user->get('mail');
        }

        return $default;
    }

    /**
     * お問い合わせ
     *
     * @param \Cake\Datasource\EntityInterface $entity お問い合わせ情報
     * @param array $options option
     * @return bool
     */
    public function inquirySend(EntityInterface $entity, array $options = [])
    {
        $result = $this->getConnection()->transactional(function () use ($entity, $options) {
            $result = $this->save($entity, $options);

            if (!$result) {
                return false;
            }

            /** @var \App\Model\Table\AdminMailsTable $adminMails */
            $adminMails = $this->getTableLocator()->get('AdminMails');
            $mails = $adminMails->find('mails')->all()->combine('id', 'mail');
            foreach ($mails as $mail) {
                $result = $this->getMailer('Admin')->send('inquiry', [$entity, $mail]);
            }

            return $result;
        });

        return $result;
    }
}
