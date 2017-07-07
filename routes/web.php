<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test/test', 'TestController@test');

//  Get single recipe and recipes by cuisine.
Route::get('/recipe/get', 'RecipeController@getRecipe');
Route::get('/recipe/cuisine', 'RecipeController@getRecipesByCuisine');

//  Rate, update and create.
Route::post('/recipe/rate', 'RecipeController@rateRecipe');
Route::post('/recipe/update', 'RecipeController@updateRecipe');
Route::post('/recipe/new', 'RecipeController@storeNewRecipe');
