<?php

if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'production');
    define('DEBUGGING', 'false');
    define('APPPATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
    define('BASEPATH', APPPATH . '..'. DIRECTORY_SEPARATOR .'system'. DIRECTORY_SEPARATOR);
}