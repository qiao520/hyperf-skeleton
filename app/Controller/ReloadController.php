<?php
declare(strict_types=1);

namespace App\Controller;

use Hyperf\HttpServer\Annotation\AutoController;
use Hyperf\Server\ServerFactory;

/**
 * Class ReloadController
 * @AutoController()
 * @package App\Controller
 */
class ReloadController extends Controller
{
    public function index()
    {
        $serverFactory = $this->container->get(ServerFactory::class);
        $swServer = $serverFactory->getServer()->getSwServer();
        $reloadStatus = $swServer->reload();

        return [
            'reloadStatus' => $reloadStatus,
        ];
    }
}
