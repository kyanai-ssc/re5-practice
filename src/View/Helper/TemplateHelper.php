<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\Mailer\DefaultMailer;
use App\Model\Entity\FormItemChoice;
use App\Utility\CommonData\CommonDataTrait;
use App\Utility\DateTimeUtility;
use Cake\Core\Configure;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Cake\View\Form\NullContext;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * TemplateHelper class
 *
 * @property \Cake\View\Helper\FormHelper $Form
 * @property \Cake\View\Helper\BreadcrumbsHelper $Breadcrumbs
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \App\View\Helper\AuthorityHelper $Authority
 * @property \App\View\Helper\SettingHelper $Setting
 * @property \App\View\Helper\TrHelper $Tr
 * @property \Cake\View\Helper\UrlHelper $Url
 */
class TemplateHelper extends Helper
{
    use CommonDataTrait;
    use LocatorAwareTrait;
    use StringTemplateTrait;

    /**
     * List of helpers used by this helper
     *
     * @var array
     */
    public $helpers = ['Form', 'Breadcrumbs', 'Html', 'Authority', 'Tr', 'Url', 'Setting'];

    protected $_defaultConfig = [
        'prefix' => '',
    ];

    /**
     * チェックボックスヘルパー
     *
     * @param string $fieldName name
     * @param array $options option
     * @return array|string
     */
    public function checkbox($fieldName, $options = [])
    {
        $classSet = Hash::get($options, 'class', []);
        if (is_array($classSet)) {
            $options['class'] = array_merge($classSet, ['cmn-check']);
        } else {
            $options['class'] = [$classSet, 'cmn-check'];
        }

        if (!empty($options['labelOptions'])) {
            $options['labelOptions'] += ['class' => ['cmn-check']];
        } else {
            $options['labelOptions'] = ['class' => ['cmn-check']];
        }

        if (isset($options['options'])) {
            //1次元の場合だけオプションのIDを調整する
            if (is_array($options['options']) && Hash::maxDimensions($options['options']) === 1) {
                $idPrefix = Hash::get($options, 'idPrefix');
                $options['options'] = $this->getValueOptionsSetting($options['options'], $fieldName, $idPrefix);
            }
        }

        $label = '';
        $labelOption = Hash::get($options, 'label', false);
        if ($options['type'] === 'checkbox' && !is_array($labelOption) && $labelOption !== false) {
            $label = $this->Form->label($fieldName, $labelOption, ['class' => 'cmn-check']);
            $options['label'] = false;
        }

        return $this->Form->control($fieldName, $options) . $label;
    }

    /**
     * ラジオヘルパー
     *
     * @param string $fieldName name
     * @param array $options option
     * @return array|string
     */
    public function radio($fieldName, $options = [])
    {
        $classSet = Hash::get($options, 'class', []);
        if (is_array($classSet)) {
            $options['class'] = array_merge($classSet, ['cmn-radio']);
        } else {
            $options['class'] = [$classSet, 'cmn-radio'];
        }

        $label = Hash::get($options, 'label', null);
        if (!is_array($label)) {
            $options['label'] = false;
        }

        if (!empty($options['labelOptions'])) {
            $options['labelOptions'] += ['class' => ['cmn-radio']];
        } else {
            $options['labelOptions'] = ['class' => ['cmn-radio']];
        }

        //1次元の場合だけオプションのIDを調整する
        if (is_array($options['options']) && Hash::maxDimensions($options['options']) === 1) {
            $idPrefix = Hash::get($options, 'idPrefix');
            $options['options'] = $this->getValueOptionsSetting($options['options'], $fieldName, $idPrefix);
        }

        return $this->Form->control($fieldName, $options);
    }

    /**
     * isRequire
     *
     * @param string $fieldName name
     * @param array $options option
     * @return array|string
     */
    public function isRequire($fieldName, $options = [])
    {
        $context = $this->Form->context();

        $require = Hash::get($options, 'always', false);
        if ($require || $context->isRequired($fieldName)) {
            $prefix = $this->getConfig('prefix');
            if (preg_match('/(.*?)\//', $prefix, $matches)) {
                $prefix = $matches[1];
            }

            return $this->getView()->element($prefix . '/Common/form/require');
        }

        return '';
    }

    /**
     * 管理側パン最上位のパンくず設定
     *
     * @param string $title タイトル
     * @param bool $data 運用データ管理フラグ
     * @return void
     */
    public function setAdminBreadcrumbs(string $title, $data = false)
    {
        $template = '<svg class="icon is-breadC"><use xlink:href = "#icon_breadC_home" ></use></svg >
            <span class="ttl-top">' . $title . '</span> ';

        $url = ['controller' => 'Index', 'action' => 'index'];
        if ($data) {
            $url = ['controller' => 'Reservations'];
        }

        $this->Breadcrumbs->prepend(
            $template,
            $url,
            ['class' => [],
                'innerAttrs' => [
                    'class' => 'btn-home',
                ],
            ]
        );
    }

    /**
     * 公開側TOPのURL設定
     *
     * @return array
     */
    public function getTopUrl()
    {
        $url = [
            'prefix' => 'User',
            'controller' => 'Index',
            'action' => 'index',
        ];

        $label = $this->commonData()->getUserLabelId();
        if (isset($label)) {
            $url['?'] = [
                'label' => $label,
            ];
        }

        return $url;
    }

    /**
     * 公開側パン最上位のパンくず設定
     *
     * @return void
     */
    public function setUserBreadcrumbs()
    {
        $this->Breadcrumbs->setTemplates([
            'wrapper' => '<ol{{attrs}}>{{content}}</ol>',
        ]);
        if ($this->Authority->isAuthority(true, 'Index', 'index')) {
            $template = '<svg class="icon-home"><use xlink:href = "#icon_breadC_home" ></use></svg>';
            $this->Breadcrumbs->prepend($template, $this->getTopUrl(), [
                'class' => [],
                'innerAttrs' => [
                    'class' => 'icon',
                ],
            ]);
        }
    }

    /**
     * inputタグを横並びにする
     *
     * @param string $type タイプ
     * @param array $options オプション
     * @return array テンプレートセット
     */
    public function getTemplateSpan($type, $options = [])
    {
        $templateSet = [
            'type' => $type,
            'label' => '',
            'templates' => [
                'formGroup' => '{{input}}',
                'inputContainer' => '<span class="input {{type}}{{required}}">{{content}}</span>',
                'input' => '<input type="{{type}}" name="{{name}}"{{attrs}}/>',
                'inputContainerError' => '<span class="input {{type}}{{required}} error">{{content}}{{error}}</span>',
                'error' => '',
            ],
        ];

        if (is_array($options)) {
            $templateSet = array_merge($templateSet, $options);
        }

        return $templateSet;
    }

    /**
     * 選択肢IDの生成
     *
     * @param array $valueOptions 選択肢
     * @param string $name フィールド名
     * @param string|null $idPrefix IDプレフィックス
     * @return array
     */
    public function getValueOptionsSetting(array $valueOptions, string $name, ?string $idPrefix = null)
    {
        $options = [];
        $name = str_replace('.', '-', $name);
        if (isset($idPrefix) && $idPrefix !== '') {
            $idPrefix = $idPrefix . '-';
        }

        foreach ($valueOptions as $value => $text) {
            $options[] = [
                'value' => $value,
                'text' => $text,
                'id' => $idPrefix . $name . '-' . $value,
            ];
        }

        return $options;
    }

    /**
     * FORM～TOの表示を調整
     *
     * @param mixed $from FROM
     * @param mixed $to TO
     * @param string $delimiter 区切り文字
     * @param bool $indention 改行フラグ
     * @param bool $beforeIndention 改行の付ける位置
     * @return mixed
     */
    public function getFromToDisplay($from, $to, $delimiter = '～', $indention = false, $beforeIndention = true)
    {
        $fromDisplay = $from;
        $toDisplay = $to;
        if (!empty($from) && $beforeIndention) {
            $fromDisplay = $from . $delimiter;
        } elseif (!empty($to)) {
            $toDisplay = $delimiter . $to;
        }

        if ($indention) {
            $fromDisplay = '<p>' . h($fromDisplay) . '</p>';
            $toDisplay = '<p>' . h($toDisplay) . '</p>';
        }

        return $fromDisplay . $toDisplay;
    }

    /**
     * @param mixed $dateTime 日時
     * @param string|null $timeFormat 時間表示のフォーマット
     * @param string|null $dateFormat 日付表示のフォーマット
     * @return string|null
     */
    public function displayDayAndWeek($dateTime, $timeFormat = null, $dateFormat = null)
    {
        $dateTime = DateTimeUtility::convertToDateTimeObject($dateTime);
        if (is_null($dateTime)) {
            return $dateTime;
        }

        if (!isset($dateFormat)) {
            $dateFormat = 'Y/m/d';
        }
        $week = Configure::readOrFail('Master.common.week.' . $dateTime->dayOfWeek);
        $dispDate = $dateTime->format($dateFormat) . '(' . $week . ')';

        if ($timeFormat) {
            $dispDate .= ' ' . $dateTime->format($timeFormat);
        }

        return $dispDate;
    }

    /**
     * @param array $arrayData 配列データ（多次元）
     * @param string $key カラムのキー
     * @param string $delimiter 区切り文字
     * @return string
     */
    public function viewArrayToString(array $arrayData, string $key, $delimiter = ',')
    {
        return implode($delimiter, array_column($arrayData, $key));
    }

    /**
     * @param array $arrayData 配列データ（多次元）
     * @param string $key カラムのキー
     * @param array $master 置き換えるマスターデータ
     * @param string $delimiter 区切り文字
     * @return string
     */
    public function viewArrayToStringForMaster(array $arrayData, string $key, array $master, $delimiter = ',')
    {
        $toString[$key] = [];
        foreach ($arrayData as $arr) {
            $toString[][$key] = $master[$arr[$key]];
        }

        return $this->viewArrayToString($toString, $key, $delimiter);
    }

    /**
     * 公開側TOPへ戻るボタン出力
     *
     * @param bool $middle middle -> login page
     * @param string $name ボタン名
     * @return string
     */
    public function userTopBtn($middle = false, $name = 'common/topBack')
    {
        if ($this->Authority->isAuthority(true, 'Index', 'index')) {
            $class = ['cmn-btn', 'is-blue'];
            if ($middle) {
                $class = ['cmn-btn', 'is-gray', 'pdl-30', 'pdr-30', 'size-middle'];
            }

            return $this->Html->link(
                $this->Tr->t($name),
                $this->getTopUrl(),
                ['class' => $class]
            );
        }

        return '';
    }

    /**
     * 項目追加のテンプレート
     *
     * @param string $name テンプレート
     * @param array $data データ
     * @param array<string, mixed> $options オプション
     * @return string
     */
    public function addInputTemplate(string $name, array $data = [], array $options = []): string
    {
        $context = $this->Form->context();
        $this->Form->context(new NullContext([]));

        $output = $this->getView()->element($name, $data, $options);

        $this->Form->context($context);

        return $output;
    }

    /**
     * QRコードを表示
     *
     * @param string $qrCode  QRコードに埋め込まれたデータ
     * @param bool $mailerFlg メールでの呼び出しの場合 true
     * @return string
     */
    public function qrCodeImage($qrCode, $mailerFlg = false)
    {
        $options = [
            'width' => DefaultMailer::QR_CODE_SIZE_WIDTH,
            'height' => DefaultMailer::QR_CODE_SIZE_HEIGHT,
        ];
        if (!$mailerFlg) {
            $options['alt'] = $this->Tr->h('reservationView/qrCode/alt');
            $options['class'] = 'qr-code';
        }

        return $this->Html->image(
            $this->Url->build(
                [
                    'prefix' => 'User',
                    'controller' => 'Qrcode',
                    'action' => 'view',
                    $qrCode,
                ],
                [
                    'fullBase' => $mailerFlg,
                ]
            ),
            $options
        );
    }

    /**
     * 繰り返し予約を表示する判定
     *
     * @param \App\Model\Entity\User $userEntity userエンティティ
     * @param \App\Model\Entity\Reservation $reservationEntity reservationエンティティ
     * @return bool
     */
    public function repeatReservation($userEntity, $reservationEntity)
    {
        /** @var \App\Model\Table\ReservationVideoMeetingsTable $reservationVideoMeetingsTable */
        $reservationVideoMeetingsTable = $this->getTableLocator()->get('ReservationVideoMeetings');

        $result = false;
        if ($userEntity->id !== null) {
            foreach ($userEntity->user_additions as $addition) {
                if ($addition->form_item_id === 34) {
                    if ($addition->value === FormItemChoice::REPEAT_RESERVATION_FLG_ON) {
                        $result = true;
                    }
                }
            }
        }

        if (!$result) {
            return false;
        }

        if (
            !$this->Setting->getSystemSetting()
                ->get('reservation_continuous_flg') === Configure::read('Master.common.flg.off')
            || $userEntity->isGuest()
            || $reservationEntity->qr_code !== null
            || $reservationVideoMeetingsTable->shouldProcessOnReserve($reservationEntity)
        ) {
            return false;
        }

        return true;
    }
}
