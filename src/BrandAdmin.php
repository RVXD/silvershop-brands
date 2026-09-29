<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverStripe\Admin\ModelAdmin;

/**
 * CMS admin for managing product brands.
 */
class BrandAdmin extends ModelAdmin
{
    private static string $url_segment = 'brands';

    private static string $menu_title = 'Brands';

    private static string $menu_icon_class = 'font-icon-tags';

    private static int $menu_priority = 0;

    private static array $managed_models = [
        Brand::class,
    ];
}
