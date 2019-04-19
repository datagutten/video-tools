<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 19.04.2019
 * Time: 10:08
 */
spl_autoload_register(function ($class_name) {
    $valid_classes = array('ttml_to_srt', 'video', 'video_download', 'video_duration_check');
    if(array_search($class_name, $valid_classes)!==false) {
        /** @noinspection PhpIncludeInspection */
        include $class_name . '.php';
    }
});
