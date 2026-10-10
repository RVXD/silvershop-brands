<?php

declare(strict_types=1);

namespace SilverShop\Brands\Tests;

use SilverShop\Brands\Brand;
use SilverShop\Brands\SalesByBrandReport;
use SilverShop\Page\Product;
use SilverStripe\Dev\SapphireTest;

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

    public function testSalesByBrandReportRunsOnEmptyData(): void
    {
        $report = new SalesByBrandReport();
        $this->assertNotEmpty((string) $report->title());

        // Exercises the raw SQLSelect (multi-join) on the test DB: no placed orders → empty, but must execute cleanly.
        $records = $report->sourceRecords();
        $this->assertCount(0, $records);
    }
}
