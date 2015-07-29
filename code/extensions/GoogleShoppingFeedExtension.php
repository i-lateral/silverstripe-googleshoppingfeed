<?php

class GoogleShoppingFeedExtension extends DataExtension {
    
	/**
	 * @var array
	 */
	private static $db = array(
        "RemoveFromShoppingFeed" => "Boolean"
	);
    
    
    /**
	 * @param FieldList
	 */
	public function updateSettingsFields(FieldList $fields) {
		$tabset = $fields->findOrMakeTab('Root.Settings');
		
		$tabset->push(new HeaderField(_t(
            'GoogleShoppingFeed.GoogleShoppingFeed',
            'Google Shopping Feed'
        )));
        
        $tabset->push(new CheckboxField("RemoveFromShoppingFeed"));
	}
    
    
    /**
	 * @param FieldList
	 */
	public function updateCMSFields(FieldList $fields) {
        if(!method_exists($this->owner, "getSettingsFields")) {
            $tabset = $fields->findOrMakeTab('Root.Settings');
            
            $tabset->push(new HeaderField(_t(
                'GoogleShoppingFeed.GoogleShoppingFeed',
                'Google Shopping Feed'
            )));
            
            $tabset->push(new CheckboxField("RemoveFromShoppingFeed"));
        }
	}
    
    
    /**
	 * @return boolean
	 */
	public function canIncludeInGoogleShoppingFeed() {
		$can = true;

		if($this->owner->hasMethod('AbsoluteLink')) {
			$hostHttp = parse_url(Director::protocolAndHost(), PHP_URL_HOST);
			$objHttp = parse_url($this->owner->AbsoluteLink(), PHP_URL_HOST);

			if($objHttp != $hostHttp) {
				$can = false;
			}
		}
		
		if($can) {
			$can = $this->owner->canView();
		}
        
        if($can && $this->owner->RemoveFromShoppingFeed) {
            $can = false;
        }

		$this->owner->invokeWithExtensions('alterCanIncludeInGoogleShoppingFeed', $can);

		return $can;
	}
    
}
