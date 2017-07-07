# Gousto Test API

## Overview
This is a RESTful API coded in PHP 7 using the Laravel 5.4 framework. It does not include any front end code. The API methods are all found in **app/Http/Controllers/RecipeController.php** and the routes are defined in **routes/web.php**.

## How to use
There are five routes to access the functions as follows:

### /recipe/get

#### Parameters (GET)

**id** The ID of the recipe whose data is to be retrieved.

#### Returns

A JSON-encoded object containing the recipe data.

### /recipe/cuisine

#### Parameters (GET)

**cuisine** The cuisine type for which to get recipes.

**page** The page within the data set to retrieve. (For testing purposes, the page size is set to just 2.)

#### Returns

A JSON-encoded object containing the recipes matching the specified cuisine.

### /recipe/rate

#### Parameters (POST)

**id** The ID of the recipe being rated.

**rating** The rating from 1 to 5.

#### Returns

Outputs "true" if operation successful.

### /recipe/update

#### Parameters (POST)

Any or all of the properties of each recipe may be set. Must specify a valid, existing "id" value or an exception will be thrown. Any properties not already present on the recipe will be ignored (e.g. trying to set a property "salt_grams" will be ignored as it isn't already set.)

#### Returns

Outputs "true" if operation successful.

### /recipe/new

#### Parameters (POST)

Any properties to be set on the new recipe. ID is ignored and is generated automatically by getting the maximum current ID and adding one.

#### Returns

Outputs "true" if operation successful.

## Additional notes
### Framework
The framework is Laravel 5.4, which I used mainly because of my familiarity with it and because of its comprehensive data structure handling libraries.

### How this solution fulfils the stated requirements
This API is completely platform-agnostic since it processes data submitted via HTTP GET and POST requests and outputs standard JSON-formatted data, therefore it is suitable for use via any platform imaginable, whether it's a website, desktop application, mobile app or other back-end server-based service.
