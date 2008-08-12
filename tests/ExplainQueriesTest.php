<?php
require_once dirname(__FILE__) . '/config.test.php';

require_once 'PHPUnit/Framework.php';
require_once 'MDB2/Debug/ExplainQueries.php';
require_once 'MDB2/Debug/ExplainQueries/HTML.php';
require_once 'MDB2.php';


class ExplainQueriesTest extends PHPUnit_Framework_TestCase
{
    private $db;

    function setUp()
    {
        $this->db = MDB2::singleton(TESTS_DB_DSN);
        $this->db->setOption('debug', true);
    }

    function testExecuteAndExplain()
    {
        $my_debug_handler = new MDB2_Debug_ExplainQueries($this->db);

        $this->db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));
        $this->db->query('SELECT * FROM intranet');
        $my_debug_handler->executeAndExplain();
    }

    function testDumpInfo()
    {
        $my_debug_handler = new MDB2_Debug_ExplainQueries($this->db);
        $this->db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));
        $this->db->query('SELECT * FROM intranet');
        //$my_debug_handler->dumpInfo();
    }

    function testUsingAnotherOutputter()
    {
        $parser = new MDB2_Debug_ExplainQueries_HTML();
        $my_debug_handler = new MDB2_Debug_ExplainQueries($this->db, $parser);
        $this->db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));
        $this->db->query('SELECT * FROM intranet');
        $my_debug_handler->dumpInfo();
    }

}
