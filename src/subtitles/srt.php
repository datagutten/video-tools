<?php
/**
 * Created by PhpStorm.
 * User: Anders
 * Date: 22.04.2019
 * Time: 09.48
 */


namespace datagutten\video_tools\subtitles;


use datagutten\tools\files\files;

class srt
{
    public $counter = 1;
    public $sub='';
    public function add_line($start, $end ,$text)
    {
        if (!str_contains($text, "\r"))
            $text = trim(str_replace("\n", "\r\n", $text));
        else
            $text = trim($text);

        $this->sub .= $this->counter ."\r\n";

        if(strlen($start)===5)
            $start = '00:' . $start;
        if(strlen($end)===5)
            $end = '00:' . $end;
        $this->sub .= sprintf("%s --> %s\r\n%s\r\n\r\n", $start, $end, $text);
        $this->counter++;
    }

    /**
     * @param string $filename Filename to save the output file as, with or without extension
     * @param bool $overwrite Overwrite output SRT file
     */
    public function save_file($filename, $overwrite = false)
    {
        $pathinfo = pathinfo($filename);
        if($pathinfo['extension']!=='srt')
            $filename = files::path_join($pathinfo['dirname'], $pathinfo['filename'].'.srt');

        if(!file_exists($filename) || $overwrite)
        {
            file_put_contents($filename, $this->sub);
            return $filename;
        }
        return null;
    }
}
