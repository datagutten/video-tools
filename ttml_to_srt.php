<?Php
class ttml_to_srt
{
    public $datetime;

    /**
     * ttml_to_srt constructor.
     * @throws Exception
     */
    function __construct()
    {
        $this->datetime = new DateTime('now', new DateTimeZone('UTC'));
    }

    /**
     * Calculate end time from start time and duration
     * @param string $start Start time
     * @param string $duration Duration
     * @return string End time
     * @throws Exception
     */
    public function end_time($start, $duration)
    {
        $date = $this->datetime;
        $start = $date->createFromFormat('H:i:s.u', $start);

        preg_match('/([0-9]+):([0-9]+):([0-9]+)\.([0-9]+)/', $duration, $dur);

        $dur_format = sprintf('PT%dH%dM%dS', $dur[1], $dur[2], $dur[3]);
        $interval = new DateInterval($dur_format);
        $interval->f = '.' . $dur[4];

        return $start->add($interval)->format('H:i:s.u');
    }

    /**
     * Convert ttml to srt
     * @param string $ttml TTML file as string
     * @return string SRT file as string
     * @throws Exception
     */
    public function convert($ttml)
    {
        $srt = '';
        $xml = simplexml_load_string($ttml);
        $count = 1;
        foreach ($xml->{'body'}->{'div'}->{'p'} as $line) {
            $attributes = $line->attributes();
            $end = $this->end_time($attributes['begin'], $attributes['dur']);
            $time = sprintf('%s --> %s', $attributes['begin'], substr($end, 0, -3));
            $time = str_replace('.', ',', $time);
            $srt .= $count . "\r\n";
            $srt .= $time . "\r\n";

            if ($line->count() === 0) {
                $text = (string)$line;
                $srt .= $text . "\r\n";
            } else {
                $child_num = 1;
                foreach ($line->children() as $tag => $child) {
                    $string = $line->saveXML();
                    $attributes = $child->attributes();

                    if ($tag === 'span' && $attributes['style'] == 'italic')
                        $srt .= sprintf("<i>%s</i>\r\n", $child);
                    elseif ($tag === 'br') {
                        $br = $child->saveXML();
                        if ($child_num === 1 && $line->count() > 1) //Line break is the first child
                        {
                            $text = substr($string, 0, strpos($string, $br));
                            $text = strip_tags($text);
                            $srt .= $text . "\r\n";
                        } elseif ($child_num > 1 && $child_num === $line->count()) //Line break is the last child
                        {
                            $text = substr($string, strpos($string, $br));
                            $text = strip_tags($text);
                            $srt .= $text . "\r\n";
                        } elseif ($line->count() === 1) //Line break is the only child
                        {
                            $text = str_replace($child->saveXML(), "\r\n", $string);
                            $text = strip_tags($text);
                            $srt .= $text . "\r\n";
                        }
                        /*else
                        {
                            var_dump($string);
                            throw new Exception('Unhandled line break combination: '.$child->saveXML());
                        }*/
                    } else
                        throw new Exception('Unable to parse child: ' . $child->saveXML());
                    $child_num++;
                }
            }

            $srt .= "\r\n";
            $count++;
        }
        return $srt;
    }

    /**
     * Convert a TTML file to SRT
     * The new file is saved with same name, but the extension is replaced
     * @param string $file ttml file to be converted
     * @throws Exception
     */
    public function convert_file($file)
    {
        $ttml = file_get_contents($file);
        $srt = $this->convert($ttml);
        $file = str_replace('ttml', 'srt', $file);
        file_put_contents($file, $srt);
    }
}