<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Extensions;

use LogicException;
use SilverStripe\Assets\Image;
use SilverStripe\ORM\ArrayList;
use SilverStripe\View\ArrayData;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\Director;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use TractorCow\AutoComplete\AutoCompleteField;
use ilateral\SilverStripe\GoogleShoppingFeed\Model\GoogleProductCategory;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\ORM\ValidationResult;

class Extension extends DataExtension
{
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

    private static $required_fields = [
        'ShoppingFeedPrice',
        'Title',
        'Condition',
        'Availability',
        'Brand'
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
                ReadonlyField::create('ShoppingFeedPrice'),
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

    public function validate(ValidationResult $result)
    {
        $owner = $this->getOwner();
        $required = Config::inst()->get(static::class, 'required_fields');
        $valid = true;

        if (((bool)$owner->RemoveFromShoppingFeed === true)) {
            return;
        }

        // If any required field is invalid
        foreach ($required as $field) {
            if (empty($owner->{$field})) {
                $valid = false;
                $result->addFieldError(
                    $field,
                    $field . ' is required for shopping feed'
                );
            }
        }

        // Either MPN or GTIN are required
        if (empty($owner->MPN) && empty($owner->GTIN)) {
            $valid = false;
            $result->addFieldError(
                'MPN',
                'MPN OR GTIN is required for shopping feed'
            );
            $result->addFieldError(
                'GTIN',
                'MPN OR GTIN is required for shopping feed'
            );
        }

        if ($valid === false) {
            $result->addMessage(
                'Fields required for shopping feed are missing',
                ValidationResult::TYPE_ERROR
            );
        }
    }

    /**
     * Can we add this object to a shopping feed?
     * 
     * @return boolean
     */
    public function canIncludeInGoogleShoppingFeed()
    {
        $owner = $this->getOwner();
        $required = Config::inst()->get(static::class, 'required_fields');
        $can = true;

        try {
            // Do we manually remove from object?
            if ((bool)$owner->RemoveFromShoppingFeed === true) {
                throw new LogicException("Item removed from feed");
            }

            // If object does not have an absolute link, it cannot
            // be shown
            if ($owner->hasMethod('AbsoluteLink') === false) {
                throw new LogicException("No absolute link set");
            }

            // Ensure the object link is to this current site
            $hostHttp = parse_url(Director::protocolAndHost(), PHP_URL_HOST);
            $objHttp = parse_url($owner->AbsoluteLink(), PHP_URL_HOST);

            if ($objHttp != $hostHttp) {
                throw new LogicException("Invalid host");
            }

            // If any required field is invalid
            foreach ($required as $field) {
                if (empty($owner->{$field})) {
                    throw new LogicException("Object must implement {$field}");
                }
            }

            if (empty($this->owner->MPN) && empty($this->owner->GTIN)) {
                throw new LogicException("Object must have an MPN OR a GTIN");
            }

            // Can any user view this item
            if ($can) {
                $can = $owner->canView();
            }

            $owner->invokeWithExtensions('alterCanIncludeInGoogleShoppingFeed', $can);

        } catch (LogicException $e) {
            $can = false;
            $owner->invokeWithExtensions('alterCanIncludeInGoogleShoppingFeed', $can);
            return $can;
        }

        return $can;
    }
}
