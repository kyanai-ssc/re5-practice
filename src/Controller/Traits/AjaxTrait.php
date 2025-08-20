<?php
declare(strict_types=1);

namespace App\Controller\Traits;

use App\View\JsonView;

/**
 * Ajax trait
 */
trait AjaxTrait
{
    /**
     * Get the View classes this controller can perform content negotiation with.
     *
     * Each view class must implement the `getContentType()` hook method
     * to participate in negotiation.
     *
     * @see Cake\Http\ContentTypeNegotiation
     * @return array<string>
     */
    public function viewClasses(): array
    {
        return [JsonView::class];
    }
}
