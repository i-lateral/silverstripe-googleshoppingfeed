<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Tests\Model;

use SilverStripe\Assets\Image;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;

/**
 * @package googleshoppingfeed
 * @subpackage tests
 */
class Test_Product extends DataObject implements TestOnly
{
    
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
        "RemoveFromShoppingFeed" => "Boolean"
    );
    
    private static $has_one = array(
        "Image"     => Image::class
    );
    
    private static $many_many = array(
        "Shipping" => Test_Shipping::class
    );

    public function canView($member = null)
    {
        return true;
    }

    public function AbsoluteLink()
    {
        return Director::absoluteBaseURL();
    }
}