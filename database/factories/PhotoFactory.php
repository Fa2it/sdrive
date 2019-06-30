<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Photo;
use Faker\Generator as Faker;

$factory->define(Photo::class, function (Faker $faker) {
    return [
			'user_id' => $faker->randomDigitNotNull, 
		    'photo_id'=> $faker->numberBetween(1, 50), 
		    'title'   => $faker->text(60),  
		    'thumbnailUrl' => $faker->imageUrl( 100, 100),
    ];
});
