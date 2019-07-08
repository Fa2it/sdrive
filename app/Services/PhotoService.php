<?php

namespace  App\Services;

use App\Photo;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PhotoService{


	/*
	 * Fetch photos from jsonplaceholder compare with user photos on 
	 * database if the same, add favorite row and return results as arry
	 * The user will be able to see photo he/she favorited before if there are any
	 *
	 */
	public function getAll(int $user_id, int $start = 0, int $limits=20 ){
		// use guzzle to get external image from api 
		// $limit set [20,40,50]
		// $start set [ [0,1..250], [0,1..125], [0,1...100] ]
		// must think about large data set using yield but user can choose
		// max  5000 rows
			$start  = ( $start <= 0 ) ? 1 : $start ; 
			$start = ( $start - 1 ) * $limits; 
			$pr = [];

			$client = new Client();
			$url = 'http://jsonplaceholder.typicode.com/';
			$url .= 'photos?_start='.$start.'&_limit='.$limits;
			$response = $client->request('GET', $url);
			if( $response->getStatusCode() == 200 ){			
				$apiPhotos =  json_decode( $response->getBody()->getContents(), true);
				$pr = $this->compareApiUserPhotos( $apiPhotos, $user_id, $start );
			}

			return $pr;

	}

	/*
	 * Helper function for getAll
	 */
	private function compareApiUserPhotos(array $apiPhotos, int $user_id , int $start)
	{
		$userPhotos = $this->getUserAll( $user_id, $start );

		if( $userPhotos->count() ){
			foreach ( $apiPhotos as $key=>$apiPhoto) {
						$apiPhoto['favorite'] = 'Like';
						$apiPhoto['btnPrimary'] = false;
						$apiPhoto['btnSecondary'] = true;
						foreach ( $userPhotos as $userPhoto) {		
						if( $userPhoto->photo_id == $apiPhoto['id'] ){
							$apiPhoto['favorite'] = 'UnLike';
							$apiPhoto['btnPrimary'] = true;
							$apiPhoto['btnSecondary'] = false;
						}
						$apiPhotos[$key] = $apiPhoto;
					}
			}
		} else {
			// user doesn't have footos map all as Like
			foreach ( $apiPhotos as $key=>$apiPhoto) {
				$apiPhoto['favorite'] = 'Like';
				$apiPhoto['btnPrimary'] = false;
				$apiPhoto['btnSecondary'] = true;				
				$apiPhotos[$key] = $apiPhoto;
			}			
		}
		return $apiPhotos;
	}







	/*
	 * Helper function for compareApiUserPhotos
	 */

	private function getUserAll(int $user_id, int $start){
		// get user photos or Favorites from database
		$end = ( $start + 20 );
		return Photo::where('user_id', $user_id )
			   ->whereBetween('photo_id', [$start, $end ])
           	   ->get();

	}

	/*
	 * Save a recored, if user favorite a Photo save it
	 *
	 */
	public function save(Request $request ){
		    // validate data 
			$validatedData = $this->validateInputs( $request );
	        // persist it 
            Photo::create( $validatedData );
	        // return response
	      
	}

	/*
	 * Helper function for save method, making sure attributes are validated
	 */

	private function validateInputs(Request $request){

		return  $request->validate([
            // 'title' => 'required|unique:posts|max:255',
            'user_id'=> 'required', 
		    'photo_id'=> 'required',
		    'title'=> 'required',
		    'thumbnailUrl'=> 'required',
        ]);
	}

	public function delete(int $user_id, int $photo_id ){

		return Photo::where([
							  ['user_id', '=', $user_id],
	                          ['photo_id', '=', $photo_id ],
	                      ])
           		->delete();
	}


	public function getStats(){
		// logic for weekend or week data
		
		$now = Carbon::now();
		if( $now->isWeekend() ){
			$startOfWeek = $now->startOfWeek()->format('Y-m-d H:i');
			$endOfWeek = $now->endOfWeek()->format('Y-m-d H:i');
			$users = DB::table('photos as p')
				->join('users as u', 'u.id', '=', 'p.user_id')
                 ->select(DB::raw('u.name as name, count(p.user_id) as favorited'))
                 ->whereBetween('p.created_at', [$startOfWeek, $endOfWeek])
                 ->groupBy('p.user_id')
                 ->orderByRaw('favorited DESC')
                 ->limit(10)
                 ->get();
			return ['Users_Stats'=> $users];
			

		 } 
		 

		$photos = DB::table('photos as p')
                 ->select(DB::raw('thumbnailUrl, photo_id , title, count(p.photo_id) as favorite'))
                 ->groupBy('p.photo_id')
                 ->orderBy('favorite', 'DESC')
                 ->limit(5)
                 ->get();
		return ['Photos_Stats'=>$photos];
	
	}



}