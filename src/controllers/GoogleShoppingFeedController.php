<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\controllers;

use NumberFormatter;
use SilverStripe\i18n\i18n;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\SiteConfig\SiteConfig;
use ilateral\SilverStripe\GoogleShoppingFeed\GoogleShoppingFeed;

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 *
 * @package googlesitemaps
 */
class GoogleShoppingFeedController extends Controller
{

    /**
     * @var array
     */
    private static $allowed_actions = array(
        'index'
    );
    
    /**
     * Specific controller action for displaying a particular list of links 
     * for a class
     * 
     * @return mixed
     */
    public function index()
    {
        if (GoogleShoppingFeed::enabled()) {
            Config::inst()->update('SSViewer', 'set_source_file_comments', false);
            
            $this->getResponse()->addHeader(
                'Content-Type',
                'application/xml; charset="utf-8"'
            );
            $this->getResponse()->addHeader(
                'X-Robots-Tag',
                'noindex'
            );

            $items = GoogleShoppingFeed::get_items();
            
            $currency = new NumberFormatter(i18n::get_locale(), NumberFormatter::CURRENCY);
            
            $this->extend('updateGoogleShoppingFeedItems', $items);

            return array(
                "SiteConfig" => SiteConfig::current_site_config(),
                'Items' => $items,
                "Currency" => $currency->getTextAttribute(NumberFormatter::CURRENCY_CODE)
            );
        } else {
            return new SS_HTTPResponse(_t("GoogleShoppingFeed.PageNotFound", 'Page not found'), 404);
        }
    }
}
