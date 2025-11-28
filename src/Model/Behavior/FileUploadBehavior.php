<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\Model\Entity\Reservation;
use App\ORM\Behavior;
use App\Utility\FileUtility;
use ArrayObject;
use Cake\Core\Exception\CakeException;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;

/**
 * FileUploadBehavior class.
 *
 * @psalm-suppress UnusedClass
 */
class FileUploadBehavior extends Behavior
{
    protected const DIRECTORY_PERMISSION = 0777;
    protected const FILE_PERMISSION = 0666;
    protected const CONTINUOUS_KEY = '0';

    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [],
        'baseUploadDirectory' => null,
        'uploadDirectory' => null,
        'uploadFileName' => null,
        'deletingFiles' => null,
    ];

    /**
     * Model.afterSaveイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \Cake\Datasource\EntityInterface $entity エンティティ
     * @param \ArrayObject $options オプション
     * @return void
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options)
    {
        if (!($entity instanceof Reservation)) {
            throw new CakeException();
        }
        if ($entity->get('repeat_reservation') === (string)Reservation::RESERVATION_TYPE_REPEAT_RESERVATION) {
            $continuousKey = static::CONTINUOUS_KEY;
        } else {
            $continuousKey = $entity->get('continuous_key');
        }

        if (!isset($options['upload'][$continuousKey]) || !is_array($options['upload'][$continuousKey])) {
            return;
        }

        foreach ($options['upload'][$continuousKey] as $formItemId => $file) {
            if (isset($file['file'])) {
                FileUtility::uploadReservationFile(
                    $options['upload'][$continuousKey][$formItemId],
                    $entity,
                    $formItemId
                );
            }
        }
    }
}
