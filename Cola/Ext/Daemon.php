<?php
/**
 * Test
class TestDaemon extends Cola_Ext_Daemon
{
    protected $_options = array(
        'maxTimes' => 3
    );
    public function main()
    {
        file_put_contents('/tmp/TestDaemon.txt', date('Y-m-d H:i:s') . "\n", FILE_APPEND | LOCK_EX);
        sleep(5);
    }
}

$daemon = new TestDaemon();
$daemon->run();
 *
 */
abstract class Cola_Ext_Daemon
{
    const LOG_ECHO = 1;
    const LOG_FILE = 2;

    /**
     * Daemon options
     *
     * @var array
     */
    protected $_options = array();

    /**
     * Signal handlers
     *
     * @var array
     */
    protected $_sigHandlers = array();

    /**
     * Things todo before main()
     *
     * @var array
     */
    protected $_todos = array();

    /**
     * Iteration counter
     *
     * @var int
     */
    protected $_cnt = 0;

    /**
     * Daemon PID
     *
     * @var int
     */
    protected $_pid;

    protected $_exit = false;

    public function __construct()
    {
        $defaults = array(
            'chuser' => false,
            'uid' => 99,
            'gid' => 99,
            'maxTimes' => 0,
            'maxMemory' => 0,
            'limitMemory' => -1,
            'log' => '/tmp/' . get_class($this) . '.log',
            'pid' => '/tmp/' . get_class($this) . '.pid',
            'help' => "Usage:\n\n{$_SERVER['_']} " . __FILE__ . " start|stop|restart|status|help\n\n",
        );

        $this->_options += $defaults;

        set_error_handler(array($this, 'errorHandler'));
        register_shutdown_function(array($this, 'shutdown'));

        ini_set('memory_limit', $this->_options['limitMemory']);
        ini_set('display_errors', 'Off');
        clearstatcache();
    }

    /**
     * Handle commands from cli
     *
     * start: start the daemon
     * stop: stop the daemon
     * restart: restart the daemon
     * status: print the daemon status
     * --help: print help message
     * -h: print help message
     *
     */
    public function run()
    {
        global $argv;
        if (empty($argv[1]) || !in_array($argv[1], array('start', 'stop', 'restart', 'status', 'help'))) {
            $argv[1] = 'help';
        }

        $action = $argv[1];
        $this->$action();
    }

    /**
     * Get daemon pid number
     *
     * @return mix, false where not running
     */
    public function pid()
    {
        if (!file_exists($this->_options['pid'])) return false;
        $pid = intval(file_get_contents($this->_options['pid']));
        return file_exists("/proc/{$pid}") ? $pid : false;
    }

    /**
     * Daemon main function
     *
     */
    abstract public function main();

    /**
     * Start Daemon
     *
     */
    public function start()
    {
        $this->log('Starting daemon...', self::LOG_ECHO | self::LOG_FILE);

        $this->_daemonize();

        $this->log('Daemon #' . $this->pid() . ' is running', self::LOG_ECHO | self::LOG_FILE);

        declare(ticks = 1) {
            while (!$this->_exit) {
                $this->_autoRestart();
            	$this->_todo();
                if ($this->_exit) break;
                try {
                    $this->main();
                } catch (Exception $e) {
                    $this->log($e->getMessage(), self::LOG_FILE);
                }

            }
        }
    }

    /**
     * Stop Daemon
     *
     */
    public function stop()
    {
        if (!$pid = $this->pid()) {
            $this->log('Daemon is not running', self::LOG_ECHO);
            exit();
        }

        posix_kill($pid, SIGTERM);
    }

    /**
     * Restart Daemon
     *
     */
    public function restart()
    {
        if (!$pid = $this->pid()) {
            $this->log('Daemon is not running', self::LOG_ECHO);
            exit();
        }

        posix_kill($pid, SIGHUP);
    }

    /**
     * Get Daemon status
     *
     */
    public function status()
    {
        if ($pid = $this->pid()) {
            $msg = "Daemon #{$pid} is running";
        } else {
            $msg = "Daemon is not running";
        }

        $this->log($msg, self::LOG_ECHO);
    }

    /**
     * Print help message
     *
     */
    public function help()
    {
        echo $this->_options['help'];
    }

    /**
     * Daemon log
     *
     * @param string $msg
     * @param int $io, 1->just echo, 2->just write, 3->echo & write
     */
    public function log($msg, $io = self::LOG_FILE)
    {
        $datetime = date('Y-m-d H:i:s');
        $msg = "[{$datetime}] {$msg}\n";

        if ((self::LOG_ECHO & $io) && !$this->_pid) {
            echo $msg, "\n";
        }

        if (self::LOG_FILE & $io) {
            file_put_contents($this->_options['log'], $msg, FILE_APPEND | LOCK_EX);
        }
    }

    /**
     * Default signal handler
     *
     * @param int $signo
     */
    public function defaultSigHandler($signo)
    {
        switch ($signo) {
            case SIGTERM:
            case SIGQUIT:
            case SIGINT:
                $this->_todos[] = array(array($this, '_stop'));
                break;
            case SIGHUP:
                $this->_todos[] = array(array($this, '_restart'));
                break;
            default:
                break;
        }
    }

    /**
     * Regist signo handler
     *
     * @param int $signo
     * @param callback $action
     */
    public function regSigHandler($signo, $action)
    {
        $this->_sigHandlers[$signo] = $action;
    }

    /**
     * Daemonize
     *
     */
    protected function _daemonize()
    {
        if (!$this->_check()) {
            exit();
        }

        if (!$this->_fork()) {
            exit();
        }

        $this->_sigHandlers += array(
            SIGTERM => array($this, 'defaultSigHandler'),
            SIGQUIT => array($this, 'defaultSigHandler'),
            SIGINT  => array($this, 'defaultSigHandler'),
            SIGHUP  => array($this, 'defaultSigHandler'),
        );

        foreach ($this->_sigHandlers as $signo => $callback) {
            pcntl_signal($signo, $callback);
        }

        file_put_contents($this->_options['pid'], $this->_pid);
    }

    /**
     * Check environments
     *
     */
    protected function _check()
    {
        if ($pid = $this->pid()) {
            $this->log("Daemon #{$pid} has already started", self::LOG_ECHO);
            return false;
        }

        $dir = dirname($this->_options['pid']);
        if (!is_writable($dir)) {
            $this->log("you do not have permission to write pid file @ {$dir}", self::LOG_ECHO);
            return false;
        }

        if (!is_writable($this->_options['log']) || !is_writable(dirname($this->_options['log']))) {
            $this->log("you do not have permission to write log file: {$this->_options['log']}", self::LOG_ECHO);
            return false;
        }

        if (!defined('SIGHUP')) { // Check for pcntl
            $this->log('PHP is compiled without --enable-pcntl directive', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        if ('cli' !== php_sapi_name()) { // Check for CLI
            $this->log('You can only create daemon from the command line (CLI-mode)', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        if (!function_exists('posix_getpid')) { // Check for POSIX
            $this->log('PHP is compiled without --enable-posix directive', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        return true;
    }

    /**
     * Fork
     *
     * @return boolean
     */
    protected function _fork()
    {
        $pid = pcntl_fork();

        if (-1 == $pid) { // error
            $this->log('Could not fork', self::LOG_ECHO | self::LOG_FILE);
            return false;
        }

        if ($pid) { // parent
            exit();
        }

        // children
        $this->_pid = posix_getpid();
        posix_setsid();

        return true;
    }

    /**
     * Run things before iteration
     *
     */
    protected function _todo()
    {
        foreach ($this->_todos as $row) {
            (1 === count($row)) ? call_user_func($row[0]) : call_user_func_array($row[0], $row[1]);
        }
    }

    /**
     * Stop daemon
     *
     * @param boolean $exit
     * @return mixed
     */
    protected function _stop()
    {
        if (!is_writeable($this->_options['pid'])) {
            $this->log('Daemon (no pid file) not running', self::LOG_ECHO);
            return false;
        }

        $pid = $this->pid();
        unlink($this->_options['pid']);
        $this->log('Daemon #' . $pid . ' has stopped', self::LOG_ECHO | self::LOG_FILE);
        $this->_exit = true;
    }

    /**
     * Restart daemon
     *
     */
    protected function _restart()
    {
        global $argv;
        $this->_stop();
        $this->log('Daemon is restarting...', self::LOG_ECHO | self::LOG_FILE);
        $cmd = $_SERVER['_'] . ' ' . implode(' ', $argv);
        $cmd = trim($cmd, ' > /dev/null 2>&1 &') . ' > /dev/null 2>&1 &';
        shell_exec($cmd);
    }

    /**
     * Check auto restart
     *
     */
    protected function _autoRestart()
    {
        if (
            (0 !== $this->_options['maxTimes'] && $this->_cnt >= $this->_options['maxTimes'])
            || (0 !== $this->_options['maxMemory'] && memory_get_usage(true) >= $this->_options['maxMemory'])
           ) {
            $this->_todos[] = array(array($this, '_restart'));
            $this->_cnt = 0;
        }

        $this->_cnt ++;
    }

    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $this->log(implode('|', array($errno, $errstr, $errfile, $errline)), self::LOG_FILE);
        return true;
    }

    /**
     * Shutdown clean up
     *
     */
    public function shutdown()
    {
        if ($error = error_get_last()) {
            $this->log(implode('|', $error), self::LOG_FILE);
        }

        if (is_writeable($this->_options['pid']) && $this->_pid) {
            unlink($this->_options['pid']);
        }
    }
}