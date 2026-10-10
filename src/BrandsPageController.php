<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use PageController;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Model\List\PaginatedList;

/**
 * Index renders the all-brands template ({@see BrandsPage}); any child URL segment is treated as a brand's
 * URLSegment and renders that brand + its products (`Layout/BrandsPage_show.ss`), or a 404.
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

        $products = PaginatedList::create($brand->Products(), $request)
            ->setPageLength((int) $this->data()->config()->get('products_per_page'));

        return $this->customise([
            'Brand' => $brand,
            'Title' => $brand->Title,
            'Products' => $products,
        ]);
    }
}
