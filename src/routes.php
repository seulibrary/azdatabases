<?php

Route::group(['prefix' => config('azdatabases.slug'), 'namespace' => 'Seumunday\\Azdatabases'], function () {
    Route::get('/', 'AzdatabasesController@index');
    // Get All DBs
    Route::get('list', 'AzdatabasesController@list');
    // Get All DBs by letter
    Route::get('az/{letter}', 'AzdatabasesController@letter')->where('letter', '[A-Za-z]');
    // Get All areas and Subjects if passed in.
    Route::get('area/{area?}/{subject?}', 'AzdatabasesController@area');
    // Get All Areas and Subjects for navigation
    Route::get('navigation', 'AzdatabasesController@navigation');
    // DB Search
    Route::get('search/{query}', 'AzdatabasesController@search');
});
