<?php
/* use 4 spaces as indent */
/**
 * A custom error handler for MDB2
 *
 * PHP Version 5
 *
 * Collects all queries being executed in a script. The
 * collection uses the collectInfo() method.
 *
 * Once the script finishes executing, the executeAndExplain() is
 * called. It exectues all unique SELECTs once again in order
 * to collect info about how much time each query takes.
 *
 * Then executeAndExplain() will execute again this time prepending
 * all SELECTs with EXPLAIN or EXPLAIN EXTENDED which gives
 * the possibility of calling SHOW WARNINGS.
 *
 * <code>
 * require 'MDB2/Debug/ExplainQueries.php';
 * $db = MDB2::singleton($dsn);
 * $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
 * $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));
 * register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
 * register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
 * </code>
 *
 * http://dev.mysql.com/doc/refman/5.1/en/explain.html
 * http://dev.mysql.com/doc/refman/5.1/en/show-warnings.html
 *
 * @author  Stoyan Stefanov <ssttoo at gmail dot com>
 * @since   0.1.0
 * @version @package-version@
 * @link    http://www.phpied.com/performance-tuning-with-mdb2/
 */

/**
 * A custom error handler for MDB2
 *
 * Collects all queries being executed in a script. The
 * collection uses the collectInfo() method.
 *
 * Once the script finishes executing, the executeAndExplain() is
 * called. It exectues all unique SELECTs once again in order
 * to collect info about how much time each query takes.
 *
 * Then executeAndExplain() will execute again this time prepending
 * all SELECTs with EXPLAIN or EXPLAIN EXTENDED which gives
 * the possibility of calling SHOW WARNINGS.
 *
 * <code>
 * require 'MDB2/Debug/ExplainQueries.php';
 * $db = MDB2::singleton($dsn);
 * $db->setOption('debug', true);
 * $my_debug_handler = new MDB2_Debug_ExplainQueries($db);
 * $db->setOption('debug_handler', array($my_debug_handler, 'collectInfo'));
 * register_shutdown_function(array($my_debug_handler, 'executeAndExplain'));
 * register_shutdown_function(array($my_debug_handler, 'dumpInfo'));
 * </code>
 *
 * @author  Stoyan Stefanov <ssttoo at gmail dot com>
 * @author  Lars Olesen <lars@legestue.net>
 *
 * @since   0.1.0
 * @version @package-version@
 * @link    http://www.phpied.com/performance-tuning-with-mdb2/
 */
class MDB2_Debug_ExplainQueries
{
    /**
     * how many queries were executed
     *
     * @var integer
     */
    private $query_count = 0;

    /**
     * which queries and their count
     *
     * @var array
     */
    private $queries = array();

    /**
     * results of EXPLAIN-ed SELECTs
     *
     * @var array
     */
    private $explains = array();

    /**
     * the MDB2 instance
     *
     * @var object
     */
    private $db = false;

    /**
     * Outputter
     *
     * @var object
     */
    private $outputter;

    /**
     * constructor that accepts MDB2 reference
     *
     * @param object $db MDB2 object
     *
     * @return void
     */
    function __construct($db, $outputter = null)
    {
        $this->db        = $db;
        $this->outputter = $outputter;
    }

    /**
     * Called on every query
     *
     * @param object  $db
     * @param object  $scope
     * @param string  $message
     * @param boolean $is_maip
     *
     * @return void
     */
    function collectInfo($db, $scope, $message, $is_manip = null)
    {
        // increment the total number of queries
        $this->query_count++;
        // the SQL is a key in the queries array
        // the value will be the count of how
        // many times each query was executed

        if(!isset($this->queries[$message])) {
            $this->queries[$message] = 1;
        } else {
            @$this->queries[$message]++;
        }
    }

    /**
     * Prints the debug information
     *
     * @return void
     */
    function dumpInfo()
    {
        if (!$this->outputter) {
            return $this->standardOutput();
        }
        echo $this->outputter->parse($this);;
    }


    /**
     * Returns the standard output
     *
     * @return void
     */
    function standardOutput()
    {
        echo '<h3>Queries on this page</h3>';
        echo '<pre>';
        print_r($this->queries);
        echo '</pre>';
        echo '<h3>EXPLAIN-ed SELECTs</h3>';
        echo '<pre>';
        print_r($this->explains);
        echo '</pre>';
    }

    /**
     * the method that will execute all SELECTs
     * with and without an EXPLAIN and will
     * create $this->explains array of debug
     * information
     * SHOW WARNINGS will be called after each
     * EXPLAIN for more information
     *
     * @return void
     */
    function executeAndExplain()
    {
        // at this point, stop debugging
        $this->db->setOption('debug', 0);
        $this->db->loadModule('Extended');

        // take the SQL for all the unique queries
        $queries = array_keys($this->queries);
        foreach ($queries AS $sql) {
            // for all SELECTs…
            $sql = trim($sql);
            if (stristr($sql,"SELECT") !== false){
                // note the start time
                $start_time = array_sum(
                    explode(" ", microtime())
                );
                // execute query
                $this->db->query($sql);
                // note the end time
                $end_time = array_sum(
                    explode(" ", microtime())
                );
                // the time the query took
                $total_time = $end_time - $start_time;

                // now execute the same query with
                // EXPLAIN EXTENDED prepended
                $explain = $this->db->getAll(
                    'EXPLAIN EXTENDED ' . $sql
                );

                $this->explains[$sql] = array();
                // update the debug array with the
                // new data from
                // EXPLAIN and SHOW WARNINGS
                if (!PEAR::isError($explain)) {
                    $this->explains[$sql]['explain'] = $explain;
                    $this->explains[$sql]['warnings'] = $this->db->getAll('SHOW WARNINGS');
                }

                // update the debug array with the
                // count and time
                $this->explains[$sql]['time'] = $total_time;
            }
        }
    }

    /**
     * Gets the queries
     *
     * @return array
     */
    function getQueries()
    {
        return $this->queries;
    }

    /**
     * Gets the explains
     *
     * @return array
     */
    function getExplains()
    {
        return $this->explains;
    }
}