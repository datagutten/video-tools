<?php
/**
 * Created by PhpStorm.
 * User: abi
 * Date: 19.04.2019
 * Time: 10:08
 */
spl_autoload_register(function ($class_name) {
    $valid_classes = array('srt', 'ttml_to_srt', 'video', 'video_download', 'video_duration_check', 'vtt_to_srt');
    if(array_search($class_name, $valid_classes)!==false) {
        /** @noinspection PhpIncludeInspection */
        include $class_name . '.php';
    }
});

if(file_exists(__DIR__.'/../tools/autoload.php'))
    require __DIR__.'/../tools/autoload.php';