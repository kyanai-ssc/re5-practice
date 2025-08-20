<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use App\ORM\Behavior;
use ArrayObject;
use Cake\Event\EventInterface;
use Cake\Utility\Hash;

/**
 * Class InputFilterBehavior
 */
class InputFilterBehavior extends Behavior
{
    protected $_defaultConfig = [
        'implementedFinders' => [],
        'implementedMethods' => [],
    ];

    /**
     * Model.beforeMarshalイベント
     *
     * @param \Cake\Event\EventInterface $event イベント
     * @param \ArrayObject $data データ
     * @param \ArrayObject $options オプション
     * @return void
     */
    public function beforeMarshal(EventInterface $event, ArrayObject $data, ArrayObject $options)
    {
        if (Hash::get($options, 'filterInputs', true)) {
            /** @var \App\Model\AppTable $table */
            $table = $this->table();

            $table->filterInputs($data);
        }
    }
}
