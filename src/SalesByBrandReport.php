<?php

declare(strict_types=1);

namespace SilverShop\Brands;

use SilverShop\Model\Order;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Model\ArrayData;
use SilverStripe\Model\List\ArrayList;
use SilverStripe\ORM\Queries\SQLSelect;
use SilverStripe\Reports\Report;

/**
 * Revenue and units sold per brand, for a period. Placed orders, product line items only. Products with no brand
 * are excluded.
 */
class SalesByBrandReport extends Report
{
    public function title()
    {
        return _t(__CLASS__ . '.TITLE', 'Sales by brand');
    }

    public function description()
    {
        return _t(__CLASS__ . '.DESC', 'Revenue and units sold per brand for a period. Placed orders, product lines only.');
    }

    public function group()
    {
        return _t('SilverShop\\Reports.GROUP', 'Shop');
    }

    public function sort()
    {
        return 320;
    }

    public function parameterFields()
    {
        return FieldList::create(
            DateField::create('StartDate', _t(__CLASS__ . '.Start', 'From (paid date)')),
            DateField::create('EndDate', _t(__CLASS__ . '.End', 'To (paid date)'))
        );
    }

    public function sourceRecords($params = null)
    {
        $statuses = array_values((array) Order::config()->get('placed_status'));
        if (empty($statuses)) {
            return ArrayList::create();
        }

        $query = SQLSelect::create();
        $query->setSelect([
            'Brand' => '"b"."Title"',
            'Units' => 'SUM("oi"."Quantity")',
            'Revenue' => 'SUM("oa"."CalculatedTotal")',
        ]);
        $query->setFrom('"SilverShop_OrderAttribute" AS "oa"');
        $query->addInnerJoin('SilverShop_OrderItem', '"oi"."ID" = "oa"."ID"', 'oi');
        $query->addInnerJoin('SilverShop_Product_OrderItem', '"poi"."ID" = "oa"."ID"', 'poi');
        $query->addInnerJoin('SilverShop_Order', '"o"."ID" = "oa"."OrderID"', 'o');
        $query->addInnerJoin('SilverShop_Product', '"p"."ID" = "poi"."ProductID"', 'p');
        $query->addInnerJoin('SilverShop_Brand', '"b"."ID" = "p"."BrandID"', 'b');

        $placeholders = implode(',', array_fill(0, count($statuses), '?'));
        $query->addWhere(['"o"."Status" IN (' . $placeholders . ')' => $statuses]);

        if (!empty($params['StartDate'])) {
            $query->addWhere(['"o"."Paid" >= ?' => $params['StartDate'] . ' 00:00:00']);
        }
        if (!empty($params['EndDate'])) {
            $query->addWhere(['"o"."Paid" <= ?' => $params['EndDate'] . ' 23:59:59']);
        }

        $query->setGroupBy(['"b"."ID"', '"b"."Title"']);
        $query->setOrderBy('SUM("oa"."CalculatedTotal")', 'DESC');

        $list = ArrayList::create();
        foreach ($query->execute() as $row) {
            $list->push(ArrayData::create([
                'Brand' => $row['Brand'],
                'Units' => (int) $row['Units'],
                'Revenue' => number_format((float) $row['Revenue'], 2),
            ]));
        }

        return $list;
    }

    public function columns()
    {
        return [
            'Brand' => _t(__CLASS__ . '.ColBrand', 'Brand'),
            'Units' => _t(__CLASS__ . '.ColUnits', 'Units sold'),
            'Revenue' => _t(__CLASS__ . '.ColRevenue', 'Revenue'),
        ];
    }
}
