<?php
/**
 * @param string $sub vtt subtitle to be converted
 * @return string converted srt subtitle
 */
function vtt_to_srt($sub)
{
    $sub=preg_replace('/([0-9:]+)\.([0-9]+) --> ([0-9:]+)\.([0-9]+)/','$1,$2 --> $3,$4',$sub);
    $sub=str_replace("WEBVTT\n\n",'',$sub);
    $sub=str_replace("\n","\r\n",$sub);
    return $sub;
}
