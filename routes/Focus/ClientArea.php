<?php

/**
 * Client Area
 *
 */
Route::group(['namespace' => 'tenant'], function () {
    Route::resource('tenants', 'TenantsController');
    //For Datatable
    Route::post('tenants/get', 'TenantsTableController')->name('tenants.get');
});
