<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * MailOutput interface.
 */
interface MailOutputInterface
{
    /**
     * メールの出力可否を判定
     *
     * @param int $autoReplyMailType 自動返信メールタイプ
     * @param bool $oldType 変更前情報の置き換え
     * @return bool 判定結果
     */
    public function canOutputMailValue(int $autoReplyMailType, bool $oldType = false);

    /**
     * メールの置換文字を取得
     *
     * @return string 置換文字
     */
    public function getMailReplaceToken();

    /**
     * メールの出力内容を取得
     *
     * @param array $data データ
     * @param array|null $options オプション
     * @return string|null 出力内容
     */
    public function getMailOutputValue(array $data, ?array $options = null);

    /**
     * メールの出力内容をエスケープ
     *
     * @return bool 判定結果
     */
    public function useEscapeValue();
}
