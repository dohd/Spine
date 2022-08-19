<?php

// supplier
Route::group(['namespace' => 'supplier'], function () {
  Route::post('suppliers/due_bills', 'SuppliersController@due_bills')->name('suppliers.due_bills');
  Route::post('suppliers/goodsreceivenote', 'SuppliersController@goodsreceivenote')->name('suppliers.goodsreceivenote');
  Route::post('suppliers/purchaseorders', 'SuppliersController@purchaseorders')->name('suppliers.purchaseorders');
  Route::post('suppliers/search', 'SuppliersController@search')->name('suppliers.search');
  Route::post('suppliers/select', 'SuppliersController@select')->name('suppliers.select');
  Route::post('suppliers/active', 'SuppliersController@active')->name('suppliers.active');
  Route::resource('suppliers', 'SuppliersController');
  // data table
  Route::post('suppliers/get', 'SuppliersTableController')->name('suppliers.get');
});
