## debug

简单的php调试库。

主要功能：

1、实现将受访URL、GET、POST、COOKIE、SESSION、SERVER等参数记录到文件。

2、log你想要log的内容。

3、提供dump、_dump两个常规函数以便日常调试。

4、增加命令行的支持


## 少啰嗦，先看东西

![最简单版本](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/1.png)

![综合使用](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/2.png)

![web调试用](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/3.png)

![命令行调试用](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/4.png)

## 安装

使用 Composer

```bash
composer require lyhiving/debug
```

```json
{
    "require": {
            "lyhiving/debug": "2.*"
    }
}
```

## 用法

### 普通青年：

直接输出到当前目录下的debug.txt（其实你可以指定到任意可以写的位置）。

```php
<?php
use lyhiving\debug\debug;

$debug = new debug(__DIR__."/debug.txt");
```

### 文艺青年：

加点GET、POST、使用log方法之类。

```php
<?php

use lyhiving\debug\debug;

$debug = new debug(__DIR__.'/debug.txt');
$_GET['im'] = 'debug';
$_GET['array'] = array('apple','orange','bananer');

$_POST['im'] = 'a POST data';
$_POST['array'] = array('apple','orange','bananer');

$debug->log('MAC','MAKE A BETTER WORLD');
$debug->log('TIME','@time');
```


### 闷骚青年：

在页面直接调用dump或者_dump的, 其实这个一般是面向web的，所以一般不定义输出文件路径。

其中IA_ROOT常量可以不定义，这样你可以看到完整的路径。如果你正在使用微擎或者微赞，这个常量是自带的。可以帮忙隐藏网站的真实路径。不过都在调试了，还怕什么~~~

```php
<?php

use lyhiving\debug\debug;
define('IA_ROOT', dirname(__DIR__));

$debug = new debug();
$debug->dump('MAKE A BETTER WORLD');
$debug->_dump('OK. I\'m the last line.');
```

在全局范围内，允许用file的方法重新设置日志路径:

```php
<?php

use lyhiving\debug\debug;


$debug = new debug();
$debug->file(__DIR__.'/new.txt')->dump('MAKE A BETTER WORLD');
$debug->_dump('OK. I\'m the last line.');
```

更多用法参考 [examples/index.php](https://github.com/lyhiving/debug/blob/master/examples/index.php) 范例。



### 习惯用命令行的中年大叔：

常用办法没变，增加log_level使得可以根据需要使用，在某种程度而言，对于命令行开发会有比较好的输出控制。
```php
//简单模式，需额外设置
$debug->set('log_level',0);
//默认为1，也可以随时设置回来
$debug->set('log_level',1);
//接管错误
$debug->set('catch_error',1);
//接管异常
$debug->set('catch_exception',1);
```
在简单模式的情况下，输出的log直接可以复制使用，方便调试。

```php
//开启记录毫秒时间，并且记录同时输出相同内容
$debug->set('log_microtime', true)->set('log_and_echo', true);
//开启记录毫秒时间，并且记录详细信息，输出时去掉所在行和执行时间，更接近console_info
$debug->set('log_microtime', true)->set('log_and_echo', 'timed');
```





在全局范围内，允许用file的方法重新设置日志路径:

```php
<?php

use lyhiving\debug\debug;
define('IA_ROOT', dirname(__DIR__));

$debug = new debug(__DIR__."/#debug.txt");
$debug->log('MAC', 'MAKE A BETTER WORLD');
$debug->log('ArrayNormal', array('apple', 'orange', 'banner'));
//Set less log info
$debug->set('log_level',0);
//If you want less infomation
$debug->log('ArrayCanCopyDirect', array('apple', 'orange', 'banner'));
$debug->dump('MAKE A BETTER WORLD');
$debug->_dump('OK. I\'m the last line.');

```

其中IA_ROOT常量可以不定义，这样你可以看到完整的路径。如果你正在使用微擎或者微赞，这个常量是自带的。可以帮忙隐藏网站的真实路径。不过都在调试了，还怕什么~~~


本文件部分来自 [@dreamxyp](https://github.com/ounun-php/ounun) , 我做了composer的适应、命令行兼容和部分冗余处理。