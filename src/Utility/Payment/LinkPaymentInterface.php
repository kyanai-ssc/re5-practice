<?php
declare(strict_types=1);

namespace App\Utility\Payment;

/**
 * LinkPaymentInterface
 */
interface LinkPaymentInterface
{
    /**
     * リンク型決済のURLを取得
     *
     * @param array $options オプション
     * @return string
     */
    public function getLinkPaymentUrl(array $options = []): string;

    /**
     * リンク型決済のパラメータを生成
     *
     * @param array $options オプション
     * @return array
     */
    public function createLinkParameter(array $options): array;

    /**
     * リンク型決済の結果を取得
     *
     * @param array $parameter パラメータ
     * @param array $options オプション
     * @return array|null
     */
    public function getLinkPaymentResult(array $parameter, array $options = []): ?array;
}
