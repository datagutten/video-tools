<?php

namespace datagutten\video_tools\tests;

use datagutten\tools\files\files;
use datagutten\video_tools\EpisodeFormat;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

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

    public function testNamedEpisode()
    {
        $episode = new EpisodeFormat();
        $episode->series = 'Test Series';
        $episode->title = 'Episode Name';
        $this->assertEquals('Test Series Episode Name', $episode->episode_name());
    }

    public function testEpisodeNumber()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';
        $episode->title = 'Episode name';
        $this->assertEquals('S02E01 - Episode name', $episode->episode_number());
        $this->assertEquals('S02E01', $episode->episode_number(false));
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

    public function testCreateFolder()
    {
        $episode = new EpisodeFormat();
        $episode->episode = 1;
        $episode->season = 2;
        $episode->series = 'Test Series';

        $base_path = files::path_join(sys_get_temp_dir(), 'episode_test');
        $filesystem = new Filesystem();
        $filesystem->remove($base_path);

        $path = $episode->file_path('mkv', $base_path);
        $this->assertFileDoesNotExist(dirname($path));
        $path = $episode->file_path('mkv', $base_path, true);
        $this->assertFileExists(dirname($path));
        $filesystem->remove($base_path);
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
