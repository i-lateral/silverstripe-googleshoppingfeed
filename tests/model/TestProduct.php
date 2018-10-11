<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Tests\Model;

use SilverStripe\Assets\Image;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;
use ilateral\SilverStripe\GoogleShoppingFeed\Model\GoogleProductCategory;
use ilateral\SilverStripe\GoogleShoppingFeed\Extensions\Extension;

/**
 * @package googleshoppingfeed
 * @subpackage tests
 */
class TestProduct extends DataObject implements TestOnly
{
    private static $table_name = 'TestProduct';

    private static $db = array(
        "Title"     => "Varchar",
        "Price"     => "Currency",
        "Weight"    => "Decimal",
        "StockID"   => "Varchar",
        "Brand"     => "Varchar",
        "Description"=> "Text",
        "Condition" => "Varchar",
        "Availability"=> "Varchar",
        "MPN"       => "Varchar",
        "GTIN"       => "Varchar",
        "RemoveFromShoppingFeed" => "Boolean"
    );
    
    private static $has_one = array(
        "Image"     => Image::class,
        "ShoppingPrimaryImage"    => Image::class,
        "ShoppingAdditionalImage" => Image::class,
        "GoogleProductCategory" => GoogleProductCategory::class
    );
    
    private static $many_many = array(
        "Shipping" => TestShipping::class
    );

    private static $extensions = [
        Extension::class
    ];

    public function canView($member = null)
    {
        return true;
    }

    public function AbsoluteLink()
    {
        return Director::absoluteBaseURL();
    }
}