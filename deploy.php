<?php
require_once 'Salty/PEAR/Server/RemoteReleaseDeployer.php';

$d = new Salty_PEAR_Server_RemoteReleaseDeployer();
$d->adminuri = 'http://public.intraface.dk/frontend.php';
$d->username = $_SERVER['argv'][1];
$d->password = $_SERVER['argv'][2];

if ($d->deployRelease($_SERVER['argv'][3])) {
    exit('Success!');
} else {
    exit('Something went terribly wrong.');
}