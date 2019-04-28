<?php
class vtt_to_srt extends srt
{
    /**
     * Convert VTT subtitle to SRT
     * @param string $sub vtt subtitle to be converted
     * @return string converted srt subtitle
     */
    public function convert($sub)
    {
        //$sub = preg_replace('/([0-9:]+)\.([0-9]+) --> ([0-9:]+)\.([0-9]+)/', '$1,$2 --> $3,$4', $sub);
        //$sub = str_replace("WEBVTT\n\n", '', $sub);
        //$sub = str_replace("\n", "\r\n", $sub);
        preg_match_all('/([0-9:]+)\.([0-9]+) --> ([0-9:]+)\.([0-9]+)\s(.+)(?:\s\s|\z)/sU', $sub, $lines);
        $count=1;
        $sub='';


        foreach ($lines[0] as $key=>$line)
        {
            $sub .= $key+1 ."\r\n";

            if(strlen($lines[1][$key])===5)
                $lines[1][$key] = '00:' . $lines[1][$key];
            if(strlen($lines[3][$key])===5)
                $lines[3][$key] = '00:' . $lines[3][$key];
            //$sub .= sprintf("%s,%s --> %s,%s\r\n%s\r\n\r\n", $lines[1][$key], $lines[2][$key], $lines[3][$key], $lines[4][$key], $lines[5][$key]);
            $this->add_line($lines[1][$key] . ',' . $lines[2][$key], $lines[3][$key] .','. $lines[4][$key], $lines[5][$key]);
        }
        return $this->sub;
    }

    /**
     * Convert a VTT file to SRT
     * @param string $file VTT file
     * @param bool $overwrite Overwrite output SRT file
     */
    public function convert_file($file, $overwrite = false)
    {
        $this->convert(file_get_contents($file));
        $this->save_file($file, $overwrite);
    }
}