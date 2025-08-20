<?php
declare(strict_types=1);

namespace App\Utility\Console;

class CommandEscaper
{
    /**
     * コマンドをエスケープ
     *
     * @param string $command コマンド
     * @param array $argument 引数
     * @return string
     */
    public static function escape(string $command, array $argument = [])
    {
        foreach ($argument as $key => $value) {
            $argument[$key] = escapeshellarg((string)$value);
        }
        $result = $command . ' ' . implode(' ', $argument);

        return $result;
    }
}
