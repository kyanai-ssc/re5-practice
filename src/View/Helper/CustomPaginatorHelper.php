<?php
declare(strict_types=1);

namespace App\View\Helper;

use Cake\Utility\Hash;
use Cake\View\Helper\PaginatorHelper;

/**
 * CustomPaginatorHelper class.
 * ページャー1ページ目表示用に調整
 */
class CustomPaginatorHelper extends PaginatorHelper
{
    /**
     * Merges passed URL options with current pagination state to generate a pagination URL.
     * if page param empty or page=1 , return page=1 param (Add Custom)
     *
     * @param array<string, mixed> $options Pagination/URL options array
     * @param string|null $model Which model to paginate on
     * @param array $url URL.
     * @return array An array of URL parameters
     */
    public function generateUrlParams(array $options = [], ?string $model = null, array $url = []): array
    {
        $paging = $this->params($model);
        $paging += ['page' => null, 'sort' => null, 'direction' => null, 'limit' => null];

        if (
            !empty($paging['sort'])
            && !empty($options['sort'])
            && strpos($options['sort'], '.') === false
        ) {
            $paging['sort'] = $this->_removeAlias($paging['sort'], $model = null);
        }
        if (
            !empty($paging['sortDefault'])
            && !empty($options['sort'])
            && strpos($options['sort'], '.') === false
        ) {
            $paging['sortDefault'] = $this->_removeAlias($paging['sortDefault'], $model);
        }

        $options += array_intersect_key(
            $paging,
            ['page' => null, 'limit' => null, 'sort' => null, 'direction' => null]
        );

        //ページの指定がない場合は1ページ目
        if (!empty($options['page']) && $options['page'] === 1) {
            $options['page'] = 1;
        }

        if (
            isset($paging['sortDefault'], $paging['directionDefault'], $options['sort'], $options['direction'])
            && $options['sort'] === $paging['sortDefault']
            && strtolower($options['direction']) === strtolower($paging['directionDefault'])
        ) {
            $options['sort'] = $options['direction'] = null;
        }
        $baseUrl = $this->_config['options']['url'] ?? [];
        if (!empty($paging['scope'])) {
            $scope = $paging['scope'];
            if (isset($baseUrl['?'][$scope]) && is_array($baseUrl['?'][$scope])) {
                $options += $baseUrl['?'][$scope];
                unset($baseUrl['?'][$scope]);
            }
            $options = [$scope => $options];
        }

        if (!empty($baseUrl)) {
            $url = Hash::merge($url, $baseUrl);
        }

        $url['?'] = $url['?'] ?? [];

        if (!empty($this->_config['options']['routePlaceholders'])) {
            $placeholders = array_flip($this->_config['options']['routePlaceholders']);
            $url += array_intersect_key($options, $placeholders);
            $url['?'] += array_diff_key($options, $placeholders);
        } else {
            $url['?'] += $options;
        }

        $url['?'] = Hash::filter($url['?']);

        return $url;
    }
}
