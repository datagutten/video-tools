<?Php
//find -name \*.no.vtt -exec php /home/video-tools/vtt_to_srt_cli.php {} \;
use datagutten\video_tools\subtitles\vtt_to_srt;

require __DIR__.'/vendor/autoload.php';
$convert = new vtt_to_srt();
$convert->convert_file($argv[1]);