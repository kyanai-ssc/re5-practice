<?php
declare(strict_types=1);

namespace App\Controller\Component;

use Cake\Controller\Component;

/**
 * フレームコンポーネント
 */
class FrameComponent extends Component
{
    /**
     * フレームでの表示を許可
     *
     * @return void
     */
    public function allow()
    {
        $response = $this->getController()->getResponse()->withoutHeader('X-Frame-Options');
        $this->getController()->setResponse($response);
    }

    /**
     * フレームでの表示をSAMEORIGINで許可
     *
     * @return void
     */
    public function sameorigin()
    {
        $response = $this->getController()->getResponse()->withHeader('X-Frame-Options', 'SAMEORIGIN');
        $this->getController()->setResponse($response);
    }
}
