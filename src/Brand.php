<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverShop\Page\Product;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
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
 * @method \SilverStripe\ORM\HasManyList<Product> Products()
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

    private static array $searchable_fields = ['Title'];

    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Title'] = _t(self::class . '.db_Title', 'Name');
        $labels['Description'] = _t(self::class . '.db_Description', 'Description');
        $labels['Logo'] = _t(self::class . '.Logo', 'Logo');
        $labels['Products'] = _t(self::class . '.Products', 'Products');

        return $labels;
    }

    public function summaryFields(): array
    {
        return [
            'Title' => _t(self::class . '.col_Title', 'Brand'),
            'Products.Count' => _t(self::class . '.col_Products', 'Products'),
        ];
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName(['SortOrder', 'Products']);

        $fields->dataFieldByName('Description')?->setRows(4);

        $fields->replaceField('Logo', UploadField::create('Logo', $this->fieldLabel('Logo'))
            ->setFolderName('brands')
            ->setAllowedFileCategories('image'));

        // Products in their own tab, as a paginated relation-editor grid (can be thousands of products).
        if ($this->isInDB()) {
            $fields->findOrMakeTab('Root.Products')->setTitle(_t(self::class . '.Products', 'Products'));
            $fields->addFieldToTab('Root.Products', GridField::create(
                'Products',
                $this->fieldLabel('Products'),
                $this->Products(),
                GridFieldConfig_RelationEditor::create()
            ));
        }

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
