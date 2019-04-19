<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 19.04.2019
 * Time: 10:08
 */
spl_autoload_register(function ($class_name) {
    /** @noinspection PhpIncludeInspection */
    include $class_name . '.php';
});
