<?Php
//find -name \*.no.vtt -exec php /home/video-tools/vtt_to_srt.php {} \;
require 'vtt_to_srt.php';
$sub=file_get_contents($argv[1]);
$sub=vtt_to_srt($sub);
$argv[1]=str_replace('.vtt','',$argv[1]);

if(!file_exists($argv[1].'.srt'))
	file_put_contents($argv[1].'.srt',$sub);