<?php

// tax return report
Route::group(['namespace' => 'tax_report'], function () {
    Route::get('tax_reports/filed_report', 'TaxReportsController@filed_report')->name('tax_reports.filed_report');
    Route::post('tax_reports/purchases', 'TaxReportsController@get_purchases')->name('tax_reports.get_purchases');
    Route::post('tax_reports/sales', 'TaxReportsController@get_sales')->name('tax_reports.get_sales');
    Route::resource('tax_reports', 'TaxReportsController');
    // data table
    Route::post('tax_reports/get_filed_items', 'FiledTaxReportsTableController')->name('tax_reports.get_filed_items');
    Route::post('tax_reports/get', 'TaxReportsTableController')->name('tax_reports.get');
});

Route::group(['namespace' => 'report'], function () {
    Route::get('reports/statement/{section}', 'StatementController@statement')->name('reports.statements');
    Route::post('reports/statement/generate/{section}', 'StatementController@generate_statement')->name('reports.generate_statement');
    Route::post('reports/statement/tax/{section}', 'StatementController@generate_tax_statement')->name('reports.generate_tax_statement');
    Route::post('reports/statement/stock/{section}', 'StatementController@generate_stock_statement')->name('reports.generate_stock_statement');
    Route::get('reports/chart/{section}', 'ChartController@chart')->name('reports.charts');
    Route::post('reports/chart/{section}', 'ChartController@chart')->name('reports.charts');
    Route::get('reports/summary/{section}', 'SummaryController@summary')->name('reports.summary');
    Route::post('reports/summary/{section}', 'SummaryController@summary')->name('reports.summary');
    Route::post('reports/pos/register', 'StatementController@pos_statement')->name('reports.pos');
});
