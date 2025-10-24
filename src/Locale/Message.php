<?php
declare(strict_types=1);

namespace App\Locale;

/**
 * Message class.
 */
class Message
{
    /**
     * 不正な遷移
     */
    public const ERROR_ILLEGAL_TRANSITION = 'ERROR_ILLEGAL_TRANSITION';

    /**
     * 未入力
     */
    public const ERROR_NOT_EMPTY = 'ERROR_NOT_EMPTY';

    /**
     * 未選択
     */
    public const ERROR_NOT_EMPTY_SELECT = 'ERROR_NOT_EMPTY_SELECT';

    /**
     * 桁数不足
     */
    public const ERROR_MIN_LENGTH = 'ERROR_MIN_LENGTH';

    /**
     * 文字数超過
     */
    public const ERROR_MAX_LENGTH = 'ERROR_MAX_LENGTH';

    /**
     * 不正値
     */
    public const ERROR_INVALID_VALUE = 'ERROR_INVALID_VALUE';

    /**
     * 不一致
     */
    public const ERROR_NOT_SAME = 'ERROR_NOT_SAME';

    /**
     * 重複
     */
    public const ERROR_DUPLICATION = 'ERROR_DUPLICATION';

    /**
     * 選択肢外
     */
    public const ERROR_IN_LIST = 'ERROR_IN_LIST';

    /**
     * メールアドレス
     */
    public const ERROR_MAIL_ADDRESS = 'ERROR_MAIL_ADDRESS';

    /**
     * 日付
     */
    public const ERROR_DATE = 'ERROR_DATE';

    /**
     * 日時
     */
    public const ERROR_DATE_TIME = 'ERROR_DATE_TIME';

    /**
     * 時間
     */
    public const ERROR_TIME = 'ERROR_TIME';

    /**
     * 0を含む数字
     */
    public const ERROR_NUMBER = 'ERROR_NUMBER';

    /**
     * 0を含まない数字
     */
    public const ERROR_NATURAL_NUMBER = 'ERROR_NATURAL_NUMBER';

    /**
     * 受付時間変更
     */
    public const ERROR_CHANGE_USAGE_TIME = 'ERROR_CHANGE_USAGE_TIME';

    /**
     * Fromより少ない
     */
    public const ERROR_LESS_THAN_FROM = 'ERROR_LESS_THAN_FROM';

    /**
     * TOより多い
     */
    public const ERROR_GRATER_THAN_TO = 'ERROR_GRATER_THAN_TO';

    /**
     * 最小単位
     */
    public const ERROR_MINIMUM_UNIT = 'ERROR_MINIMUM_UNIT';

    /**
     * 倍数
     */
    public const ERROR_MULTIPLE_NUM = 'ERROR_MULTIPLE_NUM';

    /**
     * 実施時間のチェック
     */
    public const ERROR_EVENT_TIME_MULTIPLE = 'ERROR_EVENT_TIME_MULTIPLE';

    /**
     * 桁数超過
     */
    public const ERROR_OVER_DIGIT = 'ERROR_OVER_DIGIT';

    /**
     * 桁数不足
     */
    public const ERROR_UNDER_DIGIT = 'ERROR_UNDER_DIGIT';

    /**
     * From以上_日時
     */
    public const ERROR_OVER_FROM_DATETIME = 'ERROR_OVER_FROM_DATETIME';

    /**
     * To以下_日時
     */
    public const ERROR_UNDER_TO_DATETIME = 'ERROR_UNDER_TO_DATETIME';

    /**
     * From以上_日
     */
    public const ERROR_OVER_FROM_DATE = 'ERROR_OVER_FROM_DATE';

    /**
     * From以上_時間
     */
    public const ERROR_OVER_FROM_TIME = 'ERROR_OVER_FROM_TIME';

    /**
     * 予約が存在
     */
    public const ERROR_IS_RESERVE = 'ERROR_IS_RESERVE';

    /**
     * 未来に予約が存在
     */
    public const ERROR_IS_RESERVE_FUTURE = 'ERROR_IS_RESERVE_FUTURE';

    /**
     * 半角
     */
    public const ERROR_HALF_SIZE = 'ERROR_HALF_SIZE';

    /**
     * 全角
     */
    public const ERROR_FULL_SIZE = 'ERROR_FULL_SIZE';

    /**
     * 半角数字
     */
    public const ERROR_HALF_SIZE_NUMBER = 'ERROR_HALF_SIZE_NUMBER';

    /**
     * 半角英数
     */
    public const ERROR_ALPHA_NUMERIC = 'ERROR_ALPHA_NUMERIC';

    /**
     * 半角英数記号
     */
    public const ERROR_ALNUM_SYM = 'ERROR_ALNUM_SYM';

    /**
     * 半角カナ
     */
    public const ERROR_HALF_SIZE_KATAKANA = 'ERROR_HALF_SIZE_KATAKANA';

    /**
     * 全角カナ
     */
    public const ERROR_FULL_SIZE_KATAKANA = 'ERROR_FULL_SIZE_KATAKANA';

    /**
     * 全角かな
     */
    public const ERROR_FULL_SIZE_HIRAGANA = 'ERROR_FULL_SIZE_HIRAGANA';

    /**
     * 電話番号(ハイフンなし)
     */
    public const ERROR_PHONE_NUMBER = 'ERROR_PHONE_NUMBER';

    /**
     * 電話番号(ハイフンあり)
     */
    public const ERROR_PHONE_NUMBER_HYPHEN = 'ERROR_PHONE_NUMBER_HYPHEN';

    /**
     * 電話番号形式
     */
    public const ERROR_INVALID_PHONE_NUMBER = 'ERROR_INVALID_PHONE_NUMBER';

    /**
     * 郵便番号
     */
    public const ERROR_ZIP_CODE = 'ERROR_ZIP_CODE';

    /**
     * 特定日より前の日
     */
    public const ERROR_UNDER_DATE = 'ERROR_UNDER_DATE';

    /**
     * 特定日よりあとの日
     */
    public const ERROR_OVER_DATE = 'ERROR_OVER_DATE';

    /**
     * ディレクトリの形式
     */
    public const ERROR_IS_DIRECTORY = 'ERROR_IS_DIRECTORY';

    /**
     * ファイル名
     */
    public const ERROR_IS_FILE = 'ERROR_IS_FILE';

    /**
     * カラーコード
     */
    public const ERROR_COLOR_CODE = 'ERROR_COLOR_CODE';

    /**
     * プラン権限
     */
    public const ERROR_PLAN_AUTHORITY = 'ERROR_PLAN_AUTHORITY';

    /**
     * 登録済み
     */
    public const ERROR_ALREADY_SAVE = 'ERROR_ALREADY_SAVE';

    /**
     * 予約枠在庫変更
     */
    public const ERROR_CHANGE_EVENT_STOCK = 'ERROR_CHANGE_EVENT_STOCK';

    /**
     * オプション在庫変更
     */
    public const ERROR_CHANGE_OPTION_STOCK = 'ERROR_CHANGE_OPTION_STOCK';

    /**
     * 在庫切れ
     */
    public const ERROR_OUT_OF_STOCK = 'ERROR_OUT_OF_STOCK';

    /**
     * 日付重複
     */
    public const ERROR_DUPLICATE_DATE = 'ERROR_DUPLICATE_DATE';

    /**
     * 削除不可
     */
    public const ERROR_NOT_DELETE = 'ERROR_NOT_DELETE';

    /**
     * オプションの期間内
     */
    public const ERROR_OPTION_INCLUDE_DATE = 'ERROR_OPTION_INCLUDE_DATE';

    /**
     * フォーム項目オプション選択必須
     */
    public const ERROR_FORM_PATTERN_OPTION_REQUIRE = 'ERROR_FORM_PATTERN_OPTION_REQUIRE';

    /**
     * フォーム項目オプション選択重複
     */
    public const ERROR_FORM_PATTERN_OPTION_DUPLICATE = 'ERROR_FORM_PATTERN_OPTION_DUPLICATE';

    /**
     * フォームグループ削除不可
     */
    public const ERROR_DELETE_FORM_GROUP = 'ERROR_DELETE_FORM_GROUP';

    public const ERROR_LABEL_LEVEL = 'ERROR_LABEL_LEVEL';

    /**
     * 既に存在
     */
    public const ERROR_EXISTS = 'ERROR_EXISTS';

    /**
     * 存在しない
     */
    public const ERROR_NOT_EXISTS = 'ERROR_NOT_EXISTS';

    /**
     * アップロード
     */
    public const ERROR_UPLOAD = 'ERROR_UPLOAD';

    /**
     * アップロード（拡張子）
     */
    public const ERROR_FILE_TYPE = 'ERROR_FILE_TYPE';

    /**
     * アップロード（ファイルサイズ）
     */
    public const ERROR_FILE_SIZE = 'ERROR_FILE_SIZE';

    /**
     * アップロード（環境全体のファイルサイズ）
     */
    public const ERROR_ALL_FILE_SIZE = 'ERROR_ALL_FILE_SIZE';

    /**
     * 1つ以上の設定
     */
    public const ERROR_ONE_OR_MORE = 'ERROR_ONE_OR_MORE';

    /**
     * 1つ以上の選択
     */
    public const ERROR_ONE_OR_MORE_SELECT = 'ERROR_ONE_OR_MORE_SELECT';

    /**
     * 現在日時より過去
     */
    public const ERROR_PAST = 'ERROR_PAST';

    /**
     * ログインID使用可能文字
     */
    public const ERROR_LOGIN_ID_CHARACTER = 'ERROR_LOGIN_ID_CHARACTER';

    /**
     * パスワード使用可能文字
     */
    public const ERROR_PASSWORD_CHARACTER = 'ERROR_PASSWORD_CHARACTER';

    /**
     * パスワード英数字混在
     */
    public const ERROR_PASSWORD_ALPHANUMERIC = 'ERROR_PASSWORD_ALPHANUMERIC';

    /**
     * デフォルト項目変更不可
     */
    public const ERROR_CANNOT_EDIT_DEFAULT_ITEM = 'ERROR_CANNOT_EDIT_DEFAULT_ITEM';

    /**
     * 未来に予約があるため削除不可
     */
    public const ERROR_DELETE_USER = 'ERROR_DELETE_USER';

    /**
     * 一覧チェック件数制限
     */
    public const ERROR_LIST_CHECK_LIMIT = 'ERROR_LIST_CHECK_LIMIT';

    /**
     * 利用期間エラー
     */
    public const ERROR_NOT_EXISTS_PERIOD = 'ERROR_NOT_EXISTS_PERIOD';

    /**
     * 利用日時エラー
     */
    public const ERROR_RESERVATION_TIME = 'ERROR_RESERVATION_TIME';

    /**
     * 休日予約エラー
     */
    public const ERROR_RESERVE_HOLIDAY = 'ERROR_RESERVE_HOLIDAY';

    /**
     * 受付期間エラー
     */
    public const ERROR_RECEPTION_PERIOD = 'ERROR_RECEPTION_PERIOD';

    /**
     * 登録締切エラー
     */
    public const ERROR_REGISTRATION_DEADLINE = 'ERROR_REGISTRATION_DEADLINE';

    /**
     * 編集締切エラー
     */
    public const ERROR_EDITING_DEADLINE = 'ERROR_EDITING_DEADLINE';

    /**
     * キャンセル締切エラー
     */
    public const ERROR_CANCELLATION_DEADLINE = 'ERROR_CANCELLATION_DEADLINE';

    /**
     * 重複予約エラー
     */
    public const ERROR_DUPLICATE_RESERVATION = 'ERROR_DUPLICATE_RESERVATION';

    /**
     * 回数制限エラー(権限:全期間)
     */
    public const ERROR_RESERVATION_LIMIT_AUTHORITY_ALL = 'ERROR_RESERVATION_LIMIT_AUTHORITY_ALL';

    /**
     * 回数制限エラー(権限:未来)
     */
    public const ERROR_RESERVATION_LIMIT_AUTHORITY_FUTURE = 'ERROR_RESERVATION_LIMIT_AUTHORITY_FUTURE';

    /**
     * 回数制限エラー(権限:月)
     */
    public const ERROR_RESERVATION_LIMIT_AUTHORITY_MONTH = 'ERROR_RESERVATION_LIMIT_AUTHORITY_MONTH';

    /**
     * 回数制限エラー(権限:日)
     */
    public const ERROR_RESERVATION_LIMIT_AUTHORITY_DAY = 'ERROR_RESERVATION_LIMIT_AUTHORITY_DAY';

    /**
     * 回数制限エラー(予約枠:全期間)
     */
    public const ERROR_RESERVATION_LIMIT_EVENT_ALL = 'ERROR_RESERVATION_LIMIT_EVENT_ALL';

    /**
     * 回数制限エラー(予約枠:未来)
     */
    public const ERROR_RESERVATION_LIMIT_EVENT_FUTURE = 'ERROR_RESERVATION_LIMIT_EVENT_FUTURE';

    /**
     * 回数制限エラー(予約枠:月)
     */
    public const ERROR_RESERVATION_LIMIT_EVENT_MONTH = 'ERROR_RESERVATION_LIMIT_EVENT_MONTH';

    /**
     * 回数制限エラー(予約枠:日)
     */
    public const ERROR_RESERVATION_LIMIT_EVENT_DAY = 'ERROR_RESERVATION_LIMIT_EVENT_DAY';

    /**
     * 利用規約（会員）同意エラー
     */
    public const ERROR_USER_TERMS = 'ERROR_USER_TERMS';

    /**
     * 利用規約（予約）同意エラー
     */
    public const ERROR_RESERVATION_TERMS = 'ERROR_RESERVATION_TERMS';

    /**
     * トークン決済エラー：カード番号
     */
    public const ERROR_TOKEN_PAYMENT_CARD_NO = 'ERROR_TOKEN_PAYMENT_CARD_NO';

    /**
     * トークン決済エラー：有効期限
     */
    public const ERROR_TOKEN_PAYMENT_EXPIRE = 'ERROR_TOKEN_PAYMENT_EXPIRE';

    /**
     * トークン決済エラー：セキュリティコード
     */
    public const ERROR_TOKEN_PAYMENT_SECURITY_CODE = 'ERROR_TOKEN_PAYMENT_SECURITY_CODE';

    /**
     * トークン決済エラー：カード名義人
     */
    public const ERROR_TOKEN_PAYMENT_NAME = 'ERROR_TOKEN_PAYMENT_NAME';

    /**
     * トークン決済エラー：その他
     */
    public const ERROR_TOKEN_PAYMENT_OTHER = 'ERROR_TOKEN_PAYMENT_OTHER';

    /**
     * 決済エラー
     */
    public const ERROR_PAYMENT = 'ERROR_PAYMENT';

    /**
     * 決済ロールバックエラー
     */
    public const ERROR_PAYMENT_ROLLBACK = 'ERROR_PAYMENT_ROLLBACK';

    /**
     * 3DSecure決済エラー
     */
    public const ERROR_THREE_D_SECURE_PAYMENT = 'ERROR_THREE_D_SECURE_PAYMENT';

    /**
     * 決済 本人確認情報エラー
     */
    public const ERROR_PAYMENT_KYC = 'ERROR_PAYMENT_KYC';

    /**
     * 連続予約一部エラー
     */
    public const ERROR_RESERVATION_CONTINUOUS_PARTIAL = 'ERROR_RESERVATION_CONTINUOUS_PARTIAL';

    /**
     * リンク型決済エラー
     */
    public const ERROR_PAYMENT_LINK = 'ERROR_PAYMENT_LINK';

    /**
     * 予約開始時間アラート
     */
    public const ERROR_CANNOT_RESERVE_TIME = 'ERROR_CANNOT_RESERVE_TIME';

    /**
     * キャンセル待ち通知在庫あり
     */
    public const ERROR_WAITING_CANCELLATION_IN_STOCK = 'ERROR_WAITING_CANCELLATION_IN_STOCK';

    /**
     * 料金計算エラー
     */
    public const ERROR_CALCULATE_CHARGE = 'ERROR_CALCULATE_CHARGE';

    /**
     * 料金上限エラー
     */
    public const ERROR_CHARGE_LIMIT = 'ERROR_CHARGE_LIMIT';

    /**
     * インポート実行中エラー
     */
    public const ERROR_RUNNING_IMPORT = 'ERROR_RUNNING_IMPORT';

    /**
     * ビデオ会議生成エラー
     */
    public const ERROR_CREATE_VIDEO_MEETING = 'ERROR_CREATE_VIDEO_MEETING';

    /**
     * ビデオ会議更新エラー
     */
    public const ERROR_UPDATE_VIDEO_MEETING = 'ERROR_UPDATE_VIDEO_MEETING';

    /**
     * ビデオ会議削除エラー
     */
    public const ERROR_DELETE_VIDEO_MEETING = 'ERROR_DELETE_VIDEO_MEETING';

    /**
     * サービスアカウント変更によるビデオ会議削除エラー
     */
    public const ERROR_DELETE_CHANGE_SERVICE_ACCOUNT = 'ERROR_DELETE_CHANGE_SERVICE_ACCOUNT';

    /**
     * ZoomAPI設定変更によるビデオ会議削除エラー
     */
    public const ERROR_DELETE_CHANGE_ZOOM = 'ERROR_DELETE_CHANGE_ZOOM';

    /**
     * サービスアカウント変更によるビデオ会議更新エラー
     */
    public const INFO_UPDATE_CHANGE_SERVICE_ACCOUNT = 'INFO_UPDATE_CHANGE_SERVICE_ACCOUNT';

    /**
     * ZoomAPI設定変更によるビデオ会議更新エラー
     */
    public const INFO_UPDATE_CHANGE_ZOOM = 'INFO_UPDATE_CHANGE_ZOOM';

    /**
     * サービスアカウント設定が不正でビデオ会議の作成に失敗する
     */
    public const INFO_CREATE_INVALID_SERVICE_ACCOUNT = 'INFO_CREATE_INVALID_SERVICE_ACCOUNT';

    /**
     * ZoomAPI設定が不正でビデオ会議の作成に失敗する
     */
    public const INFO_CREATE_INVALID_ZOOM = 'INFO_CREATE_INVALID_ZOOM';

    /**
     * 公開側でビデオ会議の連携に失敗する
     */
    public const INFO_VIDEO_MEETING_FAILED_PUBLIC = 'INFO_VIDEO_MEETING_FAILED_PUBLIC';

    /**
     * ビデオ会議連携の実行件数制限エラー
     */
    public const ERROR_VIDEO_MEETING_LIMIT = 'ERROR_VIDEO_MEETING_LIMIT';

    /**
     * ビデオ会議連携の削除件数制限エラー
     */
    public const ERROR_VIDEO_METTING_DELETE_MANY = 'ERROR_VIDEO_METTING_DELETE_MANY';

    /**
     * ビデオ会議連携ロールバックエラー
     */
    public const ERROR_VIDEO_MEETING_ROLLBACK = 'ERROR_VIDEO_MEETING_ROLLBACK';

    /**
     * スマートロック連携エラー
     */
    public const ERROR_REGISTRATION_SMART_LOCK = 'ERROR_REGISTRATION_SMART_LOCK';

    /**
     * フラッシュメッセージ：登録完了
     */
    public const CREATE_SUCCESS = 'CREATE_SUCCESS';

    /**
     * フラッシュメッセージ：入力エラー
     */
    public const INVALID_INPUT = 'INVALID_INPUT';

    /**
     * フラッシュメッセージ：更新完了
     */
    public const UPDATE_SUCCESS = 'UPDATE_SUCCESS';

    /**
     * フラッシュメッセージ：削除完了
     */
    public const DELETE_SUCCESS = 'DELETE_SUCCESS';

    /**
     * フラッシュメッセージ：キャンセル完了
     */
    public const CANCEL_SUCCESS = 'CANCEL_SUCCESS';

    /**
     * フラッシュメッセージ：退会完了
     */
    public const WITHDRAW_SUCCESS = 'WITHDRAW_SUCCESS';

    /**
     * フラッシュメッセージ：お知らせ設定更新完了
     */
    public const NEWS_SETTING_UPDATE_SUCCESS = 'NEWS_SETTING_UPDATE_SUCCESS';

    /**
     * テストメール：送信失敗
     */
    public const FAILED_TEST_MAIL = 'FAILED_TEST_MAIL';

    /**
     * まとめて操作件数制限
     */
    public const TOGETHER_OPERATION_MAX = 'TOGETHER_OPERATION_MAX';

    /**
     * まとめて操作1件以上
     */
    public const TOGETHER_OPERATION_ONE_OR_MORE = 'TOGETHER_OPERATION_ONE_OR_MORE';

    /**
     * プランによる制限数エラー
     */
    public const PLAN_RESTRICTION_OVER = 'PLAN_RESTRICTION_OVER';

    /**
     * プランによる会員制限数エラー
     */
    public const PLAN_USER_RESTRICTION_OVER = 'PLAN_USER_RESTRICTION_OVER';

    /**
     * ログイン失敗
     */
    public const LOGIN_FAILED = 'LOGIN_FAILED';

    /**
     * 現在のパスワードと一致しない
     */
    public const ERROR_NOW_PASSWORD_NOT_SAME = 'ERROR_NOW_PASSWORD_NOT_SAME';

    /**
     * 前回と同一パスワードに変更
     */
    public const ERROR_PASSWORD_SAME = 'ERROR_PASSWORD_SAME';

    /**
     * 無効なURL（トークンや有効期限切れ）
     */
    public const ERROR_INVALID_URL = 'ERROR_INVALID_URL';

    /**
     * キャンセル待ち通知解除完了
     */
    public const WAITING_CANCELLATION_RELEASE_SUCCESS = 'WAITING_CANCELLATION_RELEASE_SUCCESS';

    /**
     * 一致する情報がない
     */
    public const ERROR_NOT_MATCH_DATA = 'ERROR_NOT_MATCH_DATA';

    /**
     * 退会不可
     */
    public const ERROR_CANNOT_WITHDRAW = 'ERROR_CANNOT_WITHDRAW';

    /**
     * 祝日設定用エラーメッセージ
     */
    public const ERROR_NOT_UPDATE_HOLIDAYS = 'ERROR_NOT_UPDATE_HOLIDAYS';

    /**
     * 文言NGワードエラー
     */
    public const ERROR_NG_WORD = 'ERROR_NG_WORD';

    /**
     * ページエラー
     */
    public const ERROR_PAGE_NOT_FOUND = 'ERROR_PAGE_NOT_FOUND';

    /**
     * 404エラー
     */
    public const ERROR_NOT_FOUND = 'ERROR_NOT_FOUND';

    /**
     * 基本設定非公開
     */
    public const ERROR_NOT_PUBLIC = 'ERROR_NOT_PUBLIC';

    /**
     * 汎用エラーメッセージ
     */
    public const ERROR_USER_ERROR = 'ERROR_USER_ERROR';

    /**
     * 一括削除エラー
     */
    public const DELETE_MANY_ERROR = 'DELETE_MANY_ERROR';

    /**
     * システムエラー
     */
    public const ERROR_SYSTEM_ERROR = 'ERROR_SYSTEM_ERROR';

    /**
     * JSON形式
     */
    public const ERROR_INVALID_JSON = 'ERROR_INVALID_JSON';

    /**
     * 受付ステータス更新 フラッシュメッセージ：受け付けました。
     */
    public const RECEPTION_STATUS_UPDATE_SUCCESS = 'RECEPTION_STATUS_UPDATE_SUCCESS';
    /**
     * 受付ステータス更新 フラッシュメッセージ：入場を受け付けました。
     */
    public const RECEPTION_STATUS_UPDATE_ADMISSION = 'RECEPTION_STATUS_UPDATE_ADMISSION';
    /**
     * 受付ステータス更新 フラッシュメッセージ：退場を受け付けました。
     */
    public const RECEPTION_STATUS_UPDATE_EXIT = 'RECEPTION_STATUS_UPDATE_EXIT';
    /**
     * 受付ステータス更新 フラッシュメッセージ：入場受け付け済みです。
     */
    public const RECEPTION_STATUS_ALREADY_ADMISSION = 'RECEPTION_STATUS_ALREADY_ADMISSION';
    /**
     * 受付ステータス更新 フラッシュメッセージ：退場受け付け済みです。
     */
    public const RECEPTION_STATUS_ALREADY_EXIT = 'RECEPTION_STATUS_ALREADY_EXIT';
    /**
     * 受付ステータス更新 フラッシュメッセージ：欠席で登録されています。
     */
    public const RECEPTION_STATUS_ERROR_ABSENCE = 'RECEPTION_STATUS_ERROR_ABSENCE';
    /**
     * 受付ステータス更新 フラッシュメッセージ：入場を受け付けていません。
     */
    public const RECEPTION_STATUS_ERROR_NOT_RESERVED_DATE = 'RECEPTION_STATUS_ERROR_NOT_RESERVED_DATE';
    /**
     * 会員有効期限エラー(予約時)
     */
    public const ERROR_USER_EXPIRATION_RESERVE = 'ERROR_USER_EXPIRATION_RESERVE';
    /**
     * 予約時間利用済エラー
     */
    public const ERROR_TIME_USED = 'ERROR_TIME_USED';
    /**
     * Akerunユーザー存在エラー
     */
    public const ERROR_NOT_FOUND_AKERUN_USER = 'ERROR_NOT_FOUND_AKERUN_USER';

    /**
     * リフレッシュトークン取得エラー
     */
    public const ERROR_FAILED_TO_GET_REFRESH_TOKEN = 'ERROR_FAILED_TO_GET_REFRESH_TOKEN';

    /**
     * メールドメイン利用不可エラー
     */
    public const ERROR_NOT_AVAILABLE_DOMAIN = 'ERROR_NOT_AVAILABLE_DOMAIN';

    /**
     * 決済トラッキングID存在エラー
     */
    public const ERROR_NOT_FOUND_TRACKING_ID = 'ERROR_NOT_FOUND_TRACKING_ID';

    /**
     * Akerun registered_mailエラー
     */
    public const ERROR_AKERUN_REGISTERED_MAIL = 'ERROR_AKERUN_REGISTERED_MAIL';

    /**
     * Akerun registered_mailエラー(予約)
     */
    public const ERROR_AKERUN_REGISTERED_MAIL_RESERVATION = 'ERROR_AKERUN_REGISTERED_MAIL_RESERVATION';

    /**
     * 半角数字記号（ドット）
     */
    public const ERROR_HALF_SIZE_DECIMAL_NUMBER = 'ERROR_HALF_SIZE_DECIMAL_NUMBER';

    /**
     * フラッシュメッセージ：属性・権限不一致エラー
     */
    public const NO_EXIST_ATTRIBUTE_AUTHORITY_NAME = 'NO_EXIST_ATTRIBUTE_AUTHORITY_NAME';

    /**
     * 期間の範囲が366日以上
     */
    public const ERROR_OVER_DAYS = 'ERROR_OVER_DAYS';

    /**
     * 期間内で予約できる日がない
     */
    public const ERROR_NO_AVAILABLE_DATE_IN_INPUTED_TERM = 'ERROR_NO_AVAILABLE_DATE_IN_INPUTED_TERM';
}
