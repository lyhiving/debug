## debug

简单的php调试库。

主要功能：

1、实现将受访URL、GET、POST、COOKIE、SESSION、SERVER等参数记录到文件。

2、log你想要log的内容。

3、提供dump、_dump两个常规函数以便日常调试。


## 少啰嗦，先看东西

![最简单版本](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/1.png)

![综合使用](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/2.png)

![web调试用](https://raw.githubusercontent.com/lyhiving/debug/master/examples/image/3.png)

## 安装

使用 Composer

```json
{
    "require": {
            "lyhiving/debug": "1.*"
    }
}
```

## 用法

普通青年：

直接输出到当前目录下的debug.txt（其实你可以指定到任意可以写的位置）。

```php
<?php
use lyhiving\debug\debug;

$debug = new debug(__DIR__.'/debug.txt');
```

文艺青年：

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
```


闷骚青年：

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

更多用法参考 [examples](https://github.com/lyhiving/debug/blob/master/examples/index.php) 里面的范例。

本文件大部分来自 [@dreamxyp](https://github.com/ounun-php/ounun) , 我做了composer的适应和部分冗余处理。