<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Credentials (Web Dashboard)
    |--------------------------------------------------------------------------
    | Dipakai untuk login halaman admin dan seeding user admin.
    | Ganti lewat env pada produksi:
    |   ADMIN_EMAIL=admin@topspeak.app
    |   ADMIN_PASSWORD=GantiDenganPasswordKuat!
    */

    'email' => env('ADMIN_EMAIL', 'admin@topspeak.app'),

    'password' => env('ADMIN_PASSWORD', 'Admin123!'),

];