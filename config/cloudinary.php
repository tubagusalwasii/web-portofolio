<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cloudinary Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Cloudinary settings. Cloudinary is a cloud
    | service that offers a solution to a web application's entire image
    | management pipeline.
    |
    */

    'cloud_url' => env('CLOUDINARY_URL'),

    'cloud' => [
        'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
        'api_key' => env('CLOUDINARY_API_KEY'),
        'api_secret' => env('CLOUDINARY_API_SECRET'),
    ],

    'upload' => [
        'storage_path' => env('CLOUDINARY_STORAGE_PATH', 'storage'),
    ],

];
