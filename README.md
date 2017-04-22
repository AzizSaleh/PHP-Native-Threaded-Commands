# PHP-Native-Threaded-Commands
Native OOP Command Threading for PHP >= 5.0 using proc_open

# What does it do?
It allows you to run multiple command at the same time with the flexibility of restricting the number of batch runs.

# Requirements
Only requirement is proc_open, which is available from PHP 4.3.0, however due to OOP restrictions it needs at minimum PHP >= 5.0. If needed you can update the object to work with lower versions easily.

# What is contained in this repository?
Two files, Thread.php which contains the main object, and Example.php which contains some examples also listed on the readme.

# Examples
```
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
$thread->setTemplate
```