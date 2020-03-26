<?php

declare(strict_types=1);

namespace App\Process;

use Hyperf\Di\Annotation\Inject;
use Hyperf\Memory\TableManager;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;
use Hyperf\Process\ProcessCollector;
use mysql_xdevapi\Exception;
use Swoole\Table;

/**
 * @Process(name="camera_live_process")
 */
class CameraLiveProcess extends AbstractProcess
{
    /**
     * 进程数量
     * @var int
     */
    public $nums = 1;

    /**
     * 进程名称
     * @var string
     */
    public $name = 'camera_live_process';

    /**
     * 重定向自定义进程的标准输入和输出
     * @var bool
     */
    public $redirectStdinStdout = false;

    /**
     * 管道类型
     * @var int
     */
    public $pipeType = 1;

    /**
     * 是否启用协程
     * @var bool
     */
    public $enableCoroutine = true;

    /**
     * 执行具体内容
     */
    public function handle(): void
    {
        while (true) {
            try {
                $AllProcess=ProcessCollector::get($this->name);
                foreach($AllProcess as $Process){
                    //本地摄像头
                    if(file_exists('/dev/video0')) {
                        $Process->exec("/usr/local/bin/ffmpeg", [
                            '-loglevel',
                            '16',
                            '-f',
                            'v4l2',
                            '-framerate',
                            '20',
                            '-video_size',
                            '640x480',
                            '-i',
                            '/dev/video0',
                            '-f',
                            'mpegts',
                            '-codec:v',
                            'mpeg1video',
                            '-vf',
                            'transpose=1',
                            '-b:v',
                            '800k',
                            '-bf',
                            '0',
                            'unix:/tmp/sock_camera.sock',
                        ]);
                    } else {
                        //网络摄像头
                        $Process->exec("/usr/local/bin/ffmpeg", [
                            '-loglevel',
                            '16',
                            '-i',
                            'rtsp://192.168.199.159:554/live/main',
                            '-f',
                            'mpegts',
                            '-codec:v',
                            'mpeg1video',
                            '-b:v',
                            '800k',
                            '-bf',
                            '0',
                            'unix:/tmp/sock_camera.sock',
                        ]);
                    }
                }

            } catch (\Exception $e) { // 处理其他异常
                print_r($e->getMessage());
                echo "\n";
            }
            \Swoole\Coroutine::sleep(5);
        }
    }
}
