<?php

use Illuminate\Support\Facades\Route;

$middleware = [
    'account_header', // this for check header has account uuid or not
    'jwt.auth'
];

Route::group(['middleware' => $middleware], function () {
    Route::get('/sources', 'PipelineController@sources')->name('task_management.pipeline.sources');
    Route::get('/filters', 'PipelineController@filter')->name('task_management.pipeline.filter');
    Route::get('/actions', 'PipelineController@actions')->name('task_management.pipeline.actions');

    // Route for pipeline template task
    Route::post('/', 'PipelineTemplateTaskController@create')->name('task_management.pipeline_template_task.create');
    Route::put('/', 'PipelineTemplateTaskController@update')->name('task_management.pipeline_template_task.update');
    Route::delete('/', 'PipelineTemplateTaskController@delete')->name('task_management.pipeline_template_task.delete');

    // Route for pipeline templates
    Route::group(['prefix' => '/templates'], function () {
        Route::get('/', 'PipelineTemplateController@list')->name('task_management.pipeline_template.list');
        Route::post('/', 'PipelineTemplateController@create')->name('task_management.pipeline_template.create');
        Route::put('/', 'PipelineTemplateController@update')->name('task_management.pipeline_template.update');
        Route::delete('/', 'PipelineTemplateController@delete')->name('task_management.pipeline_template.delete');
        Route::get('/view', 'PipelineTemplateController@view')->name('task_management.pipeline_template.view');
    });
});
