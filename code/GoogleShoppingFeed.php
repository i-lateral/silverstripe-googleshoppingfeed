<?php
/**
 * Shopping Feeds are a way to tell Google about pages on your site that they might
 * not otherwise discover. In its simplest terms, a XML Sitemap usually called
 * a Sitemap, with a capital S—is a list of the pages on your website.
 *
 * Creating and submitting a Sitemap helps make sure that Google knows about
 * all the  pages on your site, including URLs that may not be discoverable by
 * Google's normal crawling process.
 *
 * The GoogleSitemap handle requests to 'sitemap.xml'
 * the other two classes are used to render the sitemap.
 *
 * You can notify ("ping") Google about a changed sitemap
 * automatically whenever a new page is published or unpublished.
 * By default, Google is not notified, and will pick up your new
 * sitemap whenever the GoogleBot visits your website.
 *
 * To Enable notification of Google after every publish set google_notification_enabled
 * to true in the googlesitemaps.yml config file.
 * This file is usually located in the _config folder of your project folder.
 * e.g mysite/_config/googlesitemaps.yml
 *
 * <example>
 *	---
 *	Name: customgooglesitemaps
 *	After: googlesitemaps
 *	---
 *	GoogleSitemap:
 * 		enabled: true
 * 		google_notification_enabled: true
 * 		use_show_in_search: true
 * </example>
 *
 * @see http://www.google.com/support/webmasters/bin/answer.py?hl=en&answer=34609
 *
 * @package googlesitemaps
 */
class GoogleShoppingFeed
{

    /**
     * List of {@link DataObject} class names to include.
     *
     * @var array
     */
    private static $dataobjects = array();

    /**
     * Decorates the given DataObject with {@link GoogleShoppingFeedDescorator}
     * and pushes the class name to the registered DataObjects.
     * Note that all registered DataObjects need the method AbsoluteLink().
     *
     * @param string $className  name of DataObject to register
     *
     * @return void
     */
    public static function register_dataobject($className)
    {
        if (!self::is_registered($className)) {
            $className::add_extension('GoogleShoppingFeedExtension');

            self::$dataobjects[] = $className;
        }
    }

    /**
     * Registers multiple dataobjects in a single line. See {@link register_dataobject}
     * for the heavy lifting
     *
     * @param array $dataobjects array of class names of DataObject to register
     *
     * @return void
     */
    public static function register_dataobjects($dataobjects)
    {
        foreach ($dataobjects as $obj) {
            self::register_dataobject($obj);
        }
    }

    /**
     * Checks whether the given class name is already registered or not.
     *
     * @param string $className Name of DataObject to check
     *
     * @return bool
     */
    public static function is_registered($className)
    {
        return isset(self::$dataobjects[$className]);
    }

    /**
     * Unregisters a class from the sitemap. Mostly used for the test suite
     *
     * @param string
     */
    public static function unregister_dataobject($className)
    {
        unset(self::$dataobjects[$className]);
    }

    /**
     * Clears registered {@link DataObjects}. Useful for unit tests.
     *
     * @return void
     */
    public static function clear_registered_dataobjects()
    {
        self::$dataobjects = array();
    }


    /**
     * Constructs the list of data to include in the rendered feed. Links
     * can include pages from the website, dataobjects (such as forum posts)
     * as well as custom registered paths.
     *
     * @param string
     * @param int
     *
     * @return ArrayList
     */
    public static function get_items()
    {
        $output = new ArrayList();
        $search_filter =  Config::inst()->get('GoogleShoppingFeed', 'use_show_in_search');
        $disabled_filter =  Config::inst()->get('GoogleShoppingFeed', 'use_disabled');
        $filter = array();

        // todo migrate to extension hook or DI point for other modules to
        // modify state filters
        if (class_exists('Translatable')) {
            Translatable::disable_locale_filter();
        }

        foreach (self::$dataobjects as $class) {
            if ($class == "SiteTree") {
                $search_filter = ($search_filter) ? "\"ShowInSearch\" = 1" : "";

                $instances = Versioned::get_by_stage('SiteTree', 'Live', $search_filter);
            } elseif ($class == "Product") {
                $instances = $class::get();

                if ($disabled_filter) {
                    $instances->filter("Disabled", 0);
                }
            } else {
                $instances = new DataList($class);
            }

            if ($instances) {
                foreach ($instances as $obj) {
                    if ($obj->canIncludeInGoogleShoppingFeed()) {
                        $output->push($obj);
                    }
                }
            }
        }

        return $output;
    }

    /**
     * Returns the string frequency of edits for a particular dataobject class.
     *
     * Frequency for {@link SiteTree} objects can be determined from the version
     * history.
     *
     * @param string
     *
     * @return string
     */
    public static function get_frequency_for_class($class)
    {
        foreach (self::$dataobjects as $type => $config) {
            if ($class == $type) {
                return $config['frequency'];
            }
        }
    }

    /**
     * Is GoogleSitemap enabled?
     *
     * @return boolean
     */
    public static function enabled()
    {
        return (Config::inst()->get('GoogleShoppingFeed', 'enabled', Config::INHERITED));
    }
}
