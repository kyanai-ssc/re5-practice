<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Model\Entity\FormGroup;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\View\Helper;

/**
 * SettingHelper class.
 */
class SettingHelper extends Helper
{
    use LocatorAwareTrait;

    /**
     * サイト設定を取得
     *
     * @return \Cake\Datasource\EntityInterface サイト設定
     */
    public function getSiteSetting()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        return $siteSettingsTable->getData();
    }

    /**
     * システム設定を取得
     *
     * @return \Cake\Datasource\EntityInterface システム設定
     */
    public function getSystemSetting()
    {
        /** @var \App\Model\Table\SystemSettingsTable $systemSettingsTable */
        $systemSettingsTable = $this->getTableLocator()->get('SystemSettings');

        return $systemSettingsTable->getData();
    }

    /**
     * 決済設定を取得
     *
     * @return \Cake\Datasource\EntityInterface 決済設定
     */
    public function getPaymentSetting()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        return $paymentSettingsTable->getDataOrFail();
    }

    /**
     * 決済設定の有無を判定
     *
     * @return bool
     */
    public function hasPaymentSetting()
    {
        /** @var \App\Model\Table\PaymentSettingsTable $paymentSettingsTable */
        $paymentSettingsTable = $this->getTableLocator()->get('PaymentSettings');

        return !is_null($paymentSettingsTable->getData());
    }

    /**
     * 計測タグを取得
     *
     * @param int $type 返却するタイプ
     * @return string|null タグ
     */
    public function getAnalysisTagSetting(int $type)
    {
        /** @var \App\Model\Table\AnalysisTagsTable $analysisTagsTable */
        $analysisTagsTable = $this->getTableLocator()->get('AnalysisTags');

        /** @var string|null $analysisTag */
        $analysisTag = $analysisTagsTable->getData($type);

        return $analysisTag;
    }

    /**
     * カスタムCSSの更新日時
     *
     * @return int|null タグ
     */
    public function getCustomCssModified()
    {
        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');

        $time = $siteSettingsTable->getCustomCssTimestamp();

        if ($time !== false) {
            return $time;
        }

        return null;
    }

    /**
     * マニュアルリンクを機能ごとに取得
     *
     * @return string
     */
    public function getManualLink()
    {
        $manual = Configure::read('Master.manual');
        $controller = $this->getView()->getRequest()->getParam('controller');
        $action = $this->getView()->getRequest()->getParam('action');

        if ($controller === 'FormPatterns') {
            $controller = $this->getView()->getRequest()->getParam('_name');
        } elseif ($controller === 'FormGroups') {
            $data = $this->getView()->getRequest()->getParam('pass');
            if (is_array($data)) {
                if ((string)array_shift($data) === (string)FormGroup::FORM_TYPE_USER) {
                    $controller = 'FormGroupsUser';
                } else {
                    $controller = 'FormGroupsReserve';
                }
            }
        }

        $link = '';
        if (empty($manual['function'][$controller])) {
            return $link;
        }

        $functionLink = $manual['function'][$controller];
        if (is_array($functionLink) && isset($functionLink[$action])) {
            $link = Configure::read('Env.manual.domain') . '/' . $manual['functionDir'] . '/' . $functionLink[$action];
        } elseif (!is_array($functionLink)) {
            $link = Configure::read('Env.manual.domain') . '/' . $manual['functionDir'] . '/' . $functionLink;
        }

        return $link;
    }

    /**
     * 決済代行会社 = GMO の判定
     *
     * @return bool
     */
    public function isPaymentServiceGmo()
    {
        if (!$this->hasPaymentSetting()) {
            return false;
        }

        /** @var \App\Model\Entity\PaymentSetting $paymentSetting */
        $paymentSetting = $this->getPaymentSetting();

        return $paymentSetting->isPaymentServiceGmo();
    }

    /**
     * GMO利用時 かつ 本人確認が必要かを判定
     *
     * @return bool
     */
    public function requiresKycGmo()
    {
        if (!$this->hasPaymentSetting()) {
            return false;
        }

        /** @var \App\Model\Entity\PaymentSetting $paymentSetting */
        $paymentSetting = $this->getPaymentSetting();

        return $paymentSetting->requiresKycGmo();
    }

    /**
     * SB利用時 かつ 本人確認が必要かを判定
     *
     * @return bool
     */
    public function requiresKycSb()
    {
        if (!$this->hasPaymentSetting()) {
            return false;
        }

        /** @var \App\Model\Entity\PaymentSetting $paymentSetting */
        $paymentSetting = $this->getPaymentSetting();

        return $paymentSetting->requiresKycSb();
    }
}
