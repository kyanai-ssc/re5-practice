<?php
declare(strict_types=1);

namespace App\Command\Traits;

use App\Utility\Console\CommandEscaper;
use Cake\Core\Configure;

/**
 * Command trait.
 */
trait CommandTrait
{
    /**
     * コマンドを実行
     *
     * @param string $command コマンド
     * @param array $argument 引数
     * @param bool $background バックグラウンド実行
     * @return array|null
     */
    protected function execCommand(string $command, array $argument = [], bool $background = true)
    {
        $execCommand = CommandEscaper::escape($command, $argument);

        $result = null;
        if (!$background) {
            $result = [
                'output' => [],
                'status' => -1,
            ];
            exec($execCommand . ' ' . '2>&1', $result['output'], $result['status']);
        } else {
            exec(sprintf(Configure::readOrFail('Env.shell.background'), $execCommand));
        }

        return $result;
    }
}
