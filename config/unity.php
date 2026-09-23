<?php

return [
    'play_store_url' => env('PLAY_STORE_URL'),
    'app_store_url' => env('APP_STORE_URL'),

    // Bump this whenever legal page content actually changes.
    'legal_updated_at' => '26 July 2026',

    'contact_phone' => env('UNITY_CONTACT_PHONE', '+91 73979 53636'),
    'whatsapp_number' => env('UNITY_WHATSAPP_NUMBER', '917397953636'),

    // Public, client-side Firebase Web SDK config for website phone/OTP login.
    // These identifiers are not secrets (Firebase's own docs treat them as
    // safe to ship in client JS); access is governed by Firebase security
    // rules and the server-side ID token verification in FirebaseAuthService.
    'firebase_web' => [
        'api_key' => env('FIREBASE_WEB_API_KEY'),
        'auth_domain' => env('FIREBASE_WEB_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_WEB_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_WEB_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_WEB_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_WEB_APP_ID'),
    ],
];
