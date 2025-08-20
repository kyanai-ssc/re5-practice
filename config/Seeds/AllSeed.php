<?php
declare(strict_types=1);

use App\Command\Seed\AbstractSeed;
use App\Command\Traits\CommandTrait;
use Cake\Core\Configure;
use Cake\Core\Exception\CakeException;

/**
 * AllSeed class.
 *
 * @psalm-suppress UnusedClass
 */
class AllSeed extends AbstractSeed
{
    use CommandTrait;

    /**
     * 実行したいディレクトリ名を指定
     *
     * 指定したディレクトリ以下のSeedファイルが実行される
     */
    protected const SOURCES = [
        'Initial',
    ];

    /**
     * @inheritDoc
     */
    public function run(): void
    {
        $this->checkClient();

        foreach (static::SOURCES as $source) {
            $result = $this->execCommand(Configure::readOrFail('Setting.batch.shell'), [
                'migrations',
                'seed',
                '--source=' . 'Seeds' . DS . $source,
                '--client=' . Configure::readOrFail('Client.name'),
            ], false);
            if (isset($result['status']) && $result['status'] !== 0) {
                $message = '';
                if (!empty($result['output'])) {
                    $message = implode("\n", $result['output']);
                }
                throw new CakeException($message);
            }
            if (!empty($result['output'])) {
                $this->getOutput()->writeln(implode("\n", $result['output']));
            }
        }
    }
}
