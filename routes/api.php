<?php
/**
 * api.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */

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

//Route::get(
//    '/user', function (Request $request) {
//    return 'hello';
//    return $request->user();
//}
//);


Route::group(
    ['middleware' => 'auth:api', 'namespace' => 'FireflyIII\Api\V1\Controllers', 'prefix' => 'bills', 'as' => 'api.v1.bills.'], function () {

    Route::get('', ['uses' => 'BillController@index', 'as' => 'index']);
    Route::get('{bill}', ['uses' => 'BillController@show', 'as' => 'show']);
}
);
//Route::get(
//    '/bills', function (Request $request) {
//    //return 'hello';
//    return $request->user()->bills;
//}
//);