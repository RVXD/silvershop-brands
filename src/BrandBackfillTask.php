<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Generates a URL segment for brands created before the storefront fields existed (or that otherwise lack one).
 * Idempotent: brands that already have a segment are left untouched. Run after `dev/build` on an upgraded shop.
 */
class BrandBackfillTask extends BuildTask
{
    protected string $title = 'Brands: backfill URL segments';

    protected static string $description =
        'Generates a URL segment for brands that do not have one yet (idempotent).';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $filled = 0;

        foreach (Brand::get() as $brand) {
            if ((string) $brand->URLSegment === '') {
                $brand->write(); // onBeforeWrite generates + uniques the segment
                $filled++;
            }
        }

        $output->writeln("Filled URL segments for {$filled} brand(s).");

        return Command::SUCCESS;
    }
}
