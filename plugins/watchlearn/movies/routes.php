<?php 

use Watchlearn\Movies\Models\Movie;
use Watchlearn\Movies\Models\Actor;
use Watchlearn\Movies\Models\Genre;

Route::get('seed-actors', function () {
    
    $faker = Faker\Factory::create();
    for($i = 0; $i < 100; $i++){
        Actor::create([
            'name' => $faker->firstName,
            'lastname' => $faker->lastName
        ]);
    }

    return "Actors created!";

});

Route::get('/populate-movies', function(){
    
    $faker = Faker\Factory::create();
    
    $movies = Movie::all();
    

    foreach ($movies as $movie) {
        $genres = Genre::all()->random(3);
        
        $movie->genres = $genres;
        
        $movie->created_at = $faker->date($format = 'Y-m-d H:i:s', $max = 'now');
        $movie->published = $faker->boolean($chanceOfGettingTrue = 50);
        $movie->save();
    }

    return $movies;

});

Route::get('sitemap.xml', function(){
    $movies = Movie::all();
    $genres = Genre::all();

    return Response::view('watchlearn.movies::sitemap', ['movies' => $movies, 'genres' => $genres])->header('Content-Type', 'text/xml');

});
