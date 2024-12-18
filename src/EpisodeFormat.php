<?php

namespace datagutten\video_tools;

use ArrayAccess;
use datagutten\tools\files\files;
use DateTimeImmutable;

/**
 * Format season and episode
 */
class EpisodeFormat implements ArrayAccess
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

    public function episode_number($episode_name = true): string
    {
        $name = $this->season_format();
        if (!empty($this->episode))
            $name .= sprintf('%s%02d', $this->episode_prefix, $this->episode);

        if (!empty($this->title) && !empty($this->series) && $episode_name)
            if (!empty($name)) //Append episode title
                return sprintf('%s - %s', $name, $this->title);
            else
                return $this->title;
        else
            return $name;
    }

    public function episode_name(): string
    {
        //Prepend series name
        return trim(sprintf('%s %s', $this->series_name(), $this->episode_number()));
    }

    public function file_name(string $extension = ''): string
    {
        if (!empty($extension))
            return filnavn(sprintf('%s.%s', $this->episode_name(), $extension));
        else
            return filnavn($this->episode_name());
    }

    public function folder(): string
    {
        $folder = sprintf('%s %s', $this->series_name(), $this->season_format());
        return filnavn(trim($folder));
    }

    public function file_path($extension = '', string $base_path = '', bool $create_folder = false): string
    {
        if (!empty($base_path))
            $folder = files::path_join($base_path, $this->folder());
        else
            $folder = $this->folder();

        if ($create_folder && !file_exists($folder))
            mkdir($folder, 0777, true);
        return files::path_join($folder, $this->file_name($extension));
    }

    public function offsetExists(mixed $offset): bool
    {
        return !empty($this->$offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->$offset;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->$offset);
    }
}