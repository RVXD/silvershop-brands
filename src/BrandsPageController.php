<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use PageController;
use SilverShop\Search\Facet\FacetApplier;
use SilverShop\Search\Facet\FacetRegistry;
use SilverShop\Search\Query\SearchQuery;
use SilverShop\Search\View\FacetView;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Model\List\PaginatedList;

/**
 * Index renders the all-brands template ({@see BrandsPage}); any child URL segment is treated as a brand's
 * URLSegment and renders that brand + its products (`Layout/BrandsPage_show.ss`), or a 404.
 *
 * When silvershop/search is installed, the brand's product listing gains faceted navigation (price, attributes,
 * rating, stock, …) via the shared facet engine, so `/brands/<brand>/?price=25-50` refines within the brand. The
 * integration is soft — without search, the page just lists the brand's products.
 */
class BrandsPageController extends PageController
{
    private static array $allowed_actions = ['show'];

    private static array $url_handlers = [
        '$BrandSegment!' => 'show',
    ];

    public function show(HTTPRequest $request)
    {
        $segment = (string) $request->param('BrandSegment');
        $brand = Brand::get()->filter('URLSegment', $segment)->first();
        if (!$brand) {
            return $this->httpError(404);
        }

        $base = $brand->Products();
        $facets = null;

        if (class_exists(FacetApplier::class)) {
            $query = SearchQuery::fromRequest($request, (new FacetRegistry())->names());
            $applied = FacetApplier::apply($base, $query);
            $base = $applied['products'];
            $facets = FacetView::build($applied['facets'], $request, (string) $brand->Link());
        }

        $products = PaginatedList::create($base, $request)
            ->setPageLength((int) $this->data()->config()->get('products_per_page'));

        return $this->customise([
            'Brand' => $brand,
            'Title' => $brand->Title,
            'Products' => $products,
            'Facets' => $facets,
        ]);
    }
}
