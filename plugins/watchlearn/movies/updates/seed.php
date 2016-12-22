<?php namespace Watchlearn\Movies\Updates;

use Watchlearn\Movies\Models\Movie;
use October\Rain\Database\Updates\Seeder;
use Faker;

class SeedAllTables extends Seeder
{

    public function run(){
        $faker = Faker\Factory::create();
        for($i = 0; $i < 100; $i++){
            $name = $faker->sentence($nbWords = 3, $variableNbWords = true);

            Movie::create([
                'name' => $name,
                'slug' => str_slug($name, '-'),
                'description' => $faker->paragraph($nbSentences = 3, $variableNbSentences = true),
                'year' => $faker->year($max = 'now')
            ]);
        }
    }
} 
