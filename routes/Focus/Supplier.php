<?php

// supplier
Route::group(['namespace' => 'supplier'], function () {
  Route::post('suppliers/bills', 'SuppliersController@bills')->name('suppliers.bills');
  Route::post('suppliers/goods_receive_note', 'SuppliersController@goods_receive_note')->name('suppliers.goods_receive_note');
  Route::post('suppliers/purchaseorders', 'SuppliersController@purchaseorders')->name('suppliers.purchaseorders');
  Route::post('suppliers/search', 'SuppliersController@search')->name('suppliers.search');
  Route::post('suppliers/select', 'SuppliersController@select')->name('suppliers.select');
  Route::post('suppliers/active', 'SuppliersController@active')->name('suppliers.active');
  Route::resource('suppliers', 'SuppliersController');
  // data table
  Route::post('suppliers/get', 'SuppliersTableController')->name('suppliers.get');
});

// purchase requisition
Route::group(['namespace' => 'purchase_request'], function () {
  Route::resource('purchase_requests', 'PurchaseRequestsController');
  // data table
  Route::post('purchase_requests/get', 'PurchaseRequestsTableController')->name('purchase_requests.get');
});
