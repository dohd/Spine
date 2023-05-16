<?php

// project stock
Route::group(['namespace' => 'projectstock'], function () {
  Route::get('projectstock/quotes', 'ProjectStockController@quote_index')->name('projectstock.quote_index');
  Route::resource('projectstock', 'ProjectStockController');
  // data table
  Route::post('projectstock/get_quote', 'QuoteTableController')->name('projectstock.get_quote');
  Route::post('projectstock/get', 'ProjectStockTableController')->name('projectstock.get');
});

// 
Route::group(['namespace' => 'project', 'middleware' => 'project'], function () {
  Route::post('projects/update_status', 'ProjectsController@update_status')->name('projects.update_status');
  Route::get('projects/edit_meta', 'ProjectsController@edit_meta')->name('projects.edit_meta');
  Route::post('projects/update_meta', 'ProjectsController@update_meta')->name('projects.update_meta');
  Route::post('projects/store_meta', 'ProjectsController@store_meta')->name('projects.store_meta');
  Route::post('projects/delete_meta', 'ProjectsController@delete_meta')->name('projects.delete_meta');
  Route::post('projects/log_history', 'ProjectsController@log_history')->name('projects.log_history');
  Route::post('projects/notes', 'ProjectsController@notes')->name('projects.notes');
  Route::post('projects/project_budget', 'ProjectsController@project_budget')->name('projects.project_budget');
  Route::post('projects/quotes_select', 'ProjectsController@quotes_select')->name('projects.quotes_select');
  Route::post('projects/detach_quote', 'ProjectsController@detach_quote')->name('projects.detach_quote');
  Route::post('projects/detach_budget', 'ProjectsController@detach_budget')->name('projects.detach_budget');

  // project budget
  Route::get('projects/budget/{quote}', 'ProjectsController@create_project_budget')->name('projects.create_project_budget');
  Route::get('projects/budget/{qoute_id}/{budget_id}', 'ProjectsController@edit_project_budget')->name('projects.edit_project_budget');
  Route::get('projects/budget', 'ProjectsController@view_budget')->name('projects.view_budget');
  Route::get('projects/budget_limit/{project}', 'ProjectsController@budget_limit')->name('projects.budget_limit');
  Route::post('projects/budget_store', 'ProjectsController@store_project_budget')->name('projects.store_project_budget');
  Route::post('projects/budget_update/{budget}', 'ProjectsController@update_project_budget')->name('projects.update_project_budget');
  Route::post('projects/budget_tool_update/{budget}', 'ProjectsController@update_budget_tool')->name('projects.update_budget_tool');

  // expenses
  Route::post('projects/expenses', 'ExpensesTableController')->name('projects.get_expense');
});

// project budget
Route::group(['namespace' => 'budget'], function () {
  Route::resource('budgets', 'BudgetsController');
  // data table
  Route::post('budgets/get', 'BudgetsTableController')->name('budgets.get');
});

// project task schedules
Route::group(['namespace' => 'taskschedule'], function () {
  Route::post('taskschedules/quote_product_search', 'TaskSchedulesController@quote_product_search')->name('taskschedules.quote_product_search');
  Route::resource('taskschedules', 'TaskSchedulesController');
  // data table
  Route::post('taskschedules/get', 'TaskSchedulesTableController')->name('taskschedules.get');
});

// project contract service
Route::group(['namespace' => 'contractservice'], function () {
  Route::get('contractservices/serviced_equipment', 'ContractServicesController@serviced_equipment')->name('contractservices.serviced_equipment');
  Route::post('contractservices/service_product_search', 'ContractServicesController@service_product_search')->name('contractservices.service_product_search');
  Route::resource('contractservices', 'ContractServicesController');
  // data table
  Route::post('contractservices/get_equipments', 'EquipmentsTableController')->name('contractservices.get_equipments');
  Route::post('contractservices/get', 'ContractServicesTableController')->name('contractservices.get');
});

// project contract
Route::group(['namespace' => 'contract'], function () {
  Route::get('contracts/create_add_equipment', 'ContractsController@create_add_equipment')->name('contracts.create_add_equipment');
  Route::post('contracts/store_add_equipment', 'ContractsController@store_add_equipment')->name('contracts.store_add_equipment');

  Route::post('contracts/customer_contracts', 'ContractsController@customer_contracts')->name('contracts.customer_contracts');
  Route::post('contracts/task_schedules', 'ContractsController@task_schedules')->name('contracts.task_schedules');
  Route::post('contracts/contract_equipment', 'ContractsController@contract_equipment')->name('contracts.contract_equipment');
  Route::post('contracts/customer_equipment', 'ContractsController@customer_equipment')->name('contracts.customer_equipment');
  Route::resource('contracts', 'ContractsController');
  // data table
  Route::post('contracts/get', 'ContractsTableController')->name('contracts.get');
});

// project
Route::group(['namespace' => 'project'], function () {
  
  Route::post('projects/search', 'ProjectsController@search')->name('projects.search');
  Route::resource('projects', 'ProjectsController');
  // data table
  Route::post('projects/get', 'ProjectsTableController')->name('projects.get');
  Route::post('projects/project_load_select', 'ProjectsController@project_load_select')->name('projects.project_load_select');
  Route::post('projects/search', 'ProjectsController@project_search')->name('projects.project_search');
});
