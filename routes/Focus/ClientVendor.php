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
Route::group(['namespace' => 'client_vendor_ticket'], function () {
    Route::resource('client_vendor_tickets', 'ClientVendorTicketsController');
    //For Datatable
    Route::post('client_vendor_tickets/get', 'ClientVendorTicketsTableController')->name('client_vendor_tickets.get');
});
