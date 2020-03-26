<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\Contract\OnCloseInterface;
use Hyperf\Contract\OnMessageInterface;
use Hyperf\Contract\OnOpenInterface;
use Hyperf\Memory\TableManager;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerInterface;
use Swoole\Http\Request;
use Swoole\Server;
use Swoole\Websocket\Frame;
use Swoole\WebSocket\Server as WebSocketServer;

class WebSocketController implements OnMessageInterface, OnOpenInterface, OnCloseInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function onMessage(WebSocketServer $server, Frame $frame): void
    {
    }

    public function onClose(Server $server, int $fd, int $reactorId): void
    {
        if (!TableManager::has('FdTable')) {
            return;
        }
        $table = TableManager::get('FdTable');
        if($table->exist('fd_'.$fd)){
            $table->del('fd_'.$fd);
        }
    }

    public function onOpen(WebSocketServer $server, Request $request): void
    {
        if (!TableManager::has('FdTable')) {
            return;
        }
        $table = TableManager::get('FdTable');
        $table->set('fd_'.$request->fd, ['fd'=>$request->fd]);
    }
}

