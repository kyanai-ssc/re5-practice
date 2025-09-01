<?php
declare(strict_types=1);

use App\Model\Entity\Admin;
use App\Model\Entity\FormItem;
use App\Model\Entity\Organizer;
use App\Model\Entity\PaymentMethod;
use App\Model\Entity\PaymentSetting;
use App\Model\Table\EventPlansTable;
use App\Model\Table\EventRemarksTable;
use App\Model\Table\EventSmartLocksTable;
use App\Model\Table\EventsTable;
use App\Model\Table\EventStockMarksTable;
use App\Model\Table\FormItemsTable;
use App\Utility\Payment\GmoPayment;
use Cake\Core\Configure;

return [
    'Setting' => [
        // 認証
        'auth' => [
            // 管理側
            'admin' => [
                'loginId' => [
                    'length' => [
                        'min' => 4,
                        'max' => 32,
                    ],
                    'character' => '/^[0-9A-Za-z\\-\\._]+$/',
                ],
                'password' => [
                    'length' => [
                        'min' => 8,
                        'max' => 32,
                    ],
                    'character' => '/^[\\x21-\\x7e]+$/',
                    'patterns' => [
                        '/[0-9]/',
                        '/[A-Za-z]/',
                    ],
                ],
                'lock' => [
                    'count' => 10,
                    'time' => 30,
                ],
                'initialOperatorSuffix' => '-ope',
                'authorityName' => [
                    Admin::AUTHORITY_MASTER => 'Master',
                    Admin::AUTHORITY_REGULAR => 'Regular',
                    Admin::AUTHORITY_OPERATOR => 'Operator',
                ],
            ],
            // 公開側
            'user' => [
                'loginId' => [
                    'length' => [
                        'min' => 4,
                        'max' => 32,
                    ],
                    'character' => '/^[0-9A-Za-z\\-\\._]+$/',
                ],
                'password' => [
                    'length' => [
                        'min' => 8,
                        'max' => 32,
                    ],
                    'character' => '/^[\\x21-\\x7e]+$/',
                    'patterns' => [
                        '/[0-9]/',
                        '/[A-Za-z]/',
                    ],
                ],
                'lock' => [
                    'count' => 3,
                    'time' => 30,
                ],
            ],
            // SameSite
            'samesite' => [
                'cookie' => 'None',
                'exceptUa' => [
                    '/iPhone; CPU iPhone OS 1[0-2]/',
                    '/iPad; CPU OS 1[0-2]/',
                    '/iPod touch; CPU iPhone OS 1[0-2]/',
                    '/Macintosh; Intel Mac OS X.*Version\\/1[0-2].*Safari/',
                ],
            ],
        ],
        // クッキー
        'cookie' => [
            // キー
            'key' => [
                'adminReservationCalendar' => 'adminReservationCalendar',
            ],
            // 設定
            'config' => [
                // 予約台帳
                'adminReservationCalendar' => [
                    'expires' => '3 year',
                    'httpOnly' => true,
                ],
            ],
        ],
        // ページネーション
        'pagination' => [
            'direction' => [
                'asc' => 'asc',
                'desc' => 'desc',
            ],
            'limit' => [
                'config' => [
                    'start' => 30,
                    'end' => 100,
                    'list' => [30, 50, 100],
                    'unit' => '件',
                ],
                'default' => 30,
            ],
            'url' => [
                'sort' => null,
                'direction' => null,
                'limit' => null,
                '?' => [
                    'search' => 'exec',
                ],
            ],
            'reservationHistory' => [
                'limit' => 10,
            ],
        ],
        // メール
        'mail' => [
            'admin' => [
                'fromName' => '予約システム',
                'from' => 'info@' . Configure::read('Client.host'),
                'returnPath' => 'info@' . Configure::read('Client.host'),
            ],
            'subject' => [
                'Events' => 'リザベーションエンジン 予約枠アップロード完了通知',
                'Reservations' => 'リザベーションエンジン 予約アップロード完了通知',
                'Users' => 'リザベーションエンジン 会員アップロード完了通知',
                'Inquiry' => 'リザベーションエンジン お問い合わせ',
                'AdminPasswordReset' => 'リザベーションエンジン 管理者パスワードリセット通知',
                'Holidays' => 'リザベーションエンジン 祝日設定アップロード完了通知',
                'payCanceledReservation' => 'キャンセル済みの予約が決済されました',
                'cancelNoPaymentReservation' => '未決済予約の自動キャンセル',
                'failedToCancelNoPaymentReservation' => '未決済予約の自動キャンセル失敗',
                'noticeNoPaymentReservation' => '決済期限切れ通知（ApplePay/auPAY）',
                'failedRefundPaymentReservation' => '返金連携に失敗しました',
                'failedToCancelPaymentOnSb' => '決済の取り消しに失敗しました',
            ],
            'errorSubject' => [
                'Events' => 'リザベーションエンジン 予約枠アップロードエラー通知',
                'Reservations' => 'リザベーションエンジン 予約アップロードエラー通知',
                'Users' => 'リザベーションエンジン 会員アップロードエラー通知',
                'Holidays' => 'リザベーションエンジン 祝日設定アップロードエラー通知',
                'SmartLockLinkage' => '%s連携（%s）に失敗しました',
                'SmartLockUpdateReservation' => '予約情報の更新に失敗しました',
                'SmartLockUpdateUser' => '会員情報の更新に失敗しました',
            ],
            'replace' => [
                'replaceEnclosure' => '%',
                'oldReplaceTokenPrefix' => 'old_',
                'oldReplaceLabel' => '変更前：%LABEL%',
            ],
            'replaceValue' => [
                'videoMeetingInfo' => [
                    'replaceKey' => [
                        'video_meeting_url',
                        'video_meeting_id',
                        'video_meeting_password',
                    ],
                    'value' => [
                        Organizer::VIDEO_MEETING_TYPE_ZOOM => "%VIDEO_MEETING_URL%\nID: %VIDEO_MEETING_ID%\nPW: %VIDEO_MEETING_PASSWORD%",
                        Organizer::VIDEO_MEETING_TYPE_MEET => '%VIDEO_MEETING_URL%',
                    ],
                ],
            ],
        ],
        // ラベル
        'label' => [
            'depth' => 3,
        ],
        // オプション
        'option' => [
            'interval' => 5,
        ],
        // CSV
        'csv' => [
            'common' => [
                'headerInputType' => [
                    FormItemsTable::CSV_COLUMN_USER_AUTHORITY => FormItem::INPUT_TYPE_USER_AUTHORITY,
                    FormItemsTable::CSV_COLUMN_LOGIN_ID => FormItem::INPUT_TYPE_LOGIN_ID,
                    FormItemsTable::CSV_COLUMN_PASSWORD => FormItem::INPUT_TYPE_PASSWORD,
                    FormItemsTable::CSV_COLUMN_MAIL => FormItem::INPUT_TYPE_MAIL,
                    FormItemsTable::CSV_COLUMN_LABEL => FormItem::INPUT_TYPE_LABEL,
                    FormItemsTable::CSV_COLUMN_EVENT_NAME => FormItem::INPUT_TYPE_EVENT_NAME,
                    FormItemsTable::CSV_COLUMN_USAGE_DATE => FormItem::INPUT_TYPE_USAGE_DATE,
                    FormItemsTable::CSV_COLUMN_USAGE_TIME => FormItem::INPUT_TYPE_USAGE_TIME,
                    FormItemsTable::CSV_COLUMN_RESERVATION_TIME => FormItem::INPUT_TYPE_RESERVATION_TIME,
                    FormItemsTable::CSV_COLUMN_RESERVATION_NUMBER => FormItem::INPUT_TYPE_RESERVATION_NUMBER,
                    FormItemsTable::CSV_COLUMN_EXPIRATION_DATE => FormItem::INPUT_TYPE_EXPIRATION_DATE,
                    FormItemsTable::CSV_COLUMN_AKERUN_USER_ID => FormItem::INPUT_TYPE_AKERUN_USER_ID,
                ],
            ],
            'import' => [
                'applyExt' => [
                    'text/csv',
                    'text/plain',
                    'application/csv',
                    'application/vnd.ms-excel',
                    'text/x-csv',
                    'text/html',
                ],
                'maxSize' => 10 * 1024 * 1024, //byte
                'error' => [
                    'delimiter' => "\r\n",
                    'separator' => ' : ',
                ],
                'sample' => [
                    'id' => '登録の場合は空、編集の場合はIDを入力してください。',
                    'values' => '登録したい値を入力してください。',
                    'date' => 'yyyy/mm/dd',
                    'time' => 'Hi:ss',
                    'dateTime' => 'yyyy/mm/dd Hi:ss',
                    'fullName' => '登録したい値を全角空白区切りで記載してください。',
                    'phoneNumber' => '登録したい値を「-」区切りで記載してください。',
                    'address' => '郵便番号、都道府県、市区町村、町域番地、建物名を「|」区切りで記載してください。',
                    'multiple' => '登録したい値を「|」区切りで記載してください。',
                    'association' => '※ヘッダーの（）内の入力項目順に「|」区切りで記載してください。',
                    'hasMany' => "下記の選択肢から複数選択可能\n複数設定する場合、改行で記載",
                    'belongsTo' => '下記の選択肢から1つ選択して記載',
                    'noValues' => '空欄',
                    'number' => '数字を記載',
                    'stockUnit' => "単位を記載\n必要がない場合、空欄",
                    'stockMarks' => "下記の選択肢から複数選択可能\n在庫数の少ない順に、「記号ID|数字」で改行で記載",
                    'eventCharge' => "一律料金の金額を記載\n必要ない場合は、空欄",
                    'eventUnitTime' => '5の倍数でコマ割りの分単位を記載',
                    'eventPlans' => "下記の選択肢から複数選択可能\n表示させたい順に、「公開|プラン名|プラン時間|プラン日|料金」で改行で記載",
                    'eventUsageUnitTime' => '予約時間設定（コマ割り）の倍数で記載',
                    'eventUsageUnitTimeFrom' => "予約時に選択できる最小時間を記載\n予約時間設定（開始）～予約時間設定（終了）は、予約時間設定（予約時間）で割り切れるように",
                    'eventUsageUnitTimeTo' => "予約時に選択できる最大時間を記載\n予約時間設定（開始）～予約時間設定（終了）は、予約時間設定（予約時間）で割り切れるように",
                    'eventUsageUnitDay' => 'コマ割りの日単位を記載',
                    'eventUsageUnitDayFrom' => '予約時間設定(日)の倍数で、予約時に選択できる最小日を記載',
                    'eventUsageUnitDayTo' => '予約時間設定(日)の倍数で、予約時に選択できる最大日を記載',
                    'eventIntervalTime' => '予約前後にインターバルが必要な場合は、予約時間設定（コマ割り）の倍数で記載',
                    'eventIntervalDay' => '予約前後にインターバルが必要な場合は、予約時間設定(日)の倍数で記載',
                    'eventStockRangeFrom' => ' 一回の予約で可能な最小予約数',
                    'eventStockRangeTo' => '一回の予約で可能な最大予約数',
                    'eventReceptionPeriodNumber' => '「●日前の▲から予約可能」 ●を指定',
                    'eventReceptionPeriodTime' => '「●日前の▲から予約可能」 ▲を　hh:mm　で指定',
                    'eventDeadlineNumber' => '「●日前の▲時」または「●時間前」 ●を指定',
                    'eventDeadlineType' => '「●日前の▲時」または「●時間前」 ●を指定',
                    'eventDeadlineTime' => '「●日前の▲時」または「●時間前」 ▲を　hh:mm　で指定',
                    'optionalNumber' => '必要があれば、数字を記載',
                    'optional' => '必要があれば、記載',
                    'eventMultipleWaku' => "下記の選択肢から複数選択可能\n表示したい枠を「|」で指定",
                    'eventSortNo' => '表示順を指定したい場合、記載',
                    'eventImages' => "必要がある場合、ファイル管理で登録したパスを記載\n複数設定するときは、改行で記載",
                    'eventName' => '予約枠の名称を記載',
                    'reservationEventId' => '予約枠IDを入力してください。',
                    'reservationOption' => 'オプション、予約数を「|」区切りで記載してください。',
                    'expirationDate' => 'yyyy/mm/dd～yyyy/mm/dd',
                    'eventPlanData' => [
                        'event' => [
                            'replaceKey' => [
                                'id',
                                'name',
                            ],
                            'value' => '%ID%_%NAME%',
                            'delimiter' => '：',
                        ],
                        'plan' => [
                            'replaceKey' => [
                                'id',
                                'name',
                                'usage_time',
                                'usage_day',
                                'charge',
                            ],
                            'value' => '【［%ID%］%NAME%、%CHARGE%、%USAGE_DAY%%USAGE_TIME%】',
                            'delimiter' => '',
                        ],
                    ],
                ],
                'errorLimit' => 100,
                'event' => [
                    'sample' => [
                        'name' => 'event_import_%NOW%.csv',
                        'type' => 'csv',
                    ],
                ],
                'model' => [
                    'Events',
                    'Users',
                    'Reservations',
                    'Holidays',
                ],
                'noModify' => [
                    'Events',
                    'Reservations',
                ],
                'holidays' => [
                    'sample' => [
                        'name' => 'holiday_import_%NOW%.csv',
                        'type' => 'csv',
                    ],
                ],
                'user' => [
                    'header' => [
                        FormItemsTable::CSV_COLUMN_USER_ID => '[%CSV_COLUMN_ID%] 顧客ID',
                        FormItemsTable::CSV_COLUMN_USER_AUTHORITY => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_LOGIN_ID => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_PASSWORD => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_MAIL => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_EXPIRATION_DATE => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_AKERUN_USER_ID => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USER_ADDITION => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                    ],
                    'column' => [
                        FormItemsTable::CSV_COLUMN_USER_ID => 'users.id',
                        FormItemsTable::CSV_COLUMN_USER_AUTHORITY => null,
                        FormItemsTable::CSV_COLUMN_LOGIN_ID => null,
                        FormItemsTable::CSV_COLUMN_PASSWORD => null,
                        FormItemsTable::CSV_COLUMN_MAIL => null,
                        FormItemsTable::CSV_COLUMN_EXPIRATION_DATE => null,
                        FormItemsTable::CSV_COLUMN_AKERUN_USER_ID => null,
                        FormItemsTable::CSV_COLUMN_USER_ADDITION => 'users.addition_values',
                    ],
                    'sample' => [
                        'name' => 'user_import_%NOW%.csv',
                        'type' => 'csv',
                    ],
                ],
                'reservation' => [
                    'header' => [
                        FormItemsTable::CSV_COLUMN_RESERVATION_STATUS_ID => '[%CSV_COLUMN_ID%] ステータス',
                        FormItemsTable::CSV_COLUMN_RECEPTION_STATUS_ID => '[%CSV_COLUMN_ID%] 受付ステータス',
                        FormItemsTable::CSV_COLUMN_EVENT_NAME => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USAGE_DATE => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USAGE_TIME => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_RESERVATION_TIME => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_EVENT_PLANS => '[%CSV_COLUMN_ID%] プランID',
                        FormItemsTable::CSV_COLUMN_RESERVATION_NUMBER => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_RESERVATION_ADDITION => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_CHARGE => '[%CSV_COLUMN_ID%] 料金',
                        FormItemsTable::CSV_COLUMN_PAYMENT_METHOD => '[%CSV_COLUMN_ID%] 決済方法',
                        FormItemsTable::CSV_COLUMN_PAYMENT_STATUS => '[%CSV_COLUMN_ID%] 決済ステータス',
                        FormItemsTable::CSV_COLUMN_USER_ID => '[%CSV_COLUMN_ID%] 顧客ID',
                    ],
                    'column' => [
                        FormItemsTable::CSV_COLUMN_RESERVATION_STATUS_ID => 'reservations.reservation_status_id',
                        FormItemsTable::CSV_COLUMN_RECEPTION_STATUS_ID => 'reservations.reception_status_id',
                        FormItemsTable::CSV_COLUMN_EVENT_NAME => null,
                        FormItemsTable::CSV_COLUMN_USAGE_DATE => null,
                        FormItemsTable::CSV_COLUMN_USAGE_TIME => null,
                        FormItemsTable::CSV_COLUMN_RESERVATION_TIME => null,
                        FormItemsTable::CSV_COLUMN_EVENT_PLANS => 'reservations.plan_values',
                        FormItemsTable::CSV_COLUMN_RESERVATION_NUMBER => null,
                        FormItemsTable::CSV_COLUMN_RESERVATION_ADDITION => 'reservations.addition_values',
                        FormItemsTable::CSV_COLUMN_CHARGE => 'reservations.charge',
                        FormItemsTable::CSV_COLUMN_PAYMENT_METHOD => 'reservations.payment_method_id',
                        FormItemsTable::CSV_COLUMN_PAYMENT_STATUS => 'reservations.payment_status_id',
                        FormItemsTable::CSV_COLUMN_USER_ID => 'reservations.user_id',
                    ],
                    'sample' => [
                        'name' => 'reservation_import_%NOW%.csv',
                        'type' => 'csv',
                    ],
                ],
            ],
            'download' => [
                'user' => [
                    'name' => 'user_%NOW%.csv',
                    'type' => 'csv',
                    'header' => [
                        FormItemsTable::CSV_COLUMN_USER_ID => '[%CSV_COLUMN_ID%] 顧客ID',
                        FormItemsTable::CSV_COLUMN_USER_AUTHORITY => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_LOGIN_ID => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_PASSWORD => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_MAIL => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_EXPIRATION_DATE => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_AKERUN_USER_ID => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USER_ADDITION => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_GUEST_FLG => '[%CSV_COLUMN_ID%] 顧客区分',
                        FormItemsTable::CSV_COLUMN_WITHDRAWAL_FLG => '[%CSV_COLUMN_ID%] 退会',
                        FormItemsTable::CSV_COLUMN_USER_CREATED => '[%CSV_COLUMN_ID%] 顧客登録日時',
                        FormItemsTable::CSV_COLUMN_USER_MODIFIED => '[%CSV_COLUMN_ID%] 顧客更新日時',
                    ],
                ],
                'mailDelivery' => [
                    'header' => [
                        FormItemsTable::CSV_COLUMN_MAIL_DELIVERY_STATUS => '[%CSV_COLUMN_ID%] 配信ステータス',
                    ],
                ],
                'reservation' => [
                    'name' => 'reservation_%NOW%.csv',
                    'type' => 'csv',
                    'header' => [
                        FormItemsTable::CSV_COLUMN_RESERVATION_ID => '[%CSV_COLUMN_ID%] 予約ID',
                        FormItemsTable::CSV_COLUMN_RESERVATION_STATUS_ID => '[%CSV_COLUMN_ID%] ステータス',
                        FormItemsTable::CSV_COLUMN_RECEPTION_STATUS_ID => '[%CSV_COLUMN_ID%] 受付ステータス',
                        FormItemsTable::CSV_COLUMN_USER_ID => '[%CSV_COLUMN_ID%] 顧客ID',
                        FormItemsTable::CSV_COLUMN_USER_AUTHORITY => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_LOGIN_ID => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_MAIL => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USER_ADDITION => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_GUEST_FLG => '[%CSV_COLUMN_ID%] 顧客区分',
                        FormItemsTable::CSV_COLUMN_WITHDRAWAL_FLG => '[%CSV_COLUMN_ID%] 退会',
                        FormItemsTable::CSV_COLUMN_USER_CREATED => '[%CSV_COLUMN_ID%] 顧客登録日時',
                        FormItemsTable::CSV_COLUMN_USER_MODIFIED => '[%CSV_COLUMN_ID%] 顧客更新日時',
                        FormItemsTable::CSV_COLUMN_LABEL => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_EVENT_NAME => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USAGE_DATE => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_USAGE_TIME => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_RESERVATION_TIME => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_EVENT_PLANS => '[%CSV_COLUMN_ID%] プランID',
                        FormItemsTable::CSV_COLUMN_RESERVATION_NUMBER => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_RESERVATION_ADDITION => '[%FORM_ITEM_ID%] %FORM_ITEM_NAME%',
                        FormItemsTable::CSV_COLUMN_CHARGE => '[%CSV_COLUMN_ID%] 料金',
                        FormItemsTable::CSV_COLUMN_PAYMENT_METHOD => '[%CSV_COLUMN_ID%] 決済方法',
                        FormItemsTable::CSV_COLUMN_PAYMENT_STATUS => '[%CSV_COLUMN_ID%] 決済ステータス',
                        FormItemsTable::CSV_COLUMN_SMART_LOCK_PIN => '[%CSV_COLUMN_ID%] PIN番号',
                        FormItemsTable::CSV_COLUMN_SMART_LOCK_UNIVERSAL_ACCESS_KEY => '[%CSV_COLUMN_ID%] カギ情報URL（ユニバーサルアクセスキー）',
                        FormItemsTable::CSV_COLUMN_SMART_LOCK_KEY_URL => '[%CSV_COLUMN_ID%] ロック解除URL',
                        FormItemsTable::CSV_COLUMN_RESERVATION_CREATED => '[%CSV_COLUMN_ID%] 予約登録日時',
                        FormItemsTable::CSV_COLUMN_RESERVATION_MODIFIED => '[%CSV_COLUMN_ID%] 予約更新日時',
                    ],
                    'userDeleted' => '会員削除済み',
                ],
                'adminOperationalLog' => [
                    'name' => 'adminOperationalLog_%NOW%.csv',
                    'type' => 'csv',
                    'header' => [
                        '操作時間',
                        '管理者ログインID',
                        '操作機能',
                        '操作内容',
                        '操作データ',
                    ],
                ],
                'holiday' => [
                    'header' => [
                        'date' => '[0]祝日',
                    ],
                    'name' => 'holiday_%NOW%.csv',
                    'type' => 'csv',
                ],
                'event' => [
                    'header' => [
                        EventsTable::CSV_COLUMN_ID => '[%CSV_COLUMN_ID%] 予約枠ID',
                        EventsTable::CSV_COLUMN_NAME => '[%CSV_COLUMN_ID%] 予約枠名',
                        EventsTable::CSV_COLUMN_TYPE => '[%CSV_COLUMN_ID%] 予約枠タイプ',
                        EventsTable::CSV_COLUMN_LABEL_ID => '[%CSV_COLUMN_ID%] カテゴリー',
                        EventsTable::CSV_COLUMN_EVENT_TAGS => '[%CSV_COLUMN_ID%] 絞り込みキーワード',
                        EventsTable::CSV_COLUMN_ORGANIZER_ID => '[%CSV_COLUMN_ID%] ビデオ会議主催者',
                        EventsTable::CSV_COLUMN_DATE_FROM => '[%CSV_COLUMN_ID%] 利用期間（開始)',
                        EventsTable::CSV_COLUMN_DATE_TO => '[%CSV_COLUMN_ID%] 利用期間（終了)',
                        EventsTable::CSV_COLUMN_EVENT_WEEKS => '[%CSV_COLUMN_ID%] 利用曜日(曜日)',
                        EventsTable::CSV_COLUMN_TIME_FROM => '[%CSV_COLUMN_ID%] 実施時間(開始)',
                        EventsTable::CSV_COLUMN_TIME_TO => '[%CSV_COLUMN_ID%] 実施時間(終了)',
                        EventsTable::CSV_COLUMN_STOCK => '[%CSV_COLUMN_ID%] 在庫数',
                        EventsTable::CSV_COLUMN_STOCK_UNIT => '[%CSV_COLUMN_ID%] 在庫の単位',
                        EventsTable::CSV_COLUMN_STOCK_DISPLAY_TYPE => '[%CSV_COLUMN_ID%] 在庫数表示設定',
                        EventsTable::CSV_COLUMN_EVENT_STOCK_MARKS => '[%CSV_COLUMN_ID%] 記号で表示(記号ID|数字)',
                        EventsTable::CSV_COLUMN_TIME_PLAN => '[%CSV_COLUMN_ID%] 料金設定',
                        EventsTable::CSV_COLUMN_CHARGE => '[%CSV_COLUMN_ID%] 料金',
                        EventsTable::CSV_COLUMN_MULTIPLE_TIME_PLAN_TYPE => '[%CSV_COLUMN_ID%] プラン設定',
                        EventsTable::CSV_COLUMN_EVENT_PLANS => '[%CSV_COLUMN_ID%] プラン設定(公開設定|プラン名|プラン時間|プラン日|料金)',
                        EventsTable::CSV_COLUMN_EVENT_UNIT_TIME => '[%CSV_COLUMN_ID%] 予約時間設定（コマ割り）',
                        EventsTable::CSV_COLUMN_USAGE_UNIT_TIME => '[%CSV_COLUMN_ID%] 予約時間設定(予約時間)',
                        EventsTable::CSV_COLUMN_USAGE_TIME_FROM => '[%CSV_COLUMN_ID%] 予約時間設定(開始)',
                        EventsTable::CSV_COLUMN_USAGE_TIME_TO => '[%CSV_COLUMN_ID%] 予約時間設定(終了)',
                        EventsTable::CSV_COLUMN_USAGE_UNIT_DAY => '[%CSV_COLUMN_ID%] 予約時間設定(日)',
                        EventsTable::CSV_COLUMN_USAGE_DAY_FROM => '[%CSV_COLUMN_ID%] 予約時間設定(開始日)',
                        EventsTable::CSV_COLUMN_USAGE_DAY_TO => '[%CSV_COLUMN_ID%] 予約時間設定(終了日)',
                        EventsTable::CSV_COLUMN_INTERVAL_TIME => '[%CSV_COLUMN_ID%] インターバル（時間)',
                        EventsTable::CSV_COLUMN_INTERVAL_DAY => '[%CSV_COLUMN_ID%] インターバル（日)',
                        EventsTable::CSV_COLUMN_WAITING_CANCELLATION_FLG => '[%CSV_COLUMN_ID%] キャンセル待ち通知',
                        EventsTable::CSV_COLUMN_STOCK_RANGE_FROM => '[%CSV_COLUMN_ID%] 一回の予約で押さえられる在庫数(開始)',
                        EventsTable::CSV_COLUMN_STOCK_RANGE_TO => '[%CSV_COLUMN_ID%] 一回の予約で押さえられる在庫数(終了)',
                        EventsTable::CSV_COLUMN_RESERVATION_STATUS_ID => '[%CSV_COLUMN_ID%] 予約登録時のステータス',
                        EventsTable::CSV_COLUMN_FORM_PATTERN_ID => '[%CSV_COLUMN_ID%] 予約内容の表示パターン',
                        EventsTable::CSV_COLUMN_RECEPTION_PERIOD_NUMBER => '[%CSV_COLUMN_ID%] 予約受付開始タイミング（日)',
                        EventsTable::CSV_COLUMN_RECEPTION_PERIOD_TIME => '[%CSV_COLUMN_ID%] 予約受付開始タイミング（時)',
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_NUMBER => '[%CSV_COLUMN_ID%] 予約受付締切タイミング',
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_TYPE => '[%CSV_COLUMN_ID%] 予約受付締切タイミング(単位)',
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_TIME => '[%CSV_COLUMN_ID%] 予約受付締切タイミング(日選択時の時間)',
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_CRITERION => '[%CSV_COLUMN_ID%] 登録締切日：判定基準',
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_NUMBER => '[%CSV_COLUMN_ID%] 予約変更締切タイミング',
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_TYPE => '[%CSV_COLUMN_ID%] 予約変更締切タイミング(単位)',
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_TIME => '[%CSV_COLUMN_ID%] 予約変更締切タイミング(日選択時の時間)',
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_CRITERION => '[%CSV_COLUMN_ID%] 編集締切日：判定基準',
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_NUMBER => '[%CSV_COLUMN_ID%] 予約キャンセル締切タイミング',
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_TYPE => '[%CSV_COLUMN_ID%] 予約キャンセル締切タイミング(単位)',
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_TIME => '[%CSV_COLUMN_ID%] 予約キャンセル締切タイミング(日選択時の時間)',
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_CRITERION => '[%CSV_COLUMN_ID%] 予約キャンセル締切タイミング：判定基準',
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_FUTURE => '[%CSV_COLUMN_ID%] 予約回数の上限',
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_MONTH => '[%CSV_COLUMN_ID%] 予約回数の上限(1月あたり)',
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_DAY => '[%CSV_COLUMN_ID%] 予約回数の上限(1日あたり)',
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_ALL => '[%CSV_COLUMN_ID%] 予約回数の上限(過去含む全て)',
                        EventsTable::CSV_COLUMN_DUPLICATION_CHECK_FLG => '[%CSV_COLUMN_ID%] 重複予約',
                        EventsTable::CSV_COLUMN_BACKGROUND_COLOR_TYPE => '[%CSV_COLUMN_ID%] 「空きあり」のカラー',
                        EventsTable::CSV_COLUMN_COLOR_CHIP_ID => '[%CSV_COLUMN_ID%] カラーコード',
                        EventsTable::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_FRONT => '[%CSV_COLUMN_ID%] 「空きあり」以外（予約サイト)',
                        EventsTable::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_ADMIN => '[%CSV_COLUMN_ID%] 「空きあり」以外（管理画面)',
                        EventsTable::CSV_COLUMN_FORMAT_TYPE_DISPLAY => '[%CSV_COLUMN_ID%] カレンダーへの表示',
                        EventsTable::CSV_COLUMN_QR_CODE_FLG => '[%CSV_COLUMN_ID%] QRコード',
                        EventsTable::CSV_COLUMN_EVENT_SMART_LOCK => '[%CSV_COLUMN_ID%] %CSV_COLUMN_NAME%',
                        EventsTable::CSV_COLUMN_USAGE_TIME_NOTATION => '[%CSV_COLUMN_ID%] 予約履歴表示タイプ',
                        EventsTable::CSV_COLUMN_PUBLIC_FROM => '[%CSV_COLUMN_ID%] 公開期間(開始)',
                        EventsTable::CSV_COLUMN_PUBLIC_TO => '[%CSV_COLUMN_ID%] 公開期間(終了)',
                        EventsTable::CSV_COLUMN_PUBLIC_FLG => '[%CSV_COLUMN_ID%] 公開設定',
                        EventsTable::CSV_COLUMN_SORT_NO => '[%CSV_COLUMN_ID%] 表示順',
                        EventsTable::CSV_COLUMN_EVENT_IMAGES => '[%CSV_COLUMN_ID%] 画像',
                        EventsTable::CSV_COLUMN_EVENT_REMARKS => '[%CSV_COLUMN_ID%] 注釈(表示を選択|注釈名|本文|予約枠備考を選択|)',
                        EventsTable::CSV_COLUMN_DESCRIPTION => '[%CSV_COLUMN_ID%] 説明文',
                        EventsTable::CSV_COLUMN_CREATED => '[%CSV_COLUMN_ID%] 登録日時',
                        EventsTable::CSV_COLUMN_MODIFIED => '[%CSV_COLUMN_ID%] 更新日時',
                    ],
                    'headerColumnId' => [
                        EventsTable::CSV_COLUMN_ID => 1,
                        EventsTable::CSV_COLUMN_NAME => 2,
                        EventsTable::CSV_COLUMN_TYPE => 3,
                        EventsTable::CSV_COLUMN_LABEL_ID => 4,
                        EventsTable::CSV_COLUMN_EVENT_TAGS => 5,
                        EventsTable::CSV_COLUMN_DATE_FROM => 6,
                        EventsTable::CSV_COLUMN_DATE_TO => 7,
                        EventsTable::CSV_COLUMN_EVENT_WEEKS => 8,
                        EventsTable::CSV_COLUMN_TIME_FROM => 9,
                        EventsTable::CSV_COLUMN_TIME_TO => 10,
                        EventsTable::CSV_COLUMN_STOCK => 11,
                        EventsTable::CSV_COLUMN_STOCK_UNIT => 12,
                        EventsTable::CSV_COLUMN_STOCK_DISPLAY_TYPE => 13,
                        EventsTable::CSV_COLUMN_EVENT_STOCK_MARKS => 14,
                        EventsTable::CSV_COLUMN_TIME_PLAN => 15,
                        EventsTable::CSV_COLUMN_CHARGE => 16,
                        EventsTable::CSV_COLUMN_MULTIPLE_TIME_PLAN_TYPE => 17,
                        EventsTable::CSV_COLUMN_EVENT_PLANS => 18,
                        EventsTable::CSV_COLUMN_EVENT_UNIT_TIME => 19,
                        EventsTable::CSV_COLUMN_USAGE_UNIT_TIME => 20,
                        EventsTable::CSV_COLUMN_USAGE_TIME_FROM => 21,
                        EventsTable::CSV_COLUMN_USAGE_TIME_TO => 22,
                        EventsTable::CSV_COLUMN_USAGE_UNIT_DAY => 23,
                        EventsTable::CSV_COLUMN_USAGE_DAY_FROM => 24,
                        EventsTable::CSV_COLUMN_USAGE_DAY_TO => 25,
                        EventsTable::CSV_COLUMN_INTERVAL_TIME => 26,
                        EventsTable::CSV_COLUMN_INTERVAL_DAY => 27,
                        EventsTable::CSV_COLUMN_WAITING_CANCELLATION_FLG => 28,
                        EventsTable::CSV_COLUMN_STOCK_RANGE_FROM => 29,
                        EventsTable::CSV_COLUMN_STOCK_RANGE_TO => 30,
                        EventsTable::CSV_COLUMN_RESERVATION_STATUS_ID => 31,
                        EventsTable::CSV_COLUMN_FORM_PATTERN_ID => 32,
                        EventsTable::CSV_COLUMN_RECEPTION_PERIOD_NUMBER => 33,
                        EventsTable::CSV_COLUMN_RECEPTION_PERIOD_TIME => 34,
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_NUMBER => 35,
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_TYPE => 36,
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_TIME => 37,
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_NUMBER => 38,
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_TYPE => 39,
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_TIME => 40,
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_NUMBER => 41,
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_TYPE => 42,
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_TIME => 43,
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_FUTURE => 44,
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_MONTH => 45,
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_DAY => 46,
                        EventsTable::CSV_COLUMN_RESERVATION_LIMIT_ALL => 47,
                        EventsTable::CSV_COLUMN_DUPLICATION_CHECK_FLG => 48,
                        EventsTable::CSV_COLUMN_BACKGROUND_COLOR_TYPE => 49,
                        EventsTable::CSV_COLUMN_COLOR_CHIP_ID => 50,
                        EventsTable::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_FRONT => 51,
                        EventsTable::CSV_COLUMN_BACKGROUND_COLOR_REPLACE_ADMIN => 52,
                        EventsTable::CSV_COLUMN_FORMAT_TYPE_DISPLAY => 53,
                        EventsTable::CSV_COLUMN_USAGE_TIME_NOTATION => 54,
                        EventsTable::CSV_COLUMN_PUBLIC_FROM => 55,
                        EventsTable::CSV_COLUMN_PUBLIC_TO => 56,
                        EventsTable::CSV_COLUMN_PUBLIC_FLG => 57,
                        EventsTable::CSV_COLUMN_SORT_NO => 58,
                        EventsTable::CSV_COLUMN_EVENT_IMAGES => 59,
                        EventsTable::CSV_COLUMN_EVENT_REMARKS => 60,
                        EventsTable::CSV_COLUMN_DESCRIPTION => 61,
                        EventsTable::CSV_COLUMN_CREATED => 62,
                        EventsTable::CSV_COLUMN_MODIFIED => 63,
                        EventsTable::CSV_COLUMN_ORGANIZER_ID => 64,
                        EventsTable::CSV_COLUMN_QR_CODE_FLG => 65,
                        EventsTable::CSV_COLUMN_REGISTRATION_DEADLINE_CRITERION => 66,
                        EventsTable::CSV_COLUMN_EDITING_DEADLINE_CRITERION => 67,
                        EventsTable::CSV_COLUMN_CANCELLATION_DEADLINE_CRITERION => 68,
                        EventsTable::CSV_COLUMN_EVENT_SMART_LOCK => 69,
                    ],
                    'name' => 'event_%NOW%.csv',
                    'type' => 'csv',
                    'associationsHeader' => [
                        EventsTable::CSV_COLUMN_EVENT_PLANS => [
                            EventPlansTable::CSV_COLUMN_PUBLIC_FLG => '公開設定',
                            EventPlansTable::CSV_COLUMN_NAME => 'プラン名',
                            EventPlansTable::CSV_COLUMN_USAGE_TIME => '利用時間',
                            EventPlansTable::CSV_COLUMN_USAGE_DAY => '利用日',
                            EventPlansTable::CSV_COLUMN_CHARGE => '料金',
                        ],
                        EventsTable::CSV_COLUMN_EVENT_STOCK_MARKS => [
                            EventStockMarksTable::CSV_COLUMN_SYMBOLIC => '記号ID',
                            EventStockMarksTable::CSV_COLUMN_NUMBER => '閾値',
                        ],
                        EventsTable::CSV_COLUMN_EVENT_REMARKS => [
                            EventRemarksTable::CSV_COLUMN_DETAIL_DISPLAY_FLG => '詳細表示設定',
                            EventRemarksTable::CSV_COLUMN_NAME => '項目名',
                            EventRemarksTable::CSV_COLUMN_REMARK => '内容',
                            EventRemarksTable::CSV_COLUMN_FORM_ITEM_ID => '表示箇所',
                        ],
                        EventsTable::CSV_COLUMN_EVENT_SMART_LOCK => [
                            'remoteLock' => [
                                EventSmartLocksTable::CSV_COLUMN_SMART_LOCK_DEVICE_KEY => 'デバイスキー',
                            ],
                            'akerun' => [
                                EventSmartLocksTable::CSV_COLUMN_SMART_LOCK_DEVICE_KEY => 'Akerun ID',
                                EventSmartLocksTable::CSV_COLUMN_SMART_LOCK_KEY_URL_FLG => '合鍵URL',
                            ],
                        ],
                    ],
                    'eventSmartLockHeader' => [
                        'remoteLock' => 'RemoteLOCK設定(デバイスキー)',
                        'akerun' => 'Akerun設定(Akerun ID|合鍵URL)',
                    ],
                ],
                'eventPlan' => [
                    'header' => [
                        EventsTable::CSV_COLUMN_ID => '予約枠ID',
                        EventsTable::CSV_COLUMN_NAME => '予約枠名',
                        EventsTable::CSV_COLUMN_EVENT_PLANS => 'プラン情報',
                    ],
                    'headerColumnId' => [
                        EventsTable::CSV_COLUMN_ID => 1,
                        EventsTable::CSV_COLUMN_NAME => 2,
                        EventsTable::CSV_COLUMN_EVENT_PLANS => 3,
                    ],
                    'associationsHeader' => [
                        EventsTable::CSV_COLUMN_EVENT_PLANS => [
                            EventPlansTable::CSV_COLUMN_ID => 'プランID',
                            EventPlansTable::CSV_COLUMN_NAME => 'プラン名',
                        ],
                    ],
                    'name' => 'event_plan_%NOW%.csv',
                    'type' => 'csv',
                ],
            ],
        ],
        // ダウンロード
        'download' => [
            'bounceMailHistory' => [
                'name' => 'bounce_mail_%NOW%.eml',
                'type' => 'eml',
            ],
        ],
        // 登録
        'formInput' => [
            'backQuery' => [
                'input' => 'back',
            ],
        ],
        // 検索入力
        'searchInput' => [
            'searchQuery' => [
                'search' => 'exec',
            ],
            'saveExec' => [
                'save' => 'exec',
            ],
        ],
        // 一覧チェック
        'listCheck' => [
            'limit' => 1000,
        ],
        // バッチ
        'batch' => [
            'shell' => Configure::read('Env.path.php') . ' -f ' . ROOT . DS . 'bin' . DS . 'cake.php --',
            'client' => '--client=' . Configure::read('Client.name'),
        ],
        // 予約
        'reservation' => [
            'continuousLimit' => [
                'payment' => 10,
                'noPayment' => 10,
            ],
        ],
        // 決済
        'payment' => [
            PaymentSetting::PAYMENT_SERVICE_GMO => [
                'tokenError' => [
                    'cardNo' => [
                        'type' => 'card_no',
                        'code' => ['100', '101', '102'],
                        'error' => [],
                    ],
                    'expire' => [
                        'type' => 'expire',
                        'code' => ['110', '111', '112', '113'],
                        'error' => [],
                    ],
                    'securityCode' => [
                        'type' => 'security_code',
                        'code' => ['121', '122'],
                        'error' => ['security_code_required'],
                    ],
                    'name' => [
                        'type' => 'name',
                        'code' => ['131', '132'],
                        'error' => ['name_required'],
                    ],
                    'other' => [
                        'type' => 'other',
                        'code' => [],
                        'error' => [],
                    ],
                ],
                'config' => [
                    'scheme' => 'https',
                    'sslVerify' => true,
                ],
                'host' => [
                    PaymentSetting::ENVIRONMENT_PRODUCTION => 'p01.mul-pay.jp',
                    PaymentSetting::ENVIRONMENT_STAGING => 'pt01.mul-pay.jp',
                ],
                'tokenJsUrl' => [
                    PaymentSetting::ENVIRONMENT_PRODUCTION => 'https://static.mul-pay.jp/payment/js/mp-token.js',
                    PaymentSetting::ENVIRONMENT_STAGING => 'https://stg.static.mul-pay.jp/payment/js/mp-token.js',
                ],
                'jobCodeApi' => [
                    PaymentSetting::JOB_CODE_IMMEDIATE => GmoPayment::API_JOB_CD_CAPTURE,
                    PaymentSetting::JOB_CODE_PROVISIONAL => GmoPayment::API_JOB_CD_AUTH,
                ],
                // 3Dセキュア決済期限（秒）
                'threeDSecureLimit' => 1020,
            ],
            PaymentSetting::PAYMENT_SERVICE_SB => [
                'tokenError' => [
                    'cardNo' => [
                        'type' => 'card_no',
                        'code' => ['03003', '04003', '05003', '07003', '99003'],
                        'error' => [],
                    ],
                    'expire' => [
                        'type' => 'expire',
                        'code' => ['03004', '04004', '05004', '07004', '99004'],
                        'error' => [],
                    ],
                    'securityCode' => [
                        'type' => 'security_code',
                        'code' => ['03005', '04005', '05005', '07005', '99005'],
                        'error' => ['security_code'],
                    ],
                    'nameSb' => [
                        'type' => 'name_sb',
                        'code' => ['tds2_03003', 'tds2_04003', 'tds2_05003', 'tds2_07003', 'tds2_99003','tds2_03004', 'tds2_04004', 'tds2_05004', 'tds2_07004', 'tds2_99004'],
                        'error' => ['name_sb_required', 'name_sb_invalid'],
                    ],
                    'emailAddressSb' => [
                        'type' => 'email_address_sb',
                        'code' => ['tds2_03017', 'tds2_04017', 'tds2_05017', 'tds2_07017', 'tds2_99017'],
                        'error' => ['email_address_sb_format'],
                    ],
                    'phoneNumberSb' => [
                        'type' => 'phone_number_sb',
                        'code' => ['tds2_03010', 'tds2_04010', 'tds2_05010', 'tds2_07010', 'tds2_99010'],
                        'error' => ['phone_number_sb_format'],
                    ],
                    'kyc' => [
                        'type' => 'kyc',
                        'code' => [],
                        'error' => ['email_address_sb_or_phone_number_sb_required'],
                    ],
                    'other' => [
                        'type' => 'other',
                        'code' => [],
                        'error' => [],
                    ],
                ],
                'config' => [
                    'scheme' => 'https',
                    'sslVerify' => true,
                    'url' => '/api/xmlapi.do',
                ],
                'host' => [
                    PaymentSetting::ENVIRONMENT_PRODUCTION => 'api.sps-system.com',
                    PaymentSetting::ENVIRONMENT_STAGING => 'stbfep.sps-system.com',
                ],
                'tokenJsUrl' => [
                    PaymentSetting::ENVIRONMENT_PRODUCTION => 'https://token.sps-system.com/sbpstoken/com_sbps_system_token.js',
                    PaymentSetting::ENVIRONMENT_STAGING => 'https://stbtoken.sps-system.com/sbpstoken/com_sbps_system_token.js',
                ],
                // 決済期限（秒）
                'paymentLimit' => [
                    PaymentMethod::TYPE_PAYPAY => 900,
                    PaymentMethod::TYPE_APPLE_PAY => 2400,
                    PaymentMethod::TYPE_AU_PAY => 1500,
                ],
                'linkPaymentUrl' => [
                    PaymentSetting::ENVIRONMENT_PRODUCTION => 'https://fep.sps-system.com/f01/FepBuyInfoReceive.do',
                    PaymentSetting::ENVIRONMENT_STAGING => 'https://stbfep.sps-system.com/f01/FepBuyInfoReceive.do',
                ],
                // 3Dセキュア決済期限（秒）
                'threeDSecureLimit' => 1020,
                // カード利用者決済情報トークンjsURL
                'tds2infotokenJsUrl' => [
                    PaymentSetting::ENVIRONMENT_PRODUCTION => 'https://token.sps-system.com/sbpstoken/com_sbps_system_tds2infotoken.js',
                    PaymentSetting::ENVIRONMENT_STAGING => 'https://stbtoken.sps-system.com/sbpstoken/com_sbps_system_tds2infotoken.js',
                ],
            ],
        ],
        // 操作ログ
        'operationalLog' => [
            'default' => 'ID：%id%',
            'import' => [
                'default' => '新規登録%insert_count%件、更新%update_count%件',
                'noModify' => '新規登録%insert_count%件',
            ],
            'deleteMany' => '%count%件',
        ],
        // 文言
        'word' => [
            'ngWords' => [
                'ReservationEngine',
                'リザベーション・エンジン',
                'リザベーションエンジン',
            ],
        ],
        // ビデオ会議連携
        'videoMeeting' => [
            // API連携する予約件数の制限
            'apiLimit' => 100,
        ],
        'smartLock' => [
            'manualUrl' => [
                'remoteLock' => 'function/eventsitem/#RemoteLOCK',
                'akerun' => 'function/eventsitem/#Akerun',
            ],
            'eventListUrl' => [
                'remoteLock' => 'https://connect.remotelock.jp/devices/locks/%s/events',
                'akerun' => 'https://connect.akerun.com/orgs/%s/histories/entry_exit',
            ],
            'akerun' => [
                'default_user_name' => 'ユーザー_%s',
                // AkerunユーザーIDの顧客項目の説明文が空の場合に設定される説明文
                'formItemAkerunUserIdDefaultDescription' => '<p>AkerunユーザーIDの確認方法は<a href="https://support.akerun.com/hc/ja/articles/28787388881049--%E3%82%A2%E3%82%AB%E3%82%A6%E3%83%B3%E3%83%88%E8%A8%AD%E5%AE%9A-%E3%83%A6%E3%83%BC%E3%82%B6%E3%83%BCID%E3%81%AE%E7%A2%BA%E8%AA%8D%E6%96%B9%E6%B3%95" target="_blank" rel="noopener noreferrer">こちらをご覧ください。</a></p>',
            ],
            // 未連携一覧の検索条件と画面右上の未連携一覧リンクの判定で、切り捨てる分を設定
            'truncateMinute' => 5,
        ],
        // 決済エラー
        'paymentError' => [
            'lock' => [
                'count' => 5,
                'time' => 10,
            ],
        ],
    ],
];
