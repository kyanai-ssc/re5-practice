<?php
declare(strict_types=1);

namespace App\Model\Entity\Traits;

use App\Model\Entity\FormPatternDisplayType;
use App\Model\Entity\Reservation;
use App\Model\Entity\User;

/**
 * Addition trait.
 */
trait AdditionValuesTrait
{
    /**
     * @var array
     */
    protected $formPatternDisplayTypes = [];

    /**
     * 追加項目の値を生成
     *
     * @param string $additionTableName 追加項目のテーブル
     * @param string $jsonValue 追加項目のJSON
     * @return array
     */
    protected function createAdditionValues($additionTableName, $jsonValue = null)
    {
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $additionTable = $this->getTableLocator()->get($additionTableName);

        if (!is_string($jsonValue)) {
            $values = [];
            foreach ((array)$this->get($additionTable->getTable()) as $addition) {
                $values['item_' . $addition->get('form_item_id')] = $addition->get('data');
            }

            return $values;
        }

        $additionValues = json_decode($jsonValue, true);
        if (!is_array($additionValues)) {
            return [];
        }
        $additionValues = array_filter($additionValues, function ($item) {
            return !empty($item['form_item_id']);
        });

        $additionValues = array_map(function ($item) use ($formItemsTable) {
            $formItem = $formItemsTable->getFormItem($item['form_item_id']);
            if (!isset($formItem)) {
                return $item['value'];
            }
            $item['value'] = $formItem->getInputTypeItem()->valueToPHP($item['value']);

            return $item;
        }, $additionValues);

        $result = [];
        foreach ($additionValues as $value) {
            $result['item_' . $value['form_item_id']] = $value['value'];
        }

        return $result;
    }

    /**
     * 追加項目のエンティティを生成
     *
     * @param string $additionTableName 追加項目のテーブル
     * @param mixed $additionData データ
     * @return array
     */
    protected function createAdditionEntity($additionTableName, $additionData)
    {
        $additionTable = $this->getTableLocator()->get($additionTableName);
        /** @var \App\Model\Table\FormItemsTable $formItemsTable */
        $formItemsTable = $this->getTableLocator()->get('FormItems');

        $entities = [];
        foreach ((array)$this->get($additionTable->getTable()) as $addition) {
            $entities[$addition->get('form_item_id')] = $addition;
        }

        if (is_array($additionData)) {
            foreach ($additionData as $formItemKey => $additionValue) {
                $formItemId = preg_replace('/^item_/', '', $formItemKey);
                if (
                    isset($additionValue) && $additionValue !== '' && $formItemsTable->validatePrimaryKey($formItemId)
                ) {
                    $additionData = [
                        'form_item_id' => $formItemId,
                        'data' => $additionValue,
                    ];
                    if (!isset($entities[$formItemId])) {
                        $entities[$formItemId] = $additionTable->newEntity($additionData, ['validate' => false]);
                    } else {
                        $additionTable->patchEntity($entities[$formItemId], $additionData, ['validate' => false]);
                    }
                } else {
                    unset($entities[$formItemId]);
                }
            }
        }

        return array_values($entities);
    }

    /**
     * 使用されているフォーム項目かどうかを判定
     *
     * @param int $formItemId フォーム項目ID
     * @return bool
     */
    protected function isUsedFormItem($formItemId)
    {
        /** @var \App\Model\Table\FormPatternDisplayTypesTable $formPatternDisplayTypesTable */
        $formPatternDisplayTypesTable = $this->getTableLocator()->get('FormPatternDisplayTypes');

        if (empty($this->formPatternDisplayTypes)) {
            if ($this instanceof Reservation) {
                /** @var \App\Model\Entity\Event $event */
                $event = $this->getEventEntity();

                // 表示パターン表示タイプ取得
                // 変更前ではなく変更後の予約枠IDを使用するために
                // $this->get('event_id') ではなく $this->getEventEntity()->get('id') を使用している
                $this->formPatternDisplayTypes = $formPatternDisplayTypesTable->find('formCreating', [
                    'inputs' => [
                        'event_id' => $event->get('id'),
                    ],
                ])->toArray();
            }
            if ($this instanceof User) {
                /** @var \App\Model\Entity\UserAuthority $userAuthority */
                $userAuthority = $this->getUserAuthorityEntity();

                $this->formPatternDisplayTypes = $formPatternDisplayTypesTable->find('formCreating', [
                    'inputs' => [
                        'user_authority_id' => $userAuthority->get('id'),
                    ],
                ])->toArray();
            }
        }
        $formPatternDisplayTypes = $this->formPatternDisplayTypes;

        // 表示パターンで設定がない項目や非表示になっている項目は使用されていないと判定。
        // 予約登録後に非表示に変更された項目や、カレンダーから日時を選択で枠変更してなくなった項目などが当てはまる。
        // 管理側のみ表示の項目が公開側で表示されていない場合などは当てはまらない。
        if (!isset($formPatternDisplayTypes[$formItemId])) {
            return false;
        }
        if (
            ($formPatternDisplayTypes[$formItemId]
                instanceof FormPatternDisplayType)
            && (string)$formPatternDisplayTypes[$formItemId]->get('display_type')
                === (string)FormPatternDisplayType::DISPLAY_TYPE_HIDE
        ) {
            return false;
        }

        $isGuest = true;
        if ($this instanceof Reservation) {
            /** @var \App\Model\Entity\User $user */
            $user = $this->getUserEntity();
            $isGuest = $user->isGuest();
        }
        if ($this instanceof User) {
            // $formPatternDisplayTypesの取得時は更新後の権限を使用するためgetUserAuthorityEntity()を使用したが
            // 会員とゲストを行き来することはできないので、ゲストかどうかを判定するだけなら、更新前の権限で判定しても問題ない想定
            $isGuest = $this->isGuest();
        }

        // ゲストによる操作ではない場合は、表示パターンが「表示する：ゲスト予約のみ」で設定されている項目は使用されていない
        if (
            !$isGuest
            && ($formPatternDisplayTypes[$formItemId]
                instanceof FormPatternDisplayType)
            && (string)$formPatternDisplayTypes[$formItemId]->get('display_type')
                === (string)FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE
        ) {
            return false;
        }

        return true;
    }

    /**
     * 表示パターン設定で使用されていない項目の値を削除する
     *
     * @param string $additionTableName 追加項目のテーブル
     * @return void
     */
    public function unsetUnusedValues($additionTableName)
    {
        $additionTable = $this->getTableLocator()->get($additionTableName);
        $additionName = $additionTable->getTable();

        $additions = $this->get($additionName);

        foreach ((array)$additions as $index => $addition) {
            // 使用されていない項目の値は使用しない
            if (!$this->isUsedFormItem($addition->get('form_item_id'))) {
                unset($additions[$index]);
            }
        }

        $this->set($additionName, $additions);
    }
}
