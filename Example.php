<?php
/**
 * This file contains example on running threaded commands
 *
 * @author    Aziz S. Hussain <azizsaleh@gmail.com>
 * @copyright GPL license
 * @license   http://www.gnu.org/copyleft/gpl.html
 * @link      http://www.AzizSaleh.com
 */

require_once (dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Thread.php');

/**
 * Example 1:
 *
 * Simple example to run all commands at once
*/
$thread = new Thread();
$thread->addCommand('php -r "echo 1";');
$thread->addCommand('php -r "echo 2";');
$thread->addCommand('php -r "echo 3";');

// addCommands has a second param default to false to overwrite previous entries
$overWritePrevious = false;
$thread->addCommands(
    array(
        'php -r "echo 4";',
        'php -r "echo 5";'
    ),
    $overWritePrevious
);
$thread->run();

/**
 * Example 2:
 *
 * Run commands based on a template
 */
$thread = new Thread();
$thread->setTemplate('php -r "echo %u";');
for ($x = 0; $x <= 100; $x++) {
    $thread->addCommand($x);
}

// Only allow 1 instance to run
$thread->setBatchCount(1);
$thread->run();

/**
 * Example 3:
 *
 * Run object with method chaining - Same as Example #2
 */
$thread = new $thread();
$thread->setTemplate('php -r "echo %u";')->addCommands(range(0, 100))->setBatchCount(1)->run();