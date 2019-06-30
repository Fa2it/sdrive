@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Photo Gallery, User Can Favorite/Unfavorite Photos</div>
        <div class="card-body">
        	<div class="mb-2 mt-2">
        		
        	</div>
            <photo-gallery usertoken="{{Auth::user()->api_token}}" 
            			uuid="{{Auth::user()->id}}" ></photo-gallery>
        </div>
    </div>    
</div>

@endsection
