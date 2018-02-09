<?php

Route::get('validator_builder', "\\thinkphp5\\validator\\Builder@index");
Route::post('validator_builder/add', "\\thinkphp5\\validator\\Builder@add");

define('VALIDATOR_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('VALIDATOR_VIEW_PATH', VALIDATOR_PATH . '..' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR);
define('VALIDATOR_TEMPLATE_PATH', VALIDATOR_PATH . '..' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR);
