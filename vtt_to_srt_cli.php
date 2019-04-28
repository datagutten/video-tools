<?Php
//find -name \*.no.vtt -exec php /home/video-tools/vtt_to_srt_cli.php {} \;
require 'autoload.php';
$convert = new vtt_to_srt();
$convert->convert_file($argv[1]);