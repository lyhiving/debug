<?php

namespace lyhiving\debug;

class debug
{
    /** 日志数组 */
    private $_logs = [];
    private $_logs_buffer = '';

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

    /** 是否输出 server */
    private $_server = false;

    /** 执行时间秒 */
    private $_starttime;

    /** 执行时间微妙 */
    private $_startmicrotime;
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

    public function __construct($filename = 'debug.txt', $buffer = true, $get = true, $post = true, $cookie = true, $session = true, $url = true, $server = false)
    {
        ob_start();
        register_shutdown_function(array($this, 'callback'));
        $this->_filename = $filename;
        $this->_buffer = $buffer;
        $this->_get = $get;
        $this->_post = $post;
        $this->_cookie = $cookie;
        $this->_session = $session;
        $this->_server = $server;
        $this->_url = $url;
        $this->_header_idx = 0;
        $this->marktime();
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
     * @param string $k
     * @param mixed  $log          日志内容
     * @param bool   $is_replace   是否替换
     */

    public function log(string $k, $log, $is_replace = true)
    {
        if ($k && $log) {
            if ($log == '@time') {
                $log = $this->timeoffset();
            }
            // 直接替换
            if ($is_replace) {
                $this->_logs[$k] = $log;
            } else {
                if ($this->_logs[$k]) {
                    // 已是数组, 添加到后面
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
    }

    /** 停止调试 */

    public function stop()
    {
        $this->_logs = [];
        $this->_filename = '';
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
        $this->write();
        exit($buffer);
    }

    /** 析构调试相关 */

    public function write()
    {
        if (!$this->_filename) {
            return;
        }
        $filename = $this->_filename;
        $str = '[DEBUG]:' . date('Y-m-d H:i:s') . PHP_EOL;
        if ($this->_url) {
            $str .= 'URL    : ' . (isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : '')
                . '://' . (isset($_SERVER['HTTP_HOST_ORG']) && $_SERVER['HTTP_HOST_ORG'] ? $_SERVER['HTTP_HOST_ORG'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ''))
                . ((isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'http' && $_SERVER['SERVER_PORT'] == '80') || (isset($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https' && $_SERVER['SERVER_PORT'] == '443')) ? '' : (isset($_SERVER['SERVER_PORT']) ? ':' . $_SERVER['SERVER_PORT'] : '') . $_SERVER['REQUEST_URI'] . PHP_EOL;
        }
        if ($this->_get && $_GET) {
            $t = [];
            foreach ($_GET as $k => $v) {
                if (is_array($v)) {
                    $t[] = "{@$k} => {" . json_encode($v) . '}';
                } else {
                    $t[] = "{$k} => {$v}";
                }
            }
            $str .= 'GET    : ' . implode('\n    ', $t) . PHP_EOL;
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
        if ($this->_logs) {
            $str .= 'LOGS   : ' . var_export($this->_logs, true) . PHP_EOL;
        }
        if ($this->_starttime) {
            $str .= 'TIME S/E: ' . date('Y-m-d H:i:s', $this->_starttime) . ' / ' . date('Y-m-d H:i:s') . PHP_EOL;
        }
        if ($this->_startmicrotime) {
            $str .= 'OFFTIME: ' . $this->timeoffset() . ' ms' . PHP_EOL;
        }
        if ($this->_buffer && $this->_logs_buffer) {
            $str .= '--- buffer start ---' . PHP_EOL . $this->_logs_buffer . PHP_EOL;
        }
        $this->_logs = [];
        $this->_logs_buffer = '';
        $str .= '------------------' . PHP_EOL . PHP_EOL;
        file_put_contents($filename, $str, FILE_APPEND);
    }

    public function marktime(){
        $this->_starttime = time();
        $this->_startmicrotime = microtime(true);
        return $this;
    }
    public function timeoffset($time = null, $nowmic = null)
    {
        if (!$time) {
            $time = $this->_startmicrotime;
        }
        if (!$nowmic) {
            $nowmic = microtime(true);
        }
        return sprintf("%.2f", ($nowmic - $time) * 1000);
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
        $label = ($label === null) ? '' : rtrim($label) . ' ';
        if ($label == '@time') {
            $label = $this->timeoffset();
        }
        $debug = debug_backtrace();
        $mtime = explode(' ', microtime());
        $ntime = microtime(true);
        $_ENV['dumpOrderID'] = isset($_ENV['dumpOrderID']) && $_ENV['dumpOrderID'] ? $_ENV['dumpOrderID'] + 1 : 1;
        $offtime = !isset($_ENV['dumpTimeCountDown']) || !$_ENV['dumpTimeCountDown'] ? 0 : round(($ntime - $_ENV['dumpTimeCountDown']) * 1000, 4);
        if (!isset($_ENV['dumpTimeCountDown']) || !$_ENV['dumpTimeCountDown']) {
            $_ENV['dumpTimeCountDown'] = $ntime;
        }

        $message = '<br /><font color="#fff" style="width: 30px;height: 12px; line-height: 12px;background-color:' . ($label ? 'indianred' : '#2943b3') . ';padding: 2px 6px;border-radius: 4px;">No. ' . sprintf('%02d', $_ENV['dumpOrderID']) . '</font>&nbsp;&nbsp;' . ' ~' . (defined('IA_ROOT') ? substr($debug[0]['file'], strlen(IA_ROOT)) : $debug[0]['file']) . ':(' . $debug[0]['line'] . ') &nbsp;' . date('Y/m/d H:i:s') . " $mtime[0] " . (!$offtime ? '' : '(' . $offtime . 'ms)') . '<br />' . PHP_EOL;
        if (!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = '<pre>' . $label . htmlspecialchars($output, ENT_QUOTES, 'utf-8') . '</pre>';
            } else {
                $output = $label . ' : ' . print_r($var, true);
            }
        } else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if (!extension_loaded('xdebug')) {
                $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);
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
        self::dump($var, $label, $strict, $echo);
        exit;
    }
}
