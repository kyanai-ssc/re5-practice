<?php
declare(strict_types=1);

namespace App\Model\Entity;

use App\Model\AppEntity;

/**
 * News Entity
 *
 * @property int $id
 * @property int|null $label_id
 * @property string $title
 * @property string|null $contents
 * @property \Cake\I18n\FrozenTime|null $public_from
 * @property \Cake\I18n\FrozenTime|null $public_to
 * @property int|null $sort_no
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Label $label
 * @property \App\Model\Entity\NewsAuthority[] $news_authorities
 */
class News extends AppEntity
{
    /**
     * @inheritDoc
     */
    protected $_accessible = [
        'label_id' => true,
        'title' => true,
        'contents' => true,
        'public_from' => true,
        'public_to' => true,
        'sort_no' => true,
        'created' => false,
        'modified' => false,
        'label' => false,
        'news_authorities' => true,
    ];

    /**
     * @var bool
     */
    protected $isPreview = false;

    /**
     * プレビューフラグをtrueにする
     *
     * @return void
     */
    public function previewOn()
    {
        $this->isPreview = true;
    }

    /**
     * お知らせNEW表示期間かどうか
     *
     * @return bool
     */
    public function isDisplayNews()
    {
        if ($this->isPreview) {
            return false;
        }

        /** @var \App\Model\Table\SiteSettingsTable $siteSettingsTable */
        $siteSettingsTable = $this->getTableLocator()->get('SiteSettings');
        $siteSettings = $siteSettingsTable->getData();

        /** @var \Cake\I18n\FrozenTime|null $publicFrom */
        $publicFrom = $this->get('public_from');

        if ($publicFrom === null) {
            return false;
        }

        if ($siteSettings->get('news_new_period_type') === SiteSetting::NEWS_NEW_PERIOD_TYPE_TIME) {
            $display = $publicFrom->wasWithinLast($siteSettings->get('news_new_period_number') . ' minute');
        } else {
            $display = $publicFrom->wasWithinLast($siteSettings->get('news_new_period_number') . ' days');
        }

        return $display;
    }
}
