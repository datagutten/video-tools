<?php


namespace datagutten\video_tools\subtitles;


class vtt_to_srt extends srt
{
    /**
     * Convert VTT subtitle to SRT
     * @param string $sub vtt subtitle to be converted
     * @return string converted srt subtitle
     */
    public function convert(string $sub): string
    {
        $sub = preg_replace('/([0-9:]+)\.([0-9]+) --> ([0-9:]+)\.([0-9]+)/', '$1,$2 --> $3,$4', $sub);
        $this->sub = str_replace("WEBVTT\r\n\r\n", '', $sub);
        $this->sub .= "\r\n";
        return $this->sub;
    }

    /**
     * Convert a VTT file to SRT
     * @param string $file VTT file
     * @param bool $overwrite Overwrite output SRT file
     */
    public static function convert_file($file, $overwrite = false)
    {
        $converter = new static();
        $converter->convert(file_get_contents($file));
        return $converter->save_file($file, $overwrite);
    }
}