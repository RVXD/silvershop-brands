# SilverShop Brands

Native product **brands / manufacturers** for [SilverShop](https://github.com/silvershop/silvershop-core) — a gap
silvershop (and sunnysideup/ecommerce) don't fill natively, though PrestaShop, OpenCart, BigCommerce and (recently)
WooCommerce all ship a first-class brand entity.

## What it adds

- A **`Brand`** DataObject managed under **CMS → Brands**: name, **URL segment**, summary + **content**, **SEO**
  (meta title/description), **website**, **logo** + **banner**, **featured** flag, sort order.
- A **`Brand` picker on each product** (has_one `Brand`); a brand lists its products (`$brand->Products()`).
- A **Sales by brand** report under CMS → Reports (revenue + units per brand, placed orders, date filter).
- `Brand::Link()` — the brand's storefront URL (dormant until the storefront phase adds a `BrandsPage`).

Brands are a lightweight DataObject (no page-tree clutter). The storefront data model is in place; the brand
**landing pages**, PDP brand display and brand filter are the next phase (see the consuming project's
`PLAN-brands-storefront.md`).

## Upgrading

Run `dev/build` after upgrading (adds the new columns), then the one-off backfill task to give existing brands a
URL segment:

```bash
sake dev/tasks/SilverShop-Brands-BrandBackfillTask
```

## Usage

```php
$brand = SilverShop\Brands\Brand::get()->filter('Title', 'Acme')->first();
$products = $brand->Products();                 // all products for a brand
$acme = Product::get()->filter('BrandID', $brand->ID);
```

## Status

Backend + storefront **data model** complete: Brand model with URL segment / content / SEO / website / banner /
featured, product link, CMS admin, sales-by-brand report, i18n (en/nl/de/fr/es/it), tests + CI. **Storefront pages
are next** — the brand landing page (`BrandsPage`), PDP brand display and a brand filter (the
`silvershop/category-index` → `facets` pattern applies) complete parity with PrestaShop / WooCommerce brand pages.

## Requirements

- silvershop/core ^6, silverstripe/reports ^6, silverstripe/framework ^6, PHP ^8.3
