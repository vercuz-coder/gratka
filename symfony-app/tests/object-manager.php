<?php

declare(strict_types=1);

use App\Kernel;

require dirname(__DIR__).'/config/bootstrap.php';

$kernel = new Kernel('test', true);
$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
