<?php

Route::group(['namespace' => 'advance_payment'], function () {
    Route::resource('advance_payments', 'AdvancePaymentController');
    // data table
    Route::post('advance_payments/get', 'AdvancePaymentTableController')->name('advance_payments.get');
});

Route::group(['namespace' => 'attendance'], function () {
    Route::post('attendances/employees_attendance', 'AttendanceController@employees_attendance')->name('attendances.employees_attendance');
    Route::post('attendances/day_attendance', 'AttendanceController@day_attendance')->name('attendances.day_attendance');
    Route::resource('attendances', 'AttendanceController');
    // data table
    Route::post('attendances/get', 'AttendanceTableController')->name('attendances.get');
});

Route::group(['namespace' => 'leave'], function () {
    Route::post('leave/leave_categories', 'LeaveController@leave_categories')->name('leave.leave_categories');
    Route::resource('leave', 'LeaveController');
    // data table
    Route::post('leave/get', 'LeaveTableController')->name('leave.get');
});

Route::group(['namespace' => 'leave_category'], function () {
    Route::resource('leave_category', 'LeaveCategoryController');
    // data table
    Route::post('leave_category/get', 'LeaveCategoryTableController')->name('leave_category.get');
});

Route::group(['namespace' => 'holiday_list'], function () {
    Route::resource('holiday_list', 'HolidayListController');
    // data table
    Route::post('holiday_list/get', 'HolidayListTableController')->name('holiday_list.get');
});

Route::group(['namespace' => 'hrm'], function () {
    Route::post('hrms/set_permission', 'HrmsController@set_permission')->name('hrms.set_permission');
    Route::get('hrms/payroll', 'HrmsController@payroll')->name('hrms.payroll');
    Route::get('hrms/attendance_new', 'HrmsController@attendance')->name('hrms.attendance');
    Route::get('hrms/attendance_destroy', 'HrmsController@attendance_destroy')->name('hrms.attendance_destroy');
    Route::post('hrms/attendance_destroy', 'HrmsController@attendance_destroy')->name('hrms.attendance_destroy');
    Route::get('hrms/attendance', 'HrmsController@attendance_list')->name('hrms.attendance_list');
    Route::post('hrms/attendance', 'HrmsController@attendance_store')->name('hrms.attendance_store');
    Route::get('hrms/payroll', 'HrmsController@payroll')->name('hrms.payroll');

    Route::post('hrms/related_permission', 'HrmsController@related_permission')->name('hrms.related_permission');
    Route::post('hrms/role_permission', 'HrmsController@role_permission')->name('hrms.role_permission');
    Route::post('hrms/active', 'HrmsController@active')->name('hrms.active');
    Route::post('hrms/admin_permissions', 'HrmsController@admin_permissions')->name('hrms.admin_role_permission');

    Route::resource('hrms', 'HrmsController');
    //For Datatable
    Route::post('hrms/get', 'HrmsTableController')->name('hrms.get');
    Route::post('hrms/get_attendance', 'HrmAttendanceTableController')->name('hrms.get_attendance');
});


Route::group(['namespace' => 'role'], function () {
    Route::resource('role', 'RoleController', ['except' => ['show']]);

    //For DataTables
    Route::post('role/get', 'RoleTableController')->name('role.get');
});
Route::group(['namespace' => 'salary'], function () {
    Route::post('salary/renew_contract', 'SalaryController@renew_contract')->name('salary.renew_contract');
    Route::post('salary/terminate_contract', 'SalaryController@terminate_contract')->name('salary.terminate_contract');
    Route::post('salary/select', 'SalaryController@select')->name('salary.select');
    Route::resource('salary', 'SalaryController');
    //For Datatable
    Route::post('salary/get', 'SalaryTableController')->name('salary.get');
});

Route::group(['namespace' => 'payroll'], function () {
    Route::post('payroll/get_employee', 'PayrollController@get_employee')->name('payroll.get_employee');
    Route::get('page/{id}', 'PayrollController@page')->name('payroll.page');
    Route::post('page/store_basic', 'PayrollController@store_basic')->name('payroll.store_basic');
    Route::post('page/store_allowance', 'PayrollController@store_allowance')->name('payroll.store_allowance');
    Route::post('page/store_deduction', 'PayrollController@store_deduction')->name('payroll.store_deduction');
    Route::post('page/store_paye', 'PayrollController@store_paye')->name('payroll.store_paye');
    Route::post('page/approve_payroll', 'PayrollController@approve_payroll')->name('payroll.approve_payroll');
    Route::resource('payroll', 'PayrollController');
    //For Datatable
    Route::post('payroll/get', 'PayrollTableController')->name('payroll.get');
});
