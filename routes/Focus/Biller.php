<?php

/**
 * FocusRoutes
 *
 */
Route::group(['namespace' => 'account'], function () {
    Route::get('accounts/balancesheet/{type}', 'AccountsController@balance_sheet')->name('accounts.balance_sheet');
    Route::get('accounts/trialbalance/{type}', 'AccountsController@trial_balance')->name('accounts.trial_balance');
    Route::resource('accounts', 'AccountsController');
    //For Datatable
    Route::post('accounts/get', 'AccountsTableController')->name('accounts.get');
    Route::post('accounts/search', 'AccountsController@account_search')->name('accounts.account_search');
    Route::post('accounts/search_next_account_no', 'AccountsController@search_next_account_no')->name('accounts.search_next_account_no');
});

Route::group(['namespace' => 'additional'], function () {
    Route::resource('additionals', 'AdditionalsController');
    //For Datatable
    Route::post('additionals/get', 'AdditionalsTableController')->name('additionals.get');
});

Route::group(['namespace' => 'assetequipment'], function () {
    Route::resource('assetequipments', 'AssetequipmentsController');
    Route::post('assetequipments/ledger_load', 'AssetequipmentsController@ledger_load')->name('assetequipments.ledger_load');
    Route::post('assetequipments/search', 'AssetequipmentsController@product_search')->name('assetequipments.product_search');
    //For Datatable
    Route::post('assetequipments/get', 'AssetequipmentsTableController')->name('assetequipments.get');
});

Route::group(['namespace' => 'bank'], function () {
    Route::resource('banks', 'BanksController');
    //For Datatable
    Route::post('banks/get', 'BanksTableController')->name('banks.get');
});
Route::group(['namespace' => 'banktransfer'], function () {
    Route::resource('banktransfers', 'BanktransfersController');
    //For Datatable
    Route::post('banktransfers/get', 'BanktransfersTableController')->name('banktransfers.get');
});
Route::group(['namespace' => 'branch'], function () {
    Route::get('branches/branch_load', 'BranchesController@branch_load')->name('branches.branch_load');

    Route::resource('branches', 'BranchesController');
    //For Datatable
    Route::post('branches/get', 'BranchesTableController')->name('branches.get');
});
Route::group(['namespace' => 'charge'], function () {
    Route::resource('charges', 'ChargesController');
    //For Datatable
    Route::post('charges/get', 'ChargesTableController')->name('charges.get');
});

Route::group(['namespace' => 'creditor'], function () {
    Route::resource('creditors', 'CreditorsController');
    //For Datatable
    Route::post('creditors/get', 'CreditorsTableController')->name('creditors.get');
});
Route::group(['namespace' => 'currency'], function () {
    Route::resource('currencies', 'CurrenciesController');
    //For Datatable
    Route::post('currencies/get', 'CurrenciesTableController')->name('currencies.get');
});
Route::group(['namespace' => 'customergroup'], function () {
    Route::resource('customergroups', 'CustomergroupsController');
    //For Datatable
    Route::post('customergroups/get', 'CustomergroupsTableController')->name('customergroups.get');
});

Route::group(['namespace' => 'customfield'], function () {
    Route::resource('customfields', 'CustomfieldsController');
    //For Datatable
    Route::post('customfields/get', 'CustomfieldsTableController')->name('customfields.get');
});
Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DebtorsController');
    //For Datatable
    //Route::post('deptors/get', 'DebtorsTableController')->name('deptors.get');
});
Route::group(['namespace' => 'department'], function () {
    Route::resource('departments', 'DepartmentsController');
    //For Datatable
    Route::post('departments/get', 'DepartmentsTableController')->name('departments.get');
});

Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DeptorsController');
    //For Datatable
    Route::post('deptors/get', 'DeptorsTableController')->name('deptors.get');
});

Route::group(['namespace' => 'deptor'], function () {
    Route::resource('deptors', 'DeptorsController');
    //For Datatable
    Route::post('deptors/get', 'DeptorsTableController')->name('deptors.get');
});


Route::group(['namespace' => 'equipment'], function () {
    Route::resource('equipments', 'EquipmentsController');
    Route::post('equipments/equipment_load', 'EquipmentsController@equipment_load')->name('equipments.equipment_load');
    Route::post('equipments/search/{id}', 'EquipmentsController@equipment_search')->name('equipments.equipment_search');

    //For Datatable
    Route::post('equipments/get', 'EquipmentsTableController')->name('equipments.get');
});

Route::group(['namespace' => 'equipmentcategory'], function () {
    Route::resource('equipmentcategories', 'EquipmentCategoriesController');
    //For Datatable
    Route::post('equipmentcategories/get', 'EquipmentCategoriesTableController')->name('equipmentcategories.get');
});
Route::group(['namespace' => 'event'], function () {
    Route::get('events/load_events', 'EventsController@load_events')->name('events.load_events');
    Route::post('events/update_event', 'EventsController@update_event')->name('events.update_event');
    Route::post('events/delete_event', 'EventsController@delete_event')->name('events.delete_event');

    //For Datatable
    Route::post('events/get', 'EventsTableController')->name('events.get');
    Route::resource('events', 'EventsController');
});

Route::group(['namespace' => 'djc'], function () {
    Route::delete('djcs/delete_item/{id}', 'DjcsController@delete_item')->name('djcs.delete_item');
    
    Route::resource('djcs', 'DjcsController');
    //For Datatable
    Route::post('djcs/get', 'DjcsTableController')->name('djcs.get');
});

Route::group(['namespace' => 'rjc'], function () {
    Route::delete('rjcs/delete_item/{id}', 'RjcsController@delete_item')->name('rjcs.delete_item');

    Route::resource('rjcs', 'RjcsController');
    //For Datatable
    Route::post('rjcs/get', 'RjcsTableController')->name('rjcs.get');
});



Route::group(['namespace' => 'jobschedule'], function () {
    Route::resource('jobschedules', 'JobschedulesController');

    Route::post('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');
    //For Datatable
    Route::post('jobschedules/get', 'JobschedulesTableController')->name('jobschedules.get');
});
Route::group(['namespace' => 'lead'], function () {
    Route::resource('leads', 'LeadsController');
    Route::post('leads/lead_search', 'LeadsController@lead_search')->name('leads.lead_search');
    Route::post('leads/{id}/update_status', 'LeadsController@update_status')->name('leads.update_status');

    //For Datatable
    Route::post('leads/get', 'LeadsTableController')->name('leads.get');
});

Route::group(['namespace' => 'loan'], function () {
    Route::resource('loans', 'LoansController');
    //For Datatable
    //Route::post('loans/get', 'DeptorsTableController')->name('deptors.get');
});

Route::group(['namespace' => 'makepayment'], function () {
    Route::resource('makepayments', 'MakepaymentsController');

    //Route::post('purchases/customer_load', 'PurchasesController@customer_load')->name('purchases.customer_load');

    //For Datatable
    Route::get('makepayment/single_payment/{tr_id}', 'MakepaymentsController@single_payment')->name('makepayment.single_payment');
    Route::get('makepayment/receive_single_payment/{tr_id}', 'MakepaymentsController@receive_single_payment')->name('makepayment.receive_single_payment');
});



Route::group(['namespace' => 'misc'], function () {
    Route::resource('miscs', 'MiscsController');
    //For Datatable
    Route::post('miscs/get', 'MiscsTableController')->name('miscs.get');
});
Route::group(['namespace' => 'note'], function () {
    Route::resource('notes', 'NotesController');
    //For Datatable
    Route::post('notes/get', 'NotesTableController')->name('notes.get');
});


Route::group(['namespace' => 'order'], function () {
    Route::resource('orders', 'OrdersController');
    //For Datatable
    Route::post('orders/get', 'OrdersTableController')->name('orders.get');
});
Route::group(['namespace' => 'openingbalance'], function () {
    Route::resource('openingbalances', 'OpeningbalancesController');
    //For Datatable
    //Route::post('productstocktransfers/get', 'ProductstocktransfersTableController')->name('productstocktransfers.get');
});

Route::group(['namespace' => 'prefix'], function () {
    Route::resource('prefixes', 'PrefixesController');
    //For Datatable
    Route::post('prefixes/get', 'PrefixesTableController')->name('prefixes.get');
});
Route::group(['namespace' => 'pricegroup'], function () {
    Route::resource('pricegroups', 'PricegroupsController');
    //For Datatable
    Route::post('pricegroups/get', 'PricegroupsTableController')->name('pricegroups.get');
});


Route::group(['namespace' => 'productcategory'], function () {
    Route::resource('productcategories', 'ProductcategoriesController');
    //For Datatable
    Route::post('productcategories/get', 'ProductcategoriesTableController')->name('productcategories.get');
});
Route::group(['namespace' => 'projectstocktransfer'], function () {
    Route::resource('projectstocktransfers', 'ProjectstocktransfersController');
    //For Datatable
    Route::post('projectstocktransfers/get', 'ProjectstocktransfersTableController')->name('projectstocktransfers.get');
});

Route::group(['namespace' => 'stockissuance'], function () {
    Route::post('issue_stock', 'StockIssuanceController@issue_stock')->name('stockissuance.issue_stock');
    Route::get('issued_stock_log/delete', 'StockIssuanceController@delete_log')->name('stockissuance.delete_log');
    Route::get('issued_stock_log/{id}', 'StockIssuanceController@get_issued_log')->name('stockissuance.get_issued_log');
    Route::post('issued_quote_stock', 'StockIssuanceController@post_issuedstock')->name('stockissuance.post_issuedstock');

    Route::resource('stockissuance', 'StockIssuanceController');
    // for dataTable
    Route::post('stockissuancelog/get', 'StockIssuanceLogTableController')->name('stockissuance.getlog');
    Route::post('stockissuance/get', 'StockIssuanceTableController')->name('stockissuance.get');
});

Route::group(['namespace' => 'lpo'], function () {
    Route::post('lpo/update_lpo', 'LpoController@update_lpo')->name('lpo.update_lpo');
    Route::get('lpo/delete_lpo/{id}', 'LpoController@delete_lpo')->name('lpo.delete_lpo');

    Route::resource('lpo', 'LpoController');
    // for dataTable
    Route::post('lpo/get', 'LpoTableController')->name('lpo.get');
});

Route::group(['namespace' => 'productvariable'], function () {
    Route::resource('productvariables', 'ProductvariablesController');
    //For Datatable
    Route::post('productvariables/get', 'ProductvariablesTableController')->name('productvariables.get');
});
Route::group(['namespace' => 'purchase'], function () {
    Route::resource('purchases', 'PurchasesController');
    Route::post('purchases/customer_load', 'PurchasesController@customer_load')->name('purchases.customer_load');

    //For Datatable
    Route::post('purchases/get', 'PurchasesTableController')->name('purchases.get');
});
Route::group(['namespace' => 'purchaseorder'], function () {
    Route::get('purchaseorders/create_grn/{purchaseorder}', 'PurchaseordersController@create_grn')->name('purchaseorders.create_grn');
    Route::post('purchaseorders/grn/{purchaseorder}', 'PurchaseordersController@store_grn')->name('purchaseorders.grn');
    
    Route::resource('purchaseorders', 'PurchaseordersController');
    //For Datatable
    Route::post('purchaseorders/get', 'PurchaseordersTableController')->name('purchaseorders.get');
});
Route::group(['namespace' => 'projectequipment'], function () {
    Route::resource('projectequipments', 'ProjectequipmentsController');
    Route::post('projectequipments/write_job_card', 'ProjectequipmentsController@write_job_card')->name('projectequipments.write_job_card');
    //For Datatable
    Route::post('projectequipments/get', 'ProjectequipmentsTableController')->name('projectequipments.get');
});
Route::group(['namespace' => 'quote'], function () {
    Route::post('quotes/convert', 'QuotesController@convert')->name('quotes.convert');
    Route::post('quotes/approve_quote/{quote}', 'QuotesController@approve_quote')->name('quotes.approve_quote');
   
    Route::post('quotes/storeverified', 'QuotesController@storeverified')->name('quotes.storeverified');
    Route::get('quotes/customer_quotes', 'QuotesController@customer_quotes')->name('quotes.customer_quotes');
    Route::get('quotes/verify/{quote}', 'QuotesController@verify_quote')->name('quotes.verify');
    Route::post('quotes/verified_jcs/{id}', 'QuotesController@fetch_verified_jcs')->name('quotes.fetch_verified_jcs');
    Route::get('quotes/get_verify', 'QuotesController@get_verify_quote')->name('quotes.get_verify_quote');

    // should be delete methods
    Route::get('quotes/delete_product/{id}', 'QuotesController@delete_product')->name('quotes.delete_product');
    Route::get('quotes/verified_item/{id}', 'QuotesController@delete_verified_item')->name('quotes.delete_verified_item');
    Route::get('quotes/verified_jcs/{id}', 'QuotesController@delete_verified_jcs')->name('quotes.delete_verified_jcs');
    Route::get('quotes/reset_verified/{id}', 'QuotesController@reset_verified')->name('quotes.reset_verified');

    Route::post('quotes/lpo', 'QuotesController@update_lpo')->name('quotes.lpo');
    Route::resource('quotes', 'QuotesController');
    //For Datatable
    Route::post('quotes/get_project', 'QuoteVerifyTableController')->name('quotes.get_project');
    Route::post('quotes/get', 'QuotesTableController')->name('quotes.get');
    Route::post('quotes/get_univoiced_quote', 'QuoteInvoiceTableController')->name('quotes.get_univoiced_quote');
});

Route::group(['namespace' => 'region'], function () {
    Route::resource('regions', 'RegionsController');
    Route::post('regions/load_region', 'RegionsController@load_region')->name('regions.load_region');

    Route::post('regions/get', 'RegionsTableController')->name('regions.get');
});

Route::group(['namespace' => 'section'], function () {
    Route::resource('sections', 'SectionsController');

    Route::post('sections/get', 'SectionsTableController')->name('sections.get');
});

Route::group(['namespace' => 'spvariations'], function () {
    Route::resource('spvariations', 'SpVariablesController');
    //For Datatable
    Route::post('spvariations/get', 'SpVariablesControllerTableController')->name('spvariations.get');
});
Route::group(['namespace' => 'template'], function () {
    Route::resource('templates', 'TemplatesController');
    //For Datatable
    Route::post('templates/get', 'TemplatesTableController')->name('templates.get');
});
Route::group(['namespace' => 'term'], function () {
    Route::resource('terms', 'TermsController');
    //For Datatable
    Route::post('terms/get', 'TermsTableController')->name('terms.get');
});

Route::group(['namespace' => 'transactioncategory'], function () {
    Route::resource('transactioncategories', 'TransactioncategoriesController');
    //For Datatable
    Route::post('transactioncategories/get', 'TransactioncategoriesTableController')->name('transactioncategories.get');
});

Route::group(['namespace' => 'gateway'], function () {
    Route::resource('usergatewayentries', 'UsergatewayentriesController');
    //For Datatable
    Route::post('usergatewayentries/get', 'UsergatewayentriesTableController')->name('usergatewayentries.get');
});
Route::group(['namespace' => 'warehouse'], function () {
    Route::resource('warehouses', 'WarehousesController');
    //For Datatable
    Route::post('warehouses/get', 'WarehousesTableController')->name('warehouses.get');
});
Route::group(['namespace' => 'withholding'], function () {
    Route::resource('withholdings', 'WithholdingsController');
    //For Datatable
    Route::post('withholdings/get', 'WithholdingsTableController')->name('withholdings.get');
});
