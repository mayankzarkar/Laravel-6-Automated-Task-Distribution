<?php

use Illuminate\Support\Facades\Route;

$middleware = [
    'account_header', // this for check header has account uuid or not
    'jwt.auth'
];

Route::group(['middleware' => $middleware], function () {
    Route::group(['prefix' => '/users'], function () {
        Route::post('/', 'UserTaskController@create')->name('task_management.user_task.create');
        Route::put('/', 'UserTaskController@update')->name('task_management.user_task.update');
        Route::delete('/', 'UserTaskController@delete')->name('task_management.user_task.delete');
        Route::get('/list', 'UserTaskController@list')->name('task_management.user_task.list');
        Route::get('/view', 'UserTaskController@view')->name('task_management.user_task.view');
        Route::get('/types', 'UserTaskController@types')->name('task_management.user_task.types');
        Route::get('/mark-as-completed', 'UserTaskController@markAsCompleted')->name('task_management.user_task.markAsCompleted');
        Route::get('/{slug}', 'UserTaskController@viewBySlug')->name('task_management.user_task.viewBySlug');
        Route::put('/status', 'UserTaskController@status')->name('task_management.user_task.status');
        Route::post('/note', 'UserTaskController@createNote')->name('task_management.user_task.createNote');
        Route::delete('/note', 'UserTaskController@deleteNote')->name('task_management.user_task.deleteNote');
    });
});
