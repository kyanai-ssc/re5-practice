<?php
declare(strict_types=1);

namespace App\Utility\UserFilter;

use App\Utility\StringUtility;
use php_user_filter;

/**
 * LineFeedCRLF Class.
 */
class LineFeedCRLF extends php_user_filter
{
    /**
     * 改行コードをCR+LFに置換する
     *
     * @param resource $in 入力ストリーム
     * @param resource $out 出力ストリーム
     * @param int $consumed 変更したデータ長を参照渡しで返す
     * @param bool $closing フィルタチェインの最後の処理であればtrue
     * @return int
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            if (isset($bucket->data)) {
                $bucket->data = StringUtility::replaceLinefeed($bucket->data, "\r\n");
                if (isset($bucket->datalen)) {
                    $consumed += (int)$bucket->datalen;
                }
                stream_bucket_append($out, $bucket);
            }
        }

        return PSFS_PASS_ON;
    }
}
