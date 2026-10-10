<?php

declare(strict_types=1);

namespace SilverShop\Brands\Tests;

use SilverShop\Brands\Brand;
use SilverShop\Brands\BrandsPage;
use SilverShop\Brands\BrandsPageController;
use SilverShop\Page\Product;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;

/**
 * With silvershop/search installed (a dev dependency here), the brand page's product listing gains faceted
 * navigation: selected facets narrow the brand's products and facet data is exposed for the sidebar.
 */
class BrandNavTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static bool $use_draft_site = true;

    private function showBrand(array $vars): object
    {
        $page = BrandsPage::create(['Title' => 'Brands', 'URLSegment' => 'brands']);
        $page->write();

        $rains = Brand::create(['Title' => 'Rains', 'URLSegment' => 'rains']);
        $rains->write();
        Product::create(['Title' => 'Cheap', 'BasePrice' => 10, 'BrandID' => $rains->ID])->write();
        Product::create(['Title' => 'Pricey', 'BasePrice' => 90, 'BrandID' => $rains->ID])->write();

        $request = new HTTPRequest('GET', 'brands/rains', $vars);
        $request->setSession(new Session([]));
        $request->setRouteParams(['BrandSegment' => 'rains']);

        $controller = BrandsPageController::create($page);
        $controller->setRequest($request);
        $controller->pushCurrent();
        try {
            return $controller->show($request);
        } finally {
            $controller->popCurrent();
        }
    }

    public function testBrandListingNarrowsByFacet(): void
    {
        $result = $this->showBrand(['price' => '0-25']);

        $this->assertSame(1, $result->Products->getTotalItems(), 'only the 10.00 product is in 0–25');
        $this->assertNotNull($result->Facets, 'facet sidebar data is exposed when search is installed');
    }

    public function testBrandListingUnfilteredWithoutFacetParams(): void
    {
        $result = $this->showBrand([]);

        $this->assertSame(2, $result->Products->getTotalItems(), 'both of the brand\'s products show');
    }
}
