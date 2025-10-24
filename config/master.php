<?php
declare(strict_types=1);

use App\Mailer\DefaultMailer;
use App\Model\Entity\Admin;
use App\Model\Entity\AdminAuthority;
use App\Model\Entity\AdminListItem;
use App\Model\Entity\AdminOperationalLog;
use App\Model\Entity\AdminSearchItem;
use App\Model\Entity\AnalysisTag;
use App\Model\Entity\AutoReplyMail;
use App\Model\Entity\BounceMail;
use App\Model\Entity\ColorChip;
use App\Model\Entity\Event;
use App\Model\Entity\EventPlan;
use App\Model\Entity\EventRemark;
use App\Model\Entity\FormGroup;
use App\Model\Entity\FormItem;
use App\Model\Entity\FormItemDetail;
use App\Model\Entity\FormItemOptionGroup;
use App\Model\Entity\FormPatternDisplayType;
use App\Model\Entity\Label;
use App\Model\Entity\MailDelivery;
use App\Model\Entity\MailDeliveryHistory;
use App\Model\Entity\Option;
use App\Model\Entity\Organizer;
use App\Model\Entity\PaymentError;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\PaymentSetting;
use App\Model\Entity\PaymentStatus;
use App\Model\Entity\RecaptchaSetting;
use App\Model\Entity\ReceptionStatus;
use App\Model\Entity\Reservation;
use App\Model\Entity\ReservationPayment;
use App\Model\Entity\ReservationSmartLock;
use App\Model\Entity\ReservationStatus;
use App\Model\Entity\SiteSetting;
use App\Model\Entity\SmartLock;
use App\Model\Entity\SystemSetting;
use App\Model\Entity\TagGroup;
use App\Model\Entity\Term;
use App\Model\Entity\User;
use App\Model\Entity\UserAuthority;
use App\Model\Entity\Word;
use App\Model\EventCalendar\AbstractCalendarPopup;
use App\Model\Table\ReservationsTable;

return [
    'Master' => [
        'manual' => [
            'functionDir' => 'function',
            'function' => [
                'EventHolidays' => [
                    'list' => 'holidayslist',
                    'edit' => 'holidaysedit',
                ],
                'Events' => [
                    'list' => 'eventslist',
                    'edit' => 'eventsitem',
                    'add' => 'eventsitem',
                    'copy' => 'eventsitem',
                    'togetherEdit' => 'eventsedit',
                ],
                'FileGroups' => [
                    'list' => 'filelist',
                    'edit' => 'fileitem',
                    'add' => 'fileitem',
                ],
                'FormGroupsUser' => 'customerauthorityformslist',
                'FormGroupsReserve' => 'reserveformslist',
                'formPatternsReserve' => [
                    'list' => 'reservepatternslist',
                    'edit' => 'reservepatternsitem',
                    'add' => 'reservepatternsitem',
                    'togetherEdit' => 'reservepatternsedit',
                ],
                'formPatternsReserveEdit' => [
                    'edit' => 'reservepatternsitem',
                ],
                'formPatternsUser' => [
                    'list' => 'customerauthoritypatternslist',
                    'edit' => 'customerauthorityformsitem',
                    'add' => 'customerauthorityformsitem',
                    'togetherEdit' => 'customerauthorityformsedit',
                ],
                'formPatternsUserEdit' => [
                    'edit' => 'customerauthorityformsitem',
                ],
                'Holidays' => 'publicholidayedit',
                'Index' => 'top',
                'Labels' => [
                    'list' => 'labellist',
                    'edit' => 'labelitem',
                    'add' => 'labelitem',
                ],
                'MailDeliveries' => [
                    'list' => 'maildeliverylist',
                    'view' => 'maildeliverylist/#smoothplay13',
                    'add' => 'maildelivery',
                    'addConf' => 'maildelivery/#smoothplay12',
                    'addFinish' => 'maildelivery/#smoothplay13',
                ],
                'News' => [
                    'list' => 'news',
                    'edit' => 'newsitem',
                    'add' => 'newsitem',
                    'setting' => 'newssetting',
                ],
                'Options' => [
                    'list' => 'optionslist',
                    'edit' => 'optionsitem',
                    'add' => 'optionsitem',
                ],
                'PassReset' => [
                    'certification' => 'managerpwreset',
                ],
                'Payment' => 'payment',
                'Reservations' => [
                    'calendar' => 'reservationscalendar',
                    'add' => 'reservationscalendar/#smoothplay24',
                    'addConf' => 'reservationscalendar/#smoothplay31',
                    'addFinish' => 'reservationscalendar/#smoothplay32',
                    'list' => 'reservationslist',
                    'view' => 'reservationsedit',
                    'edit' => 'reservationsedit/#smoothplay5',
                    'editConf' => 'reservationsedit/#smoothplay2',
                    'editFinish' => 'reservationsedit/#smoothplay3',
                    'deleteMany' => 'reservationslist/#smoothplay11',
                ],
                'SiteSetting' => 'siteedit',
                'Tags' => [
                    'list' => 'taglist',
                    'edit' => 'tagitem',
                    'add' => 'tagitem',
                ],
                'Terms' => 'termsedit',
                'UserAuthorities' => [
                    'list' => 'customerauthoritylist',
                    'edit' => 'customerauthorityitem',
                    'add' => 'customerauthorityitem',
                ],
                'Users' => [
                    'add' => 'customerregistration/#smoothplay24',
                    'addConf' => 'customerregistration/#smoothplay2',
                    'addFinish' => 'customerregistration/#smoothplay3',
                    'list' => 'customerlist',
                    'view' => 'customeredit',
                    'edit' => 'customeredit/#smoothplay5',
                    'editConf' => 'customeredit/#smoothplay2',
                    'editFinish' => 'customeredit/#smoothplay3',
                    'deleteMany' => 'customerlist/#smoothplay13',
                ],
                'Words' => [
                    'wordEdit' => 'wordedit',
                    'errorWordEdit' => 'errorwordedit',
                    'statusWordEdit' => 'statuswordedit',
                    'prefWordEdit' => 'prefwordedit',
                    'paymentMethodWordEdit' => 'paymentstatuswordedit',
                    'paymentStatusWordEdit' => 'paymentwordedit',
                    'receptionStatusWordEdit' => 'receptionstatuswordedit',
                ],
                'AdminOperationalLogs' => 'operationallogs',
                'Admins' => [
                    'list' => 'managerlist',
                    'add' => 'manageritem',
                    'edit' => 'manageritem',
                ],
                'AnalysisTags' => 'analysistagedit',
                'Auth' => [
                    'login' => 'login',
                ],
                'AutoReplyMails' => [
                    'list' => 'autoreplymailslist',
                    'edit' => 'autoreplymailsitem',
                    'add' => 'autoreplymailsitem',
                    'copy' => 'autoreplymailsitem',
                ],
                'BounceMails' => [
                    'list' => 'bouncemailslist',
                    'view' => 'bouncemailslist/#smoothplay10',
                ],
                'Organizers' => [
                    'list' => 'organizerlist',
                    'add' => 'organizeritem_new',
                    'edit' => 'organizeritem_new',
                ],
                'ZoomConnectUsers' => [
                    'list' => 'zoomconnect',
                    'view' => 'zoomconnect',
                    'add' => 'zoomconnect',
                    'edit' => 'zoomconnect',
                ],
                'Cms' => 'designedit',
                'ColorChips' => 'colorchipsedit',
                'SmartLocks' => [
                    'akerun' => 'akerun',
                ],
                'AdminAuthorities' => 'managerpattern',
                'ReceptionStatuses' => 'receptionstatuslist',
                'Recaptcha' => [
                    'edit' => 'recaptcha',
                ],
                'PaymentErrors' => [
                    'list' => 'paymenterrors',
                ],
            ],
        ],
        // 共通
        'common' => [
            'flg' => [
                'off' => 0,
                'on' => 1,
            ],
            'week' => [
                1 => '月',
                2 => '火',
                3 => '水',
                4 => '木',
                5 => '金',
                6 => '土',
                7 => '日',
            ],
            'mailFormatName' => [
                DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT => 'テキスト',
                DefaultMailer::MAIL_FORMAT_CONTENTS_HTML => 'HTML',
            ],
            // Emailクラスの設定値
            'mailFormat' => [
                DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT => 'text',
                DefaultMailer::MAIL_FORMAT_CONTENTS_HTML => 'both',
            ],
            // 挨拶文・本文・署名の区切り文字
            'mailFormatDelimiter' => [
                DefaultMailer::MAIL_FORMAT_CONTENTS_TEXT => "\n\n",
                DefaultMailer::MAIL_FORMAT_CONTENTS_HTML => '<br/>',
            ],
            'listCheckId' => [
                'check' => 1,
                'remove' => 2,
            ],
            'listCheck' => [
                1 => '一括チェック',
                2 => '一括チェック解除',
            ],
        ],
        'payment' => [
            'credit' => [
                'job' => [
                    PaymentSetting::JOB_CODE_IMMEDIATE => '即時売上',
                    PaymentSetting::JOB_CODE_PROVISIONAL => '仮売上',
                ],
                'jobCodePaymentStatusType' => [
                    PaymentSetting::JOB_CODE_IMMEDIATE => PaymentStatus::TYPE_PAYMENT_COMPLETE,
                    PaymentSetting::JOB_CODE_PROVISIONAL => PaymentStatus::TYPE_PAYMENT_TEMP,
                ],
                '3DSecure' => [
                    PaymentSetting::THREE_D_SECURE_FLG_ON => '利用する',
                    PaymentSetting::THREE_D_SECURE_FLG_OFF => '利用しない',
                ],
                'brand' => [
                    PaymentSetting::CARD_BLAND_JCB => 'JCB',
                    PaymentSetting::CARD_BLAND_VISA => 'VISA',
                    PaymentSetting::CARD_BLAND_MASTERCARD => 'MasterCard',
                    PaymentSetting::CARD_BLAND_AMERICAN_EXPRESS => 'American Express',
                    PaymentSetting::CARD_BLAND_DINERS_CLUB => 'DinersClub',
                ],
                'brandImage' => [
                    PaymentSetting::CARD_BLAND_JCB => 'card_jcb.gif',
                    PaymentSetting::CARD_BLAND_VISA => 'card_visa.gif',
                    PaymentSetting::CARD_BLAND_MASTERCARD => 'card_master_card.gif',
                    PaymentSetting::CARD_BLAND_AMERICAN_EXPRESS => 'card_american_express.gif',
                    PaymentSetting::CARD_BLAND_DINERS_CLUB => 'card_diners_club.gif',
                ],
                'phoneType' => [
                    ReservationPayment::PHONE_TYPE_WORK => 'payment/phoneNumber/work',
                    ReservationPayment::PHONE_TYPE_HOME => 'payment/phoneNumber/home',
                    ReservationPayment::PHONE_TYPE_MOBILE => 'payment/phoneNumber/mobile',
                ],
            ],
            'method' => [
                PaymentMethod::TYPE_CARD => 'クレジットカード',
                PaymentMethod::TYPE_CASH => '現金',
                PaymentMethod::TYPE_BANK => '銀行振込',
                PaymentMethod::TYPE_PAYPAY => 'PayPay',
                PaymentMethod::TYPE_APPLE_PAY => 'ApplePay',
                PaymentMethod::TYPE_AU_PAY => 'auPAY',
            ],
            'status' => [
                PaymentStatus::TYPE_PAYMENT_YET => '未決済',
                PaymentStatus::TYPE_PAYMENT_TEMP => '仮決済',
                PaymentStatus::TYPE_PAYMENT_COMPLETE => '決済済み',
                PaymentStatus::TYPE_PAYMENT_CANCEL => '決済キャンセル',
                PaymentStatus::TYPE_RECEIVE_YET => '未入金',
                PaymentStatus::TYPE_RECEIVE_CONFIRM => '入金確認中',
                PaymentStatus::TYPE_RECEIVE_COMPLETE => '入金済み',
                PaymentStatus::TYPE_REFUND_PROCESS => '返金処理中',
                PaymentStatus::TYPE_REFUND_COMPLETE => '返金済み',
                PaymentStatus::TYPE_PAYMENT_COMPLETE_CHANGED => '決済済み（変更あり）',
            ],
            'statusClass' => [
                PaymentStatus::TYPE_PAYMENT_YET => 'is-Kmikessai',
                PaymentStatus::TYPE_PAYMENT_TEMP => 'is-Kkari',
                PaymentStatus::TYPE_PAYMENT_COMPLETE => 'is-Ksumi',
                PaymentStatus::TYPE_PAYMENT_CANCEL => 'is-Kcancel',
                PaymentStatus::TYPE_RECEIVE_YET => 'is-Kmikessai',
                PaymentStatus::TYPE_RECEIVE_CONFIRM => 'is-Kkakunin',
                PaymentStatus::TYPE_RECEIVE_COMPLETE => 'is-Ksumi',
                PaymentStatus::TYPE_REFUND_PROCESS => 'is-Kkakunin',
                PaymentStatus::TYPE_REFUND_COMPLETE => 'is-Kcancel',
                PaymentStatus::TYPE_PAYMENT_COMPLETE_CHANGED => 'is-Ksumi-Changed',
            ],
            'reservationPaymentStatus' => [
                ReservationPayment::DISPLAY_STATUS_UNSETTLED => '決済中',
                ReservationPayment::DISPLAY_STATUS_EXPIRED => '決済連携期限切れ',
                ReservationPayment::DISPLAY_STATUS_COMPLETED => '決済済み',
                ReservationPayment::DISPLAY_STATUS_CANCEL => '取消/返金済み',
                ReservationPayment::DISPLAY_STATUS_ERROR => '決済エラー',
            ],
            'display' => [
                PaymentMethod::DISPLAY_FLG_ON => '表示',
                PaymentMethod::DISPLAY_FLG_OFF => '非表示',
            ]
        ],
        // 管理者
        'admin' => [
            // 権限
            'authority' => [
                Admin::AUTHORITY_MASTER => 'マスター管理者',
                Admin::AUTHORITY_REGULAR => '運用管理者',
                Admin::AUTHORITY_OPERATOR => 'オペレーター管理者',
            ],
            // 権限利用許可画面
            'authorityAcl' => [
                Admin::AUTHORITY_MASTER => [
                    'All__all',
                ],
                Admin::AUTHORITY_REGULAR => [
                    'All__all',
                ],
                Admin::AUTHORITY_OPERATOR => [
                    'Admins__all',
                    'AdminSearchItems__all',
                    'AdminListItems__all',
                    'FileGroups__all',
                    'Index__all',
                    'News__all',
                    'Reservations__all',
                    'Users__all',
                    'Words__all',
                    'Auth__all',
                    'MailDeliveries__all',
                    'BounceMails__all',
                    'Events_planDownload',
                ],
            ],
            'systemAdminAcl' => [
                'Reservations__all',
                'Users__all',
                'MailDeliveries__all',
                'BounceMails__all',
                'ReceptionStatuses__all',
            ],
            // デフォルト権限
            'canAccessAuthority' => [
                'common' => [
                    'Auth__all',
                    'Admins__all',
                    'Index__all',
                ],
                'master' => [
                    'AdminAuthorities__all',
                ],
            ],
        ],
        // 管理者検索項目
        'adminSearchItems' => [
            // タイプ
            'type' => [
                AdminSearchItem::TYPE_USER_LIST => true,
                AdminSearchItem::TYPE_RESERVATION_LIST => true,
                AdminSearchItem::TYPE_MAIL_DELIVERIES => true,
                AdminSearchItem::TYPE_RESERVATION_USER => true,
            ],
            // 項目
            'items' => [
                AdminSearchItem::ITEM_USER_ID => '顧客ID',
                AdminSearchItem::ITEM_GUEST_FLG => '顧客区分',
                AdminSearchItem::ITEM_WITHDRAWAL_FLG => '退会',
                AdminSearchItem::ITEM_RESERVATION_ID => '予約ID',
                AdminSearchItem::ITEM_EVENT_ID => '予約枠ID',
                AdminSearchItem::ITEM_RESERVATION_STATUS_ID => 'ステータス',
                AdminSearchItem::ITEM_RECEPTION_STATUS_ID => '受付ステータス',
                AdminSearchItem::ITEM_USAGE_TIMESTAMP => '利用日時',
                AdminSearchItem::ITEM_PAYMENT_METHOD => '決済方法',
                AdminSearchItem::ITEM_PAYMENT_STATUS => '決済ステータス',
                AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS => '決済連携状況',
                AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS => 'スマートロック連携状況',
                AdminSearchItem::ITEM_USER_INS_TIMESTAMP => '顧客登録日時',
                AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP => '予約登録日時',
                AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING => 'ビデオ会議連携',
            ],
            // 項目の検索入力キー
            'itemsSearchInputKey' => [
                AdminSearchItem::ITEM_USER_ID => 'user_id',
                AdminSearchItem::ITEM_GUEST_FLG => 'guest_flg',
                AdminSearchItem::ITEM_WITHDRAWAL_FLG => 'withdrawal_flg',
                AdminSearchItem::ITEM_RESERVATION_ID => 'reservation_id',
                AdminSearchItem::ITEM_EVENT_ID => 'event_id',
                AdminSearchItem::ITEM_RESERVATION_STATUS_ID => 'reservation_status_id',
                AdminSearchItem::ITEM_RECEPTION_STATUS_ID => 'reception_status_id',
                AdminSearchItem::ITEM_USAGE_TIMESTAMP => 'usage_timestamp',
                AdminSearchItem::ITEM_PAYMENT_METHOD => 'payment_method',
                AdminSearchItem::ITEM_PAYMENT_STATUS => 'payment_status',
                AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS => 'reservation_payment_status',
                AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS => 'reservation_smartlock_status',
                AdminSearchItem::ITEM_USER_INS_TIMESTAMP => 'user_created',
                AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP => 'reservation_created',
                AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING => 'reservation_video_meeting',
            ],
            // フォームタイプ別の項目
            'formTypeItems' => [
                FormGroup::FORM_TYPE_USER => [
                    AdminSearchItem::ITEM_USER_ID,
                    AdminSearchItem::ITEM_GUEST_FLG,
                    AdminSearchItem::ITEM_WITHDRAWAL_FLG,
                    AdminSearchItem::ITEM_USER_INS_TIMESTAMP,
                ],
                FormGroup::FORM_TYPE_RESERVATION => [
                    AdminSearchItem::ITEM_RESERVATION_ID,
                    AdminSearchItem::ITEM_EVENT_ID,
                    AdminSearchItem::ITEM_RESERVATION_STATUS_ID,
                    AdminSearchItem::ITEM_RECEPTION_STATUS_ID,
                    AdminSearchItem::ITEM_USAGE_TIMESTAMP,
                    AdminSearchItem::ITEM_PAYMENT_METHOD,
                    AdminSearchItem::ITEM_PAYMENT_STATUS,
                    AdminSearchItem::ITEM_RESERVATION_PAYMENT_STATUS,
                    AdminSearchItem::ITEM_RESERVATION_SMARTLOCK_STATUS,
                    AdminSearchItem::ITEM_RESERVATION_INS_TIMESTAMP,
                    AdminSearchItem::ITEM_RESERVATION_VIDEO_METTING,
                ],
            ],
        ],
        // 管理者一覧項目
        'adminListItems' => [
            // タイプ
            'type' => [
                AdminListItem::TYPE_USER_LIST => true,
                AdminListItem::TYPE_RESERVATION_LIST => true,
                AdminListItem::TYPE_MAIL_DELIVERIES => true,
            ],
            // 項目
            'items' => [
                AdminListItem::ITEM_USER_ID => '顧客ID',
                AdminListItem::ITEM_GUEST_FLG => '顧客区分',
                AdminListItem::ITEM_WITHDRAWAL_FLG => '退会',
                AdminListItem::ITEM_USER_INS_TIMESTAMP => '顧客登録日時',
                AdminListItem::ITEM_RESERVATION_ID => '予約ID',
                AdminListItem::ITEM_RESERVATION_STATUS_ID => 'ステータス',
                AdminListItem::ITEM_RECEPTION_STATUS_ID => '受付ステータス',
                AdminListItem::ITEM_USAGE_TIMESTAMP => '利用日時',
                AdminListItem::ITEM_EVENT_PLANS => 'プラン',
                AdminListItem::ITEM_CHARGE => '料金',
                AdminListItem::ITEM_PAYMENT_METHOD => '決済方法',
                AdminListItem::ITEM_PAYMENT_STATUS => '決済ステータス',
                AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS => '決済連携状況',
                AdminListItem::ITEM_RESERVATION_INS_TIMESTAMP => '予約登録日時',
            ],
            // ソートキー
            'sortKey' => [
                AdminListItem::ITEM_USER_ID => 'Users.id',
                AdminListItem::ITEM_GUEST_FLG => 'Users.guest_flg',
                AdminListItem::ITEM_WITHDRAWAL_FLG => 'Users.withdrawal_flg',
                AdminListItem::ITEM_USER_INS_TIMESTAMP => 'Users.created',
                AdminListItem::ITEM_RESERVATION_ID => 'Reservations.id',
                AdminListItem::ITEM_RESERVATION_STATUS_ID => 'Reservations.reservation_status_id',
                AdminListItem::ITEM_USAGE_TIMESTAMP => 'Reservations.usage_timestamp_from',
                AdminListItem::ITEM_EVENT_PLANS => null,
                AdminListItem::ITEM_CHARGE => 'Reservations.charge',
                AdminListItem::ITEM_PAYMENT_METHOD => 'Reservations.payment_method_id',
                AdminListItem::ITEM_PAYMENT_STATUS => 'Reservations.payment_status_id',
                AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS => 'ReservationPayments.status',
                AdminListItem::ITEM_RESERVATION_INS_TIMESTAMP => 'Reservations.created',
                AdminListItem::ITEM_RECEPTION_STATUS_ID => 'Reservations.reception_status_id',
            ],
            // 項目別クラス
            'itemsClass' => [
                AdminListItem::ITEM_USER_ID => 'reId',
                AdminListItem::ITEM_GUEST_FLG => 'w-120 min-maxW-120',
                AdminListItem::ITEM_WITHDRAWAL_FLG => 'w-100 min-maxW-100',
                AdminListItem::ITEM_USER_INS_TIMESTAMP => 'w-220 min-maxW-220',
                AdminListItem::ITEM_RESERVATION_ID => 'reId',
                AdminListItem::ITEM_RESERVATION_STATUS_ID => 'status',
                AdminListItem::ITEM_USAGE_TIMESTAMP => 'w-350 min-maxW-350',
                AdminListItem::ITEM_EVENT_PLANS => 'w-200 min-maxW-200',
                AdminListItem::ITEM_CHARGE => 'w-120 min-maxW-120',
                AdminListItem::ITEM_PAYMENT_METHOD => 'w-160 min-maxW-160',
                AdminListItem::ITEM_PAYMENT_STATUS => 'w-160 min-maxW-160',
                AdminListItem::ITEM_RESERVATION_INS_TIMESTAMP => 'w-220 min-maxW-220',
                AdminListItem::ITEM_RECEPTION_STATUS_ID => 'w-220 min-maxW-220',
            ],
            'formTypeItemsClass' => [
                FormItem::INPUT_TYPE_MAIL => 'w-200 min-maxW-200',
                FormItem::INPUT_TYPE_SEARCH_ADDRESS => 'w-300 min-maxW-300',
            ],
            // フォームタイプ別の項目
            'formTypeItems' => [
                FormGroup::FORM_TYPE_USER => [
                    AdminListItem::ITEM_USER_ID,
                    AdminListItem::ITEM_GUEST_FLG,
                    AdminListItem::ITEM_WITHDRAWAL_FLG,
                    AdminListItem::ITEM_USER_INS_TIMESTAMP,
                ],
                FormGroup::FORM_TYPE_RESERVATION => [
                    AdminListItem::ITEM_RESERVATION_ID,
                    AdminListItem::ITEM_RESERVATION_STATUS_ID,
                    AdminListItem::ITEM_RECEPTION_STATUS_ID,
                    AdminListItem::ITEM_USAGE_TIMESTAMP,
                    AdminListItem::ITEM_EVENT_PLANS,
                    AdminListItem::ITEM_CHARGE,
                    AdminListItem::ITEM_PAYMENT_METHOD,
                    AdminListItem::ITEM_PAYMENT_STATUS,
                    AdminListItem::ITEM_RESERVATION_PAYMENT_STATUS,
                    AdminListItem::ITEM_RESERVATION_INS_TIMESTAMP,
                ],
            ],
        ],
        // 操作ログ
        'operation' => [
            'function' => [
                AdminOperationalLog::FUNCTION_ADMINS => '管理者情報',
                AdminOperationalLog::FUNCTION_SITE_SETTING => '基本設定',
                AdminOperationalLog::FUNCTION_LABELS => 'カテゴリー設定',
                AdminOperationalLog::FUNCTION_TAGS => '絞り込みキーワード設定',
                AdminOperationalLog::FUNCTION_OPTIONS => 'オプション設定',
                AdminOperationalLog::FUNCTION_EVENTS => '予約枠設定',
                AdminOperationalLog::FUNCTION_AUTO_REPLY_MAILS => '自動返信メール設定',
                AdminOperationalLog::FUNCTION_EVENT_HOLIDAYS => '休業設定',
                AdminOperationalLog::FUNCTION_RESERVATION_FORM_PATTERNS => '予約内容の表示パターン',
                AdminOperationalLog::FUNCTION_USER_FORM_PATTERNS => '顧客情報の表示パターン',
                AdminOperationalLog::FUNCTION_USER_AUTHORITIES => '顧客の権限設定',
                AdminOperationalLog::FUNCTION_FILE_GROUPS => 'ファイル管理',
                AdminOperationalLog::FUNCTION_NEWS => 'お知らせ',
                AdminOperationalLog::FUNCTION_TERMS => '利用規約・個人情報取り扱い・特商法 設定',
                AdminOperationalLog::FUNCTION_CMS => 'デザイン設定',
                AdminOperationalLog::FUNCTION_ANALYSIS_TAGS => '計測タグ設定',
                AdminOperationalLog::FUNCTION_COLOR_CHIPS => '予約枠のカラー設定',
                AdminOperationalLog::FUNCTION_WORDS => '文言設定',
                AdminOperationalLog::FUNCTION_HOLIDAYS => '祝日設定',
                AdminOperationalLog::FUNCTION_MAIL_DELIVERIES => 'メール配信',
                AdminOperationalLog::FUNCTION_BOUNCE_MAILS => '不達メール',
                AdminOperationalLog::FUNCTION_FORM_GROUPS_USERS => '顧客情報の項目設定',
                AdminOperationalLog::FUNCTION_FORM_GROUPS_RESERVATIONS => '予約内容の項目設定',
                AdminOperationalLog::FUNCTION_USERS => '顧客管理',
                AdminOperationalLog::FUNCTION_RESERVATIONS => '予約管理',
                AdminOperationalLog::FUNCTION_ORGANIZERS => '主催者設定',
                AdminOperationalLog::FUNCTION_ADMIN_AUTHORITIES => '利用許可画面のパターン設定',
                AdminOperationalLog::FUNCTION_RECEPTION_STATUSES => '受付状況一覧',
                AdminOperationalLog::FUNCTION_SMART_LOCKS => 'スマートロック設定',
                AdminOperationalLog::FUNCTION_ZOOM_CONNECT_USERS => 'Zoom連携ユーザー管理',
                AdminOperationalLog::FUNCTION_RECAPTCHA => 'reCAPTCHAv3設定',
                AdminOperationalLog::FUNCTION_PAYMENT_ERRORS => '決済エラー回数一覧',
                AdminOperationalLog::FUNCTION_PAYMENT_SETTING => '決済設定',
            ],
            'functionCode' => [
                'Admins' => AdminOperationalLog::FUNCTION_ADMINS,
                'SiteSetting' => AdminOperationalLog::FUNCTION_SITE_SETTING,
                'Labels' => AdminOperationalLog::FUNCTION_LABELS,
                'Tags' => AdminOperationalLog::FUNCTION_TAGS,
                'Options' => AdminOperationalLog::FUNCTION_OPTIONS,
                'Events' => AdminOperationalLog::FUNCTION_EVENTS,
                'AutoReplyMails' => AdminOperationalLog::FUNCTION_AUTO_REPLY_MAILS,
                'EventHolidays' => AdminOperationalLog::FUNCTION_EVENT_HOLIDAYS,
                'formPatternsReserve' => AdminOperationalLog::FUNCTION_RESERVATION_FORM_PATTERNS,
                'formPatternsUser' => AdminOperationalLog::FUNCTION_USER_FORM_PATTERNS,
                'UserAuthorities' => AdminOperationalLog::FUNCTION_USER_AUTHORITIES,
                'FileGroups' => AdminOperationalLog::FUNCTION_FILE_GROUPS,
                'News' => AdminOperationalLog::FUNCTION_NEWS,
                'Terms' => AdminOperationalLog::FUNCTION_TERMS,
                'Cms' => AdminOperationalLog::FUNCTION_CMS,
                'AnalysisTags' => AdminOperationalLog::FUNCTION_ANALYSIS_TAGS,
                'ColorChips' => AdminOperationalLog::FUNCTION_COLOR_CHIPS,
                'Words' => AdminOperationalLog::FUNCTION_WORDS,
                'Holidays' => AdminOperationalLog::FUNCTION_HOLIDAYS,
                'MailDeliveries' => AdminOperationalLog::FUNCTION_MAIL_DELIVERIES,
                'BounceMails' => AdminOperationalLog::FUNCTION_BOUNCE_MAILS,
                'FormGroupsUsers' => AdminOperationalLog::FUNCTION_FORM_GROUPS_USERS,
                'FormGroupsReservations' => AdminOperationalLog::FUNCTION_FORM_GROUPS_RESERVATIONS,
                'Users' => AdminOperationalLog::FUNCTION_USERS,
                'Reservations' => AdminOperationalLog::FUNCTION_RESERVATIONS,
                'Organizers' => AdminOperationalLog::FUNCTION_ORGANIZERS,
                'AdminAuthorities' => AdminOperationalLog::FUNCTION_ADMIN_AUTHORITIES,
                'ReceptionStatuses' => AdminOperationalLog::FUNCTION_RECEPTION_STATUSES,
                'SmartLocks' => AdminOperationalLog::FUNCTION_SMART_LOCKS,
                'ZoomConnectUsers' => AdminOperationalLog::FUNCTION_ZOOM_CONNECT_USERS,
                'Recaptcha' => AdminOperationalLog::FUNCTION_RECAPTCHA,
                'PaymentErrors' => AdminOperationalLog::FUNCTION_PAYMENT_ERRORS,
                'Payment' => AdminOperationalLog::FUNCTION_PAYMENT_SETTING,
            ],
            'type' => [
                AdminOperationalLog::TYPE_ADD => '新規作成',
                AdminOperationalLog::TYPE_EDIT => '編集',
                AdminOperationalLog::TYPE_DELETE => '削除',
                AdminOperationalLog::TYPE_TOGETHER_EDIT => '一括編集',
                AdminOperationalLog::TYPE_UPDATE_PUBLIC => '公開設定更新',
                AdminOperationalLog::TYPE_NEWS_SETTING => 'お知らせ表示設定',
                AdminOperationalLog::TYPE_WORD_EDIT => '文言設定編集',
                AdminOperationalLog::TYPE_COPY => '複製',
                AdminOperationalLog::TYPE_ERROR_WORD_EDIT => 'エラー文言設定編集',
                AdminOperationalLog::TYPE_STATUS_WORD_EDIT => '予約ステータス文言設定編集',
                AdminOperationalLog::TYPE_PREF_WORD_EDIT => '都道府県文言設定編集',
                AdminOperationalLog::TYPE_SEND_EXCLUDE_OFF => '「送信しない」へ一括編集',
                AdminOperationalLog::TYPE_SEND_EXCLUDE_ON => '「送信する」へ一括編集',
                AdminOperationalLog::TYPE_TOGETHER_DELETE => '一括削除',
                AdminOperationalLog::TYPE_PAYMENT_METHOD_WORD_EDIT => '決済ステータス文言設定編集',
                AdminOperationalLog::TYPE_PAYMENT_STATUS_WORD_EDIT => '決済方法文言設定編集',
                AdminOperationalLog::TYPE_WITHDRAW => '退会',
                AdminOperationalLog::TYPE_CANCEL => 'キャンセル',
                AdminOperationalLog::TYPE_CSV_UPLOAD => 'CSVアップロード',
                AdminOperationalLog::TYPE_UPDATE_STATUS => 'ステータス変更',
                AdminOperationalLog::TYPE_RECEPTION_STATUS_WORD_EDIT => '受付ステータス文言設定編集',
                AdminOperationalLog::TYPE_AKERUN => 'Akerun設定',
                AdminOperationalLog::TYPE_UNLOCK => 'ロック解除',
                AdminOperationalLog::TYPE_APPROVAL => '承認',
            ],
            'typeCode' => [
                'add' => AdminOperationalLog::TYPE_ADD,
                'edit' => AdminOperationalLog::TYPE_EDIT,
                'delete' => AdminOperationalLog::TYPE_DELETE,
                'copy' => AdminOperationalLog::TYPE_COPY,
                'cancel' => AdminOperationalLog::TYPE_CANCEL,
                'togetherEdit' => AdminOperationalLog::TYPE_TOGETHER_EDIT,
                'updatePublic' => AdminOperationalLog::TYPE_UPDATE_PUBLIC,
                'setting' => AdminOperationalLog::TYPE_NEWS_SETTING,
                'wordEdit' => AdminOperationalLog::TYPE_WORD_EDIT,
                'errorWordEdit' => AdminOperationalLog::TYPE_ERROR_WORD_EDIT,
                'statusWordEdit' => AdminOperationalLog::TYPE_STATUS_WORD_EDIT,
                'prefWordEdit' => AdminOperationalLog::TYPE_PREF_WORD_EDIT,
                'sendOff' => AdminOperationalLog::TYPE_SEND_EXCLUDE_OFF,
                'sendOn' => AdminOperationalLog::TYPE_SEND_EXCLUDE_ON,
                'togetherDelete' => AdminOperationalLog::TYPE_TOGETHER_DELETE,
                'deleteMany' => AdminOperationalLog::TYPE_TOGETHER_DELETE,
                'paymentMethodWordEdit' => AdminOperationalLog::TYPE_PAYMENT_METHOD_WORD_EDIT,
                'paymentStatusWordEdit' => AdminOperationalLog::TYPE_PAYMENT_STATUS_WORD_EDIT,
                'withdraw' => AdminOperationalLog::TYPE_WITHDRAW,
                'import' => AdminOperationalLog::TYPE_CSV_UPLOAD,
                'updateStatus' => AdminOperationalLog::TYPE_UPDATE_STATUS,
                'receptionStatusWordEdit' => AdminOperationalLog::TYPE_RECEPTION_STATUS_WORD_EDIT,
                'akerun' => AdminOperationalLog::TYPE_AKERUN,
                'unlock' => AdminOperationalLog::TYPE_UNLOCK,
                'approval' => AdminOperationalLog::TYPE_APPROVAL,
            ],
        ],
        //会員権限
        'userAuthority' => [
            'selectAll' => [
                UserAuthority::SELECT_ALL => '全て',
            ],
            'front' => [
                'All' => '全て',
                'Login' => 'ログイン',
                'Logout' => 'ログアウト',
                'Index' => 'トップページ',
                'Reminder' => 'リマインダー',
                'User' => 'ユーザー',
                'Reservations' => '予約',
                'WaitingCancellation' => 'キャンセル待ち',
                'Guest' => 'ゲストログイン',
                'Event' => '予約枠',
                'News' => 'お知らせ',
                'Rule' => '利用規約',
                'Inquiry' => 'お問い合わせ',
                'Optin' => 'メール認証',
                'Api' => '埋め込みカレンダー',
                'Policy' => '利用規約',
                'Privacy' => '個人情報の取り扱いについて',
                'Zip' => '住所検索',
            ],
            'frontCode' => [
                'All' => 'All__all',
            ],
            'frontValue' => [
                'All' => [
                    'All__all' => '全て',
                ],
                // トップページ
                'Index' => [
                    'Index__all' => 'トップページ',
                ],
                // リマインダー
                'Reminder' => [
                    'Reminder__all' => 'リマインダー',
                ],
                // ユーザー
                'User' => [
                    'User_add' => '会員登録',
                    'User_detail' => '会員情報詳細',
                    'User_edit' => '会員情報変更',
                    'User_withdraw' => '退会',
                    'User_passwordEdit' => 'パスワード変更',
                ],
                // 予約
                'Reservations' => [
                    'Reservations_calendar' => '予約状況',
                    'Reservations_add' => '予約登録',
                    'Reservations_view' => '予約詳細',
                    'Reservations_edit' => '予約変更',
                    'Reservations_cancel' => '予約キャンセル',
                    'Reservations_history' => '予約履歴',
                ],
                //キャンセル待ち
                'WaitingCancellation' => [
                    'WaitingCancellation__all' => 'キャンセル待ち',
                ],
                // 予約枠
                'Events' => [
                    'Events__all' => '予約枠詳細',
                ],
                // お知らせ
                'News' => [
                    'News__all' => 'お知らせ',
                ],
                // 利用規約
                'Rule' => [
                    'Rule__all' => '利用規約',
                ],
                // お問い合わせ
                'Inquiry' => [
                    'Inquiry__all' => 'お問い合わせ',
                ],
                // オプトイン
                'Optin' => [
                    'Optin__all' => 'メール認証',
                ],
            ],
            //別の権限で見る場合コントローラー名_アクション名で設定
            'replaceAuthority' => [
                'User_mailAdd' => 'Optin__all',
                'User_token' => 'Optin__all',
                'User_mailEdit' => 'User_edit',
                'User_mailEditApproval' => 'User_edit',
                'User_mailEditApprovalFinish' => 'User_edit',
                'Reservations_mailAdd' => 'Optin__all',
                'Reservations_token' => 'Optin__all',
                'Guest_login' => 'Reservations_view',
                'Guest_code' => 'Reservations_view',
                'Guest_reservationDetail' => 'Reservations_view',
                'Guest_reservationCancel' => 'Reservations_cancel',
                'Guest_reservationEdit' => 'Reservations_edit',
            ],
        ],
        // 予約枠
        'event' => [
            'common' => [
                Event::COMMON_FLG_ON => '利用する',
                Event::COMMON_FLG_OFF => '利用しない',
            ],
            'calendarType' => [
                Event::CALENDAR_TYPE_TIME_1DAY => '時間表示（1日）',
                Event::CALENDAR_TYPE_TIME_1WEEK => '時間表示（1週間）',
                Event::CALENDAR_TYPE_DAY_1DAY => '日にち表示（1日）',
                Event::CALENDAR_TYPE_DAY_1WEEK => '日にち表示（1週間）',
                Event::CALENDAR_TYPE_MONTH => '1ヶ月表示',
                Event::CALENDAR_TYPE_SUBJECT_1DAY => '時間割表示（1日）',
                Event::CALENDAR_TYPE_SUBJECT_1WEEK => '時間割表示（1週間）',
                Event::CALENDAR_TYPE_LIST => '一覧表示',
            ],
            'calendarTypeClass' => [
                Event::CALENDAR_TYPE_TIME_1DAY => 'TimeType',
                Event::CALENDAR_TYPE_TIME_1WEEK => 'TimeType',
                Event::CALENDAR_TYPE_DAY_1DAY => 'DayType',
                Event::CALENDAR_TYPE_DAY_1WEEK => 'DayType',
                Event::CALENDAR_TYPE_MONTH => 'MonthType',
                Event::CALENDAR_TYPE_SUBJECT_1DAY => 'SubjectType',
                Event::CALENDAR_TYPE_SUBJECT_1WEEK => 'SubjectType',
                Event::CALENDAR_TYPE_LIST => 'ListType',
            ],
            'calendarTypeWordKey' => [
                Event::CALENDAR_TYPE_TIME_1DAY => 'Time1Day',
                Event::CALENDAR_TYPE_TIME_1WEEK => 'Time1Week',
                Event::CALENDAR_TYPE_DAY_1DAY => 'Day1Day',
                Event::CALENDAR_TYPE_DAY_1WEEK => 'Day1Week',
                Event::CALENDAR_TYPE_MONTH => 'Month',
                Event::CALENDAR_TYPE_SUBJECT_1DAY => 'Subject1Day',
                Event::CALENDAR_TYPE_SUBJECT_1WEEK => 'Subject1Week',
                Event::CALENDAR_TYPE_LIST => 'List',
            ],
            'calendarTypeSvg' => [
                Event::CALENDAR_TYPE_TIME_1DAY => 'icon_calendar_time',
                Event::CALENDAR_TYPE_TIME_1WEEK => 'icon_calendar_time',
                Event::CALENDAR_TYPE_DAY_1DAY => 'icon_calendar_day',
                Event::CALENDAR_TYPE_DAY_1WEEK => 'icon_calendar_day',
                Event::CALENDAR_TYPE_MONTH => 'icon_calendar_month',
                Event::CALENDAR_TYPE_SUBJECT_1DAY => 'icon_calendar_subject',
                Event::CALENDAR_TYPE_SUBJECT_1WEEK => 'icon_calendar_subject',
                Event::CALENDAR_TYPE_LIST => 'icon_calendar_list',
            ],
            'calendarTypeWeekDayText' => [
                Event::CALENDAR_TYPE_TIME_1DAY => 'Day',
                Event::CALENDAR_TYPE_TIME_1WEEK => 'Week',
                Event::CALENDAR_TYPE_DAY_1DAY => 'Day',
                Event::CALENDAR_TYPE_DAY_1WEEK => 'Week',
                Event::CALENDAR_TYPE_MONTH => null,
                Event::CALENDAR_TYPE_SUBJECT_1DAY => 'Day',
                Event::CALENDAR_TYPE_SUBJECT_1WEEK => 'Week',
                Event::CALENDAR_TYPE_LIST => null,
            ],
            'type' => [
                Event::TYPE_TIME => '時間単位での予約',
                Event::TYPE_DAY => '日にち単位での予約',
            ],
            'typeUnit' => [
                Event::TYPE_TIME => '分',
                Event::TYPE_DAY => '日',
            ],
            'publicFlg' => [
                Event::PUBLIC_FLG_ON => '公開',
                Event::PUBLIC_FLG_OFF => '非公開',
            ],
            'backgroundColorType' => [
                Event::BACKGROUND_COLOR_TYPE_DEFAULT => 'デフォルト',
                Event::BACKGROUND_COLOR_TYPE_COLOR_CODE => 'カラーの変更',
            ],
            'plan' => [
                Event::PLAN_SINGLE => '一律',
                Event::PLAN_MULTIPLE => 'プラン',
            ],
            'deadlineType' => [
                Event::DEADLINE_TYPE_TIME => '時間前',
                Event::DEADLINE_TYPE_DAY => '日前',
            ],
            'deadlineTime' => [
                'interval' => 1,
                'min' => 0,
                'max' => 23,
                'suffix' => '時',
            ],
            'stockDisplayType' => [
                Event::STOCK_DISPLAY_TYPE_NUMBER => '数字で表示',
                Event::STOCK_DISPLAY_TYPE_ICON => '記号で表示',
            ],
            'symbolicDisp' => [
                Event::SYMBOLIC_DISP_HIDE => '表示なし',
                Event::SYMBOLIC_DISP_DOUBLECIRCLE => '◎',
                Event::SYMBOLIC_DISP_CIRCLE => '〇',
                Event::SYMBOLIC_DISP_TRIANGLE => '▲',
                Event::SYMBOLIC_DISP_CROSS => '×',
                Event::SYMBOLIC_DISP_EMPTY => '空',
                Event::SYMBOLIC_DISP_FULL => '満',
                Event::SYMBOLIC_DISP_NONE => '--',
            ],
            'symbolicDispClass' => [
                Event::SYMBOLIC_DISP_HIDE => '',
                Event::SYMBOLIC_DISP_DOUBLECIRCLE => 'icon_dubblecircle',
                Event::SYMBOLIC_DISP_CIRCLE => 'icon_circle',
                Event::SYMBOLIC_DISP_TRIANGLE => 'icon_triangle',
                Event::SYMBOLIC_DISP_CROSS => 'icon_cross',
                Event::SYMBOLIC_DISP_EMPTY => 'icon_empty',
                Event::SYMBOLIC_DISP_FULL => 'icon_full',
                Event::SYMBOLIC_DISP_NONE => 'icon_none',
            ],
            'multipleTimePlanType' => [
                Event::MULTIPLE_TIME_PLAN_TYPE_SINGLE => 'ラジオボタンで表示（一つのみ選択可）',
                Event::MULTIPLE_TIME_PLAN_TYPE_MULTI => 'チェックボックスで表示（複数選択可）',
            ],
            'images' => [
                'num' => 3, //画像設定可能数
            ],
            'week' => [
                Event::WEEK_MONDAY => '月',
                Event::WEEK_TUESDAY => '火',
                Event::WEEK_WEDNESDAY => '水',
                Event::WEEK_THURSDAY => '木',
                Event::WEEK_FRIDAY => '金',
                Event::WEEK_SATURDAY => '土',
                Event::WEEK_SUNDAY => '日',
                Event::WEEK_HOLIDAY => '祝日',
            ],
            'usageTimeNotation' => [
                Event::USAGE_TIME_NOTATION_USE_TIME => '利用時間で表示',
                Event::USAGE_TIME_NOTATION_END_TIME => '終了時間で表示',
            ],
            'duplicationCheckFlg' => [
                Event::COMMON_FLG_ON => '対象にする',
                Event::COMMON_FLG_OFF => '対象外',
            ],
            'qrCodeFlg' => [
                Event::QR_CODE_FLG_ON => '表示する',
                Event::QR_CODE_FLG_OFF => '表示しない',
            ],
            'deadlineCriterion' => [
                Event::CRITERION_FROM => '開始時間',
                Event::CRITERION_TO => '終了時間',
            ],
        ],
        'eventPlans' => [
            'publicFlg' => [
                EventPlan::PUBLIC_FLG_ON => '公開',
                EventPlan::PUBLIC_FLG_OFF => '非公開',
            ],
        ],
        'eventRemarks' => [
            'detailDisplayFlg' => [
                EventRemark::DETAIL_DISPLAY_FLG_ON => '表示',
                EventRemark::DETAIL_DISPLAY_FLG_OFF => '非表示',
            ],
        ],
        // オプション
        'option' => [
            'publicFlg' => [
                Option::PUBLIC_FLG_ON => '公開',
                Option::PUBLIC_FLG_OFF => '非公開',
            ],
        ],
        // オプショングループ
        'optionGroup' => [
            'selectType' => [
                FormItemOptionGroup::SELECT_TYPE_SINGLE => '１つ選択',
                FormItemOptionGroup::SELECT_TYPE_MULTIPLE => '複数選択',
            ],
        ],
        // 会員
        'user' => [
            'guestFlg' => [
                User::GUEST_FLG_OFF => '会員',
                User::GUEST_FLG_ON => 'ゲスト',
            ],
            'withdrawalFlg' => [
                User::WITHDRAWAL_FLG_OFF => '未退会',
                User::WITHDRAWAL_FLG_ON => '退会済み',
            ],
        ],
        // 予約
        'reservation' => [
            'reservationType' => [
                ReservationsTable::RESERVATION_TYPE_EXISTING_USER => '会員から選択して予約',
                ReservationsTable::RESERVATION_TYPE_NEW_USER => '会員登録と同時に予約',
                ReservationsTable::RESERVATION_TYPE_NON_USER => 'ゲスト予約',
            ],
            'reservationTypeWord' => [
                ReservationsTable::RESERVATION_TYPE_EXISTING_USER => '',
                ReservationsTable::RESERVATION_TYPE_NEW_USER => 'reservation/newUser',
                ReservationsTable::RESERVATION_TYPE_NON_USER => 'reservation/nonUser',
            ],
            'statusClass' => [
                ReservationStatus::STATUS_TYPE_FIXED => 'is-kakutei',
                ReservationStatus::STATUS_TYPE_CANCEL => 'is-cancel',
                ReservationStatus::STATUS_TYPE_TENTATIVE => 'is-kari',
                ReservationStatus::STATUS_TYPE_VISIT => 'is-sumi',
                ReservationStatus::STATUS_TYPE_ABSENCE => 'is-kesseki',
            ],
            'calendarPopupType' => [
                AbstractCalendarPopup::TYPE_EVENT_LIST,
                AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE,
                AbstractCalendarPopup::TYPE_MULTIPLE_DATE_TIMETABLE,
            ],
            'calendarPopupTypeClass' => [
                AbstractCalendarPopup::TYPE_EVENT_LIST => 'EventListPopup',
                AbstractCalendarPopup::TYPE_SINGLE_DATE_TIMETABLE => 'SingleDateTimetablePopup',
                AbstractCalendarPopup::TYPE_MULTIPLE_DATE_TIMETABLE => 'MultipleDateTimetablePopup',
            ],
            'repeatReservationType' => [
                Reservation::RESERVATION_TYPE_ONE_RESERVATION => '１回予約',
                Reservation::RESERVATION_TYPE_REPEAT_RESERVATION => '複数日予約',
            ]
        ],
        // 受付
        'reception' => [
            'statusClass' => [
                ReceptionStatus::STATUS_TYPE_ADMISSION => 'is-nyujotyu',
                ReceptionStatus::STATUS_TYPE_EXIT => 'is-taijo',
            ],
        ],
        // フォーム
        'form' => [
            // フォームタイプ
            'formType' => [
                FormGroup::FORM_TYPE_USER => '顧客情報',
                FormGroup::FORM_TYPE_RESERVATION => '予約内容',
            ],
            'operationUrl' => [
                FormGroup::FORM_TYPE_USER => 'FormGroupsUsers',
                FormGroup::FORM_TYPE_RESERVATION => 'FormGroupsReservations',
            ],
            // 予約画面表示可能なタイプ
            'canReservationDisplayFormType' => [
                FormGroup::FORM_TYPE_USER,
            ],
            // グループ名表示
            'nameDisplayFlg' => [
                FormGroup::NAME_DISPLAY_FLG_ON => '表示',
                FormGroup::NAME_DISPLAY_FLG_OFF => '非表示',
            ],
            // 入力タイプ
            'inputType' => [
                FormItem::INPUT_TYPE_USER_AUTHORITY => '顧客の権限',
                FormItem::INPUT_TYPE_MAIL => 'メールアドレス',
                FormItem::INPUT_TYPE_MAIL_CONFIRM => 'メールアドレス（確認）',
                FormItem::INPUT_TYPE_LOGIN_ID => 'ログインID',
                FormItem::INPUT_TYPE_PASSWORD => 'パスワード',
                FormItem::INPUT_TYPE_PASSWORD_CONFIRM => 'パスワード（確認）',
                FormItem::INPUT_TYPE_USAGE_DATE => '利用日',
                FormItem::INPUT_TYPE_USAGE_TIME => '利用時間',
                FormItem::INPUT_TYPE_RESERVATION_TIME => '予約時間',
                FormItem::INPUT_TYPE_RESERVATION_NUMBER => '予約数',
                FormItem::INPUT_TYPE_LABEL => '【予約枠】カテゴリー',
                FormItem::INPUT_TYPE_TAG => '【予約枠】絞り込みキーワード',
                FormItem::INPUT_TYPE_EVENT_NAME => '【予約枠】予約枠名',
                FormItem::INPUT_TYPE_EVENT_SCHEDULE_DATE => '【予約枠】予約利用期間',
                FormItem::INPUT_TYPE_EVENT_SCHEDULE_TIME => '【予約枠】実施時間',
                FormItem::INPUT_TYPE_EVENT_USAGE_TIME => '【予約枠】予約時間設定',
                FormItem::INPUT_TYPE_INTERVAL_TIME => '【予約枠】インターバル',
                FormItem::INPUT_TYPE_EVENT_CHARGE => '【予約枠】料金',
                FormItem::INPUT_TYPE_REGISTRATION_DEADLINE => '【予約枠】予約受付締切タイミング',
                FormItem::INPUT_TYPE_EDITING_DEADLINE => '【予約枠】予約変更締切タイミング',
                FormItem::INPUT_TYPE_CANCELLATION_DEADLINE => '【予約枠】予約キャンセル締切タイミング',
                FormItem::INPUT_TYPE_TEXT => 'テキストボックス',
                FormItem::INPUT_TYPE_TEXTAREA => 'テキストエリア',
                FormItem::INPUT_TYPE_MULTI_TEXTBOX => 'マルチテキストボックス',
                FormItem::INPUT_TYPE_RADIO => 'ラジオボタン',
                FormItem::INPUT_TYPE_SELECT => 'セレクトボックス',
                FormItem::INPUT_TYPE_CHECKBOX => 'チェックボックス',
                FormItem::INPUT_TYPE_DATE_SELECT => '年月日',
                FormItem::INPUT_TYPE_FULL_NAME => '氏名',
                FormItem::INPUT_TYPE_PHONE_NUMBER => '電話番号',
                FormItem::INPUT_TYPE_PREFECTURE => '都道府県',
                FormItem::INPUT_TYPE_SEARCH_ADDRESS => '住所検索',
                FormItem::INPUT_TYPE_RESERVATION_OPTION => 'オプション予約',
                FormItem::INPUT_TYPE_EVENT_REMARK => '予約枠備考',
                FormItem::INPUT_TYPE_VIDEO_MEETING_ORGANIZER => '【ビデオ会議】主催者',
                FormItem::INPUT_TYPE_VIDEO_MEETING_TYPE => '【ビデオ会議】種別',
                FormItem::INPUT_TYPE_VIDEO_MEETING_URL => '【ビデオ会議】URL',
                FormItem::INPUT_TYPE_VIDEO_MEETING_ID => '【ビデオ会議】ID',
                FormItem::INPUT_TYPE_VIDEO_MEETING_PASSWORD => '【ビデオ会議】パスワード',
                FormItem::INPUT_TYPE_EXPIRATION_DATE => '有効期間',
                FormItem::INPUT_TYPE_AKERUN_USER_ID => 'AkerunユーザーID',
            ],
            // 入力タイプクラス
            'inputTypeClass' => [
                FormItem::INPUT_TYPE_USER_AUTHORITY => 'UserAuthority',
                FormItem::INPUT_TYPE_MAIL => 'Mail',
                FormItem::INPUT_TYPE_MAIL_CONFIRM => 'MailConfirm',
                FormItem::INPUT_TYPE_LOGIN_ID => 'LoginId',
                FormItem::INPUT_TYPE_PASSWORD => 'Password',
                FormItem::INPUT_TYPE_PASSWORD_CONFIRM => 'PasswordConfirm',
                FormItem::INPUT_TYPE_USAGE_DATE => 'UsageDate',
                FormItem::INPUT_TYPE_USAGE_TIME => 'UsageTime',
                FormItem::INPUT_TYPE_RESERVATION_TIME => 'ReservationTime',
                FormItem::INPUT_TYPE_RESERVATION_NUMBER => 'ReservationNumber',
                FormItem::INPUT_TYPE_LABEL => 'Label',
                FormItem::INPUT_TYPE_TAG => 'Tag',
                FormItem::INPUT_TYPE_EVENT_NAME => 'EventName',
                FormItem::INPUT_TYPE_EVENT_SCHEDULE_DATE => 'EventScheduleDate',
                FormItem::INPUT_TYPE_EVENT_SCHEDULE_TIME => 'EventScheduleTime',
                FormItem::INPUT_TYPE_EVENT_USAGE_TIME => 'EventUsageTime',
                FormItem::INPUT_TYPE_INTERVAL_TIME => 'IntervalTime',
                FormItem::INPUT_TYPE_EVENT_CHARGE => 'EventCharge',
                FormItem::INPUT_TYPE_REGISTRATION_DEADLINE => 'RegistrationDeadline',
                FormItem::INPUT_TYPE_EDITING_DEADLINE => 'EditingDeadline',
                FormItem::INPUT_TYPE_CANCELLATION_DEADLINE => 'CancellationDeadline',
                FormItem::INPUT_TYPE_TEXT => 'Text',
                FormItem::INPUT_TYPE_TEXTAREA => 'Textarea',
                FormItem::INPUT_TYPE_MULTI_TEXTBOX => 'MultiTextbox',
                FormItem::INPUT_TYPE_RADIO => 'Radio',
                FormItem::INPUT_TYPE_SELECT => 'Select',
                FormItem::INPUT_TYPE_CHECKBOX => 'Checkbox',
                FormItem::INPUT_TYPE_DATE_SELECT => 'DateSelect',
                FormItem::INPUT_TYPE_FULL_NAME => 'FullName',
                FormItem::INPUT_TYPE_PHONE_NUMBER => 'PhoneNumber',
                FormItem::INPUT_TYPE_PREFECTURE => 'Prefecture',
                FormItem::INPUT_TYPE_SEARCH_ADDRESS => 'SearchAddress',
                FormItem::INPUT_TYPE_RESERVATION_OPTION => 'ReservationOption',
                FormItem::INPUT_TYPE_EVENT_REMARK => 'EventRemark',
                FormItem::INPUT_TYPE_VIDEO_MEETING_ORGANIZER => 'VideoMeetingOrganizer',
                FormItem::INPUT_TYPE_VIDEO_MEETING_TYPE => 'VideoMeetingType',
                FormItem::INPUT_TYPE_VIDEO_MEETING_URL => 'VideoMeetingUrl',
                FormItem::INPUT_TYPE_VIDEO_MEETING_ID => 'VideoMeetingId',
                FormItem::INPUT_TYPE_VIDEO_MEETING_PASSWORD => 'VideoMeetingPassword',
                FormItem::INPUT_TYPE_EXPIRATION_DATE => 'ExpirationDate',
                FormItem::INPUT_TYPE_AKERUN_USER_ID => 'AkerunUserId',
            ],
            // 必須
            'requiredFlg' => [
                FormItem::REQUIRED_FLG_ON => '必須',
                FormItem::REQUIRED_FLG_OFF => '任意',
            ],
            // 予約画面表示
            'reservationDisplayFlg' => [
                FormItem::RESERVATION_DISPLAY_FLG_ON => '表示',
                FormItem::RESERVATION_DISPLAY_FLG_OFF => '非表示',
            ],
            // 入力変換
            'textInputTranslate' => [
                FormItemDetail::TEXT_INPUT_TRANSLATE_HALF_SIZE_KATAKANA => '半角カナ',
                FormItemDetail::TEXT_INPUT_TRANSLATE_HALF_SIZE_NUMBER => '半角数字',
                FormItemDetail::TEXT_INPUT_TRANSLATE_FULL_SIZE_KATAKANA => '全角カナ',
                FormItemDetail::TEXT_INPUT_TRANSLATE_FULL_SIZE_NUMBER => '全角数字',
            ],
            // 入力変換クラス
            'textInputTranslateClass' => [
                FormItemDetail::TEXT_INPUT_TRANSLATE_HALF_SIZE_KATAKANA => 'HalfSizeKatakana',
                FormItemDetail::TEXT_INPUT_TRANSLATE_HALF_SIZE_NUMBER => 'HalfSizeNumber',
                FormItemDetail::TEXT_INPUT_TRANSLATE_FULL_SIZE_KATAKANA => 'FullSizeKatakana',
                FormItemDetail::TEXT_INPUT_TRANSLATE_FULL_SIZE_NUMBER => 'FullSizeNumber',
            ],
            // 入力チェック
            'textInputCheck' => [
                FormItemDetail::TEXT_INPUT_CHECK_MAIL => 'メールアドレス',
                FormItemDetail::TEXT_INPUT_CHECK_ZIP_CODE => '郵便番号',
                FormItemDetail::TEXT_INPUT_CHECK_PHONE_NUMBER_WITH_HYPHEN => '電話番号（ハイフン有り）',
                FormItemDetail::TEXT_INPUT_CHECK_PHONE_NUMBER_WITHOUT_HYPHEN => '電話番号（ハイフン無し）',
                FormItemDetail::TEXT_INPUT_CHECK_DATE_FORMAT => '年月日',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE => '半角',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_NUMBER => '半角数字',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_ALPHAMERIC => '半角英数字',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_ALPHANUMERIC_SYMBOL => '半角英数記号',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_KATAKANA => '半角カナ',
                FormItemDetail::TEXT_INPUT_CHECK_FULL_SIZE => '全角',
                FormItemDetail::TEXT_INPUT_CHECK_FULL_SIZE_HIRAGANA => '全角かな',
                FormItemDetail::TEXT_INPUT_CHECK_FULL_SIZE_KATAKANA => '全角カナ',
            ],
            // 入力チェッククラス
            'textInputCheckClass' => [
                FormItemDetail::TEXT_INPUT_CHECK_MAIL => 'Mail',
                FormItemDetail::TEXT_INPUT_CHECK_ZIP_CODE => 'ZipCode',
                FormItemDetail::TEXT_INPUT_CHECK_PHONE_NUMBER_WITH_HYPHEN => 'PhoneNumberWithHyphen',
                FormItemDetail::TEXT_INPUT_CHECK_PHONE_NUMBER_WITHOUT_HYPHEN => 'PhoneNumberWithoutHyphen',
                FormItemDetail::TEXT_INPUT_CHECK_DATE_FORMAT => 'DateFormat',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE => 'HalfSize',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_NUMBER => 'HalfSizeNumber',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_ALPHAMERIC => 'HalfSizeAlphameric',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_ALPHANUMERIC_SYMBOL => 'HalfSizeAlphanumericSymbol',
                FormItemDetail::TEXT_INPUT_CHECK_HALF_SIZE_KATAKANA => 'HalfSizeKatakana',
                FormItemDetail::TEXT_INPUT_CHECK_FULL_SIZE => 'FullSize',
                FormItemDetail::TEXT_INPUT_CHECK_FULL_SIZE_HIRAGANA => 'FullSizeHiragana',
                FormItemDetail::TEXT_INPUT_CHECK_FULL_SIZE_KATAKANA => 'FullSizeKatakana',
            ],
            // 日付上限タイプ
            'dateUpperLimitType' => [
                FormItemDetail::DATE_UPPER_LIMIT_TYPE_ABSOLUTE => '年月日で設定',
                FormItemDetail::DATE_UPPER_LIMIT_TYPE_RELATIVE => '年数で設定',
            ],
        ],
        // フォームパターン
        'formPattern' => [
            'inList' => [
                'displayOnlyItemType' => [
                    FormItem::INPUT_TYPE_LOGIN_ID => true,
                    FormItem::INPUT_TYPE_PASSWORD => true,
                ],
                'onlyAdminItemType' => [
                    FormItem::INPUT_TYPE_EXPIRATION_DATE => true,
                ],
                'displayAndOnlyAdminItemType' => [
                    FormItem::INPUT_TYPE_AKERUN_USER_ID => true,
                ],
            ],
            'formTypeName' => [
                FormGroup::FORM_TYPE_USER => '顧客情報の表示パターン',
                FormGroup::FORM_TYPE_RESERVATION => '予約内容の表示パターン',
            ],
            'formTypeUrl' => [
                FormGroup::FORM_TYPE_USER => 'formPatternsUser',
                FormGroup::FORM_TYPE_RESERVATION => 'formPatternsReserve',
            ],
            'displayType' => [
                FormPatternDisplayType::DISPLAY_TYPE_HIDE => FormPatternDisplayType::DISPLAY_TYPE_HIDE,
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY,
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN,
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE,
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN => FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN,
            ],
            'displayTypeExample' => [
                FormPatternDisplayType::DISPLAY_TYPE_HIDE => '①表示しない',
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY => '②表示する',
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_ADMIN => '③表示する：管理画面のみ',
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_ONLY_GUEST_RESERVE => '④表示する：ゲスト予約のみ',
                FormPatternDisplayType::DISPLAY_TYPE_DISPLAY_EDIT_ONLY_ADMIN => '⑤表示する：管理画面からのみ編集可能',
            ],
            'togetherEdit' => [
                'updateLimit' => 10,
            ],
            'selectGroupAll' => [
                'key' => 'all',
                'value' => '全て',
            ],
        ],
        // 自動返信メール
        'autoReplyMail' => [
            'type' => [
                AutoReplyMail::TYPE_USER_ADD => '会員登録',
                AutoReplyMail::TYPE_USER_EDIT => '会員変更',
                AutoReplyMail::TYPE_USER_DELETE => '退会申請',
                AutoReplyMail::TYPE_ID_REMINDER => 'ログインIDリマインダー',
                AutoReplyMail::TYPE_PASSWORD_REMINDER => 'パスワードリマインダー',
                AutoReplyMail::TYPE_RESERVE_ADD => '予約登録',
                AutoReplyMail::TYPE_RESERVE_EDIT => '予約変更',
                AutoReplyMail::TYPE_RESERVE_CANCEL => '予約キャンセル',
                AutoReplyMail::TYPE_RESERVE_CANCELWAIT => 'キャンセル待ち通知',
                AutoReplyMail::TYPE_RESERVE_REMINDER => '予約リマインダー',
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE => '利用終了リマインダー',
                AutoReplyMail::TYPE_OPTIN_USER => 'メール認証：会員登録',
                AutoReplyMail::TYPE_OPTIN_RESERVE => 'メール認証：予約登録',
                AutoReplyMail::TYPE_STATUS_UPDATE => '予約ステータス変更',
                AutoReplyMail::TYPE_NOT_MEMBER_LOGIN => 'ゲスト予約確認用認証コード',
                AutoReplyMail::TYPE_RESERVE_CANCELWAIT_RELEASE => 'キャンセル待ち通知解除',
                AutoReplyMail::TYPE_OPTIN_MAIL_EDIT => 'メールアドレス変更認証',
            ],
            // 会員データを置き換え可能なタイプ
            'userType' => [
                AutoReplyMail::TYPE_USER_ADD,
                AutoReplyMail::TYPE_USER_EDIT,
                AutoReplyMail::TYPE_USER_DELETE,
                AutoReplyMail::TYPE_ID_REMINDER,
                AutoReplyMail::TYPE_PASSWORD_REMINDER,
                AutoReplyMail::TYPE_RESERVE_ADD,
                AutoReplyMail::TYPE_RESERVE_EDIT,
                AutoReplyMail::TYPE_RESERVE_CANCEL,
                AutoReplyMail::TYPE_RESERVE_REMINDER,
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
                AutoReplyMail::TYPE_STATUS_UPDATE,
                AutoReplyMail::TYPE_OPTIN_MAIL_EDIT,
            ],
            // 予約データを置き換え可能なタイプ
            'reservationType' => [
                AutoReplyMail::TYPE_RESERVE_ADD,
                AutoReplyMail::TYPE_RESERVE_EDIT,
                AutoReplyMail::TYPE_RESERVE_CANCEL,
                AutoReplyMail::TYPE_RESERVE_REMINDER,
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
                AutoReplyMail::TYPE_STATUS_UPDATE,
            ],
            // 変更前の会員データを置き換え可能なタイプ
            'oldUserType' => [
                AutoReplyMail::TYPE_USER_EDIT,
            ],
            // 変更前の予約データを置き換え可能なタイプ
            'oldReservationType' => [
                AutoReplyMail::TYPE_RESERVE_EDIT,
            ],
            'canSetting' => [
                'userAuthority' => [
                    AutoReplyMail::TYPE_USER_ADD,
                    AutoReplyMail::TYPE_USER_EDIT,
                    AutoReplyMail::TYPE_USER_DELETE,
                    AutoReplyMail::TYPE_RESERVE_ADD,
                    AutoReplyMail::TYPE_RESERVE_EDIT,
                    AutoReplyMail::TYPE_RESERVE_CANCEL,
                    AutoReplyMail::TYPE_RESERVE_REMINDER,
                    AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
                    AutoReplyMail::TYPE_STATUS_UPDATE,
                    AutoReplyMail::TYPE_RESERVE_CANCELWAIT,
                ],
                'label' => [
                    AutoReplyMail::TYPE_RESERVE_ADD,
                    AutoReplyMail::TYPE_RESERVE_EDIT,
                    AutoReplyMail::TYPE_RESERVE_CANCEL,
                    AutoReplyMail::TYPE_RESERVE_REMINDER,
                    AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
                    AutoReplyMail::TYPE_STATUS_UPDATE,
                    AutoReplyMail::TYPE_RESERVE_CANCELWAIT,
                ],
            ],
            'canDelete' => [
                AutoReplyMail::TYPE_ID_REMINDER => false,
                AutoReplyMail::TYPE_PASSWORD_REMINDER => false,
                AutoReplyMail::TYPE_OPTIN_USER => false,
                AutoReplyMail::TYPE_OPTIN_RESERVE => false,
                AutoReplyMail::TYPE_NOT_MEMBER_LOGIN => false,
                AutoReplyMail::TYPE_RESERVE_CANCELWAIT_RELEASE => false,
                AutoReplyMail::TYPE_OPTIN_MAIL_EDIT => false,
            ],
            // 再送可能なタイプ
            'canResend' => [
                AutoReplyMail::TYPE_USER_ADD,
                AutoReplyMail::TYPE_USER_EDIT,
                AutoReplyMail::TYPE_USER_DELETE,
                AutoReplyMail::TYPE_RESERVE_ADD,
                AutoReplyMail::TYPE_RESERVE_EDIT,
                AutoReplyMail::TYPE_RESERVE_CANCEL,
                AutoReplyMail::TYPE_RESERVE_REMINDER,
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE,
                AutoReplyMail::TYPE_STATUS_UPDATE,
            ],
            // カテゴリ設定されている管理者のタイプ
            'adminLabelIdType' => [
                AutoReplyMail::TYPE_RESERVE_ADD => '予約登録',
                AutoReplyMail::TYPE_RESERVE_EDIT => '予約変更',
                AutoReplyMail::TYPE_RESERVE_CANCEL => '予約キャンセル',
                AutoReplyMail::TYPE_RESERVE_CANCELWAIT => 'キャンセル待ち通知',
                AutoReplyMail::TYPE_RESERVE_REMINDER => '予約リマインダー',
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE => '利用終了リマインダー',
                AutoReplyMail::TYPE_STATUS_UPDATE => '予約ステータス変更',
            ],
        ],
        'mailDelivery' => [
            'sendType' => [
                MailDelivery::SEND_TYPE_IMMEDIATELY => '即時配信',
                MailDelivery::SEND_TYPE_RESERVE => '予約配信',
            ],
            'sendTime' => [
                'interval' => '30',
                'min' => '00:00',
                'max' => '23:30',
                'suffix' => '時',
            ],
            'status' => [
                MailDelivery::SEND_STATUS_NOT_SEND => '未配信',
                MailDelivery::SEND_STATUS_SENDING => '配信中',
                MailDelivery::SEND_STATUS_SENT => '配信済み',
                MailDelivery::SEND_STATUS_CANCEL => 'キャンセル',
            ],
        ],
        'mailDeliveryHistory' => [
            'status' => [
                MailDeliveryHistory::SEND_STATUS_NOT_SEND => '未配信',
                MailDeliveryHistory::SEND_STATUS_SUCCESS => '成功',
                MailDeliveryHistory::SEND_STATUS_FAILED => '失敗',
                MailDeliveryHistory::SEND_STATUS_EXCLUDE => '除外',
            ],
        ],
        'mailReplace' => [
            'token' => [
                DefaultMailer::REPLACE_USER_ID => 'user_id',
                DefaultMailer::REPLACE_USER_CREATED => 'user_created',
                DefaultMailer::REPLACE_RESERVE_ID => 'reserve_id',
                DefaultMailer::REPLACE_RESERVE_START_DATE => 'reserve_start_date',
                DefaultMailer::REPLACE_RESERVE_START_TIME => 'reserve_start_time',
                DefaultMailer::REPLACE_RESERVE_END_DATE => 'reserve_end_date',
                DefaultMailer::REPLACE_RESERVE_END_TIME => 'reserve_end_time',
                DefaultMailer::REPLACE_RESERVE_PLAN => 'reserve_plan',
                DefaultMailer::REPLACE_RESERVE_STATUS => 'reserve_status',
                DefaultMailer::REPLACE_RESERVE_CHARGE => 'reserve_charge',
                DefaultMailer::REPLACE_RESERVE_PAYMENT_METHOD => 'reserve_payment_method',
                DefaultMailer::REPLACE_RESERVE_PAYMENT_STATUS => 'reserve_payment_status',
                DefaultMailer::REPLACE_RESERVE_CRATED => 'reserve_created',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_PIN => 'reserve_smart_lock_pin',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY => 'reserve_smart_lock_universal_access_key',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_KEY_URL => 'reserve_smart_lock_key_url',
                DefaultMailer::REPLACE_VIDEO_MEETING_INFO => 'video_meeting_info',
                DefaultMailer::REPLACE_ADDITIONAL_CHANGE_URL => 'change_url',
                DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL => 'guest_login_url',
                DefaultMailer::REPLACE_ADDITIONAL_LIMIT => 'limit',
                DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_USER => 'register_url',
                DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_RESERVE => 'register_url',
                DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_CODE => 'code',
                DefaultMailer::REPLACE_ADDITIONAL_CANCELWAIT_RELEASE_URL => 'cancelwait_conf_url',
                DefaultMailer::REPLACE_ADDITIONAL_EVENT_NAME => 'event_name',
                DefaultMailer::REPLACE_ADDITIONAL_START_DATE => 'reserve_start_date',
                DefaultMailer::REPLACE_ADDITIONAL_START_TIME => 'reserve_start_time',
                DefaultMailer::REPLACE_ADDITIONAL_DETAIL_URL => 'reserve_detail_url',
                DefaultMailer::REPLACE_ADDITIONAL_QR_CODE => 'qr_code',
                DefaultMailer::REPLACE_ADDITIONAL_MAIL_EDIT_APPROVAL_URL => 'mail_edit_approval_url',
            ],
            'label' => [
                DefaultMailer::REPLACE_USER_ID => '顧客ID',
                DefaultMailer::REPLACE_USER_CREATED => '顧客登録日時',
                DefaultMailer::REPLACE_RESERVE_ID => '予約ID',
                DefaultMailer::REPLACE_RESERVE_START_DATE => '開始日',
                DefaultMailer::REPLACE_RESERVE_START_TIME => '開始時間',
                DefaultMailer::REPLACE_RESERVE_END_DATE => '終了日',
                DefaultMailer::REPLACE_RESERVE_END_TIME => '終了時間',
                DefaultMailer::REPLACE_RESERVE_PLAN => '複数プラン',
                DefaultMailer::REPLACE_RESERVE_STATUS => 'ステータス',
                DefaultMailer::REPLACE_RESERVE_CHARGE => '予約料金',
                DefaultMailer::REPLACE_RESERVE_PAYMENT_METHOD => '決済方法',
                DefaultMailer::REPLACE_RESERVE_PAYMENT_STATUS => '決済状況',
                DefaultMailer::REPLACE_RESERVE_CRATED => '予約登録日時',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_PIN => 'PIN番号',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY => 'カギ情報URL（ユニバーサルアクセスキー）',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_KEY_URL => 'ロック解除URL',
                DefaultMailer::REPLACE_VIDEO_MEETING_INFO => 'ビデオ会議情報',
                DefaultMailer::REPLACE_ADDITIONAL_CHANGE_URL => 'パスワード変更用URL',
                DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL => 'ゲスト用予約確認画面　URL',
                DefaultMailer::REPLACE_ADDITIONAL_LIMIT => '有効期限',
                DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_USER => '会員登録用URL',
                DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_RESERVE => '予約・会員登録用URL',
                DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_CODE => 'ゲスト予約確認用認証コード',
                DefaultMailer::REPLACE_ADDITIONAL_CANCELWAIT_RELEASE_URL => 'キャンセル待ち通知解除URL',
                DefaultMailer::REPLACE_ADDITIONAL_EVENT_NAME => '予約枠名',
                DefaultMailer::REPLACE_ADDITIONAL_START_DATE => '開始日',
                DefaultMailer::REPLACE_ADDITIONAL_START_TIME => '開始時間',
                DefaultMailer::REPLACE_ADDITIONAL_DETAIL_URL => '予約詳細画面URL',
                DefaultMailer::REPLACE_ADDITIONAL_QR_CODE => 'QRコード',
                DefaultMailer::REPLACE_ADDITIONAL_MAIL_EDIT_APPROVAL_URL => 'メールアドレス変更認証用URL',
            ],
            'column' => [
                DefaultMailer::REPLACE_USER_ID => 'user.id',
                DefaultMailer::REPLACE_USER_CREATED => 'user.created',
                DefaultMailer::REPLACE_RESERVE_ID => 'reservation.id',
                DefaultMailer::REPLACE_RESERVE_START_DATE => 'reservation.usage_timestamp_from',
                DefaultMailer::REPLACE_RESERVE_START_TIME => 'reservation.usage_timestamp_from',
                DefaultMailer::REPLACE_RESERVE_END_DATE => 'reservation.usage_timestamp_to',
                DefaultMailer::REPLACE_RESERVE_END_TIME => 'reservation.usage_timestamp_to',
                DefaultMailer::REPLACE_RESERVE_PLAN => 'reservation.reservation_event_plans',
                DefaultMailer::REPLACE_RESERVE_STATUS => 'reservation.reservation_status_id',
                DefaultMailer::REPLACE_RESERVE_CHARGE => 'reservation.charge',
                DefaultMailer::REPLACE_RESERVE_PAYMENT_METHOD => 'reservation.payment_method_id',
                DefaultMailer::REPLACE_RESERVE_PAYMENT_STATUS => 'reservation.payment_status_id',
                DefaultMailer::REPLACE_RESERVE_CRATED => 'reservation.created',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_PIN => 'reservation.reservation_smart_lock.smart_lock_pin',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY => 'reservation.reservation_smart_lock.smart_lock_key_url',
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_KEY_URL => 'reservation.reservation_smart_lock.smart_lock_key_url',
                DefaultMailer::REPLACE_VIDEO_MEETING_INFO => 'reservation.reservation_video_meetings',
                DefaultMailer::REPLACE_ADDITIONAL_CHANGE_URL => 'additional.change_url',
                DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL => 'additional.guest_login_url',
                DefaultMailer::REPLACE_ADDITIONAL_LIMIT => 'additional.limit',
                DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_USER => 'additional.register_url',
                DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_RESERVE => 'additional.register_url',
                DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_CODE => 'additional.code',
                DefaultMailer::REPLACE_ADDITIONAL_CANCELWAIT_RELEASE_URL => 'additional.cancelwait_conf_url',
                DefaultMailer::REPLACE_ADDITIONAL_EVENT_NAME => 'additional.event_name',
                DefaultMailer::REPLACE_ADDITIONAL_START_DATE => 'additional.usage_timestamp',
                DefaultMailer::REPLACE_ADDITIONAL_START_TIME => 'additional.usage_timestamp',
                DefaultMailer::REPLACE_ADDITIONAL_QR_CODE => 'additional.qr_code',
                DefaultMailer::REPLACE_ADDITIONAL_MAIL_EDIT_APPROVAL_URL => 'additional.mail_edit_approval_url',
            ],
            // 会員データ
            'user' => [
                DefaultMailer::REPLACE_USER_ID,
                DefaultMailer::REPLACE_USER_CREATED,
                DefaultMailer::REPLACE_FORM_ITEM_USER,
            ],
            // 予約データ
            'reservation' => [
                DefaultMailer::REPLACE_RESERVE_ID,
                DefaultMailer::REPLACE_RESERVE_START_DATE,
                DefaultMailer::REPLACE_RESERVE_START_TIME,
                DefaultMailer::REPLACE_RESERVE_END_DATE,
                DefaultMailer::REPLACE_RESERVE_END_TIME,
                DefaultMailer::REPLACE_RESERVE_PLAN,
                DefaultMailer::REPLACE_RESERVE_STATUS,
                DefaultMailer::REPLACE_RESERVE_CHARGE,
                DefaultMailer::REPLACE_RESERVE_PAYMENT_METHOD,
                DefaultMailer::REPLACE_RESERVE_PAYMENT_STATUS,
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_PIN,
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_UNIVERSAL_ACCESS_KEY,
                DefaultMailer::REPLACE_RESERVE_SMART_LOCK_KEY_URL,
                DefaultMailer::REPLACE_RESERVE_CRATED,
                DefaultMailer::REPLACE_FORM_ITEM_RESERVATION,
                DefaultMailer::REPLACE_VIDEO_MEETING_INFO,
                DefaultMailer::REPLACE_ADDITIONAL_DETAIL_URL,
                DefaultMailer::REPLACE_ADDITIONAL_QR_CODE,
            ],
            // 変更前会員データ
            'oldUser' => [
                DefaultMailer::REPLACE_FORM_ITEM_USER,
            ],
            // 変更前予約データ
            'oldReservation' => [
                DefaultMailer::REPLACE_RESERVE_START_DATE,
                DefaultMailer::REPLACE_RESERVE_START_TIME,
                DefaultMailer::REPLACE_RESERVE_END_DATE,
                DefaultMailer::REPLACE_RESERVE_END_TIME,
                DefaultMailer::REPLACE_RESERVE_PLAN,
                DefaultMailer::REPLACE_RESERVE_STATUS,
                DefaultMailer::REPLACE_RESERVE_CHARGE,
                DefaultMailer::REPLACE_RESERVE_PAYMENT_METHOD,
                DefaultMailer::REPLACE_RESERVE_PAYMENT_STATUS,
                DefaultMailer::REPLACE_FORM_ITEM_RESERVATION,
                DefaultMailer::REPLACE_VIDEO_MEETING_INFO,
            ],
            // 追加データ
            'additional' => [
                AutoReplyMail::TYPE_RESERVE_CANCELWAIT => [
                    DefaultMailer::REPLACE_ADDITIONAL_EVENT_NAME,
                    DefaultMailer::REPLACE_ADDITIONAL_START_DATE,
                    DefaultMailer::REPLACE_ADDITIONAL_START_TIME,
                ],
                AutoReplyMail::TYPE_PASSWORD_REMINDER => [
                    DefaultMailer::REPLACE_ADDITIONAL_CHANGE_URL,
                    DefaultMailer::REPLACE_ADDITIONAL_LIMIT,
                ],
                AutoReplyMail::TYPE_RESERVE_ADD => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL,
                ],
                AutoReplyMail::TYPE_RESERVE_EDIT => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL,
                ],
                AutoReplyMail::TYPE_RESERVE_CANCEL => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL,
                ],
                AutoReplyMail::TYPE_RESERVE_REMINDER => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL,
                ],
                AutoReplyMail::TYPE_RESERVE_REMINDER_CLOSE => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL,
                ],
                AutoReplyMail::TYPE_OPTIN_USER => [
                    DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_USER,
                    DefaultMailer::REPLACE_ADDITIONAL_LIMIT,
                ],
                AutoReplyMail::TYPE_OPTIN_RESERVE => [
                    DefaultMailer::REPLACE_ADDITIONAL_REGISTER_URL_RESERVE,
                    DefaultMailer::REPLACE_ADDITIONAL_LIMIT,
                ],
                AutoReplyMail::TYPE_STATUS_UPDATE => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_URL,
                ],
                AutoReplyMail::TYPE_NOT_MEMBER_LOGIN => [
                    DefaultMailer::REPLACE_ADDITIONAL_GUEST_LOGIN_CODE,
                    DefaultMailer::REPLACE_ADDITIONAL_LIMIT,
                ],
                AutoReplyMail::TYPE_RESERVE_CANCELWAIT_RELEASE => [
                    DefaultMailer::REPLACE_ADDITIONAL_CANCELWAIT_RELEASE_URL,
                    DefaultMailer::REPLACE_ADDITIONAL_LIMIT,
                ],
                AutoReplyMail::TYPE_OPTIN_MAIL_EDIT => [
                    DefaultMailer::REPLACE_ADDITIONAL_MAIL_EDIT_APPROVAL_URL,
                    DefaultMailer::REPLACE_ADDITIONAL_LIMIT,
                ],
            ],
        ],
        'bounceMail' => [
            'sendExclude' => [
                BounceMail::SEND_EXCLUDE_FLG_ON => '送信しない',
                BounceMail::SEND_EXCLUDE_FLG_OFF => '送信する',
            ],
        ],
        // ラベル
        'label' => [
            'publicFlg' => [
                Label::PUBLIC_FLG_ON => '公開',
                Label::PUBLIC_FLG_OFF => '非公開',
            ],
            'displayLevel' => [
                Label::DISPLAY_LEVEL_ALL => '全階層',
                Label::DISPLAY_LEVEL_ONE => '1階層のみ',
            ],
            'type' => [
                'create' => 'create',
                'self' => 'self',
                'other' => 'other',
                'reservations' => 'reservations',
            ],
        ],
        // タグ
        'tag' => [
            'publicFlg' => [
                TagGroup::PUBLIC_FLG_ON => '公開',
                TagGroup::PUBLIC_FLG_OFF => '非公開',
            ],
        ],
        // 基本設定
        'system' => [
            'canEdit' => [
                /*
                 * カスタマイズ等で編集不可にしたい場合は
                 * フィールド名 => false　で設定する
                 *
                'login_use_flg' => false,
                'reservation_continuous_flg' => false,
                'reservation_add_user_flg' => false,
                'reservation_add_not_user_flg' => false,
                'optin_flg' => false,
                'user_add_flg' => false,
                 */
            ],
            'common' => [
                SiteSetting::COMMON_USE_FLG_OFF => '利用しない',
                SiteSetting::COMMON_USE_FLG_ON => '利用する',
            ],
            'reservationReminderFlg' => [
                SiteSetting::COMMON_USE_FLG_OFF => '配信しない',
                SiteSetting::COMMON_USE_FLG_ON => '配信する',
            ],
            'reservationEditEventFlg' => [
                SiteSetting::COMMON_USE_FLG_OFF => '許可しない',
                SiteSetting::COMMON_USE_FLG_ON => '許可する',
            ],
            'tagSearchMethod' => [
                SiteSetting::TAG_SEARCH_METHOD_OR => 'OR検索',
                SiteSetting::TAG_SEARCH_METHOD_AND => 'AND検索',
            ],
            'loginFlg' => [
                SiteSetting::LOGIN_USE_FLG_OFF => '利用しない',
                SiteSetting::LOGIN_USE_FLG_ON => '利用する',
                SiteSetting::LOGIN_USE_FLG_REQUIRE => '必須',
            ],
            'reservationReminderType' => [
                SiteSetting::RESERVATION_REMINDER_TYPE_TIME => '時間前',
                SiteSetting::RESERVATION_REMINDER_TYPE_DAY => '日前',
            ],
            'reservationFormTypeFirst' => [
                SiteSetting::RESERVATION_FORM_TYPE_FIRST_USER => '顧客情報の項目が上',
                SiteSetting::RESERVATION_FORM_TYPE_FIRST_RESERVE => '予約内容の項目が上',
            ],
            'calendarTimeDefault' => [
                SiteSetting::CALENDAR_TIME_DEFAULT_1WEEK => '1Week',
                SiteSetting::CALENDAR_TIME_DEFAULT_1DAY => '1Day',
            ],
            'frontPublicFlg' => [
                SiteSetting::FRONT_PUBLIC_FLG_OFF => '非公開',
                SiteSetting::FRONT_PUBLIC_FLG_ON => '公開',
            ],
            'newsNewPeriodType' => [
                SiteSetting::NEWS_NEW_PERIOD_TYPE_TIME => '時間',
                SiteSetting::NEWS_NEW_PERIOD_TYPE_DAY => '日間',
            ],
            'reservationEditPaymentFlg' => [
                SiteSetting::COMMON_USE_FLG_OFF => '利用しない',
                SiteSetting::COMMON_USE_FLG_ON => '利用する',
            ],
            'calendarRegistrationDeadlineDisplayFlg' => [
                SiteSetting::CALENDAR_REGISTRATION_DEADLINE_DISPLAY_FLG_OFF => '表示しない',
                SiteSetting::CALENDAR_REGISTRATION_DEADLINE_DISPLAY_FLG_ON => '表示する',
            ],
        ],
        'cms' => [
            'siteTheme' => [
                'custom_color_1' => '上下バー（ヘッダーフッター）',
                'custom_color_2' => 'ボタン/リンク',
                'custom_color_3' => 'キャンセル/リセット/戻るボタン',
                'custom_color_4' => '基本背景色',
                'custom_color_5' => 'コンテンツ背景色',
            ],
            'siteThemeDefaultColor' => [
                'custom_color_1' => '#6c6c6c',
                'custom_color_2' => '#2f8cff',
                'custom_color_3' => '#6c757d',
                'custom_color_4' => '#f4f7f6',
                'custom_color_5' => '#ffffff',
            ],
        ],
        'file' => [
            'applyExt' => [
                'png' => 'image/png',
                'jpe' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'jpg' => 'image/jpeg',
                'gif' => 'image/gif',
                // adobe
                'pdf' => 'application/pdf',
                // ms office
                'doc' => 'application/msword',
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'xls' => 'application/vnd.ms-excel',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                // パスワード付き（keyは使われてないようなので仮）
                'xlsxp' => 'application/encrypted',
                'ppt' => 'application/vnd.ms-powerpoint',
                'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ],
            'maxSize' => 10 * 1024 * 1024, //byte
        ],
        'term' => [
            'type' => [
                Term::TYPE_TERMS_TOP => '利用規約：トップページ',
                Term::TYPE_TERMS_RESERVE => '利用規約：予約時',
                Term::TYPE_TERMS_USER => '個人情報規約：会員登録時',
                Term::TYPE_SCTL_TOP => '特定商取引法：トップページ',
                Term::TYPE_SCTL_RESERVE => '特定商取引法：予約時',
            ],
        ],
        'analysisTag' => [
            'typeName' => [
                AnalysisTag::TYPE_AFTER_HEAD_OPEN => 'head開始タグ直後',
                AnalysisTag::TYPE_BEFORE_HEAD_CLOSE => 'head閉じタグ直前',
                AnalysisTag::TYPE_AFTER_BODY_OPEN => 'body開始タグ直後',
                AnalysisTag::TYPE_BEFORE_BODY_CLOSE => 'body閉じタグ直前',
                AnalysisTag::TYPE_RESERVATION_FINISH => '予約完了ページ',
            ],
            'typeIndex' => [
                0 => AnalysisTag::TYPE_AFTER_HEAD_OPEN,
                1 => AnalysisTag::TYPE_BEFORE_HEAD_CLOSE,
                2 => AnalysisTag::TYPE_AFTER_BODY_OPEN,
                3 => AnalysisTag::TYPE_BEFORE_BODY_CLOSE,
                4 => AnalysisTag::TYPE_RESERVATION_FINISH,
            ],
        ],
        'colorChip' => [
            'type' => [
                ColorChip::TYPE_TERM_END => '受付期間外',
                ColorChip::TYPE_FULL => '空きなし',
                ColorChip::TYPE_EMPTY => '空きあり',
                ColorChip::TYPE_RESERVED => '予約済み',
                ColorChip::TYPE_CONTINUOUS => '予約中',
                ColorChip::TYPE_ADD => '追加',
            ],
            'frontDisplayFlg' => [
                ColorChip::FRONT_DISPLAY_FLG_ON => '公開',
                ColorChip::FRONT_DISPLAY_FLG_OFF => '非公開',
            ],
        ],
        'word' => [
            'category' => [
                Word::CATEGORY_COMMON => '共通',
                Word::CATEGORY_USER => '会員',
                Word::CATEGORY_RESERVE => '予約',
            ],
        ],
        // システム設定
        'systemSetting' => [
            'contractPlan' => [
                SystemSetting::CONTRACT_PLAN_LITE => 'ライト',
                SystemSetting::CONTRACT_PLAN_BASIC => 'ベーシック',
                SystemSetting::CONTRACT_PLAN_CUSTOMIZE => 'カスタマイズ',
                SystemSetting::CONTRACT_PLAN_EXPAND_BASIC => 'エクスパンド（ベーシック）',
                SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE => 'エクスパンド（カスタマイズ）',
                SystemSetting::CONTRACT_PLAN_PACKAGE => 'パッケージ',
                SystemSetting::CONTRACT_PLAN_CUSTOMIZE_BASIC => 'カスタマイズ',
            ],
            'canAddAdminContractPlan' => [
                SystemSetting::CONTRACT_PLAN_EXPAND_BASIC,
                SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE,
                SystemSetting::CONTRACT_PLAN_PACKAGE,
            ],
            'canEditAdminAuthorityContractPlan' => [
                SystemSetting::CONTRACT_PLAN_EXPAND_BASIC,
                SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE,
                SystemSetting::CONTRACT_PLAN_PACKAGE,
            ],
            // ビデオ会議API（Zoom／Meet）連携
            'canCoordinateVideoMeeting' => [
                SystemSetting::CONTRACT_PLAN_BASIC,
                SystemSetting::CONTRACT_PLAN_CUSTOMIZE,
                SystemSetting::CONTRACT_PLAN_EXPAND_BASIC,
                SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE,
                SystemSetting::CONTRACT_PLAN_PACKAGE,
                SystemSetting::CONTRACT_PLAN_CUSTOMIZE_BASIC,
            ],
            'restriction' => [
                //会員制限 ※nullは無制限
                'user' => [
                    SystemSetting::CONTRACT_PLAN_LITE => 1000,
                    SystemSetting::CONTRACT_PLAN_BASIC => null,
                    SystemSetting::CONTRACT_PLAN_CUSTOMIZE => null,
                    SystemSetting::CONTRACT_PLAN_EXPAND_BASIC => null,
                    SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE => null,
                    SystemSetting::CONTRACT_PLAN_PACKAGE => null,
                    SystemSetting::CONTRACT_PLAN_CUSTOMIZE_BASIC => null,
                ],
                //メール配信数制限 ※nullは無制限
                'mail' => [
                    SystemSetting::CONTRACT_PLAN_LITE => 1000,
                    SystemSetting::CONTRACT_PLAN_BASIC => 100000,
                    SystemSetting::CONTRACT_PLAN_CUSTOMIZE => 100000,
                    SystemSetting::CONTRACT_PLAN_EXPAND_BASIC => 100000,
                    SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE => 100000,
                    SystemSetting::CONTRACT_PLAN_PACKAGE => null,
                    SystemSetting::CONTRACT_PLAN_CUSTOMIZE_BASIC => 100000,
                ],
                //ファイル制限（バイト）※nullは無制限
                'file' => [
                    SystemSetting::CONTRACT_PLAN_LITE => 100 * 1024 * 1024,
                    SystemSetting::CONTRACT_PLAN_BASIC => 5 * 1024 * 1024 * 1024,
                    SystemSetting::CONTRACT_PLAN_CUSTOMIZE => 5 * 1024 * 1024 * 1024,
                    SystemSetting::CONTRACT_PLAN_EXPAND_BASIC => null,
                    SystemSetting::CONTRACT_PLAN_EXPAND_CUSTOMIZE => null,
                    SystemSetting::CONTRACT_PLAN_PACKAGE => null,
                    SystemSetting::CONTRACT_PLAN_CUSTOMIZE_BASIC => 5 * 1024 * 1024 * 1024,
                ],
            ],
        ],
        // 主催者
        'organizer' => [
            'videoMeetingType' => [
                Organizer::VIDEO_MEETING_TYPE_ZOOM => 'Zoom',
            ],
            'zoomConnectType' => [
                Organizer::ZOOM_CONNECT_TYPE_JWT => 'JWT',
                Organizer::ZOOM_CONNECT_TYPE_OAUTH => 'Oauth',
            ],
        ],
        // ビデオ会議連携
        'videoMeeting' => [
            'search' => [
                'on' => '連携済み',
                'off' => '連携なし',
            ],
        ],
        // 利用許可画面のパターン設定
        'adminAuthority' => [
            'selectAll' => [
                AdminAuthority::SELECT_ALL => '全て',
            ],
            'frontCode' => [
                'All' => 'All__all',
            ],
            'accessSetting' => [
                'All__all' => '全て',
                // 予約設定
                'Events__all' => '予約枠設定',
                'Labels__all' => 'カテゴリー設定',
                'Tags__all' => '絞り込みキーワード設定',
                'Options__all' => 'オプション設定',
                'EventHolidays__all' => '休業設定',
                'Holidays__all' => '祝日設定',
                'FormPatterns_' . FormGroup::FORM_TYPE_RESERVATION . '__all' => '予約内容の表示パターン',
                'FormGroups_' . FormGroup::FORM_TYPE_RESERVATION . '__all' => '予約内容の項目設定',
                // 顧客設定
                'UserAuthorities__all' => '顧客の権限設定',
                'FormGroups_' . FormGroup::FORM_TYPE_USER . '__all' => '顧客情報の項目設定',
                'FormPatterns_' . FormGroup::FORM_TYPE_USER . '__all' => '顧客情報の表示パターン',
                // メール設定
                'AutoReplyMails__all' => '自動返信メール設定',
                // 文言設定
                'Words_wordEdit' => '文言設定',
                'Words_errorWordEdit' => 'エラー文言設定',
                'Words_statusWordEdit' => '予約ステータス文言設定',
                'Words_prefWordEdit' => '都道府県文言設定',
                'Words_paymentMethodWordEdit' => '決済方法文言設定',
                'Words_paymentStatusWordEdit' => '決済ステータス文言設定',
                'Words_receptionStatusWordEdit' => '受付ステータス文言設定',
                // 予約サイト設定
                'SiteSetting__all' => '基本設定',
                'Cms__all' => 'デザイン設定',
                'AnalysisTags__all' => '計測タグ設定',
                'Terms__all' => '利用規約・個人情報取り扱い・特商法 設定',
                'ColorChips__all' => '予約枠のカラー設定',
                'Payment__all' => 'クレジット決済情報',
                // ビデオ会議設定
                'Organizers__all' => '主催者設定',
                // Zoom連携ユーザー管理
                'ZoomConnectUsers__all' => 'Zoom連携ユーザー管理',
                // スマートロック設定
                'SmartLocks__all' => 'Akerun設定',
                // reCAPTCHAv3設定
                'Recaptcha__all' => 'reCAPTCHAv3設定',
                // 決済エラー回数一覧
                'PaymentErrors__all' => '決済エラー回数一覧',
            ],
            'accessOperator' => [
                'All__all' => '全て',
                // 予約管理
                'Reservations_calendar' => '予約台帳',
                'Reservations_list' => '予約一覧',
                'Reservations_download' => '予約データダウンロード',
                'ReceptionStatuses__all' => '受付状況一覧',
                // 顧客管理
                'Users_list' => '顧客一覧',
                'Users_download' => '顧客データダウンロード',
                'Users_add' => '会員登録',
                // メール管理
                'MailDeliveries__all' => 'メール配信履歴',
                'BounceMails__all' => '不達メール',
                // CMS管理
                'News__all' => 'お知らせ',
                'FileGroups__all' => 'ファイル管理',
                'AdminOperationalLogs__all' => '操作ログ',
            ],
            // 決済機能利用
            'usePayment' => [
                'Words_paymentMethodWordEdit',
                'Words_paymentStatusWordEdit',
                'Payment__all',
                'PaymentErrors__all',
            ],
            // ビデオ会議設定利用
            'useVideoMeeting' => [
                'Organizers__all',
                'ZoomConnectUsers__all',
            ],
            // スマートロック設定利用
            'useSmartLocks' => [
                'SmartLocks__all',
            ],
            // reCAPTCHAv3設定
            'useRecaptcha' => [
                'Recaptcha__all',
            ],
            // 設定されている権限による追加の権限
            'addAuthority' => [
                // 予約台帳利用
                'Reservations_calendar' => [
                    'Reservations_add',
                ],
                // 予約一覧利用
                'Reservations_list' => [
                    'Reservations_edit',
                    'Reservations_view',
                    'Reservations_detail',
                    'Reservations_delete',
                    'Reservations_deleteMany',
                    'Reservations_upload',
                    'Reservations_sample',
                    'Reservations_cancel',
                    'Reservations_downloadChecked',
                ],
                // 顧客一覧利用
                'Users_list' => [
                    'Users_detail',
                    'Users_view',
                    'Users_delete',
                    'Users_deleteMany',
                    'Users_edit',
                    'Users_upload',
                    'Users_sample',
                    'Users_downloadChecked',
                    'Users_withdraw',
                ],
                //会員登録利用
                'Users_add' => [
                    'Users_approval',
                ]
            ],
        ],
        'api' => [
            'AppAccessToken' => [
                'expiration' => 1 * DAY,
            ],
        ],
        'smartLock' => [
            'name' => [
                SmartLock::TYPE_REMOTE_LOCK => 'RemoteLOCK',
                SmartLock::TYPE_AKERUN => 'Akerun',
            ],
            'reservationSmartLockStatus' => [
                ReservationSmartLock::DISPLAY_STATUS_UNLINKED => '未連携',
            ],
        ],
        // Akerun設定
        'akerun' => [
            'appUseFlg' => [
                SmartLock::APP_USE_FLG_ON => '利用する',
                SmartLock::APP_USE_FLG_OFF => '利用しない',
            ],
        ],
        // reCATPTCHAv3設定
        'recaptcha' => [
            'useFlg' => [
                RecaptchaSetting::USE_FLG_ON => '利用する',
                RecaptchaSetting::USE_FLG_OFF => '利用しない',
            ],
            'action' => [
                RecaptchaSetting::ACTION_USER => 'user',
                RecaptchaSetting::ACTION_RESERVE => 'reserve',
                RecaptchaSetting::ACTION_INQUIRY => 'inquiry',
                RecaptchaSetting::ACTION_TEST => 'test',
            ],
        ],
        // 決済エラー回数一覧
        'paymentErrors' => [
            'status' => [
                PaymentError::TYPE_LOCK => 'ロック',
                PaymentError::TYPE_UNLOCK => 'ロック解除',
            ],
        ],

    ],
];
