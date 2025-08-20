<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Utility\Security;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelMedium;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QrCodeUtility
{
    public const QR_CODE_SIZE = 150;
    public const QR_CODE_MARGIN = 10;
    public const QR_CODE_ENCODING = 'UTF-8';
    public const QR_CODE_FOREGROUND_COLOR = ['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0];
    public const QR_CODE_BACKGROUND_COLOR = ['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0];

    public const RANDOM_STRING_LENGTH = 16;

    /**
     * QRコードで保持する情報を生成
     *
     * @param string|int $id 予約ID
     * @param \Cake\I18n\FrozenTime $created 登録日時
     * @return string
     */
    public static function createQrCodeData($id, $created)
    {
        return Security::hash(
            ((string)$id) . $created->format('YmdHis') . Security::randomString(self::RANDOM_STRING_LENGTH),
            'sha256'
        );
    }

    /**
     * QRコード画像を文字列で生成して返す
     *
     * @param string $qrCodeData QRコードに埋め込むデータ
     * @return string
     */
    public static function createQrCodeImage($qrCodeData)
    {
        $qrCode = new QrCode($qrCodeData);
        $qrCode->setSize(self::QR_CODE_SIZE);
        $qrCode->setMargin(self::QR_CODE_MARGIN);
        $qrCode->setEncoding(new Encoding(self::QR_CODE_ENCODING));
        $qrCode->setErrorCorrectionLevel(new ErrorCorrectionLevelMedium());
        $qrCode->setForegroundColor(new Color(
            self::QR_CODE_FOREGROUND_COLOR['r'],
            self::QR_CODE_FOREGROUND_COLOR['g'],
            self::QR_CODE_FOREGROUND_COLOR['b'],
            self::QR_CODE_FOREGROUND_COLOR['a'],
        ));
        $qrCode->setBackgroundColor(new Color(
            self::QR_CODE_BACKGROUND_COLOR['r'],
            self::QR_CODE_BACKGROUND_COLOR['g'],
            self::QR_CODE_BACKGROUND_COLOR['b'],
            self::QR_CODE_BACKGROUND_COLOR['a'],
        ));

        $writer = new PngWriter();

        return $writer->write($qrCode)->getString();
    }
}
