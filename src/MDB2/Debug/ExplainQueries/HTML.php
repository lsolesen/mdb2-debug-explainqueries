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

require_once 'HTML/Table.php';

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
class MDB2_Debug_ExplainQueries_HTML
{
    /**
     * To update this object
     *
     * @param object  $debug MDB2_Debug_ExplainQueries object
     *
     * @return void
     */
    function parse($debug)
    {
        $query_table = new HTML_Table;

        foreach ($debug->getQueries() as $query => $count) {
            $query_table->addRow(array($query, $count));
        }

        $explains = '';

        foreach ($debug->getExplains() as $query => $explain) {
            $explain_table = new HTML_Table;
            $explain_table->setCaption($query);
            foreach ($explain as $key => $array) {
                switch ($key) {
                case 'explain':
                    $explain_table->addRow($array);
                break;
                case 'warnings':
                    $explain_table->addRow($array);
                break;
                case 'time':
                    $explain_table->addRow(array('Time' => $array));
                break;
                }
            }
            $explains .= $explain_table->toHTML();
        }

        return $query_table->toHTML() . $explains;
    }
}
