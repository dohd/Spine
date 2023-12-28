<?php

/**
 * Client Area
 *
 */
Route::group(['namespace' => 'tenant'], function () {
    Route::post('tenants/customers', 'TenantsController@customers')->name('tenants.customers');
    Route::post('tenants/select', 'TenantsController@select')->name('tenants.select');
    Route::resource('tenants', 'TenantsController');
    //For Datatable
    Route::post('tenants/get', 'TenantsTableController')->name('tenants.get');
});
Route::group(['namespace' => 'tenant_service'], function () {
    Route::resource('tenant_services', 'TenantServicesController');
    //For Datatable
    Route::post('tenant_services/get', 'TenantServicesTableController')->name('tenant_services.get');
});
Route::group(['namespace' => 'tenant_invoice'], function () {
    Route::resource('tenant_invoices', 'TenantInvoicesController');
    //For Datatable
    Route::post('tenant_invoices/get', 'TenantInvoicesTableController')->name('tenant_invoices.get');
});
Route::group(['namespace' => 'tenant_deposit'], function () {
    Route::resource('tenant_deposits', 'TenantDepositsController');
    //For Datatable
    Route::post('tenant_deposits/get', 'TenantDepositsTableController')->name('tenant_deposits.get');
});
Route::group(['namespace' => 'tenant_ticket'], function () {
    Route::patch('tenant_tickets/status/{tenant_ticket}', 'TenantTicketsController@status')->name('tenant_tickets.status');
    Route::post('tenant_tickets/reply', 'TenantTicketsController@reply')->name('tenant_tickets.reply');
    Route::resource('tenant_tickets', 'TenantTicketsController');
    //For Datatable
    Route::post('tenant_tickets/get', 'TenantTicketsTableController')->name('tenant_tickets.get');
});