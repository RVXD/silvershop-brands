# SilverShop Brands

Native product **brands / manufacturers** for [SilverShop](https://github.com/silvershop/silvershop-core) — a gap
silvershop (and sunnysideup/ecommerce) don't fill natively, though PrestaShop, OpenCart, BigCommerce and (recently)
WooCommerce all ship a first-class brand entity.

## What it adds

- A **`Brand`** DataObject (title, description, logo, sort order), managed under **CMS → Brands**.
- A **`Brand` picker on each product** (has_one `Brand`); a brand lists its products (`$brand->Products()`).
- A **Sales by brand** report under CMS → Reports (revenue + units per brand, placed orders, date filter).

Brands are a lightweight DataObject (no page-tree clutter). Brand landing pages / storefront brand filtering are a
natural follow-up.

## Usage

```php
$brand = SilverShop\Brands\Brand::get()->filter('Title', 'Acme')->first();
$products = $brand->Products();                 // all products for a brand
$acme = Product::get()->filter('BrandID', $brand->ID);
```

## Status

v1.0 — **backend**: the Brand model, product link, CMS admin, sales-by-brand report, i18n (en/nl/de/fr/es/it), tests
+ CI. **No storefront yet** — brand landing pages, a `Brand.URLSegment`, a PDP brand display and a storefront brand
filter/facet (the `silvershop/category-index` → `facets` index pattern applies) are the planned follow-up for parity
with PrestaShop / WooCommerce brand pages.

## Requirements

- silvershop/core ^6, silverstripe/reports ^6, silverstripe/framework ^6, PHP ^8.3
