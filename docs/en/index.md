# Google Shooping Feed Module

Module that adds a google shopping feed to your Silverstripe site. This
modules uses a lot of code taken from the google sitemap module (as the
functionality is similar).

Using this module will allow you to create an xml shopping feed that can
be submitted to Google Shopping. 

## Required Params

You can add whatever objects you like to the feed, but they **MUST** provide
the following params:

* StockID (unique ID for the product)
* Title (name of the object)
* Description (further details about the object)
* AbsoluteLink (absolute URL for this product)
* Image (an image file associated with this product)
* Price (Price in a currency format)
* Condition (string)
* Availability (string)
* Brand (brand name string)
* MPN (manufacturers product number)
* Shipping - an SS_List of objects, including the following:
  * Country (2 character country code)
  * Service (Name of shipping)
  * Price

## Configuration

Most module configuration is done via the SilverStripe Config API.
Create a new config file `mysite/_config/googleshoppingfeed.yml` with the
following outline:

	---
	Name: customgoogleshoppingfeed
	After: googleshoppingfeed
	---
	GoogleShoppingFeed:
  		enabled: true
  		google_notification_enabled: false
  		use_show_in_search: true
  		use_disabled: false

### Including DataObjects

The module provides support for including DataObject subclasses as pages in the 
SiteTree such as comments, forum posts and other pages which are stored in your
database as DataObject subclasses.

To include a DataObject instance in the Sitemap it requires that your subclass 
defines two functions:

 * AbsoluteLink() function which returns the URL for this DataObject
 * canView() function which returns a boolean value.

The following is a barebones example of a DataObject called
'MyDataObject'. It  assumes that you have a controller called
'MyController' which has a show method to show the DataObject by its ID.

	<?php
	
	class MyDataObject extends DataObject {
		
		function canView($member = null) {
			return true;
		}
		
		function AbsoluteLink() {
			return Director::absoluteURL($this->Link());
		}
		
		function Link() {
			return 'MyController/show/'. $this->ID;
		}
	}


After those methods have been defined on your DataObject you now need to
tell this module that it should be listed in the sitemap.xml file. To do
that, include the following in your _config.php file.

	GoogleShoppingFeed::register_dataobject('MyDataObject');
    

