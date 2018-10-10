<?php

namespace ilateral\SilverStripe\GoogleShoppingFeed\Model;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;


class GoogleProductCategory extends DataObject
{
    private static $table_name = "GoogleProductCetegory";
    
    private static $db = [
        "GoogleID" => "Int",
        "Title" => "Varchar(255)"
    ];

    /**
     * Build the initial list of Categories
     *
     * @return void
     */
    public function RequireDefaultRecords()
    {
        parent::requireDefaultRecords();

        if (!GoogleProductCategory::get()->exists()) {
            DB::alteration_message("Creating categories (this may take 5 - 10 mins)", 'created');

            $default_categories = $this->getGoogleCategories();
            $count = 0;

            foreach ($default_categories as $key => $value) {
                $new_cat = GoogleProductCategory::create([
                    "GoogleID" => $key,
                    "Title" => $value
                ]);

                $new_cat->write();
                $count++;
            }

            DB::alteration_message("Created {$count} Categories", 'created');
        }
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
        $file = __DIR__ . "../../thirdparty/google_product_taxonomy.txt";
        $fopen = fopen($file, 'r');
        $fread = fread($fopen, filesize($file));
        fclose($fopen);
        $result = [];

        foreach (explode("\n", $fread) as $string) {
            $exploded = explode(" - ", $string);
            if ($string && count($exploded) == 2) {
                $result[$exploded[0]] = $exploded[1];
            }
        }

        return $result;
    }

    public function canDelete($member = null)
    {
        return false;
    }
}