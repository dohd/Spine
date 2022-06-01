<?php

/**
 * project
 *
 */
Route::group(['namespace' => 'project', 'middleware' => 'project'], function () {
  Route::post('projects/update_status', 'ProjectsController@update_status')->name('projects.update_status');
  Route::post('projects/store_meta', 'ProjectsController@store_meta')->name('projects.store_meta');
  Route::post('projects/delete_meta', 'ProjectsController@delete_meta')->name('projects.delete_meta');
  Route::post('projects/log_history', 'ProjectsController@log_history')->name('projects.log_history');
  Route::post('projects/notes', 'ProjectsController@notes')->name('projects.notes');
  Route::post('projects/invoices', 'ProjectsController@invoices')->name('projects.invoices');

  // Project budget
  Route::get('projects/budget/{quote}', 'ProjectsController@create_project_budget')->name('projects.create_project_budget');
  Route::get('projects/budget/{qoute_id}/{budget_id}', 'ProjectsController@edit_project_budget')->name('projects.edit_project_budget');
  Route::post('projects/budget_store', 'ProjectsController@store_project_budget')->name('projects.store_project_budget');
  Route::post('projects/budget_update/{budget}', 'ProjectsController@update_project_budget')->name('projects.update_project_budget');
  Route::post('projects/budget_tool_update/{budget}', 'ProjectsController@update_budget_tool')->name('projects.update_budget_tool');


  Route::get('projects/budget_delete_item/{id}', 'ProjectsController@delete_budget_item')->name('projects.delete_budget_item');
  Route::delete('projects/budget_delete_skillset/{id}', 'ProjectsController@delete_budget_skillset')->name('projects.delete_budget_skillset');
});

Route::group(['namespace' => 'contract'], function () {
  Route::resource('contracts', 'ContractsController');
  //For Datatable
  Route::post('contracts/get', 'ContractsTableController')->name('contracts.get');
});

Route::group(['namespace' => 'project'], function () {
  Route::resource('projects', 'ProjectsController');
  Route::post('projects/search', 'ProjectsController@search')->name('projects.search');
  //For Datatable
  Route::post('projects/get', 'ProjectsTableController')->name('projects.get');
  Route::post('projects/project_load_select', 'ProjectsController@project_load_select')->name('projects.project_load_select');
  Route::post('projects/search', 'ProjectsController@project_search')->name('projects.project_search');
});
