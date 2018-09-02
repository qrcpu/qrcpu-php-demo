<?php
// +----------------------------------------------------------------------
// | 配置文件
// +----------------------------------------------------------------------

return [
	'is_img_cdn'=>false,//是否单独使用CDN域名
	'img_domain'=>'http://demo.qrcpu.com',//图片域名 或 CDN域名 
	'token_keys'=>['test-qrcpu.com','test2019-qrcpu.com'],//和小程序 app.js 的 tokenKey 要一致，后台配置多个，可在更新小程序时兼容一段时间后删除
	'qrcpu_config'=> array(
			'appcode'=> '7a11d5482f274b788d7-------',//云市场 appcode
			'cpu_id'=>'cpu16-------',// qrcpu 官网 > 开发者配置
			'cpu_key'=>'fnbIF-------',// qrcpu 官网 > 开发者配置
		)
];
