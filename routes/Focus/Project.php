<?php

/**
 * project
 *
 */
Route::group(['namespace' => 'project', 'middleware' => 'project'], function () {
  Route::post('projects/load', 'ProjectsController@load')->name('projects.load');
  Route::post('projects/update_status', 'ProjectsController@update_status')->name('projects.update_status');
  Route::post('projects/store_meta', 'ProjectsController@store_meta')->name('projects.store_meta');
  Route::post('projects/delete_meta', 'ProjectsController@delete_meta')->name('projects.delete_meta');
  Route::post('projects/log_history', 'ProjectsController@log_history')->name('projects.log_history');
  Route::post('projects/notes', 'ProjectsController@notes')->name('projects.notes');
  Route::post('projects/invoices', 'ProjectsController@invoices')->name('projects.invoices');

  Route::get('projects/quote_budget/{quote}', 'ProjectsController@quote_budget')->name('projects.quote_budget');
  Route::post('projects/quote_budget', 'ProjectsController@store_quote_budget')->name('projects.store_quote_budget');
  Route::post('projects/quote_budget/{budget}', 'ProjectsController@update_quote_budget')->name('projects.update_quote_budget');
  Route::delete('projects/quote_budget/delete_budget_item/{id}', 'ProjectsController@delete_budget_item')->name('projects.delete_budget_item');
  Route::delete('projects/quote_budget/delete_skillset/{id}', 'ProjectsController@delete_skillset')->name('projects.delete_skillset');
});

Route::group(['namespace' => 'project'], function () {
  Route::resource('projects', 'ProjectsController');
  Route::post('projects/search', 'ProjectsController@search')->name('projects.search');
  //For Datatable
  Route::post('projects/get', 'ProjectsTableController')->name('projects.get');
  Route::post('projects/project_load_select', 'ProjectsController@project_load_select')->name('projects.project_load_select');
  Route::post('projects/search/{bill_type}', 'ProjectsController@project_search')->name('projects.project_search');
});
