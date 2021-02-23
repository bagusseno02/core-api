<?php

return [
    'jwt_secret' => env('JWT_SECRET_KEY', 'defaultsecretkey'),
    'log_retention' => env('LOG_RETENTION', 5),
    'log_path' => env('LOG_PATH', storage_path("logs")),
    'log_file_name' => env('LOG_FILE_NAME', "debug.log"),
    'microservices_folder' => env('MICROSERVICES_FOLDER', 'App\Http\Curl\Services\\'),
    'internal_username' => ($internalUsername = env('INTERNAL_USERNAME', 'default_user')),
    'internal_password' => ($internalPassword = env('INTERNAL_PASSWORD', 'default_password')),
    'm2m_token' => env('M2M_TOKEN', base64_encode($internalUsername.':'.$internalPassword)),
    'language' => env('APP_LANG', 'en_US')
];