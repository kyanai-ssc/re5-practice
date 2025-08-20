<?php
declare(strict_types=1);

namespace App\Form\Admin;

use App\Locale\Message;
use Cake\Form\Schema;
use Cake\Validation\Validator;

/**
 * 一括削除フォーム
 */
trait DeleteManyFormTrait
{
    /**
     * @var array
     */
    protected $deleteChecked = [];

    /**
     * チェック情報を保持
     *
     * @param array $checked チェック情報
     * @return void
     */
    public function setDeleteChecked(array $checked)
    {
        $this->deleteChecked = $checked;
    }

    /**
     * チェック情報を返却
     *
     * @return array
     */
    public function gerDeleteChecked()
    {
        return $this->deleteChecked;
    }

    /**
     * スキーマを生成
     *
     * @param \Cake\Form\Schema $schema スキーマ
     * @return \Cake\Form\Schema スキーマ
     */
    protected function buildDeleteManySchema(Schema $schema)
    {
        $schema
            ->addField('checked', 'string');

        return $schema;
    }

    /**
     * バリデータを生成
     *
     * @param \Cake\Validation\Validator $validator バリデータ
     * @param array $options オプション
     * @return \Cake\Validation\Validator バリデータ
     */
    protected function buildDeleteManyValidator(Validator $validator, array $options = [])
    {
        $validator
            ->requirePresence('checked', true, __(Message::ERROR_NOT_EMPTY_SELECT))
            ->allowEmptyFile('checked', __(Message::ERROR_NOT_EMPTY_SELECT), false)
            ->add('checked', [
                'isSame' => [
                    'rule' => function ($value) {
                        if ($value !== json_encode($this->gerDeleteChecked())) {
                            return false;
                        }

                        return true;
                    },
                    'last' => true,
                    'message' => __(Message::ERROR_NOT_SAME),
                ],
            ]);

        return $validator;
    }
}
