<?php


use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class videoTest extends TestCase
{
    function testDuration()
    {
        $duration = video::duration(__DIR__.'/test_data/Reklame Kornmo Treider 41.mp4');
        $this->assertSame(49, $duration);
    }
    function testDurationMediainfo()
    {
        if(PHP_OS=='WINNT')
            $this->markTestSkipped('mediainfo does not work on windows');
        $duration = video::duration(__DIR__.'/test_data/Reklame Kornmo Treider 41.mp4', 'mediainfo');
        $this->assertSame(49, $duration);
    }
    function testSnapshotSteps()
    {
        $steps = video::snapshotsteps(__DIR__.'/test_data/Reklame Kornmo Treider 41.mp4');
        $this->assertSame([9,18,27,36], $steps);
    }
    function testSnapshots()
    {
        $steps = video::snapshotsteps(__DIR__.'/test_data/Reklame Kornmo Treider 41.mp4');
        $snapshots = video::snapshots(__DIR__.'/test_data/Reklame Kornmo Treider 41.mp4', $steps, __DIR__.'/snapshots');
        $path = __DIR__.'/snapshots/Reklame Kornmo Treider 41.mp4';
        $snapshots_expected = [$path.'/0009.png', $path.'/0018.png', $path.'/0027.png', $path.'/0036.png'];
        $this->assertSame($snapshots_expected, $snapshots);
    }

    function tearDown(): void
    {
        $filesystem = new Filesystem();
        $filesystem->remove(__DIR__.'/snapshots');
    }
}
