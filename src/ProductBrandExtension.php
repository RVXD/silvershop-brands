<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;

/**
 * Applied to {@link \SilverShop\Page\Product}. Adds the has_one Brand link and a Brand picker in the CMS.
 *
 * @property \SilverShop\Page\Product $owner
 */
class ProductBrandExtension extends Extension
{
    private static array $has_one = [
        'Brand' => Brand::class,
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create(
                'BrandID',
                _t(self::class . '.Brand', 'Brand'),
                Brand::get()->map('ID', 'Title')->toArray()
            )->setEmptyString(_t(self::class . '.NoBrand', '(none)'))
        );
    }
}
