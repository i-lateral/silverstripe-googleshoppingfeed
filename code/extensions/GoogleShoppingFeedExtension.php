<?php

class GoogleShoppingFeedExtension extends DataExtension
{
    
    /**
     * @var array
     */
    private static $db = array(
        "RemoveFromShoppingFeed" => "Boolean",
        "Condition" => 'Enum(array("new","refurbished","used"),"new")',
        "Availability" => 'Enum(array("in stock","out of stock","pre-order"),"in stock")',
        "Brand" => "Varchar",
        "MPN" =>  "Varchar(255)",
        "GTIN" => "Varchar(255)"
    );

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
                TextField::create("GTIN")
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
        if($this->hasCMSSettingsFields()) {
            $tabset = $fields->findOrMakeTab('Root.Settings');

            if ($tabset) {
                $this->addCMSFieldsToTabset($tabset);
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
        if (!$this->hasCMSSettingsFields()) {
            $tabset = $fields->findOrMakeTab('Root.Settings');

            if ($tabset) {
                $this->addCMSFieldsToTabset($tabset);
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
        
        // If no price or title.
        if (!$this->owner->Title || !$this->owner->Price || !$this->owner->Condition || !$this->owner->Availability || !$this->owner->Brand || !($this->owner->MPN || $this->owner->GTIN)) {
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
