# sdrive
Life Testing Tips:
	
	Run : composer install 
	
	Make a copy of .env  from .env.example
		cp .env.example to .env
	
	Configuration
		1. encryption Key:  php artisan key:generate

			Before using Laravel's encrypter, you must set a key option in your config/app.php configuration file. 
			You should use the php artisan key:generate command to generate this key since this Artisan command will use 
			PHP's secure random bytes generator to build your key. If this value is not properly set, 
			all values encrypted by Laravel will be insecure.
			https://laravel.com/docs/5.8/encryption
			
		2. Database: set up credentials in .env file  ( DB_DATABASE =?, DB_USERNAME=?, DB_PASSWORD=? ) 
			make sure your have created your DB_DATABASE

		3. Run Migrations : php artisan migrate
		
		4. Load Test Data ;  php artisan db:seed
		   ***  Don't Worry about : Integrity constraint violation: 1062 Duplicate entry '6-39' for key 'user_photo'
	
		5. Run sever : php artisan serve 

		6. Register and play around
