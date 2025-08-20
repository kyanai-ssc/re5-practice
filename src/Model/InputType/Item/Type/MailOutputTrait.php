<?php
declare(strict_types=1);

namespace App\Model\InputType\Item\Type;

/**
 * MailOutput trait.
 */
trait MailOutputTrait
{
    /**
     * メールの出力可否を判定
     *
     * @param int $autoReplyMailType 自動返信メールタイプ
     * @param bool $oldType 変更前情報の置き換え
     * @return bool 判定結果
     */
    public function canOutputMailValue(int $autoReplyMailType, bool $oldType = false)
    {
        return true;
    }

    /**
     * メールの出力内容をエスケープ
     *
     * @return bool 判定結果
     */
    public function useEscapeValue()
    {
        return true;
    }
}
