<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Tests\Model;

use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;

/**
 * @package googleshoppingfeed
 * @subpackage tests
 */
class Test_Shipping extends DataObject implements TestOnly
{

    private static $db = array(
        'Title' => 'Varchar(10)',
        'Price' => 'Currency',
        'Country' => 'Varchar(2)'
    );
    
    private static $belongs_many_many = array(
        "Products" => Test_Product::class
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
