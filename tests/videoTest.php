<?php


use datagutten\tools\files\files;
use datagutten\video_tools\video;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class videoTest extends TestCase
{
    /**
     * @var string
     */
    private $test_file;

    function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->test_file = files::path_join(__DIR__, 'test_data', 'Reklame Kornmo Treider 41.mp4');
    }

    function testDuration()
    {
        $duration = video::duration($this->test_file);
        $this->assertSame(49, $duration);
    }
    function testDurationMediainfo()
    {
        if(PHP_OS=='WINNT')
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
        $snapshot_folder = files::path_join(__DIR__, 'snapshots');
        $snapshots = video::snapshots($this->test_file, $steps, $snapshot_folder);
        $path = files::path_join($snapshot_folder, 'Reklame Kornmo Treider 41.mp4');
        $snapshots_expected = [
            files::path_join($path, '0009.png'),
            files::path_join($path, '0018.png'),
            files::path_join($path, '0027.png'),
            files::path_join($path, '0036.png')
        ];
        $this->assertSame($snapshots_expected, $snapshots);
    }

    function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(files::path_join(__DIR__, 'snapshots'));
    }
}
