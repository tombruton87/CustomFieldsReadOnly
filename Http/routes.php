<?php
// SPDX-License-Identifier: AGPL-3.0-or-later
// Copyright (C) 2024 Tom Bruton

Route::group([
    'middleware' => ['web', 'auth', 'roles'],
    'roles'      => ['admin'],
    'prefix'     => \Helper::getSubdirectory(),
    'namespace'  => 'Modules\CustomFieldsReadOnly\Http\Controllers',
], function () {
    Route::post('/custom-fields-readonly/ajax-admin', [
        'uses'     => 'CustomFieldsReadOnlyController@ajaxAdmin',
        'laroute'  => true,
    ])->name('customfieldsreadonly.ajax_admin');
});
