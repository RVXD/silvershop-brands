<?php

declare(strict_types=1);

namespace SilverShop\Brands\Search;

use SilverShop\Brands\Brand;
use SilverShop\Search\Facet\SearchFacet;
use SilverShop\Search\Query\SearchQuery;
use SilverStripe\ORM\DataList;

/**
 * Brand facet for silvershop/search. Ships here (not in the search module) so brands owns its own filtering, and is
 * registered into the search FacetRegistry only when silvershop/search is installed (see _config/search.yml). Facet
 * values are brand URL segments, for readable facet URLs (`?brand=rains,nike`).
 */
class BrandFacet implements SearchFacet
{
    public function getName(): string
    {
        return 'brand';
    }

    public function getTitle(): string
    {
        return _t(self::class . '.TITLE', 'Brand');
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function apply(DataList $products, array $selected): DataList
    {
        if (!$selected) {
            return $products;
        }

        $ids = Brand::get()->filter('URLSegment', $selected)->column('ID');

        // A selected segment that matches no brand should yield no products, not be ignored.
        return $products->filter('BrandID', $ids ?: [-1]);
    }

    public function getOptions(DataList $context, SearchQuery $query): array
    {
        $selected = array_map('strval', $query->selected($this->getName()));
        $options = [];

        foreach (Brand::get() as $brand) {
            $value = (string) $brand->URLSegment;
            if ($value === '') {
                continue;
            }
            $count = (int) $context->filter('BrandID', $brand->ID)->count();
            $active = in_array($value, $selected, true);
            if ($count === 0 && !$active) {
                continue;
            }
            $options[] = [
                'value' => $value,
                'label' => $brand->Title,
                'count' => $count,
                'active' => $active,
            ];
        }

        return $options;
    }
}
