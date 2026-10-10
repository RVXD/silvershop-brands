# SilverShop Brands

Native product **brands / manufacturers** for [SilverShop](https://github.com/silvershop/silvershop-core) — a gap
silvershop (and sunnysideup/ecommerce) don't fill natively, though PrestaShop, OpenCart, BigCommerce and (recently)
WooCommerce all ship a first-class brand entity.

## What it adds

- A **`Brand`** DataObject managed under **CMS → Brands**: name, **URL segment**, summary + **content**, **SEO**
  (meta title/description), **website**, **logo** + **banner**, **featured** flag, sort order.
- A **`Brand` picker on each product** (has_one `Brand`); a brand lists its products (`$brand->Products()`).
- A **Sales by brand** report under CMS → Reports (revenue + units per brand, placed orders, date filter).
- A **`BrandsPage`** (optional, add it in the CMS): an **all-brands index** and a **per-brand page** at
  `/<brands-page>/<brand-url-segment>` listing that brand's products (paginated). `Brand::Link()` resolves to it.
- A **PDP brand display** include — `<% include SilverShop/Brands/ProductBrand %>` (logo + name linking to the brand);
  drop it into your theme's `Product.ss`.
- A **brand filter** on category listings — `?brand=<id|url-segment>` narrows a `ProductCategory` to one brand.

Brands are a lightweight DataObject (no page-tree clutter); the storefront is driven by the optional `BrandsPage`.
All templates (`SilverShop/Brands/Layout/BrandsPage.ss`, `BrandsPage_show.ss`, `Includes/ProductBrand.ss`) are
theme-overridable. Full multi-facet refine (brand + price + attributes) is the job of the planned `silvershop/facets`.

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

v1.2 — **storefront complete**: Brand model (URL segment / content / SEO / website / banner / featured), product
link, CMS admin, sales-by-brand report, **BrandsPage landing + per-brand product listing**, **PDP brand display**,
a **brand filter** on category listings, i18n (en/nl/de/fr/es/it), tests + CI. Reaches PrestaShop / WooCommerce
brand-page parity. Beyond scope (planned `silvershop/facets`): multi-facet refine (brand + price + attributes).

## Requirements

- silvershop/core ^6, silverstripe/reports ^6, silverstripe/framework ^6, PHP ^8.3
