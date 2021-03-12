<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Extensions;

use SilverStripe\Assets\Image;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use TractorCow\AutoComplete\AutoCompleteField;
use ilateral\SilverStripe\GoogleShoppingFeed\Model\GoogleProductCategory;


class Extension extends DataExtension
{
    
    /**
     * @var array
     */
    private static $db = [
        "RemoveFromShoppingFeed" => "Boolean",
        "Condition" => 'Enum(array("new","refurbished","used"),"new")',
        "Availability" => 'Enum(array("in stock","out of stock","pre-order"),"in stock")',
        "Brand" => "Varchar",
        "MPN" =>  "Varchar(255)",
        "GTIN" => "Varchar(255)",
    ];

    private static $has_one = [
        "ShoppingPrimaryImage"    => Image::class,
        "ShoppingAdditionalImage" => Image::class,
        "GoogleProductCategory" => GoogleProductCategory::class
    ];

    /**
     * Simple method to get the primary image for the feed.
     * This method tried to assume som common image association
     * name (if the default field is not used)
     * 
     * If no image is found, returns an empty image object.
     *
     * @return Image
     */
    public function getPrimaryImage()
    {
        if ($this->owner->ShoppingPrimaryImage()->exists()) {
            return $this->owner->ShoppingPrimaryImage();
        }
        
        if (method_exists($this->owner, "Image") && $this->owner->Image()->exists()) {
            return $this->owner->Image();
        }

        if (method_exists($this->owner, "FeaturedImage") && $this->owner->FeaturedImage()->exists()) {
            return $this->owner->FeaturedImage();
        }
        
        if (method_exists($this->owner, "SummaryImage") && $this->owner->SummaryImage()->exists()) {
            return $this->owner->SummaryImage();
        }

        if (method_exists($this->owner, "SortedImages") && $this->owner->SortedImages()->exists()) {
            return $this->owner->SortedImages()->first();
        }

        if (method_exists($this->owner, "Images") && $this->owner->Images()->exists()) {
            return $this->owner->Images()->first();
        }

        return Image::create();
    }

    /**
     * Simple method to get the additional image for the feed.
     * 
     * If no image is found, returns an empty image object.
     *
     * @return Image
     */
    public function getAdditionalImage()
    {
        if ($this->owner->ShoppingAdditionalImage()->exists()) {
            return $this->owner->ShoppingAdditionalImage();
        }

        if (method_exists($this->owner, "SortedImages") && $this->owner->SortedImages()->exists()) {
            return $this
                ->owner
                ->SortedImages()
                ->limit(1,1)
                ->first();
        }

        if (method_exists($this->owner, "Images") && $this->owner->Images()->exists()) {
            return $this
                ->owner
                ->Images()
                ->limit(1,1)
                ->first();
        }
        
        return Image::create();
    }

    /**
     * Get a list of google shopping categories which are formatted as:
     * 
     * Key: ID of category
     * Value: Full name of category
     *
     * @return array
     */
    public function getGoogleCategories()
    {
        // Get a list of Google Categories from the 
        // product file.
        $file = BASE_PATH . "/googleshoppingfeed/thirdparty/google_product_taxonomy.txt";
        $fopen = fopen($file, 'r');
        $fread = fread($fopen, filesize($file));
        fclose($fopen);
        $result = ArrayList::create();

        foreach (explode("\n", $fread) as $string) {
            $exploded = explode(" - ", $string);
            if ($string && count($exploded) == 2) {
                $result->add(ArrayData::create([
                    "ID" => $exploded[0],
                    "Title" => $exploded[1]
                ]));
            }
        }

        return $result;
    }

    /**
     * Single function to add all fields to a tabset
     * 
     * @return null
     */
    public function addCMSFieldsToTabset($tabset)
    { 
        

        $tabset->push(ToggleCompositeField::create(
            "ShoppingFeedSettings",
            _t(
                'GoogleShoppingFeed.GoogleShoppingFeed',
                'Google Shopping Feed'
            ),
            [
                CheckboxField::create("RemoveFromShoppingFeed"),
                DropdownField::create(
                    "Condition",
                    null,
                    singleton($this->owner->ClassName)->dbObject('Condition')->enumValues()
                ),
                DropdownField::create(
                    "Availability",
                    null,
                    singleton($this->owner->ClassName)->dbObject('Availability')->enumValues()
                ),
                TextField::create("Brand"),
                TextField::create("MPN"),
                TextField::create("GTIN"),
                AutoCompleteField::create(
                    'GoogleProductCategoryID',
                    $this->owner->fieldLabel("GoogleProductCategory"),
                    '',
                    GoogleProductCategory::class,
                    'Title'
                ),
                UploadField::create("ShoppingPrimaryImage")
                    ->setFolderName("google-shopping"),
                UploadField::create("ShoppingAdditionalImage")
                ->setFolderName("google-shopping")
            ]
        ));
    }

    /**
     * Functuion to check if the extended object has settings fields in the CMS
     *
     * @return Boolean
     */
    public function hasCMSSettingsFields()
    {
        return method_exists($this->owner, "getSettingsFields");
    }
    
    /**
     * Add these fields to settings fields in the CMS (if it is used)
     *
     * @param FieldList
     */
    public function updateSettingsFields(FieldList $fields)
    {
        if($this->owner->hasCMSSettingsFields()) {
            $tabset = $fields->findOrMakeTab('Root.Settings');

            if ($tabset) {
                $this->owner->addCMSFieldsToTabset($tabset);
            }
        }
    }
    
    
    /**
     * Add the fields to "CMSFields" (if we are not using settings fields). 
     * 
     * @param FieldList
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName("RemoveFromShoppingFeed");
        $fields->removeByName("Condition");
        $fields->removeByName("Availability");
        $fields->removeByName("Brand");
        $fields->removeByName("MPN");
        $fields->removeByName("GTIN");
        $fields->removeByName('GoogleProductCategoryID');
        $fields->removeByName("ShoppingPrimaryImage");
        $fields->removeByName("ShoppingAdditionalImage");
        
        if (!$this->owner->hasCMSSettingsFields()) {
            $tabset = $fields->findOrMakeTab('Root.Settings');

            if ($tabset) {
                $this->owner->addCMSFieldsToTabset($tabset);
            }
        }
    }
    
    
    /**
     * Can we add this object to a shopping feed?
     * 
     * @return boolean
     */
    public function canIncludeInGoogleShoppingFeed()
    {
        $can = true;

        // If object does not link to the current website or absolute
        // link not set.
        if ($this->owner->hasMethod('AbsoluteLink')) {
            $hostHttp = parse_url(Director::protocolAndHost(), PHP_URL_HOST);
            $objHttp = parse_url($this->owner->AbsoluteLink(), PHP_URL_HOST);

            if ($objHttp != $hostHttp) {
                $can = false;
            }
        } else {
            $can = false;
        }
        
        // If no price, title or other requred fields
        if (!$this->owner->Title || !$this->owner->ShoppingFeedPrice || !$this->owner->Condition || !$this->owner->Availability || !$this->owner->Brand || !($this->owner->MPN || $this->owner->GTIN)) {
            $can = false;
        }
        
        // Can any user view this item
        if ($can) {
            $can = $this->owner->canView();
        }
        
        if ($can && $this->owner->RemoveFromShoppingFeed) {
            $can = false;
        }

        $this->owner->invokeWithExtensions('alterCanIncludeInGoogleShoppingFeed', $can);

        return $can;
    }
}
