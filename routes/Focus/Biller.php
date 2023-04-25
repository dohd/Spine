<?php

/**
 * FocusRoutes
 *
 */

// Utility bills
Route::group(['namespace' => 'utility_bill'], function () {
    Route::get('utility-bills/create-kra', 'UtilityBillController@create_kra_bill')->name('utility-bills.create_kra_bill');

    Route::post('utility-bills/employee_bills', 'UtilityBillController@employee_bills')->name('utility-bills.employee_bills');
    Route::post('utility-bills/goods-receive-note', 'UtilityBillController@goods_receive_note')->name('utility-bills.goods_receive_note');
    Route::post('utility-bills/store-kra', 'UtilityBillController@store_kra_bill')->name('utility-bills.store_kra_bill');
    Route::post('utility-bills/store-kra', 'UtilityBillController@store_kra_bill')->name('utility-bills.store_kra_bill');
    Route::resource('utility-bills', 'UtilityBillController');
    // data table
    Route::post('utility-bills/get', 'UtilityBillTableController')->name('utility-bills.get');
});

// supplier bill payment
Route::group(['namespace' => 'billpayment'], function () {
    Route::resource('billpayments', 'BillPaymentController');
    // data table
    Route::post('billpayments/get', 'BillPaymentTableController')->name('billpayments.get');
  });
  

//  Accounts
Route::group(['namespace' => 'account'], function () {
    Route::get('accounts/profit_and_loss/{type}', 'AccountsController@profit_and_loss')->name('accounts.profit_and_loss');
    Route::get('accounts/balancesheet/{type}', 'AccountsController@balance_sheet')->name('accounts.balance_sheet');
    Route::get('accounts/trialbalance/{type}', 'AccountsController@trial_balance')->name('accounts.trial_balance');
    Route::get('accounts/project_gross_profit', 'AccountsController@project_gross_profit')->name('accounts.project_gross_profit');
    Route::get('accounts/cashbook', 'AccountsController@cashbook')->name('accounts.cashbook');

    Route::post('accounts/search_next_account_no', 'AccountsController@search_next_account_no')->name('accounts.search_next_account_no');
    Route::post('accounts/search', 'AccountsController@account_search')->name('accounts.account_search');
    Route::resource('accounts', 'AccountsController');
    //For Datatable
    Route::post('accounts/cashbook/transactions', 'CashbookTableController')->name('accounts.get_cashbook');
    Route::post('accounts/project_gross_profit/get', 'ProjectGrossProfitTableController')->name('accounts.get_project_gross_profit');
    Route::post('accounts/get', 'AccountsTableController')->name('accounts.get');
});

// Tax Return
Route::group(['namespace' => 'tax_report'], function () {
    Route::get('tax_reports/filed_report', 'TaxReportsController@filed_report')->name('tax_reports.filed_report');
    Route::post('tax_reports/purchases', 'TaxReportsController@get_purchases')->name('tax_reports.get_purchases');
    Route::post('tax_reports/sales', 'TaxReportsController@get_sales')->name('tax_reports.get_sales');
    Route::resource('tax_reports', 'TaxReportsController');
    // data table
    Route::post('tax_reports/get_filed_items', 'FiledTaxReportsTableController')->name('tax_reports.get_filed_items');
    Route::post('tax_reports/get', 'TaxReportsTableController')->name('tax_reports.get');
});
Route::group(['namespace' => 'tax_prn'], function () {
    Route::resource('tax_prns', 'TaxPrnsController');
    // data table
    Route::post('tax_prns/get', 'TaxPrnsTableController')->name('tax_prns.get');
});


Route::group(['namespace' => 'allowance'], function () {
    Route::resource('allowances', 'AllowancesController');
    //For Datatable
    Route::post('allowances/get', 'AllowancesTableController')->name('allowances.get');
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
    Route::post('branches/select', 'BranchesController@select')->name('branches.select');
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

Route::group(['namespace' => 'employeesalary'], function () {
    Route::resource('employeesalaries', 'EmployeeSalariesController');
    //For Datatable
    Route::post('salaries/get', 'SalariesTableController')->name('salaries.get');
    Route::post('employeesalaries/get', 'EmployeeSalariesTableController')->name('employeesalaries.get');
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
    Route::resource('djcs', 'DjcsController');
    //For Datatable
    Route::post('djcs/get', 'DjcsTableController')->name('djcs.get');
});

Route::group(['namespace' => 'rjc'], function () {
    Route::post('rjcs/project_extra_details', 'RjcsController@project_extra_details')->name('rjcs.project_extra_details');
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
    Route::patch('leads/update_status/{lead}', 'LeadsController@update_status')->name('leads.update_status');
    Route::patch('leads/update_reminder/{lead}', 'LeadsController@update_reminder')->name('leads.update_reminder');
    Route::post('leads/lead_search', 'LeadsController@lead_search')->name('leads.lead_search');
    Route::resource('leads', 'LeadsController');

    //For Datatable
    Route::post('leads/get', 'LeadsTableController')->name('leads.get');
});
Route::group(['namespace' => 'lender'], function () {
    Route::resource('lenders', 'LendersController');

    //For Datatable
    Route::post('lenders/get', 'LendersTableController')->name('lenders.get');
});

Route::group(['namespace' => 'loan'], function () {
    Route::get('loans/lender_loans', 'LoansController@lender_loans')->name('loans.lender_loans');
    Route::post('loans/lenders', 'LoansController@lenders')->name('loans.lenders');
    Route::get('loans/pay_loans', 'LoansController@pay_loans')->name('loans.pay_loans');
    Route::post('loans/store_loans', 'LoansController@store_loans')->name('loans.store_loans');
    Route::get('loans/approve/{loan}', 'LoansController@approve_loan')->name('loans.approve_loan');
    Route::resource('loans', 'LoansController');
    //For Datatable
    Route::post('loans/get', 'LoansTableController')->name('loans.get');
});

Route::group(['namespace' => 'journal'], function () {
    Route::post('journals/journal_accounts', 'JournalsController@journal_accounts')->name('journals.journal_accounts');
    Route::resource('journals', 'JournalsController');
    //For Datatable
    Route::post('journals/get', 'JournalsTableController')->name('journals.get');
});

Route::group(['namespace' => 'reconciliation'], function () {
    Route::get('reconciliations/last_reconciliation', 'ReconciliationsController@last_reconciliation')->name('reconciliations.last_reconciliation');
    Route::get('reconciliations/ledger_transactions', 'ReconciliationsController@ledger_transactions')->name('reconciliations.ledger_transactions');
    Route::resource('reconciliations', 'ReconciliationsController');
    //For Datatable
    Route::post('reconciliations/get', 'ReconciliationsTableController')->name('reconciliations.get');
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

Route::group(['namespace' => 'client_product'], function () {
    Route::resource('client_products', 'ClientProductsController');
    //For Datatable
    Route::post('client_products/get', 'ClientProductsTableController')->name('client_products.get');
});

Route::group(['namespace' => 'pricelistSupplier'], function () {
    Route::resource('pricelistsSupplier', 'PriceListsController');
    //For Datatable
    Route::post('pricelists/get', 'PriceListTableController')->name('pricelistsSupplier.get');
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
    Route::post('purchases/quote', 'PurchasesController@quote_product_search')->name('purchase.quote_purchase_search');


    //For Datatable
    Route::post('purchases/get', 'PurchasesTableController')->name('purchases.get');
});

Route::group(['namespace' => 'projectequipment'], function () {
    Route::resource('projectequipments', 'ProjectequipmentsController');
    Route::post('projectequipments/write_job_card', 'ProjectequipmentsController@write_job_card')->name('projectequipments.write_job_card');
    //For Datatable
    Route::post('projectequipments/get', 'ProjectequipmentsTableController')->name('projectequipments.get');
});

// quotes
Route::group(['namespace' => 'quote'], function () {
    Route::post('quotes/convert', 'QuotesController@convert')->name('quotes.convert');
    Route::post('quotes/approve_quote/{quote}', 'QuotesController@approve_quote')->name('quotes.approve_quote');

    Route::post('quotes/close_quote/{quote}', 'QuotesController@close_quote')->name('quotes.close_quote');
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
});

// partial verification
Route::group(['namespace' => 'verification'], function () {
    Route::get('verifications/quote_index', 'VerificationsController@quote_index')->name('verifications.quote_index');
    Route::resource('verifications', 'VerificationsController');
    //For Datatable
    Route::post('verifications/get', 'VerificationsTableController')->name('verifications.get');
    Route::post('verifications/quotes/get', 'VerificationQuotesTableController')->name('verifications.get_quotes');
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
    Route::post('warehouses/products', 'WarehousesController@warehouse_products')->name('warehouse_products.get');
    //For Datatable
    Route::post('warehouses/get', 'WarehousesTableController')->name('warehouses.get');
});

Route::group(['namespace' => 'withholding'], function () {
    Route::resource('withholdings', 'WithholdingsController');
    //For Datatable
    Route::post('withholdings/get', 'WithholdingsTableController')->name('withholdings.get');
});

Route::group(['namespace' => 'creditnote'], function () {
    Route::get('creditnotes/print_creditnote/{creditnote}', 'CreditNotesController@print_creditnote')->name('creditnotes.print_creditnote');
    Route::resource('creditnotes', 'CreditNotesController');
    // for DataTable
    Route::post('creditnotes/get', 'CreditNotesTableController')->name('creditnotes.get');
});

