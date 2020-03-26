<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnReceiveInterface;
use Hyperf\Memory\TableManager;
use Swoole\Server as SwooleServer;


class UnixStreamSocketController implements OnReceiveInterface
{
    public function onReceive(SwooleServer $server, int $fd, int $fromId, string $data): void
    {
        $table = TableManager::get('FdTable');
        foreach ($table as $user){
            $server->push($user['fd'], $data, WEBSOCKET_OPCODE_BINARY);
        }

    }

}




