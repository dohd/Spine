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
    Route::get('client_vendor_tickets/vendor_access/{client_vendor_ticket}', 'ClientVendorTicketsController@vendor_access')->name('client_vendor_tickets.vendor_access');
    Route::patch('client_vendor_tickets/progress/{client_vendor_ticket}', 'ClientVendorTicketsController@update_progress')->name('client_vendor_tickets.update_progress');

    Route::patch('client_vendor_tickets/status/{tenant_ticket}', 'ClientVendorTicketsController@status')->name('client_vendor_tickets.status');
    Route::post('client_vendor_tickets/reply', 'ClientVendorTicketsController@reply')->name('client_vendor_tickets.reply');
    Route::resource('client_vendor_tickets', 'ClientVendorTicketsController');
    //For Datatable
    Route::post('client_vendor_tickets/get', 'ClientVendorTicketsTableController')->name('client_vendor_tickets.get');
});
Route::group(['namespace' => 'client_vendor_tag'], function () {
    Route::resource('client_vendor_tags', 'ClientVendorTagsController');
    //For Datatable
    Route::post('client_vendor_tags/get', 'ClientVendorTagsTableController')->name('client_vendor_tags.get');
});
Route::group(['namespace' => 'client_user'], function () {
    Route::resource('client_users', 'ClientUsersController');
    //For Datatable
    Route::post('client_users/get', 'ClientUsersTableController')->name('client_users.get');
});
