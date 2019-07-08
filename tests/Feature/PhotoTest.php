<?php



namespace Tests\Feature;

use App\User;
use App\Photo;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhotoTest extends TestCase
{

    use RefreshDatabase;  // clean Db at end of Test

    /**
     * Test Guess User can Access Main page /
     *
     * @return void
     */
    public function testGuessUserCanAccessMainPage()
    {
        $this->withoutExceptionHandling(); 

        $response = $this->get('/');

        $response->assertStatus(200);
    }


    /**
     * Guess User can not Access Protected page home
     *
     * @return void
     */
    public function testGuessUserCanNotAccessHomePage()
    {
        /*
         * Unauthorized Exception, comment to hide
         */ 
        // $this->withoutExceptionHandling(); 
        $response = $this->get('/home');

        /*
         * Response status code [302] is not an unauthorized status code.
         * Why ??  $response->assertUnauthorized();
         */

        $response->assertRedirect('/login');

        $response->assertStatus(302);
    }


    /**
     * Register User can Access Protected page home
     *
     * @return void
     */
    public function testRegisterUserCanAccessHomePage()
    {
        // dump(env('APP_ENV'), env('DB_CONNECTION'));

        $this->withoutExceptionHandling();
        $user = factory(User::class)->create();
        $response = $this->actingAs($user)->get('/home');
        $response->assertStatus(200);    
         
    }


    /**
     * Register User can access api/photos/{start}/{limits}
     * Register User can see a list of Photos 
     *
     * @return void
     */
    public function testRegisterCanSeeAListOFPhotos()
    {

        $this->withoutExceptionHandling();

        // create Fake user
        $user = factory(User::class)->create();

        // Fake user make a post with Api Bearer Authorization token
        $response = $this->actingAs($user, 'api')
                         ->get( 'api/photos/1/20');
            

        $this->assertAuthenticatedAs($user);

        // See that 20 records were deliver 
        $response->assertJsonCount( 20, 'Photos');

        // Fake user make a post with Api Bearer Authorization token
        $response = $this->actingAs($user, 'api')
                         ->get( 'api/photos/5/40');               
           
        // See that 40 records were deliver 
        $response->assertJsonCount( 40, 'Photos');
    }

     /**
     * Register User can access api/photo/create
     * Testing for Validation Inputs
     *
     * @return void
     */
    public function testRegisterUserValidateInputsRequires()
    {
        // $this->session();

        // $this->withoutExceptionHandling();

        // create Fake user
        $user = factory(User::class)->create();

        // Register users favorite a photo with invalid record 
        $invalidata = [ 'photo_id'=>'', 'title'=> '','thumbnailUrl'=> ''];
        $attributes = factory(Photo::class)->raw( $invalidata );

        // Note of Integrity constraint violation: Photo should have user Id
        $attributes['user_id'] = $user->id;

        // Fake user make a post with Api Bearer Authorization token
        $response = $this->actingAs($user, 'api')
                         ->post( 'api/photo/create', $attributes );

        // shows that validation works, this throws exceptions errors
        $response->assertSessionHasErrors(['photo_id','title','thumbnailUrl']);

                          
    }   


    /**
     * Register User can access api/photo/create
     * Register User can favorite and unfavorite a photo which is recorded 
     * in the database photos create/delete
     *
     * @return void
     */
    public function testRegisterCanFavoriteAPhotoCreateAndDeleteARecord()
    {

        $this->withoutExceptionHandling();

        // create Fake user
        $user = factory(User::class)->create();
       
        // Register users favorite a photo creates a record 
        $attributes = factory(Photo::class)->raw(); 

        // Note of Integrity constraint violation: Photo should have user Id
        $attributes['user_id'] = $user->id;

        // Fake user make a post with Api Bearer Authorization token
        $response = $this->actingAs($user, 'api')
                         ->post( 'api/photo/create', $attributes );
            

        $this->assertAuthenticatedAs($user);


        // See that the data was saved in the database                 
        $this->assertDatabaseHas('photos', $attributes); 


        // Fake user make a post with Api Bearer Authorization token
        $response = $this->actingAs($user, 'api')
                         ->delete( 'api/photo/delete/'.$attributes['photo_id'] );
            

        // See that the data was removed in the database                 
        $this->assertDatabaseMissing('photos', $attributes);   
         
    }


    /**
     * Guess Users can Access stats api page,
     * Guess Users can see a list of photos by favorite during the week
     * Guess Users can a list of Registerd User that have Most favourited photos
     * Only  during the  weekends
     * Test for Algorithm 
     *
     * @return void
     */
    public function testGuessUserCanAccessStatsApi()
    {

        // Access Api
        $this->withoutExceptionHandling();

        $this->RegisterUsersFavorites();

       
        $now = Carbon::now();

        //Guess Users can see a list of User Who has the most favorite during the week 
        $wkend = $now->endOfWeek();
        Carbon::setTestNow($wkend);

        $response = $this->get('api/stats');
        $response->assertStatus(200); 
        // See that 3 user records were deliver 
        $response->assertJsonCount( 3, 'Users_Stats');

        // See the user who favorite the most expected 3
        $uwithmf = json_decode($response->content(), TRUE);
        $this->assertEquals(4, $uwithmf['Users_Stats'][0]['favorited']);



         //Guess Users can see a list of photos by favorite during the week  
        $wkstart = $now->startOfWeek();
        Carbon::setTestNow($wkstart);

        $response = $this->get('api/stats');
        $response->assertStatus(200); 
         // See that 3 records were deliver 
        $response->assertJsonCount( 4, 'Photos_Stats');

        // Most favorite photo is photo 1 with 3 votes
        $pwithmf = json_decode($response->content(), TRUE);
        $this->assertEquals(3, $pwithmf['Photos_Stats'][0]['favorite']);

      

        // Good Idea always to reset to normal date
        Carbon::setTestNow();
    
         
    }

    private function RegisterUsersFavorites()
    {
        /* 
         * simulate data into database with different 
         * 3 users favorite  6 photos
         * user1 choses:( 1, 2, 3, 4 ), 
         * user2 choses:( 1, 4,   ),
         * user3 choses:( 1, 4,   ),
         * image 1 url = 3 votes
         * image 4 url = 2 votes
         * image 3 url = 1 vote
         */

        // need Authenticated fake user 3 users
        $user1 = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $user3 = factory(User::class)->create();

        // Fake photo the users can favorite / unfavorite
 
        $attributes1  = factory(Photo::class)->raw(); 
        $attributes2  = factory(Photo::class)->raw(); 
        $attributes3  = factory(Photo::class)->raw(); 
        $attributes4  = factory(Photo::class)->raw();
        
        
        /**
         *  clean up the attributes to match fake user_id
         *  2. Creat test photo records in a database
         */
        $attributes1['user_id'] = $user1->id;
        $response1 = $this->actingAs($user1, 'api')
                 ->post('api/photo/create', $attributes1 );

        $attributes2['user_id'] = $user1->id;
        $response2 = $this->actingAs($user1, 'api')
                 ->post('api/photo/create', $attributes2 );

        $attributes3['user_id'] = $user1->id;
        $response3 = $this->actingAs($user1, 'api')
                 ->post('api/photo/create', $attributes3 );

        $attributes4['user_id'] = $user1->id;
        $response4 = $this->actingAs($user1, 'api')
                 ->post('api/photo/create', $attributes4 );        

        $attributes1['user_id'] = $user2->id;
        $response5 = $this->actingAs($user2, 'api')
                 ->post('api/photo/create', $attributes1 );

        $attributes4['user_id'] = $user2->id;
        $response6 = $this->actingAs($user2, 'api')
                 ->post('api/photo/create', $attributes4 );

        $attributes1['user_id'] = $user3->id;
        $response7 = $this->actingAs($user3, 'api')
                 ->post('api/photo/create', $attributes1 );

        $attributes4['user_id'] = $user3->id;
        $response8 = $this->actingAs($user3, 'api')
                 ->post('api/photo/create', $attributes4 );

    }

    /**
     * Guess Users can not Access Protected Api api/photo/create
     * @return void
     */
    public function testGuessUserCanNotAccessCreateApi(){

        $response = $this->post('api/photo/create', []);
        $response->assertStatus(302); 

    }

    /**
     * Guess Users can not Access Protected Api api/photo/delete/1
     * @return void
     */
    public function testGuessUserCanNotAccessDeleteApi(){

        $response = $this->delete('api/photo/delete/1');
        $response->assertStatus(302); 

    }

    /**
     * Guess Users can not Access Protected Api api/photos/1/20
     * @return void
     */
    public function testGuessUserCanNotAccessPhotosApi(){
        
        $response = $this->get( 'api/photos/1/20');
        $response->assertStatus(302); 

    }            

    /**
     * Register Users has favorited a Photo, this should be displayed
     * on the Photo Galary page this test is dependent on external api
     * @return void
     */
    public function testRegistersUserCanSeeTheirFavoritedPhotosAsUnLike(){

        $this->withoutExceptionHandling();

        $user1 = factory(User::class)->create();

        $attributes1  = factory(Photo::class)->raw();;

        $attributes1['user_id'] = $user1->id;
        // case for the first 20 photos
        $attributes1['photo_id'] = 6;
        $response1 = $this->actingAs($user1, 'api')
                 ->post('api/photo/create', $attributes1 );

        // See that the data was saved in the database                 
        $this->assertDatabaseHas('photos', $attributes1); 

        
        $response = $this->actingAs($user1, 'api')
                 ->get('api/photos/1/20');
        
         $response
            ->assertStatus(200);
        $p = json_decode($response->content(), TRUE);
        // var_dump($p);
        // the 6th Item of the Photos array
        $this->assertEquals(6, $p['Photos'][5]['id']);
        $this->assertEquals('UnLike', $p['Photos'][5]['favorite']);
		$this->assertEquals(true, $p['Photos'][5]['btnPrimary']);
		$this->assertEquals(false, $p['Photos'][5]['btnSecondary']);
        $this->assertEquals('Like', $p['Photos'][6]['favorite']);
        $this->assertEquals(false, $p['Photos'][6]['btnPrimary']);
        $this->assertEquals(true, $p['Photos'][6]['btnSecondary']);

    }

    /**
     * Register Users with no Favorite Photos, can see all Photos 
     * He or she sees should be Like button
     * @return void
     */
    public function testRegistersUserCanSeePhotosLikesOnly(){

        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $userdata =[
            'name' => $user->name,
            'email' => $user->email,
            'api_token' => $user->api_token,

        ];
        // See that the data was saved in the database                 
        $this->assertDatabaseHas('users', $userdata); 

        
        $response = $this->actingAs($user, 'api')
                 ->get('api/photos/1/20');
        
         $response
            ->assertStatus(200);
        $p = json_decode($response->content(), TRUE);
        // the 6th Item of the Photos array
        $this->assertEquals('Like', $p['Photos'][15]['favorite']);
        $this->assertEquals('Like', $p['Photos'][8]['favorite']);
         

    }



}
