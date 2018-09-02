# qrcpu-php-demo
本demo 实现 qrcpu.com 提供的到云市场全部接口使用方法，通过 微信小程序 qrcpu-weapp 项目进行对接


安装注意事项：
一、修改配置文件： qrcpu-php-demo/config/custom.php

二、PHP >= 5.6.0，并开启php扩展 PDO、MBstring 

三、网站根目录：qrcpu-php-demo/public 

	1.如果你的服务器不支持跨目录，可复制 public 里的全部文件到上级目录 cp -a qrcpu-php-demo/public/*  ../
	2.修改index.php目录位置 ：require __DIR__ . '/thinkphp/base.php';

四、目录：runtime 和 qrcpu-php-demo\public\imgcdn 两个目录需要 读写权限

五、其它请参考下文档

帮助文档: http://www.qrcpu.com/help.html

thinnkphp5.1在线手册：https://www.kancloud.cn/manual/thinkphp5_1/353946



