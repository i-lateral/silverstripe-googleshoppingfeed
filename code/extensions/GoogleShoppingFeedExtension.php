<?php

class GoogleShoppingFeedExtension extends DataExtension {
    
	/**
	 * @var array
	 */
	private static $db = array(
        "Condition" => "Varchar",
        "Availability" => "Varchar",
        "Brand" => "Varchar",
        "MPN" => "Varchar"
	);
    
    
    /**
	 * @param FieldList
	 */
	public function updateSettingsFields(FieldList $fields) {
		$tabset = $fields->findOrMakeTab('Root.Settings');
		
		$tabset->push(new Tab(
            'GoogleShoppingFeed',
            _t('GoogleShoppingFeed.GoogleShoppingFeed', 'Google Shopping Feed'),
			new TextField("Condition"),
			new TextField("Availability"),
			new TextField("Brand"),
			new TextField("MPN")
		));
	}
    
    
    /**
	 * @param FieldList
	 */
	public function updateCMSFields(FieldList $fields) {
        if(!method_exists($this->owner, "getSettingsFields")) {
            $tabset = $fields->findOrMakeTab('Root.Settings');
            
            $tabset->push(new Tab(
                'GoogleShoppingFeed.GoogleShoppingFeed',
                _t('GoogleShoppingFeed.GoogleShoppingFeed', 'Google Shopping Feed'),
                new TextField("Condition"),
                new TextField("Availability"),
                new TextField("Brand"),
                new TextField("MPN")
            ));
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

		$this->owner->invokeWithExtensions('alterCanIncludeInGoogleShoppingFeed', $can);

		return $can;
	}
    
}
