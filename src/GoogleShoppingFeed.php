<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed;

use SilverStripe\ORM\DataList;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataObject;
use SilverStripe\Core\Config\Config;
use SilverStripe\Versioned\Versioned;
use SilverCommerce\CatalogueAdmin\Model\CatalogueProduct;
use ilateral\SilverStripe\GoogleShoppingFeed\Extensions\Extension;

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
    public static function getItems()
    {
        $output = ArrayList::create();
        $search_filter = Config::inst()->get(__CLASS__, 'use_show_in_search');
        $disabled_filter = Config::inst()->get(__CLASS__, 'use_disabled');
        $filter = [];
        $classes = [];
        $all_classes = ClassInfo::subclassesFor(DataObject::class);

        unset($all_classes[strtolower(DataObject::class)]);

        foreach ($all_classes as $class) {
            if ($class::has_extension(Extension::class, null, true)) {
                $classes[] = $class;
            }
        }

        // todo migrate to extension hook or DI point for other modules to 
        foreach ($classes as $class) {
            if ($class == SiteTree::class) {
                $search_filter = ($search_filter) ? "\"ShowInSearch\" = 1" : "";
                $instances = Versioned::get_by_stage('SiteTree', 'Live', $search_filter);
            } elseif ($class == CatalogueProduct::class) {
                $instances = $class::get();

                if ($disabled_filter) {
                    $instances->filter("Disabled", 0);
                }
            } else {
                $instances = DataList::create($class);
            }

            if ($instances) {
                foreach ($instances as $obj) {
                    if ($obj->canIncludeInGoogleShoppingFeed()) {
                        $output->push($obj);
                    }
                }
            }
        }

        $output->removeDuplicates();

        return $output;
    }

    /**
     * Static interface to instance level ->getItems() for backward compatibility.
     *
     * @param string
     * @param int
     *
     * @return ArrayList
     * @deprecated Please create an instance and call ->getSitemaps() instead.
     */
    public static function get_items()
    {
        return static::inst()->getItems();
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
        return (Config::inst()->get(self::class, 'enabled'));
    }
}
