<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\Admin;
use App\Validation\MailValidation;
use Cake\Core\Configure;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * AdminMails Model
 *
 * @method \App\Model\Entity\AdminMail newEmptyEntity()
 * @method \App\Model\Entity\AdminMail newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\AdminMail[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\AdminMail get($primaryKey, $options = [])
 * @method \App\Model\Entity\AdminMail findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\AdminMail patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\AdminMail[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\AdminMail|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminMail saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\AdminMail[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminMail[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminMail[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\AdminMail[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class AdminMailsTable extends AppTable
{
    public const MAIL_MAX = 254;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->belongsTo('Admins', [
            'foreignKey' => 'admin_id',
            'joinType' => 'INNER',
        ]);
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('mail', true, __(Message::ERROR_NOT_EMPTY))
            ->allowEmptyString('mail', __(Message::ERROR_NOT_EMPTY), false)
            ->add('mail', MailValidation::getMailValidator());

        return $validator;
    }

    /**
     * @inheritDoc
     */
    protected function searchConfiguration()
    {
        //公開側予約履歴用
        $this->searchManager()->useCollection('mail');
        $this->searchManager()
            ->value('adminId', [
                'fields' => 'admin_id',
            ]);
    }

    /**
     * メールアドレス取得ファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findMails(Query $query, array $options)
    {
        $query->select(['id', 'mail']);

        return $this->callFinder('search', $query, ['search' => $options, 'collection' => 'mail']);
    }

    /**
     * 自動返信メール送信時のファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findAutoReplyMail(Query $query, array $options)
    {
        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $query->select([
            'id',
            'mail',
        ]);
        $query->join([
            'table' => 'admins',
            'alias' => 'Admins',
            'type' => 'INNER',
            'conditions' => [
                'Admins.id = AdminMails.admin_id',
            ],
        ]);
        $query->where([
            'Admins.system_admin_flg' => Admin::SYSTEM_ADMIN_FLG_OFF,
        ]);

        $labelIds = Hash::get($options, 'inputs.label_id');
        if (is_scalar($labelIds) && ((string)$labelIds !== '') || is_array($labelIds) && !empty($labelIds)) {
            $labelColumn = [];
            $limit = Configure::readOrFail('Setting.label.depth');
            foreach (array_values((array)$labelIds) as $index => $labelId) {
                $query->join([
                    'table' => 'labels',
                    'alias' => 'labels_' . $index . '_0',
                    'type' => 'LEFT',
                    'conditions' => [
                        'labels_' . $index . '_0.id' => $labelId,
                    ],
                ]);
                $query = $labelsTable->joinQuery($query, 'labels_' . $index . '_0', [], (string)$index);

                for ($i = 0; $i < $limit; ++$i) {
                    $labelColumn[] = 'labels_' . $index . '_' . $i . '.id';
                }
            }

            $query->where([
                'OR' => [
                    'Admins.label_id IS NULL',
                    'Admins.label_id IN (' . implode(',', $labelColumn) . ')',
                ],
            ]);
        }

        $query->order([
            'Admins.id' => 'ASC',
            'AdminMails.id' => 'ASC',
        ]);

        return $query;
    }
}
