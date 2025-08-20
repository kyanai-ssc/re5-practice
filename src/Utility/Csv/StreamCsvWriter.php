<?php
declare(strict_types=1);

namespace App\Utility\Csv;

use App\Utility\UserFilter\LineFeedCRLF;
use Cake\Core\Exception\CakeException;
use Throwable;

/**
 * StreamCsvWriter class.
 */
class StreamCsvWriter extends CsvWriter
{
    protected string $streamName = 'php://output';

    /**
     * @inheritDoc
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->fileConverter = null;
    }

    /**
     * @inheritDoc
     */
    protected function openFile(): void
    {
        $fileHandle = null;
        try {
            $fileHandle = fopen($this->streamName, 'wb');
            if ($fileHandle === false) {
                throw new CakeException('error fopen.');
            }
            $this->fileHandle = $fileHandle;

            stream_filter_register('appLineFeedCRLF', LineFeedCRLF::class);
            stream_filter_append($this->fileHandle, 'appLineFeedCRLF');

            if ($this->getConfig('fileBom')) {
                if (!fwrite($this->fileHandle, "\xEF\xBB\xBF")) {
                    throw new CakeException('error fwrite.');
                }
            }
        } catch (Throwable $e) {
            if (is_resource($fileHandle)) {
                fclose($fileHandle);
            }
            $this->fileHandle = null;

            throw $e;
        }
    }
}
