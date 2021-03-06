<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Tests;

use SilverStripe\Assets\Image;
use SilverStripe\Dev\TestOnly;
use SilverStripe\ORM\DataObject;
use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use ilateral\SilverStripe\GoogleShoppingFeed\GoogleShoppingFeed;
use ilateral\SilverStripe\GoogleShoppingFeed\Tests\Model\TestProduct;
use ilateral\SilverStripe\GoogleShoppingFeed\Tests\Model\TestShipping;

/**
 * @package googleshoppingfeed
 * @subpackage tests
 */
class GoogleShoppingFeedTest extends FunctionalTest
{

    public static $fixture_file = 'GoogleShoppingFeedTest.yml';

    protected static $extra_dataobjects = [
        TestProduct::class,
        TestShipping::class
    ];

    public function setUp()
    {
        parent::setUp();
        
        GoogleShoppingFeed::clear_registered_dataobjects();
    }

    public function tearDown()
    {
        parent::tearDown();

        GoogleShoppingFeed::clear_registered_dataobjects();
    }

    public function testGetItems()
    {
        $items = GoogleShoppingFeed::getItems(TestProduct::class, 1);
        $this->assertEquals(3, $items->count());
    }

    public function testAccessingXMLFile()
    {
        $response = $this->get('shoppingfeed.xml');
        $body = $response->getBody();

        // the feed should contain <g:id> elements to both those files and not the other
        // dataobject as it hasn't been registered
        $expected = "<g:id>ip-123</g:id>";
        $this->assertEquals(1, substr_count($body, $expected), 'Product with code ip-123 exists');
        
        $expected = "<g:id>cb-123</g:id>";
        $this->assertEquals(1, substr_count($body, $expected), 'Product with code cb-123 exists');

        $expected = "<g:id>dn-123</g:id>";
        $this->assertEquals(1, substr_count($body, $expected), 'Product with code dn-123 exists');
    }

    public function testAccess()
    {
        Config::inst()->update(GoogleShoppingFeed::class, 'enabled', true);
        
        $response = $this->get('shoppingfeed.xml');

        $this->assertEquals(200, $response->getStatusCode(), 'Feed returns a 200 success when enabled');
        $this->assertEquals('application/xml; charset="utf-8"', $response->getHeader('Content-Type'));
        
        $response = $this->get('shoppingfeed.xml');
        $this->assertEquals(200, $response->getStatusCode(), 'Feed returns a 200 success when enabled with products');
        $this->assertEquals('application/xml; charset="utf-8"', $response->getHeader('Content-Type'));

        Config::inst()->remove(GoogleShoppingFeed::class, 'enabled');
        Config::inst()->update(GoogleShoppingFeed::class, 'enabled', false);
        
        $response = $this->get('shoppingfeed.xml');
        $this->assertEquals(404, $response->getStatusCode(), 'Feed returns a 404 when disabled');
    }
    
    public function testRemoveFromFeed()
    {
        Config::inst()->update(GoogleShoppingFeed::class, 'enabled', true);
        
        $response = $this->get('shoppingfeed.xml');
        $body = $response->getBody();

        // Check that the feed does not contain a removed product
        $expected = "<g:id>rm-123</g:id>";
        $result = (substr_count($body, $expected)) ? true : false;
        $this->assertFalse($result);
    }
}