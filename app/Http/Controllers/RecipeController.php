<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class RecipeController extends Controller
{
    /** Number of rows in each page on multiple-record data sets. */
    const PAGE_LENGTH = 2;

    /** @var Collection The data from the CSV file stored as a collection.  */
    protected $csvData;

    /** @var array An associative array of column headers. */
    protected $columnHeaders = [];

    /**
     * RecipeController constructor.
     */
    public function __construct()
    {
        //  Initialize csvData collection.
        $this->csvData = app(Collection::class);

        //  Read CSV file into memory.
        $csvFileHandle = fopen(storage_path('app/recipe-data.csv'), 'r');

        while ($thisLine = fgetcsv($csvFileHandle)) {
            //  First line contain column headers.
            if (!$this->columnHeaders) {
                $this->columnHeaders = $thisLine;
                continue;
            }

            //  Subsequent lines contain data.
            $this->csvData->add(array_combine($this->columnHeaders, $thisLine));
        }
    }

    /**
     * Returns a JSON-encoded object containing the specified recipe data.
     *
     * @param Request $request The Request object containing the submitted GET data.
     * @return void
     */
    public function getRecipe(Request $request)
    {
        //  Check ID was supplied.
        $id = $request->input('id');
        if (!$id) {
            throw new InvalidArgumentException('"id" GET parameter missing.');
        }

        //  Validate recipe ID.
        if (!$recipe = $this->csvData->where('id', '=', $id)->first()) {
            abort(404, 'The specified recipe could not be found.');
        }

        //  Data found -- output encoded data.
        echo json_encode($recipe);
    }

    /**
     * Get all recipes for the specified cuisine and returns as JSON-encoded object.
     *
     * @param Request $request The Request object containing the submitted GET data.
     * @return void
     */
    public function getRecipesByCuisine(Request $request)
    {
        //  Initialize variables from GET data. (Default to page 1 if not supplied.)
        $cuisine = $request->input('cuisine');
        $page = $request->input('page', 1);

        //  Check 'cuisine' variable was supplied.
        if (!$cuisine) {
            throw new InvalidArgumentException('"id" GET parameter missing.');
        }

        //  Get matching recipes. If none found, redirect to 404 page.
        if (!$recipes = $this->csvData->where('recipe_cuisine', '=', $cuisine)->forPage($page, static::PAGE_LENGTH)->all()) {
            abort(404, 'No recipes found for the specified cuisine type.');
        }

        //  Data found -- output encoded data.
        echo json_encode($recipes);
    }

    /**
     * Rate a recipe with a rating from 1 to 5. Echo "true" to the output if successful
     * or "false" if not.
     *
     * @return void
     */
    public function rateRecipe(Request $request)
    {
        //  Check POST data contains correct parameters.
        if (!$request->has('id') || !$request->has('rating')) {
            throw new InvalidArgumentException('POST data must contain "recipe_id" and "rating" parameters.');
        }

        //  Get required values of recipe ID and rating from POST data.
        $id = $request->input('id');
        $rating = $request->input('rating');

        //  Validate recipe ID.
        if (!$recipe = $this->csvData->where('id', '=', $id)->pop()) {
            abort(404, 'The specified recipe could not be found.');
        }

        //  Validate rating.
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
            throw new InvalidArgumentException('Rating must be a number between 1 and 5');
        }

        //  Get selected recipe and its index in the collection (so it can be replaced easily).
        $index = $this->csvData->where('id', '=', $id)->keys()->first();

        //  Set rating and replace existing recipe entry.
        $recipe['rating'] = $rating;
        $this->csvData[$index] = $recipe;

        //  Output status. Note that this would be derived from the results of database read/write operations.
        echo "true";
    }

    /**
     * Update a specified recipe with the supplied data. Note that it is assumed that the POST data
     * holds the ID of the recipe to be updated and is not subject to change after its initial creation.
     *
     * @param Request $request The received POST data containing the data with which to update the specified recipe.
     * @return void
     */
    public function updateRecipe(Request $request)
    {
        if (!$request->has('id')) {
            throw new InvalidArgumentException('POST data must contain at least the recipe ID.');
        }

        //  Get the ID from the Request object.
        $id = $request->input('id');

        //  Validate recipe ID.
        if (!$recipe = $this->csvData->where('id', '=', $id)->pop()) {
            abort(404, 'The specified recipe could not be found.');
        }

        //  Get selected recipe and its index in the collection (so it can be replaced easily).
        $index = $this->csvData->where('id', '=', $id)->keys()->first();

        /**
         * @var string $key The key of the POST variable being processed
         * @var mixed $val The value to be entered into the data element for the above key.
         */
        foreach ($request->except(['id', '_token']) as $key => $val) {
            //  Update process so we should check key exists.
            if (array_key_exists($key, $recipe)) {
                $recipe[$key] = $val;
            }
        }

        //  Replace the old item with the updated one.
        $this->csvData[$index] = $recipe;

        echo "true";
    }

    /**
     * Create a new recipe with the supplied data. Note that the ID field is assumed to be auto-incrementing and
     * NOT supplied in the POST data -- if it is supplied, it is ignored in favour of a value calculated from the
     * IDs in the current data collection.
     *
     * @param Request $request The received POST data containing the data for the new recipe.
     * @return void
     */
    public function storeNewRecipe(Request $request)
    {
        //  Get next ID by adding one to the current max value of existing IDs.
        $newId = $this->csvData->max('id');

        //  Initialize new recipe associative array.
        $newRecipe = ['id' => $newId];

        /**
         * @var string $key The key of the POST variable being processed
         * @var mixed $val The value to be entered into the data element for the above key.
         */
        foreach ($request->except(['id', '_token']) as $key => $val) {
            $newRecipe[$key] = $val;
        }

        //  Add recipe to array.
        $this->csvData->add($newRecipe);

        echo "true";
    }
}
