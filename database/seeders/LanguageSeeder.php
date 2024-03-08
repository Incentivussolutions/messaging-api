<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $data = array(
        array(
            "name" => "English - UK",
            "code" => "en_GB",
            "status" => 1
        ),
        array(
            "name" => "Irish",
            "code" => "IE",
            "status" => 1
        )
    );
    public function run(): void
    {
        foreach($this->data as $language) {
            Language::create($language);
        }
    }
}
