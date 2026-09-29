<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverShop\Page\Product;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;

/**
 * A product brand / manufacturer. Products link to a brand via a has_one; a brand lists its products.
 * Managed under CMS → Brands. Kept as a lightweight DataObject (no page-tree clutter); brand landing pages
 * can be added later.
 *
 * @property string $Title
 * @property string $Description
 * @property int $SortOrder
 */
class Brand extends DataObject
{
    private static string $table_name = 'SilverShop_Brand';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'Description' => 'HTMLText',
        'SortOrder' => 'Int',
    ];

    private static array $has_one = [
        'Logo' => Image::class,
    ];

    private static array $owns = ['Logo'];

    private static array $has_many = [
        'Products' => Product::class . '.Brand',
    ];

    private static string $default_sort = '"SortOrder" ASC, "Title" ASC';

    private static array $summary_fields = [
        'Title' => 'Brand',
        'Products.Count' => 'Products',
    ];

    private static array $searchable_fields = ['Title'];

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('SortOrder');

        $fields->dataFieldByName('Description')?->setRows(4);

        $fields->replaceField('Logo', UploadField::create('Logo', _t(self::class . '.Logo', 'Logo'))
            ->setFolderName('brands')
            ->setAllowedFileCategories('image'));

        return $fields;
    }

    public function canView($member = null)
    {
        return true;
    }

    public function canEdit($member = null)
    {
        return Permission::check('CMS_ACCESS', 'any', $member);
    }

    public function canCreate($member = null, $context = [])
    {
        return Permission::check('CMS_ACCESS', 'any', $member);
    }

    public function canDelete($member = null)
    {
        return Permission::check('CMS_ACCESS', 'any', $member);
    }
}
