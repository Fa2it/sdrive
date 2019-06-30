<?php


use App\User;
use App\Photo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $users = factory(User::class, 10)
           ->create()
           ->each(function ($user) {
           	    $rn = mt_rand(1,5);
           	    for( $i = 0; $i < $rn; $i++)
                	factory(Photo::class)->create([
                		'user_id' => $user->id
                	]);
            });
    }
}
