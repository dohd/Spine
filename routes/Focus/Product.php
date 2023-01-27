<?php

Route::group(['namespace' => 'stock_transfer'], function () {
    Route::resource('stock_transfers', 'StockTransfersController');
    // data table
    Route::post('stock_transfers/get', 'StockTransfersTableController')->name('stock_transfers.get');
});

Route::group(['namespace' => 'opening_stock'], function () {
    Route::post('opening_stock/product_variation', 'OpeningStockController@product_variation')->name('opening_stock.product_variation');
    Route::resource('opening_stock', 'OpeningStockController');
    // data table
    Route::post('opening_stock/get', 'OpeningStockTableController')->name('opening_stock.get');
});

Route::group(['namespace' => 'goodsreceivenote'], function () {
    Route::resource('goodsreceivenote', 'GoodsReceiveNoteController');

    Route::post('goodsreceivenote/get', 'GoodsReceiveNoteTableController')->name('goodsreceivenote.get');
});

Route::group(['namespace' => 'purchaseorder'], function () {
    Route::get('purchaseorders/create_grn/{purchaseorder}', 'PurchaseordersController@create_grn')->name('purchaseorders.create_grn');
    Route::post('purchaseorders/grn/{purchaseorder}', 'PurchaseordersController@store_grn')->name('purchaseorders.grn');

    Route::post('purchaseorders/goods', 'PurchaseordersController@goods')->name('purchaseorders.goods');
    Route::resource('purchaseorders', 'PurchaseordersController');
    // data table
    Route::post('purchaseorders/get', 'PurchaseordersTableController')->name('purchaseorders.get');
});

Route::group(['namespace' => 'product'], function () {
    Route::get('products/get_products', 'ProductsController@getProducts')->name('products.getProducts');
    Route::get('products/label', 'ProductsController@product_label')->name('products.product_label');
    Route::get('products/quick_add', 'ProductsController@quick_add')->name('products.quick_add');
    Route::get('products/standard', 'ProductsController@standard')->name('products.standard');
    Route::post('products/standard', 'ProductsController@standard')->name('products.standard');
    Route::post('products/label', 'ProductsController@product_label')->name('products.product_label');
    Route::get('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');
    Route::post('products/stock_transfer', 'ProductsController@stock_transfer')->name('products.stock_transfer');

    //For Datatable
    Route::post('products/get', 'ProductsTableController')->name('products.get');
    Route::post('products/search/{bill_type}', 'ProductsController@product_search')->name('products.product_search');
    Route::post('products/quote', 'ProductsController@quote_product_search')->name('products.quote_product_search');
    Route::post('products/purchase_search', 'ProductsController@purchase_search')->name('products.purchase_search');

    Route::post('products/product_sub_load', 'ProductsController@product_sub_load')->name('products.product_sub_load');
    Route::post('products/pos/{bill_type}', 'ProductsController@pos')->name('products.product_search');
    Route::resource('products', 'ProductsController');
});
