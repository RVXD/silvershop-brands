<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

/**
 * Applied to {@link \SilverShop\Page\ProductCategory}. A lightweight single-brand filter: when a `?brand=<id|segment>`
 * request var is present, narrow the category listing to that brand via the core `updateProductsShowable` hook.
 * (Full multi-facet refine — brand + price + attributes — is the job of the planned silvershop/facets module.)
 *
 * @extends Extension<\SilverShop\Page\ProductCategory>
 */
class ProductCategoryBrandFilterExtension extends Extension
{
    public function updateProductsShowable(&$products): void
    {
        // When silvershop/search is installed, its category facet navigation owns the brand facet (including
        // multi-select ?brand=a,b), so defer to it rather than double-filter or mishandle comma-separated values.
        if (class_exists(\SilverShop\Search\Facet\FacetApplier::class)) {
            return;
        }

        $controller = Controller::curr();
        if (!$products || !$controller || !$controller->getRequest()) {
            return;
        }

        $value = trim((string) $controller->getRequest()->getVar('brand'));
        if ($value === '') {
            return;
        }

        $brand = is_numeric($value)
            ? Brand::get()->byID((int) $value)
            : Brand::get()->filter('URLSegment', $value)->first();

        if ($brand) {
            $products = $products->filter('BrandID', $brand->ID);
        }
    }
}
