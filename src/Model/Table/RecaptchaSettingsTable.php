<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Locale\Message;
use App\Model\AppTable;
use App\Model\Entity\RecaptchaSetting;
use ArrayObject;
use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Query;
use Cake\Utility\Hash;
use Cake\Validation\Validator;

/**
 * RecaptchaSettings Model
 *
 * @method \App\Model\Entity\RecaptchaSetting newEmptyEntity()
 * @method \App\Model\Entity\RecaptchaSetting newEntity(array $data, array $options = [])
 * @method \App\Model\Entity\RecaptchaSetting[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\RecaptchaSetting get($primaryKey, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting findOrCreate($search, ?callable $callback = null, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\RecaptchaSetting[] patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\RecaptchaSetting|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting[]|\Cake\Datasource\ResultSetInterface|false saveMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting[]|\Cake\Datasource\ResultSetInterface|false deleteMany(iterable $entities, $options = [])
 * @method \App\Model\Entity\RecaptchaSetting[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, $options = [])
 */
class RecaptchaSettingsTable extends AppTable
{
    /**
     * サイトキー：MAX
     */
    public const SITE_KEY_MAX = 100;

    /**
     * シークレットキー：MAX
     */
    public const SECRET_KEY_MAX = 100;

    /**
     * @var \App\Model\Entity\SiteSetting|null
     */
    protected $cacheData = null;

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->getBehavior('AdminOperationLog')->setConfig([
            'saveOperation' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function buildFieldValueOptions()
    {
        $fieldValueOptions = [
            'useFlg' => Configure::readOrFail('Master.recaptcha.useFlg'),
        ];

        return $fieldValueOptions;
    }

    /**
     * Model.afterSaveCommitイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function afterSaveCommit(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        $this->deleteCacheData();
    }

    /**
     * @inheritDoc
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->requirePresence('use_flg', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyString('use_flg', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('use_flg', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'inList' => [
                    'rule' => [
                        'inList',
                        array_keys($this->getFieldValueOptions('useFlg')),
                    ],
                    'last' => true,
                    'message' => __(Message::ERROR_IN_LIST),
                ],
            ]);

        $validator
            ->requirePresence('site_key', true, __(Message::ERROR_NOT_EMPTY))
            ->notEmptyString('site_key', __(Message::ERROR_NOT_EMPTY), function ($context) {
                if ((string)Hash::get($context, 'data.use_flg') !== (string)RecaptchaSetting::USE_FLG_ON) {
                    return false;
                }

                return true;
            })
            ->add('site_key', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SITE_KEY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, [static::SITE_KEY_MAX]),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
            ]);

        $validator
            ->requirePresence('secret_key', true, __(Message::ERROR_NOT_EMPTY))
            ->notEmptyString('secret_key', __(Message::ERROR_NOT_EMPTY), function ($context) {
                if ((string)Hash::get($context, 'data.use_flg') !== (string)RecaptchaSetting::USE_FLG_ON) {
                    return false;
                }

                return true;
            })
            ->add('secret_key', [
                'isScalar' => [
                    'rule' => ['isScalar'],
                    'last' => true,
                    'message' => __(Message::ERROR_INVALID_VALUE),
                ],
                'maxLength' => [
                    'rule' => ['maxLength', static::SECRET_KEY_MAX],
                    'last' => true,
                    'message' => __(Message::ERROR_MAX_LENGTH, [static::SECRET_KEY_MAX]),
                ],
                'alnumSym' => [
                    'rule' => ['alnumSym'],
                    'last' => true,
                    'message' => __(Message::ERROR_ALNUM_SYM),
                ],
            ]);

        return $validator;
    }

    /**
     * データを取得
     *
     * @return \App\Model\Entity\RecaptchaSetting|null データ
     */
    public function getData()
    {
        if (!isset($this->cacheData)) {
            $this->cacheData = Cache::remember('data', function () {
                $query = $this->find('default');

                return $query->first();
            }, 'recaptchaSettings');
        }

        return $this->cacheData;
    }

    /**
     * デフォルトのファインダー
     *
     * @param \Cake\ORM\Query $query The query to find with
     * @param array $options The options to use for the find
     * @return \Cake\ORM\Query The query builder
     */
    public function findDefault(Query $query, array $options)
    {
        $query->select([
            'id',
            'use_flg',
            'site_key',
            'secret_key',
        ]);

        return $query;
    }

    /**
     * キャッシュファイル削除
     *
     * @return void
     */
    public function deleteCacheData()
    {
        Cache::delete('data', 'recaptchaSettings');
        $this->cacheData = null;
    }
}
