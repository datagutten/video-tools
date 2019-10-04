<?Php
//find -name \*.no.vtt -exec php /home/video-tools/vtt_to_srt_cli.php {} \;
require __DIR__.'/vendor/autoload.php';
$convert = new vtt_to_srt();
$convert->convert_file($argv[1]);