<?php
require 'config.php';

require_once 'PHPUnit/Framework.php';
require_once 'MDB2/Debug/ExplainQueries.php';
require_once 'MDB2/Debug/ExplainQueries/HTML.php';
require_once 'MDB2.php';


$db = MDB2::factory(TESTS_DB_DSN);
$db->setOption('debug', true);
$parser = new MDB2_Debug_ExplainQueries_HTML();
$my_debug_handler = new MDB2_Debug_ExplainQueries($db);
$db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));
$db->query('SELECT * FROM intranet');
$my_debug_handler->executeAndExplain();
$my_debug_handler->dumpInfo();
