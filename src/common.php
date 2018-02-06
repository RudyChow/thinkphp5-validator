<?php

\think\Route::get('validator_builder', "\\thinkphp5\\validator\\Builder@index");
\think\Route::post('validator_builder/add', "\\thinkphp5\\validator\\Builder@add");

define('VALIDATOR_PATH', __DIR__ . DS);
define('VALIDATOR_VIEW_PATH', VALIDATOR_PATH . '..' . DS . 'view' . DS);
define('VALIDATOR_TEMPLATE_PATH', VALIDATOR_PATH . '..' . DS . 'template' . DS);
define('VALIDATOR_OUTPUT_PATH', APP_PATH . 'common' . DS . 'validate' . DS);
