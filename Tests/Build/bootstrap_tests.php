<?php
/**
 * Created by PhpStorm.
 * User: stefan
 * Date: 04.04.17
 * Time: 17:36
 */
$standalone_autoloader = dirname(dirname(__DIR__)) . '/vendor/autoload.php';
$dependent_autoloader = dirname(dirname(dirname(dirname(__DIR__)))) . '/autoload.php';

if (is_file($standalone_autoloader)) {
    require_once($standalone_autoloader);
} elseif (is_file($dependent_autoloader)) {
    require_once($dependent_autoloader);
}
