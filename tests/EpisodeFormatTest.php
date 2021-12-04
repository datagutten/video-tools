<?php

use datagutten\video_tools\EpisodeFormat;
use PHPUnit\Framework\TestCase;

class EpisodeFormatTest extends TestCase
{
    public function testSimpleSeasonEpisode()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $this->assertEquals('Test Series S02E01', $episode->episode_name());
    }

    public function testEpisodeWithoutSeason()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->series = 'Test Series';
        $this->assertEquals('Test Series EP01', $episode->episode_name());
    }

    public function testEpisodeNoSeries()
    {
        $episode = new EpisodeFormat();
        $episode->title = 'TV Show';
        $this->assertEquals('TV Show', $episode->episode_name());
    }

    public function testEpisodeNoSeriesYear()
    {
        $episode = new EpisodeFormat();
        $episode->title = 'TV Show';
        $episode->year = 2021;
        $this->assertEquals('TV Show (2021)', $episode->episode_name());
    }

    public function testEpisodeWithTitle()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $episode->title = 'Episode name';
        $this->assertEquals('Test Series S02E01 - Episode name', $episode->episode_name());
    }

    public function testNamedSeason()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->series = 'Test Series';
        $episode->season_name = 'Season Name';
        $this->assertEquals('Test Series - Season Name EP01', $episode->episode_name());
    }

    public function testSeasonWithName()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $episode->season_name = 'Season Name';
        $this->assertEquals('Test Series - Season Name S02E01', $episode->episode_name());
    }

    public function testFileName()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $this->assertEquals('Test Series S02E01.mkv', $episode->file_name('mkv'));
    }

    public function testFileNameNoExtension()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $this->assertEquals('Test Series S02E01', $episode->file_name());
    }

    public function testFileAndFolderName()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $this->assertEquals(sprintf('Test Series S02%sTest Series S02E01.mkv', DIRECTORY_SEPARATOR), $episode->file_path('mkv'));
    }

    public function testArrayAccess()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $this->assertSame($episode->episode, $episode['episode']);
        $episode['episode'] = 2;
        $this->assertSame($episode->episode, $episode['episode']);
        $this->assertFalse(isset($episode['season']));
        unset($episode['episode']);
        $this->assertFalse(isset($episode['episode']));
    }
}
