<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use Page;

/**
 * Optional storefront holder: lists all brands, and shows one brand + its products at a child URL
 * (`/<this-page>/<brand-url-segment>`). Add one in the CMS to switch the brand storefront on — until then
 * {@link Brand::Link()} stays null and brands are backend-only.
 */
class BrandsPage extends Page
{
    private static string $table_name = 'SilverShop_BrandsPage';

    private static string $singular_name = 'Brands Page';

    private static string $plural_name = 'Brands Pages';

    private static string $description = 'Lists brands and shows each brand with its products.';

    private static string $icon_class = 'font-icon-p-list';

    /** Products per page on a single brand view. */
    private static int $products_per_page = 12;

    /**
     * All brands, for the index template.
     */
    public function Brands()
    {
        return Brand::get();
    }

    /**
     * Brands flagged Featured, for an optional highlight block.
     */
    public function FeaturedBrands()
    {
        return Brand::get()->filter('Featured', true);
    }
}
