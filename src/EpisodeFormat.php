<?php

namespace datagutten\video_tools;

use datagutten\tools\files\files;
use DateTimeImmutable;
use function PHPUnit\Framework\returnArgument;

/**
 * Format season and episode
 */
class EpisodeFormat implements \ArrayAccess
{
    /**
     * @var string Series name
     */
    public string $series;
    /**
     * @var int Season
     */
    public int $season;
    /**
     * @var int Episode
     */
    public int $episode;
    /**
     * @var int Production year
     */
    public int $year;
    /**
     * @var string Episode title
     */
    public string $title;
    /**
     * @var string Season name
     */
    public string $season_name;

    private string $episode_prefix = 'EP';
    /**
     * @var string Episode description
     */
    public string $description;
    /**
     * @var DateTimeImmutable Episode date
     */
    public DateTimeImmutable $date;

    public function series_name(): string
    {
        if (empty($this->series)) //Not part of a series
        {
            if (isset($this->year))
                return sprintf('%s (%d)', $this->title, $this->year);
            else
                return $this->title;
        }
        else
            return $this->series;
    }

    public function season_format(): string
    {
        $season = '';
        if (!empty($this->season_name)) //Named season
            $season .= sprintf('- %s ', $this->season_name);

        if (!empty($this->season)) //Series has numbered seasons
        {
            $this->episode_prefix = 'E';
            $season .= sprintf('S%02d', $this->season); //Add season number
        }
        return $season;
    }

    public function episode_name(): string
    {
        $name = $this->season_format();
        if (!empty($this->episode))
            $name .= sprintf('%s%02d', $this->episode_prefix, $this->episode);

        if (!empty($this->title))
            if (!empty($name)) //Append episode title
                $name = sprintf('%s - %s', $name, $this->title);

        //Prepend series name
        return trim(sprintf('%s %s', $this->series_name(), $name));
    }

    public function file_name(string $extension = '')
    {
        if (!empty($extension))
            return filnavn(sprintf('%s.%s', $this->episode_name(), $extension));
        else
            return filnavn($this->episode_name());
    }

    public function folder(): string
    {
        $folder = sprintf('%s %s', $this->series_name(), $this->season_format());
        return filnavn($folder);
    }

    public function file_path($extension = ''): string
    {
        return files::path_join($this->folder(), $this->file_name($extension));
    }

    public function offsetExists($offset): bool
    {
        return !empty($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->$offset);
    }
}