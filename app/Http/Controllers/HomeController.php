<?php

namespace App\Http\Controllers;

use App\Services\PhotoService;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    private $ps;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PhotoService $ps)
    {
        $this->ps = $ps;
        $this->middleware('auth', ['only'=>['home'] ]);
        
    }

    /** 
     * Show the Main Page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function mainpage()
    {
        return view('welcome');
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function home()
    {
        return view('home');
    }

    /**
     * Api to display photos to Registered users   
     *
     */
    public function show( Request $request, $start, $limits)
    {
        // find user token to get Id
        $photos = $this->ps->getAll( $request->user()->id , $start, $limits);
        
        return response()->json( ['Photos'=>  $photos], 200);
    }

    /**
     * Api to creat a photo by the user if the user favorite the photo on the 
     *
     */
    public function create(Request $request )
    { 
        return response()->json($this->ps->save( $request ), 200);
    } 

    /**
     * Api to delete /unfavorite a user photo
     *
     */
    public function delete(Request $request, $photoId )
    {   

        return response()->json( $this->ps->delete( $request->user()->id, $photoId  ) , 200);
    }  


    /**
     * Api to display statistics to User
     *
     */
    public function stats()
    {   

        return response()->json($this->ps->getStats(), 200);
    }          

}
