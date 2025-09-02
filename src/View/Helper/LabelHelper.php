<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Cake\View\Helper;

/**
 * LabelHelper class.
 */
class LabelHelper extends Helper
{
    use LocatorAwareTrait;

    /**
     * 上位ラベルの名称を取得
     *
     * @param mixed $parentArr データ
     * @return string|null
     */
    public function getParentName($parentArr)
    {
        $nameArr = [];

        foreach ($parentArr as $row) {
            if (isset($row['name'])) {
                $nameArr[] = $row['name'];
            }
        }

        if (count($nameArr) >= 1) {
            return implode(' / ', $nameArr);
        }

        return null;
    }

    /**
     * ラベル選択を出力
     *
     * @param array $options オプション
     * @return string HTML
     */
    public function renderSelect(array $options = [])
    {
        $type = Hash::get($options, 'type');
        $labelId = Hash::get($options, 'labelId');
        $excludeId = Hash::get($options, 'excludeId', '');
        $onlyPublic = Hash::get($options, 'onlyPublic', false);

        $public = Hash::get($options, 'public', false);
        $userLabelId = Hash::get($options, 'userLabelId', null);

        if ($public && !is_null($userLabelId) && !isset($labelId)) {
            $labelId = $userLabelId;
        }

        /** @var \App\Model\Table\LabelsTable $labelsTable */
        $labelsTable = $this->getTableLocator()->get('Labels');

        $formType = $labelsTable->setAjaxForm($type);
        if (!isset($labelId)) {
            $labelId = $this->getView()->getRequest()->getData($formType['search_id']);
        }
        if (!$labelsTable->validatePrimaryKey($labelId)) {
            $labelId = null;
        }

        $displayMax = Hash::get($options, 'displayMax', $formType['max_depth']);
        $labelLists = $labelsTable->getParentLabel($labelId, $excludeId, $onlyPublic, $displayMax, $userLabelId);

        $template = 'Admin/Labels/select';
        if ($public) {
            $template = 'User/Labels/select';
        }

        $data = [
            'labelLists' => $labelLists,
            'formType' => $formType,
            'excludeId' => $excludeId,
        ];

        $output = $this->getView()->element($template, $data);

        return $output;
    }

    /**
     * 会員権限の名称を取得
     *
     * @param int $user_authority_id 会員権限id
     * @return string
     */
    public function getUserAuthorityName($user_authority_id)
    {
        /** @var \App\Model\Table\UserAuthoritiesTable $userAuthoritiesTable */
        $userAuthoritiesTable = $this->fetchTable('UserAuthorities');

        $userAuthority = $userAuthoritiesTable->get($user_authority_id);

        return $userAuthority->get('name');
    }
}
