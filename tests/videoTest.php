<?php

namespace datagutten\video_tools\tests;

use datagutten\tools\files\files;
use datagutten\video_tools\exceptions\DurationNotFoundException;
use datagutten\video_tools\video;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class videoTest extends TestCase
{
    /**
     * @var string
     */
    private $test_file;
    /**
     * @var array
     */
    private $test_files;

    function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->test_file = files::path_join(__DIR__, 'test_data', 'Reklame Kornmo Treider 41.mp4');
        foreach (['mp4', 'mkv'] as $extension)
        {
            $this->test_files[$extension] = files::path_join(__DIR__, 'test_data', 'sample.' . $extension);
            if (!file_exists($this->test_files[$extension]))
                copy('http://techslides.com/demos/samples/sample.' . $extension, $this->test_files[$extension]);
        }
    }

    public function extensionProvider()
    {
        $extensions = [];
        foreach (array_keys($this->test_files) as $extension)
        {
            $extensions[] = [$extension];
        }
        return $extensions;
    }

    public function testSeconds_to_time()
    {
        $time = video::seconds_to_time(80);
        $this->assertEquals('00:01:20', $time);

        $time = video::seconds_to_time(90);
        $this->assertEquals('00:01:30', $time);

        $time = video::seconds_to_time(3680);
        $this->assertEquals('01:01:20', $time);
    }

    /**
     * @dataProvider extensionProvider
     * @param $extension
     * @throws DependencyFailedException
     * @throws DurationNotFoundException
     */
    function testDuration($extension)
    {
        $duration = video::duration($this->test_files[$extension]);
        $this->assertSame(5, $duration);
    }

    function testDurationMediainfo()
    {
        if (PHP_OS == 'WINNT')
            $this->markTestSkipped('mediainfo does not work on windows');
        $duration = video::duration($this->test_file, 'mediainfo');
        $this->assertSame(49, $duration);
    }
    function testSnapshotSteps()
    {
        $steps = video::snapshotsteps($this->test_file);
        $this->assertSame([9,18,27,36], $steps);
    }

    function testSnapshots()
    {
        $steps = video::snapshotsteps($this->test_file);
        $snapshots = video::snapshots($this->test_file, $steps);
        $path = files::path_join(__DIR__, 'test_data', 'snapshots', 'Reklame Kornmo Treider 41.mp4');
        $snapshots_expected = [
            files::path_join($path, '0009.png'),
            files::path_join($path, '0018.png'),
            files::path_join($path, '0027.png'),
            files::path_join($path, '0036.png')
        ];
        $this->assertSame($snapshots_expected, $snapshots);
        foreach($snapshots as $snapshot)
        {
            $this->assertFileExists($snapshot);
        }
    }

    function testSnapshotsPath()
    {
        $path = files::path_join(__DIR__, 'test_data', 'snapshots');
        $filesystem = new filesystem();
        $filesystem->remove($path);

        $steps = video::snapshotsteps($this->test_file);

        $snapshots = video::snapshots($this->test_file, $steps, $path);
        $snapshots_expected = [
            files::path_join($path, '0009.png'),
            files::path_join($path, '0018.png'),
            files::path_join($path, '0027.png'),
            files::path_join($path, '0036.png')
        ];
        $this->assertSame($snapshots_expected, $snapshots);
        foreach($snapshots as $snapshot)
        {
            $this->assertFileExists($snapshot);
        }
    }

    function testParseEpisode()
    {
        $this->assertSame(['season' => 1, 'episode' => 2], video::parse_episode('S01E02'));
        $this->assertSame(['season' => 1], video::parse_episode('S01'));
        $this->assertSame(['episode' => 1], video::parse_episode('EP01'));
    }

    function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(files::path_join(__DIR__, 'snapshots'));
    }
}
