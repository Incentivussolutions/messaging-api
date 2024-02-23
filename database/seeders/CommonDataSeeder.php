<?php

namespace Database\Seeders;

use App\Models\CommonData;
use Illuminate\Database\Seeder;

class CommonDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    protected $data = array(
        array(
            "type" => "TEMPLATE_LANGUAGE",
            "value" => 1,
            "description" => "English - GB",
            "reference" => "en_GB",
            "sample" => NULL
        ),
        array(
            "type" => "TEMPLATE_LANGUAGE",
            "value" => 2,
            "description" => "Irish",
            "reference" => "ga",
            "sample" => NULL
        ),
        array(
            "type" => "INPUT_TYPE",
            "value" => 1,
            "description" => "Text",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "INPUT_TYPE",
            "value" => 2,
            "description" => "Image",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "INPUT_TYPE",
            "value" => 3,
            "description" => "Video",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "INPUT_TYPE",
            "value" => 4,
            "description" => "File",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "INPUT_TYPE",
            "value" => 5,
            "description" => "Location",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "FIELD_TYPE",
            "value" => 1,
            "description" => "URL",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "FIELD_TYPE",
            "value" => 2,
            "description" => "Upload",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 1,
            "description" => "Firstname",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 2,
            "description" => "Lastname",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 3,
            "description" => "Email",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 4,
            "description" => "City",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 5,
            "description" => "County",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 6,
            "description" => "Country",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 7,
            "description" => "Additional_Field1",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 8,
            "description" => "Additional_Field2",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 9,
            "description" => "Additional_Field3",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 10,
            "description" => "Additional_Field4",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 11,
            "description" => "Additional_Field5",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 12,
            "description" => "Additional_Field6",
            "reference" => NULL,
            "sample" => NULL
        ),
        array(
            "type" => "PLACHOLDER",
            "value" => 13,
            "description" => "Additional_Field7",
            "reference" => NULL,
            "sample" => NULL
        ),
    );
    public function run()
    {
        foreach($this->data as $global_data) {
            CommonData::create($global_data);
        }
    }
}
