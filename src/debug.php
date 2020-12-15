<?php
namespace lyhiving\debug;

class debug
{
    public $buffer;
    /** cli argv */
    private $_argv = [];
    /** 日志数组 */
    private $_logs = [];

    private $_iscli = 0;

    /** log级别 */
    private $_log_level = 1;

    private $_debug_pre;

    private $_logs_buffer = '';

    private $_logs_error = '';

    private $_logs_exception = '';

    /** header idx */
    private $_header_idx = 0;

    /** 输出文件名 */
    private $_filename = '';

    /** 是否输出 buffer */
    private $_buffer = true;

    /** 是否输出 get */
    private $_get = true;

    /** 是否输出 post */
    private $_post = true;

    /** 是否输出 cookie */
    private $_cookie = true;

    /** 是否输出 session */
    private $_session = true;

    /** 是否输出 url */
    private $_url = true;

    /** 是否输出 env */
    private $_env = false;

    /** 是否输出 server */
    private $_server = false;

    /** 是否捕获错误 catch_error */
    private $_catch_error = true;

    /** 是否捕获异常 catch_exception */
    private $_catch_exception = true;

    /**记录毫秒时间 */
    private $_log_microtime = false;

    /**记录开始执行的时间 */
    private $_start_microtime;
    /**
     * 构造函数
     * Debug constructor.
     * @param string $filename      输出文件名
     * @param bool $buffer   是否输出 buffer
     * @param bool $get      是否输出 get
     * @param bool $post     是否输出 post
     * @param bool $cookie     是否输出 cookie
     * @param bool $session     是否输出 session
     * @param bool $url      是否输出 url
     * @param bool $server     是否输出 server
     */
    public function __construct($filename = 'debug.txt', $buffer = true, $get = true, $post = true, $cookie = true, $session = true, $url = true, $server = false, $env = false, $catch_exception = true, $catch_error = true, $error_level = E_ERROR | E_WARNING | E_PARSE)
    {
        global $argv;
        $this->_iscli = $this->is_cli();
        if ($catch_error) {
            var_dump(__LINE__ . ":" . __LINE__);
            set_error_handler(array($this, 'error_handler'), $error_level);
        }
        if ($catch_exception) {
            var_dump(__LINE__ . ":" . __LINE__);
            set_exception_handler(array($this, 'exception_handler'));
        }
        if (!$this->_iscli) {
            ob_start();
            register_shutdown_function(array($this, 'callback'));
        } else {
            $this->_argv = $argv;
        }

        $this->_start_microtime = microtime(true);
        $this->_filename = $filename;
        $this->_buffer = $buffer;
        $this->_get = $get;
        $this->_post = $post;
        $this->_cookie = $cookie;
        $this->_session = $session;
        $this->_server = $server;
        $this->_url = $url;
        $this->_header_idx = 0;
    }

    /**
     * 判断是否在命令行模式
     */
    public function is_cli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? 1 : 0;
    }

    /**
     * 通过配置单独定义函数的配置
     */
    public function set($k, $v)
    {
        $_k = '_' . $k;
        $this->$_k = $v;
        return $this;
    }

    /**
     * 设置文件路径
     */
    public function file($filename)
    {
        $this->_filename = $filename;
        return $this;
    }

    /**
     * 调试日志
     * @param mixed  $k
     * @param mixed  $log          日志内容
     * @param bool   $is_replace   是否替换
     */
    public function log($k, $log = '', $is_replace = true)
    {
        $this->_debug_pre = debug_backtrace();
        $this->console($log, $k);
        if ($k && $log) {
            // 直接替换
            if ($is_replace) {
                $this->_logs[$k] = $log;
            } else {
                if ($this->_logs[$k]) {
                    // 已是数组,添加到后面
                    if (is_array($this->_logs[$k])) {
                        $this->_logs[$k][] = $log;
                    } else {
                        $this->_logs[$k] = array($this->_logs[$k], $log);
                    }
                } else {
                    $this->_logs[$k] = $log;
                }
            }
        }
        return $this;
    }

    /** 停止调试 */
    public function stop()
    {
        $this->_logs = [];
        $this->_filename = '';
    }

    public function exception_handler($exception)
    {
        $this->_logs_exception .= $exception . PHP_EOL;
        $this->_logs_exception .= "--- exception end ---";
        $this->write();
    }

    public function error_handler($errno, $errstr, $errfile, $errline)
    {
        if ($this->_catch_error) {
            switch ($errno) {
                case E_WARNING:
                    // x / 0 错误 PHP7 依然不能很友好的自动捕获 只会产生 E_WARNING 级的错误
                    // 捕获判断后 throw new DivisionByZeroError($error_msg)
                    // 或者使用 intdiv(x, 0) 方法 会自动抛出 DivisionByZeroError 的错误
                    if (strcmp('Division by zero', $error_msg) == 0) {
                        throw new \DivisionByZeroError($error_msg);
                    }

                    $level_tips = 'PHP Warning: ';
                    break;
                case E_NOTICE:
                    $level_tips = 'PHP Notice: ';
                    break;
                case E_DEPRECATED:
                    $level_tips = 'PHP Deprecated: ';
                    break;
                case E_USER_ERROR:
                    $level_tips = 'User Error: ';
                    break;
                case E_USER_WARNING:
                    $level_tips = 'User Warning: ';
                    break;
                case E_USER_NOTICE:
                    $level_tips = 'User Notice: ';
                    break;
                case E_USER_DEPRECATED:
                    $level_tips = 'User Deprecated: ';
                    break;
                case E_STRICT:
                    $level_tips = 'PHP Strict: ';
                    break;
                default:
                    $level_tips = 'Unkonw Type Error: ';
                    break;
            }
            $this->_logs_error .= $level_tips . $errstr . ' in ' . $errfile . ' on ' . $errline . PHP_EOL;
        }
    }

    /** 内部内调 */
    public function callback()
    {
        $buffer = ob_get_contents();
        ob_clean();
        ob_implicit_flush(1);
        if ($this->_buffer) {
            $this->_logs_buffer = $buffer;
        }
        if ($this->_catch_error) {
            $error = error_get_last();
            if ($error && ($error["type"] === ($error["type"] & (E_ERROR | E_USER_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_PARSE)))) {
                $errno = $error["type"];
                $errfile = $error["file"];
                $errline = $error["line"];
                $errstr = $error["message"];
                $this->error_handler($errno, $errstr, $errfile, $errline);
            }
        }
        $this->write();
        exit($buffer);
    }

    public function log_time()
    {
        if ($this->_log_microtime) {
            return udate(' Y-m-d H:i:s.u ');
        } else {
            return date(' Y-m-d H:i:s ');
        }
    }

    /**
     * 直接命令行记录LOG, 比较多信息
     */
    public function console_log($var, $label = null, $echo = true)
    {

        $debug = $this->_debug_pre && is_array($this->_debug_pre) ? $this->_debug_pre : debug_backtrace();
        $str = '[DEBUG]:' . $this->log_time() . (defined('IA_ROOT') ? substr($debug[0]['file'], strlen(IA_ROOT)) : $debug[0]['file']) . ':(' . $debug[0]['line'] . ")" . PHP_EOL;
        $str .= '[TIMED]: ' . $this->timeoffset() .' ms'. PHP_EOL;
        if (is_string($var)) {
            $str .= ($label ? '\'' . $label . '\' =>\'' : '') . $var . ($label ? '\',' : '') . PHP_EOL;
        } else {
            $str .= ($label ? '\'' . $label . '\' =>' : '') . json_encode($var, JSON_UNESCAPED_UNICODE) . ($label ? ',' : '') . PHP_EOL;
        }
        if (!$this->_filename) {
            if ($echo) {
                echo $str;
            }
            return;
        }
        file_put_contents($this->_filename, $str, FILE_APPEND);
    }

    /**
     * 直接命令行输出
     */
    public function console_info($var, $label = null, $echo = true)
    {
        if (is_string($var)) {
            $str .= ($label ? '\'' . $label . '\' =>\'' : '') . $var . ($label ? '\',' : '') . PHP_EOL;
        } else {
            $str .= ($label ? '\'' . $label . '\' =>' : '') . var_export($var, true) . ($label ? ',' : '') . PHP_EOL;
        }
        if (!$this->_filename) {
            if ($echo) {
                echo $str;
            }
            return;
        }
        file_put_contents($this->_filename, $str, FILE_APPEND);
    }

    /**
     * 直接命令行输出
     */
    public function console($var, $label = null)
    {
        $this->_log_level ? $this->console_log($var, $label, !$this->buffer) : $this->console_info($var, $label, !$this->buffer);
        return $this;
    }

    //计算毫秒
    public function timeoffset()
    {
        return (microtime(true) - $this->_start_microtime) * 1000;
    }

    /** 析构调试相关 */
    public function write()
    {
        if (!$this->_filename) {
            return;
        }
        $filename = $this->_filename;
        $str = '[DEBUG]:' . $this->log_time() . PHP_EOL;
        $str .= '[TIMED]: ' . $this->timeoffset() .' ms'. PHP_EOL;
        if ($this->_iscli) {
            $str .= '[MODE]: CLI' . PHP_EOL;
            $str .= 'ARGV    : ' . var_export($this->_argv, true) . PHP_EOL;
        } else {
            if ($this->_url) {
                $str .= '[MODE]: WEB' . PHP_EOL;
                $str .= 'URL    : ' . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . (($_SERVER['REQUEST_SCHEME'] == 'http' && $_SERVER['SERVER_PORT'] == '80') || ($_SERVER['REQUEST_SCHEME'] == 'https' && $_SERVER['SERVER_PORT'] == '443') ? '' : ':' . $_SERVER['SERVER_PORT']) . $_SERVER['REQUEST_URI'] . PHP_EOL;
            }
        }
        if ($this->_get && $_GET) {
            $t = [];
            foreach ($_GET as $k => $v) {
                if (is_array($v)) {
                    $t[] = "{@$k} => {" . json_encode($v, JSON_UNESCAPED_UNICODE) . "}";
                } else {
                    $t[] = "{$k} => {$v}";
                }
            }
            $str .= 'GET    : ' . implode("\n    ", $t) . PHP_EOL;
        }
        if ($this->_post && $_POST) {
            $str .= 'POST   : ' . var_export($_POST, true) . PHP_EOL;
        }
        if ($this->_cookie && isset($_COOKIE) && $_COOKIE) {
            $str .= 'COOKIE : ' . var_export($_COOKIE, true) . PHP_EOL;
        }
        if ($this->_session && isset($_SESSION) && $_SESSION) {
            $str .= 'SESSION: ' . var_export($_SESSION, true) . PHP_EOL;
        }
        if ($this->_server && isset($_SERVER) && $_SERVER) {
            $str .= 'SERVER : ' . var_export($_SERVER, true) . PHP_EOL;
        }
        if ($this->_env && isset($_ENV) && $_ENV) {
            $str .= 'ENV : ' . var_export($_ENV, true) . PHP_EOL;
        }
        if ($this->_logs) {
            $str .= 'LOGS   : ' . var_export($this->_logs, true) . PHP_EOL;
        }
        if ($this->_buffer && $this->_logs_buffer) {
            $str .= '--- buffer start ---' . PHP_EOL . $this->_logs_buffer . PHP_EOL;
        }
        if ($this->_catch_error && $this->_logs_error) {
            $str .= '--- error start ---' . PHP_EOL . $this->_logs_error . "--- error end ---" . PHP_EOL;
        }
        if ($this->_catch_exception && $this->_logs_exception) {
            $str .= '--- exception start ---' . PHP_EOL . $this->_logs_exception . PHP_EOL;
        }
        $this->_logs = [];
        $this->_logs_buffer = '';
        $str .= "------------------" . PHP_EOL . PHP_EOL;
        file_put_contents($filename, $str, FILE_APPEND);
        return $this;
    }

    /**
     * 在header输出头数据
     * @param string $k
     * @param mixed  $v
     * @param bool   $debug
     */
    public static function header(string $k, $v, bool $debug = false, string $funs = '', string $line = '')
    {
        // static $idx = 0;
        if ($debug && !headers_sent()) {
            self::$_header_idx++;
            if ($line) {
                $key[] = $line;
                if ($funs) {
                    $key[] = $funs;
                }
                if ($k) {
                    $key[] = $k;
                }
            } else {
                $key[] = $k;
                if ($funs) {
                    $key[] = $funs;
                }
            }
            $key = implode('-', $key);
            $idx = str_pad(self::$_header_idx, 4, '0', STR_PAD_LEFT);
            header("{$idx}-{$key}: {$v}", false);
        }
    }

    /**
     * +----------------------------------------------------------
     * 变量输出
     * +----------------------------------------------------------
     * @param string $var 变量名
     * @param string $label 显示标签
     * @param string $echo 是否显示
     * +----------------------------------------------------------
     * @return string
     * +----------------------------------------------------------
     */
    public function dump($var, $label = null, $strict = true, $echo = true)
    {
        if ($this->_iscli) {
            return $this->console($var, $label);
        }
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        $debug = $this->_debug_pre && is_array($this->_debug_pre) ? $this->_debug_pre : debug_backtrace();
        $mtime = explode(' ', microtime());
        $ntime = microtime(true);
        $_ENV['dumpOrderID'] = isset($_ENV['dumpOrderID']) && $_ENV['dumpOrderID'] ? $_ENV['dumpOrderID'] + 1 : 1;
        $offtime = !isset($_ENV['dumpTimeCountDown']) || !$_ENV['dumpTimeCountDown'] ? 0 : round(($ntime - $_ENV['dumpTimeCountDown']) * 1000, 4);
        if (!isset($_ENV['dumpTimeCountDown']) || !$_ENV['dumpTimeCountDown']) {
            $_ENV['dumpTimeCountDown'] = $ntime;
        }

        $message = '<br /><font color="#fff" style="width: 30px;height: 12px; line-height: 12px;background-color:' . ($label ? 'indianred' : '#2943b3') . ';padding: 2px 6px;border-radius: 4px;">No. ' . sprintf('%02d', $_ENV['dumpOrderID']) . '</font>&nbsp;&nbsp;' . " ~" . (defined('IA_ROOT') ? substr($debug[0]['file'], strlen(IA_ROOT)) : $debug[0]['file']) . ':(' . $debug[0]['line'] . ") &nbsp;" . $this->log_time() . " $mtime[0] " . (!$offtime ? "" : "(" . $offtime . "ms)") . '<br />' . PHP_EOL;
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = "<pre>" . $label . htmlspecialchars($output, ENT_QUOTES, 'utf-8') . "</pre>";
            } else {
                $output = $label . " : " . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES, 'utf-8') . '</pre>';
            }
        }
        $output = $message . $output;
        if ($echo) {
            echo ($output);
            return null;
        } else {
            return $output;
        }
    }

    public function _dump($var, $label = null, $strict = true, $echo = true)
    {
        $debug = debug_backtrace();
        $this->_debug_pre = $debug;
        $this->dump($var, $label, $strict, $echo);
        exit;
    }
}
