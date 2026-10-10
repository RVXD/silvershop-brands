<?php

declare(strict_types=1);

namespace SilverShop\Brands\Tests;

use SilverShop\Brands\Brand;
use SilverShop\Brands\Search\BrandFacet;
use SilverShop\Page\Product;
use SilverShop\Search\Query\SearchQuery;
use SilverStripe\Dev\SapphireTest;

class BrandFacetTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static bool $use_draft_site = true;

    private function seed(): void
    {
        $rains = Brand::create(['Title' => 'Rains', 'URLSegment' => 'rains']);
        $rains->write();
        $nike = Brand::create(['Title' => 'Nike', 'URLSegment' => 'nike']);
        $nike->write();

        foreach ([['Rain Jacket', 89, $rains->ID], ['Rain Boots', 59, $rains->ID], ['Trainers', 79, $nike->ID]] as $row) {
            [$title, $price, $brandID] = $row;
            $product = Product::create(['Title' => $title, 'BasePrice' => $price, 'BrandID' => $brandID]);
            $product->write();
        }
    }

    public function testApplyFiltersToSelectedBrands(): void
    {
        $this->seed();
        $facet = new BrandFacet();

        $this->assertSame(2, $facet->apply(Product::get(), ['rains'])->count());
        $this->assertSame(1, $facet->apply(Product::get(), ['nike'])->count());
        $this->assertSame(3, $facet->apply(Product::get(), ['rains', 'nike'])->count(), 'multiple brands are OR-ed');
    }

    public function testUnknownSegmentYieldsNoProducts(): void
    {
        $this->seed();

        $this->assertSame(0, (new BrandFacet())->apply(Product::get(), ['ghost'])->count());
    }

    public function testApplyWithNoSelectionIsUnfiltered(): void
    {
        $this->seed();

        $this->assertSame(3, (new BrandFacet())->apply(Product::get(), [])->count());
    }

    public function testOptionsCarryCountsAndActiveFlag(): void
    {
        $this->seed();

        $options = (new BrandFacet())->getOptions(Product::get(), new SearchQuery(filters: ['brand' => ['rains']]));
        $byValue = [];
        foreach ($options as $option) {
            $byValue[$option['value']] = $option;
        }

        $this->assertSame(2, $byValue['rains']['count']);
        $this->assertTrue($byValue['rains']['active'], 'selected brand is active');
        $this->assertSame(1, $byValue['nike']['count']);
        $this->assertFalse($byValue['nike']['active']);
    }
}
