<?php

use Illuminate\Http\Request;

/*
  |--------------------------------------------------------------------------
  | API Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register API routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | is assigned the "api" middleware group. Enjoy building your API!
  |
 */

Route::middleware('auth:api', 'scopes:profile')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api', 'scopes:user_groups')->get('/user/{id}/groups', function (Request $request) {
    $request->validate([
        'recursive' => 'required'
    ]);

    return $request->user()->groups();
});

Route::middleware('auth:api', 'scopes:user_groups')->get('/user/{id}/inGroup', function (Request $request) {
    $request->validate(['group' => 'required']);

    return $request->user()->inGroup();
});