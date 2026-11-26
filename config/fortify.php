<?php

use Laravel\Fortify\Features;

return [

    /*
    | Authentication guard used by Fortify. Must be a StatefulGuard (session).
    */
    'guard' => 'web',

    /*
    | Password broker (only used if password reset is enabled below).
    */
    'passwords' => 'users',

    /*
    | The credential used to sign in, and the user column it maps to.
    */
    'username' => 'username',

    // Only used by password reset, which is not enabled (users have no email column).
    'email' => 'email',
    'lowercase_usernames' => true,

    /*
    | Where users go after signing in.
    */
    'home' => '/',

    /*
    | Route prefix / domain for Fortify's routes (/login, /logout).
    */
    'prefix' => '',
    'domain' => null,

    /*
    | Middleware applied to Fortify's routes.
    */
    'middleware' => ['web'],

    /*
    | Named rate limiters. "login" is defined in App\Providers\FortifyServiceProvider.
    */
    'limiters' => [
        'login' => 'login',
    ],

    /*
    | Fortify serves the login view (see FortifyServiceProvider::loginView).
    */
    'views' => true,

    /*
    | Only plain sign in / sign out is enabled. This POS has no public sign-up:
    | staff accounts are created by the owner. Add Features::resetPasswords()
    | here later (needs mail configured) to enable "forgot password".
    */
    'features' => [
        // Features::registration(),
        // Features::resetPasswords(),
        // Features::emailVerification(),
    ],

];
