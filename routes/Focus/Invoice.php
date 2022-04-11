<?php

/**
 * invoices
 *
 */
Route::group(['namespace' => 'invoice'], function () {
    Route::post('bill_status', 'InvoicesController@update_status')->name('bill_status');
    Route::get('pos', 'InvoicesController@pos')->name('invoices.pos');
    Route::post('pos_create', 'InvoicesController@pos_store')->name('invoices.pos_store');
    Route::post('draft_store', 'InvoicesController@draft_store')->name('invoices.draft_store');
    Route::post('drafts_load', 'InvoicesController@drafts_load')->name('invoices.drafts_load');
    Route::get('draft_view/{id}', 'InvoicesController@draft_view')->name('invoices.draft_view');
    Route::post('pos_update', 'InvoicesController@pos_update')->name('invoices.pos_update');
    Route::get('invoices/create_payment', 'InvoicesController@create_payment')->name('invoices.create_payment');
    Route::post('invoices/store_payment', 'InvoicesController@store_payment')->name('invoices.store_payment');
    Route::get('invoices/client_invoices', 'InvoicesController@client_invoices')->name('invoices.client_invoices');

    // project invoice
    Route::get('invoices/edit_project_invoice/{invoice}', 'InvoicesController@edit_project_invoice')->name('invoices.edit_project_invoice');
    Route::post('invoices/update_project_invoice/{invoice}', 'InvoicesController@update_project_invoice')->name('invoices.update_project_invoice');
    Route::get('filter_invoice_quotes', 'InvoicesController@filter_invoice_quotes')->name('invoices.filter_invoice_quotes');
    Route::post('store_project_invoice', 'InvoicesController@store_project_invoice')->name('invoices.store_project_invoice');
    Route::get('project_invoice', 'InvoicesController@project_invoice')->name('invoices.project_invoice');
    Route::resource('invoices', 'InvoicesController');

    //For Datatable
    Route::post('invoices/get', 'InvoicesTableController')->name('invoices.get');
    Route::get('invoices/print_document/{id}/{type}', 'InvoicesController@print_document')->name('invoices.print_document');
});

Route::group(['namespace' => 'printer'], function () {
    Route::get('browser_print', 'PrinterController@browser_print')->name('pos.browser_print');
    Route::post('register/open', 'RegistersController@open')->name('register.open');
    Route::get('register/close', 'RegistersController@close')->name('register.close');
    Route::get('register/load', 'RegistersController@load')->name('register.load');
});
