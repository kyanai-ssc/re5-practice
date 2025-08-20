<?php
declare(strict_types=1);

namespace App\Controller\Component;

use App\Utility\ArrayUtility;
use Cake\Controller\Component;
use Cake\Core\Exception\CakeException;
use Cake\Event\EventInterface;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * リクエストフィルターコンポーネント
 */
class RequestFilterComponent extends Component
{
    use LocatorAwareTrait;

    /**
     * @var array
     */
    protected $filters = null;

    /**
     * @inheritDoc
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->filters = [
            [$this, 'filterNullByte'],
        ];
    }

    /**
     * Component startup.
     *
     * @param \Cake\Event\EventInterface $event An Event instance
     * @return void
     */
    public function startup(EventInterface $event)
    {
        $this->filterInputs();
    }

    /**
     * キーを振りなおしてリクエストにセット
     *
     * @param string $modelName モデル名
     * @param string|array $key 振り直しを実施するキー
     * @param array $options オプション
     * @return void
     */
    public function setRebalanceInputs(string $modelName, $key = [], array $options = [])
    {
        $model = $this->getTableLocator()->get($modelName);
        if (!method_exists($model, 'rebalanceArrayKey')) {
            throw new CakeException();
        }

        $this->getController()->setRequest(
            $this->getController()->getRequest()->withParsedBody(
                $model->rebalanceArrayKey($this->getController()->getRequest()->getData(), $key, $options)
            )
        );
    }

    /**
     * データをフィルタリングする
     *
     * @return void
     */
    protected function filterInputs()
    {
        $request = $this->getController()->getRequest();
        $inputs = [
            'query' => $request->getQuery(),
            'data' => $request->getData(),
        ];

        $data = ArrayUtility::arrayMapRecursive(function ($value) {
            foreach ($this->filters as $filter) {
                $value = call_user_func($filter, $value);
            }

            return $value;
        }, $inputs);

        $this->getController()->setRequest($request->withQueryParams($data['query'])->withParsedBody($data['data']));
    }

    /**
     * ヌルバイトを除去する
     *
     * @param mixed $data データ
     * @return mixed フィルタリング後のデータ
     */
    protected function filterNullByte($data)
    {
        if (!is_string($data)) {
            return $data;
        }

        $value = preg_replace('/\\0/', '', $data);
        if (!is_string($value)) {
            return null;
        }

        return $value;
    }
}
