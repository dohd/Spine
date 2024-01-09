<?php

/**
 * Client Vendor
 *
 */
Route::group(['namespace' => 'client_vendor'], function () {
    Route::resource('client_vendors', 'ClientVendorsController');
    //For Datatable
    Route::post('client_vendors/get', 'ClientVendorsTableController')->name('client_vendors.get');
});
