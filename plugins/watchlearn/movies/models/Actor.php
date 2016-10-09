<?php namespace Watchlearn\Movies\Models;

use Model;

/**
 * Model
 */
class Actor extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'watchlearn_movies_actors';


    public $belongsToMany =[
        'movies' =>[
            'Watchlearn\Movies\Models\Movie',
            'table' => 'watchlearn_movies_actors_movies',
            'order' => 'name'
        ]
    ];

    public function getFullNameAttribute(){
        return $this->name . " " . $this->lastname;
    }
}