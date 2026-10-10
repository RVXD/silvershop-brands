<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverShop\Page\Product;
use SilverStripe\Assets\Image;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Permission;
use SilverStripe\View\Parsers\URLSegmentFilter;

/**
 * A product brand / manufacturer. Products link to a brand via a has_one; a brand lists its products.
 * Managed under CMS → Brands. Kept as a lightweight DataObject (no page-tree clutter). Carries the fields a
 * storefront brand page needs (URL segment, content, SEO, website, banner); the front-end itself is added by a
 * later phase via an optional BrandsPage — until then {@link Link()} returns null and the data stays dormant.
 *
 * @property string $Title
 * @property string $URLSegment
 * @property string $Description
 * @property string $Content
 * @property string $MetaTitle
 * @property string $MetaDescription
 * @property string $Website
 * @property bool $Featured
 * @property int $SortOrder
 * @method \SilverStripe\ORM\HasManyList<Product> Products()
 */
class Brand extends DataObject
{
    private static string $table_name = 'SilverShop_Brand';

    private static array $db = [
        'Title' => 'Varchar(255)',
        'URLSegment' => 'Varchar(255)',
        'Description' => 'HTMLText',
        'Content' => 'HTMLText',
        'MetaTitle' => 'Varchar(255)',
        'MetaDescription' => 'Varchar(255)',
        'Website' => 'Varchar(2048)',
        'Featured' => 'Boolean',
        'SortOrder' => 'Int',
    ];

    private static array $has_one = [
        'Logo' => Image::class,
        'Banner' => Image::class,
    ];

    private static array $owns = ['Logo', 'Banner'];

    private static array $has_many = [
        'Products' => Product::class . '.Brand',
    ];

    // Non-unique: uniqueness is enforced in onBeforeWrite. (A DB unique index would fail dev/build on existing rows
    // that still share an empty URLSegment before BrandBackfillTask runs.)
    private static array $indexes = [
        'URLSegment' => true,
    ];

    private static string $default_sort = '"SortOrder" ASC, "Title" ASC';

    private static array $searchable_fields = ['Title'];

    protected function onBeforeWrite(): void
    {
        parent::onBeforeWrite();

        $current = (string) $this->URLSegment;
        $source = $current !== '' ? $current : (string) $this->Title;
        if ($source !== '') {
            $this->URLSegment = $this->uniqueURLSegment($source);
        }
    }

    /**
     * A filtered, collision-free URL segment derived from the given string (title or an edited segment).
     */
    private function uniqueURLSegment(string $source): string
    {
        $base = URLSegmentFilter::create()->filter($source);
        if ($base === '') {
            $base = 'brand';
        }

        $segment = $base;
        $suffix = 2;
        while ($this->segmentTaken($segment)) {
            $segment = $base . '-' . $suffix++;
        }

        return $segment;
    }

    private function segmentTaken(string $segment): bool
    {
        return static::get()
            ->filter('URLSegment', $segment)
            ->exclude('ID', (int) $this->ID)
            ->exists();
    }

    /**
     * The brand's storefront URL, or null until a BrandsPage provides the front-end (a later phase).
     */
    public function Link(): ?string
    {
        if (!class_exists(BrandsPage::class)) {
            return null;
        }
        $page = BrandsPage::get()->first();

        return $page ? $page->Link($this->URLSegment) : null;
    }

    public function fieldLabels($includerelations = true): array
    {
        $labels = parent::fieldLabels($includerelations);
        $labels['Title'] = _t(self::class . '.db_Title', 'Name');
        $labels['URLSegment'] = _t(self::class . '.db_URLSegment', 'URL segment');
        $labels['Description'] = _t(self::class . '.db_Description', 'Summary');
        $labels['Content'] = _t(self::class . '.db_Content', 'Content');
        $labels['MetaTitle'] = _t(self::class . '.db_MetaTitle', 'Meta title');
        $labels['MetaDescription'] = _t(self::class . '.db_MetaDescription', 'Meta description');
        $labels['Website'] = _t(self::class . '.db_Website', 'Website');
        $labels['Featured'] = _t(self::class . '.db_Featured', 'Featured');
        $labels['Logo'] = _t(self::class . '.Logo', 'Logo');
        $labels['Banner'] = _t(self::class . '.Banner', 'Banner');
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
        $fields->removeByName(['SortOrder', 'Products', 'MetaTitle', 'MetaDescription']);

        $fields->insertAfter(
            'Title',
            TextField::create('URLSegment', $this->fieldLabel('URLSegment'))
                ->setDescription(_t(self::class . '.URLSegmentDesc', 'Used in the brand page web address. Auto-filled from the name.'))
        );

        $fields->dataFieldByName('Description')?->setRows(3)
            ?->setDescription(_t(self::class . '.DescriptionDesc', 'Short summary (listings, meta fallback).'));
        $fields->dataFieldByName('Content')?->setRows(8);

        foreach (['Logo', 'Banner'] as $image) {
            $fields->replaceField($image, UploadField::create($image, $this->fieldLabel($image))
                ->setFolderName('brands')
                ->setAllowedFileCategories('image'));
        }

        // SEO overrides in their own tab (fall back to Title / Description when blank — applied by the storefront).
        $fields->addFieldsToTab('Root.SEO', [
            TextField::create('MetaTitle', $this->fieldLabel('MetaTitle')),
            TextField::create('MetaDescription', $this->fieldLabel('MetaDescription')),
        ]);

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
