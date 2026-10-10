<?php

declare(strict_types=1);

namespace SilverShop\Brands\Tests;

use SilverShop\Brands\Brand;
use SilverShop\Brands\SalesByBrandReport;
use SilverShop\Page\Product;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\DB;

class BrandsTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static bool $use_draft_site = true;

    public function testBrandModelAndProductLink(): void
    {
        $brand = Brand::create(['Title' => 'Acme']);
        $brand->write();

        $product = Product::create(['Title' => 'Widget', 'BrandID' => $brand->ID]);
        $product->write();

        $this->assertSame($brand->ID, (int) $product->BrandID, 'product carries the Brand has_one');
        $this->assertSame($brand->ID, $product->Brand()->ID, 'Brand() resolves to the brand');
        $this->assertTrue(
            $brand->Products()->filter('ID', $product->ID)->exists(),
            'the brand lists its product via the has_many'
        );
    }

    public function testBrandDefaultSortAndSummary(): void
    {
        Brand::create(['Title' => 'Zeta', 'SortOrder' => 2])->write();
        Brand::create(['Title' => 'Alpha', 'SortOrder' => 1])->write();

        $titles = Brand::get()->column('Title');
        $this->assertSame(['Alpha', 'Zeta'], $titles, 'default sort is SortOrder then Title');
        $this->assertArrayHasKey('Title', (new Brand())->summaryFields());
    }

    public function testUrlSegmentGeneratedAndUniqued(): void
    {
        $a = Brand::create(['Title' => 'Acme Tools']);
        $a->write();
        $this->assertSame('acme-tools', $a->URLSegment, 'segment filtered from the name');

        $b = Brand::create(['Title' => 'Acme Tools']);
        $b->write();
        $this->assertSame('acme-tools-2', $b->URLSegment, 'collision uniqued with a suffix');

        // An edited segment is re-filtered.
        $a->URLSegment = 'Acme & Co!';
        $a->write();
        $this->assertSame('acme-and-co', $a->URLSegment);
    }

    public function testUrlSegmentBackfilledOnWrite(): void
    {
        $brand = Brand::create(['Title' => 'Zen']);
        $brand->write();
        $this->assertSame('zen', $brand->URLSegment);

        // Simulate a legacy row created before the field existed.
        DB::prepared_query('UPDATE "SilverShop_Brand" SET "URLSegment" = ? WHERE "ID" = ?', ['', $brand->ID]);

        // Re-writing (what BrandBackfillTask does) regenerates it.
        $legacy = Brand::get()->byID($brand->ID);
        $legacy->write();
        $this->assertSame('zen', Brand::get()->byID($brand->ID)->URLSegment);
    }

    public function testNewFieldsPersistAndLinkDormant(): void
    {
        $brand = Brand::create([
            'Title' => 'Acme',
            'Content' => '<p>About Acme</p>',
            'MetaTitle' => 'Acme | Shop',
            'Website' => 'https://acme.example',
            'Featured' => true,
        ]);
        $brand->write();

        $reloaded = Brand::get()->byID($brand->ID);
        $this->assertSame('<p>About Acme</p>', $reloaded->Content);
        $this->assertSame('Acme | Shop', $reloaded->MetaTitle);
        $this->assertSame('https://acme.example', $reloaded->Website);
        $this->assertTrue((bool) $reloaded->Featured);

        // No storefront yet → Link() is dormant.
        $this->assertNull($brand->Link());
    }

    public function testSalesByBrandReportRunsOnEmptyData(): void
    {
        $report = new SalesByBrandReport();
        $this->assertNotEmpty((string) $report->title());

        // Exercises the raw SQLSelect (multi-join) on the test DB: no placed orders → empty, but must execute cleanly.
        $records = $report->sourceRecords();
        $this->assertCount(0, $records);
    }
}
