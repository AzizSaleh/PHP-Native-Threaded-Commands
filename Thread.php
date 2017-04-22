<?php
/**
 * This file contains the Threaded class
 *
 * @author    Aziz S. Hussain <azizsaleh@gmail.com>
 * @copyright GPL license
 * @license   http://www.gnu.org/copyleft/gpl.html
 * @link      http://www.AzizSaleh.com
 */

/**
 * Thread
 *
 * Thread class
 *
 * Uses proc_open to handle the connections
 *
 * @author    Aziz S. Hussain <azizsaleh@gmail.com>
 * @copyright GPL license
 * @license   http://www.gnu.org/copyleft/gpl.html
 * @link      http://www.AzizSaleh.com
 * @extends   MY_Controller
 */
class Thread
{
    /**
     * Commands to run
     *
     * @var array
     */
    public $commands = array();

    /**
     * Running threads
     *
     * @var array
     */
    protected $_threads = array();

    /**
     * Result streams
     *
     * @var array
     */
    protected $_resultStream = array();

    /**
     * Description relationship
     *
     * @var array
     */
    protected $_descRealtion = array();

    /**
     * Pipe options
     *
     * @var array
     */
    protected $_options = array(1 => array('pipe', 'w'));

    /**
     * Batch run - Limit to x number of threads in any moment
     *
     * @var int
     */
    protected $_batchCount = PHP_INT_MAX;

    /**
     * Template
     *
     * @var string
     */
    protected $_template = '';

    /**
     * Set template
     *
     * @param string $template
     *
     * @return Thread
     */
    public function setTemplate($template)
    {
        $this->_template = $template;

        return $this;
    }

    /**
     * Set batch count
     *
     * @param int|false $count
     *
     * @return Thread
     */
    public function setBatchCount($count)
    {
        if (!is_numeric($count) && $count !== false) {
            $this->_log('Batch count must be a number or set to false to disable', true);
        }

        $this->_batchCount = $count;

        return $this;
    }

    /**
     * Add command to run
     *
     * @param string $command
     *
     * @return Thread
     */
    public function addCommand($command)
    {
        $this->commands[] = $command;

        return $this;
    }

    /**
     * Add multiple commands to run
     *
     * @param array $commands
     * @param boolean $overWrite
     *
     * @return Thread
     */
    public function addCommands($commands, $overWrite = false)
    {
        if (!is_array($commands)) {
            $this->_log('addCommands must be passed an array of commands', true);
        }

        if ($overWrite === true) {
            $this->commands = $commands;
        } else {
            $this->commands = array_merge($this->commands, $commands);
        }

        return $this;
    }

    /**
     * Run commands
     */
    public function run()
    {
         // No commands to run
        if (empty($this->commands)) {
            $this->_log('No commands to run', true);
        }

        while(true) {
            // No more commands to run
            if (empty($this->commands)) {
                break;
            }

            // Start workers
            while (true) {

                // Any commands left to do?
                if (count($this->commands) > 0 && count($this->_threads) < $this->_batchCount) {

                    $currentCommand = array_shift($this->commands);

                    // Construct the process command with process ID & current db to use
                    if (empty($this->_template)) {
                        $command = $currentCommand;
                    } else {
                        $command = sprintf($this->_template, $currentCommand);
                    }

                    $pipes = array();

                    // Open thread
                    $this->_log('Starting Command: ' . $currentCommand);
                    $this->_threads[] = proc_open($command, $this->_options, $pipes);
                    $this->_resultStream[] = $pipes;
                    $this->_descRealtion[] = $currentCommand;

                    // If this thread started
                    if (end($this->_threads) == false) {
                        // If it fails, close the thread & pipe
                        $closeCount = count($this->_threads)-1;
                        unset($this->_threads[$closeCount]);
                        unset($this->_resultStream[$closeCount]);
                        unset($this->_descRealtion[$closeCount]);

                        // Put table back in if failed
                        array_unshift($this->commands, $currentCommand);
                    }
                } else if (count($this->_threads) <= 0) {
                    break;
                }

                foreach($this->_threads as $sub => $thisThread) {
                    // Get the status
                    $status = proc_get_status($thisThread);
                    // If its not running or stopped, close it & get the results
                    if ($status['running'] != 'true' || $status['signaled'] == 'true') {

                        $results = stream_get_contents($this->_resultStream[$sub][1]);
                        $exitCode = proc_close($thisThread);

                        // Any errors
                        if ($exitCode != 0) {
                            $this->_log('Error Processing Command: ' .
                                $this->_descRealtion[$sub] .
                                ' | Results: [' . $results  . ']');
                        } else {
                            $this->_log('Completed Command: ' .
                                $this->_descRealtion[$sub] .
                                ' | Results: [' . $results  . ']');
                        }

                        $this->_log( count($this->commands) . ' Commands Remaining' . PHP_EOL );

                        // Close the pipe & threads
                        unset($this->_threads[$sub]);
                        unset($this->_resultStream[$sub]);
                        unset($this->_descRealtion[$sub]);
                    }
                }
            }
        }
    }

    /**
     * Log messages
     *
     * @param string $message
     * @param boolean $exit
     */
    protected function _log($message, $exit = false)
    {
        echo $message . PHP_EOL;

        if ($exit === true) {
            exit(1);
        }
    }
}