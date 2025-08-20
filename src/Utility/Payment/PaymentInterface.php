<?php
declare(strict_types=1);

namespace App\Utility\Payment;

/**
 * PaymentInterface
 */
interface PaymentInterface
{
    /**
     * 決済トークン用のJSを取得
     *
     * @return string
     */
    public function getTokenJsUrl(): string;

    /**
     * オーダーIDを取得
     *
     * @return string
     */
    public function getOrderId(): string;

    /**
     * オーダーIDを設定
     *
     * @param string $orderId オーダーID
     * @return void
     */
    public function setOrderId(string $orderId): void;

    /**
     * データIDを取得
     *
     * @return string
     */
    public function getDataId(): string;

    /**
     * データIDを設定
     *
     * @param string $dataId データID
     * @return void
     */
    public function setDataId(string $dataId): void;

    /**
     * エラーを判定
     *
     * @return bool
     */
    public function hasErrors(): bool;

    /**
     * エラーメッセージを取得
     *
     * @return array|null
     */
    public function getErrors(): ?array;

    /**
     * エラーメッセージを設定
     *
     * @param array $errors エラーメッセージ
     * @return void
     */
    public function setErrors(array $errors): void;

    /**
     * トークン決済を実行
     *
     * @param array $options オプション
     * @return array|null
     */
    public function executeTokenPayment(array $options): ?array;

    /**
     * 直近の決済をキャンセル
     *
     * @return void
     */
    public function cancelLastPayment(): void;

    /**
     * 決済情報の取得可否
     *
     * @param array $options オプション
     * @return bool
     */
    public function canGetPaymentData(array $options = []): bool;

    /**
     * 決済情報の取得
     *
     * @param array $options オプション
     * @return array|null
     */
    public function getPaymentData(array $options = []): ?array;

    /**
     * 決済をキャンセル
     *
     * @param array $options オプション
     * @return bool
     */
    public function cancelPayment(array $options = []): bool;

    /**
     * 3Dセキュアの判定
     *
     * @param array $paymentData 決済データ
     * @param array $options オプション
     * @return bool
     */
    public function shouldThreeDSecure(array $paymentData, array $options = []): bool;

    /**
     * 3Dセキュアの結果取得
     *
     * @param array $parameter パラメータ
     * @param array $options オプション
     * @return array|null
     */
    public function getThreeDSecurePaymentResult(array $parameter, array $options = []): ?array;

    /**
     * 決済済み判定
     *
     * @param array $paymentData 決済データ
     * @param array $options オプション
     * @return bool
     */
    public function isPaymentCompleted(array $paymentData, array $options = []): bool;
}
