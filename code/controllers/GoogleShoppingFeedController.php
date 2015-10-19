<?php

require_once BASE_PATH . "/" . THIRDPARTY_DIR . "/Zend/Currency.php";

/**
 * Controller for displaying the xml feed.
 *
 * <code>
 * http://site.com/shoppingfeed.xml
 * </code>
 *
 * @package googlesitemaps
 */
class GoogleShoppingFeedController extends Controller {

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
	public function index() {

		if(GoogleShoppingFeed::enabled()) {
			Config::inst()->update('SSViewer', 'set_source_file_comments', false);
			
			$this->getResponse()->addHeader('Content-Type', 'application/xml; charset="utf-8"');
			$this->getResponse()->addHeader('X-Robots-Tag', 'noindex');

			$items = GoogleShoppingFeed::get_items();
            
            $currency = new Zend_Currency(i18n::get_locale());
            
			$this->extend('updateGoogleShoppingFeedItems', $items);

			return array(
                "SiteConfig" => SiteConfig::current_site_config(),
				'Items' => $items,
                "Currency" => $currency->getShortName()
			);
		} else {
			return new SS_HTTPResponse(_t("GoogleShoppingFeed.PageNotFound",'Page not found'), 404);
		}
	}
}
