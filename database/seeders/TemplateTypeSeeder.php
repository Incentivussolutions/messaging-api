<?php

namespace Database\Seeders;

use App\Models\TemplateType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TemplateTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    protected $data = array(
        array(
            "name" => "Approved"
        ),
        array(
            "name" => "Custom"
        )
    );
    public function run(): void
    {
        foreach($this->data as $template_type) {
            TemplateType::create($template_type);
        }
    }
}
