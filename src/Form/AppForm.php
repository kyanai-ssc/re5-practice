<?php
declare(strict_types=1);

namespace App\Form;

use App\Model\InputsTrait;
use App\Utility\CommonData\CommonDataTrait;
use ArrayObject;
use Cake\Event\EventManager;
use Cake\Form\Form;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\Utility\Hash;
use Kuchen\Validation\Validation\Validator;

/**
 * Form abstraction used to create forms not tied to ORM backed models,
 * or to other permanent datastores.
 */
class AppForm extends Form
{
    use CommonDataTrait;
    use InputsTrait;
    use LocatorAwareTrait;
    use PaginateTrait;

    /**
     * @inheritDoc
     */
    public function __construct(?EventManager $eventManager = null)
    {
        parent::__construct($eventManager);

        $this->_validatorClass = Validator::class;
        $this->initialize();
    }

    /**
     * @inheritDoc
     */
    public function validate(array $data, ?string $validator = null): bool
    {
        $inputs = [];
        foreach ($this->getSchema()->fields() as $field) {
            $inputs[$field] = Hash::get($data, $field);
        }
        $result = new ArrayObject(Hash::expand($inputs));

        $this->filterInputs($result);
        $this->setData($result->getArrayCopy());

        if (!isset($data['reservations']['validate_reserve_date_flg'])) {
            return parent::validate($this->getData());
        } else {
            return true;
        }
    }

    /**
     * 初期化処理
     *
     * @return void
     */
    protected function initialize()
    {
    }
}
