<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace datagutten\video_tools\tests;

use datagutten\tools\files\files;
use datagutten\video_tools\exceptions\DurationNotFoundException;
use datagutten\video_tools\video;
use datagutten\video_tools\VideoTestData;
use DateInterval;
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
            $this->test_files[$extension] = VideoTestData::download_file($extension);
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

        $time = video::seconds_to_time(135.493);
        $this->assertEquals('00:02:15', $time);
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

    public function testChapters()
    {
        $interval = new DateInterval('PT1S');
        $interval->f = 0.5;
        $points = [
            ['chapter1',$interval],
            ['chapter2',  new DateInterval('PT1M2S')]
        ];
        $chapters = video::mkvmerge_chapters($points);
        $this->assertStringContainsString('CHAPTER01=00:00:01:500', $chapters);
        $this->assertStringContainsString('CHAPTER02=00:01:02:000', $chapters);
    }

    function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(files::path_join(__DIR__, 'snapshots'));
    }
}
