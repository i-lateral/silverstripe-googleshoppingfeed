# Google Shopping Feed Changelog


## 1.0.0

Initial official release of module, including:

* Standard GA fields
* Auto detection of images for image and additional image link
* Creation of full Google Product Category table and mapping of products to categories.

## 1.0.1

* Fix issues with image URL's

## 2.0.0

* upgraded for silverstripe 4

## 2.0.1

* fixed url of google category file

## 2.0.2

* Only explicitly check for objects that implement The shopping feed extension
* Better PSR-* support

## 2.0.3

* Add RSS friendly fields to the feed (for feed readers)

## 2.1.0

* Switch to custom param for google shopping price
* Switch service name in template for shipping

## 2.1.1

* Add validation to shopping fields on save

## 2.1.2

* Add UPI exists field
* Improve validation inline with UPI data
* Add some translations

## 2.1.3

* Remove Brand from required fields config

# 2.1.4

* Ensure UPI is respected by canIncludeInShoppingFeed