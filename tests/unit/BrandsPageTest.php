<?php

declare(strict_types=1);

namespace SilverShop\Brands\Tests;

use SilverShop\Brands\Brand;
use SilverShop\Brands\BrandsPage;
use SilverShop\Brands\BrandsPageController;
use SilverShop\Brands\ProductCategoryBrandFilterExtension;
use SilverShop\Page\Product;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Control\Session;
use SilverStripe\Dev\SapphireTest;

class BrandsPageTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static bool $use_draft_site = true;

    public function testBrandLinkResolvesViaBrandsPage(): void
    {
        $brand = Brand::create(['Title' => 'Acme']);
        $brand->write();
        $this->assertNull($brand->Link(), 'dormant without a BrandsPage');

        $page = BrandsPage::create(['Title' => 'Brands']);
        $page->write();

        $link = $brand->Link();
        $this->assertNotNull($link, 'resolves once a BrandsPage exists');
        $this->assertStringContainsString($brand->URLSegment, (string) $link);
    }

    public function testBrandsPageListsBrandsAndFeatured(): void
    {
        $page = BrandsPage::create(['Title' => 'Brands']);
        $page->write();

        Brand::create(['Title' => 'Plain'])->write();
        Brand::create(['Title' => 'Star', 'Featured' => true])->write();

        $this->assertSame(2, $page->Brands()->count());
        $this->assertSame(['Star'], $page->FeaturedBrands()->column('Title'));
    }

    public function testBrandFilterNarrowsByRequestVar(): void
    {
        $brand = Brand::create(['Title' => 'Acme']);
        $brand->write();
        $match = Product::create(['Title' => 'Branded', 'BrandID' => $brand->ID]);
        $match->write();
        $other = Product::create(['Title' => 'Unbranded']);
        $other->write();

        $request = new HTTPRequest('GET', '/', ['brand' => $brand->URLSegment]);
        $request->setSession(new Session([]));
        $controller = Controller::create();
        $controller->setRequest($request);
        $controller->pushCurrent();

        try {
            $list = Product::get();
            (new ProductCategoryBrandFilterExtension())->updateProductsShowable($list);
            $ids = array_map('intval', $list->column('ID'));
            $this->assertContains((int) $match->ID, $ids);
            $this->assertNotContains((int) $other->ID, $ids);
        } finally {
            $controller->popCurrent();
        }
    }

    public function testShowBuildsBrandViewForKnownSegment(): void
    {
        $page = BrandsPage::create(['Title' => 'Brands']);
        $page->write();
        $brand = Brand::create(['Title' => 'Acme']);
        $brand->write();
        Product::create(['Title' => 'Widget', 'BrandID' => $brand->ID])->write();

        $controller = BrandsPageController::create($page);
        $request = new HTTPRequest('GET', 'brands/' . $brand->URLSegment);
        $request->setRouteParams(['BrandSegment' => $brand->URLSegment]);
        $request->setSession(new Session([]));
        $controller->setRequest($request);
        $controller->pushCurrent();

        try {
            // Runs the full action body (incl. PaginatedList) up to customise(); doesn't render the template.
            $result = $controller->show($request);
            $this->assertSame($brand->ID, $result->Brand->ID);
            $this->assertSame(1, $result->Products->getTotalItems());
        } finally {
            $controller->popCurrent();
        }
    }

    public function testShowReturns404ForUnknownBrand(): void
    {
        $page = BrandsPage::create(['Title' => 'Brands']);
        $page->write();

        $controller = BrandsPageController::create($page);
        $request = new HTTPRequest('GET', 'brands/nope');
        $request->setRouteParams(['BrandSegment' => 'nope']);

        $this->expectException(HTTPResponse_Exception::class);
        $controller->show($request);
    }
}
