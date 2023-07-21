# Google Shooping Feed Module

Module that adds a google shopping feed to your Silverstripe site. This
modules uses a lot of code taken from the google sitemap module (as the
functionality is similar).

Using this module will allow you to create an xml shopping feed that can
be submitted to Google Shopping. 

## Required Params

You can add whatever objects you like to the feed, but they **MUST** provide
the following params:

* StockID (unique ID for the product) or ID
* Title (name of the object)
* Content (further details about the object)
* AbsoluteLink (absolute URL for this product)
* Image (an image file associated with this product)
* ShoppingFeedPrice (Price in a currency format)
* Shipping - an SS_List of objects, including the following:
  * Country (2 character country code) _optional_
  * Service (Name of shipping) _optional_
  * ShoppingFeedPrice

## Provided Params
In addition to the above, this module provides the following parameters
automatically (along with fields under the "Google Shopping Feed" header
in the CMS:

* Condition (string)
* Availability (string)
* Brand (brand name string)
* MPN (manufacturers product number)
  
## Example Product

If you have a simple product catalogue that you want to connect to the
shopping feed, then you would need to use a DataObject (or SiteTree)
extension. The following is a basic example of a Product object that
contains all the required properties and associations. In order to
handle shipping the Product object has a many_many association to a
seperate shipping object.

    class Product extends DataObject {

        private static $db = [
            "Title" => "Varchar(255)",
            "StockID" => "Varchar",
            "Price" => "Currency",
            "URLSegment" => "Varchar",
            "Content" => "HTMLText",
            "Weight" => "Decimal",
            "PackSize" => "Int",
            "Featured" => "Boolean"
        ];

        private static $has_one = [
            "Image" => "Image"
        ];
        
        private static $many_many = [
            "Shipping" => "Shipping"
        ];

        private static $casting = [
            'ShoppingFeedPrice' = 'Decimal
        ];
        
        public function Link($action = null) {
            return Controller::join_links(
                Director::baseURL(),
                $this->RelativeLink($action)
            );
        }

        public function AbsoluteLink($action = null) {
            if($this->hasMethod('alternateAbsoluteLink')) {
                return $this->alternateAbsoluteLink($action);
            } else {
                return Director::absoluteURL($this->Link($action));
            }
        }

        public function getShoppingFeedPrice()
        {
            return $this->Price;
        }

        /**
        * If using SilverCommerce postage, you
        * could generate a list of valid shipping
        * with something like this
        *
        * @return \SilverStripe\ORM\ArrayList
        */
        public function getShipping()
        {
            $country = substr($this->getLocale(), 3, 2);
            $region = Region::get()->filter('CountryCode', $country)->first();
            $return = ArrayList::create();

            $parcel = Parcel::create(
                substr($this->getLocale(), 3, 2),
                $region->Code
            );

            $parcel
                ->setValue($this->PriceAndTax)
                ->setWeight($this->Weight)
                ->setItems(1);

            /** @var \SilverCommerce\Postage\Helpers\PostageOption $postage */
            foreach ($parcel->getPostageOptions() as $postage) {
                $return->add(ArrayData::create([
                    'Country' => $country,
                    'Service' => $postage->getName(),
                    'ShoppingFeedPrice' => round($postage->getTotalPrice(), 2)
                ]));
            }

            return $return;   
        }
    }
    
    class Shipping extends DataObject {

        private static $db = array(
            "Title" => "Varchar(255)",
            "Price" => "Currency",
            "Location" => "Varchar(2)"
        );
        
        private static $belongs_many_many = array(
            "Products" => "Product"
        );

        public function getShoppingFeedPrice()
        {
            return $this->Price;
        }
        
    }
    
You will still need to enable the product DataObject using the config
below.
    
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
  		use_show_in_search: false
