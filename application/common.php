<?php

//错误级别
error_reporting(E_ERROR | E_PARSE );

//临时上传文件，有用的全部放oss
define('IMGCDN_PATH', think\facade\Env::get('root_path').'public/imgcdn'); 


function get_img2local($imgurl){
	
	$img_domain = config('custom.img_domain');
	$is_img_cdn = config('custom.is_img_cdn');
	$img_domain = rtrim($img_domain,'/');

	$img_parse_url = parse_url($imgurl);
	$img_parse_url['pathinfo'] = pathinfo($img_parse_url['path']);

	$_ext = strtolower($img_parse_url['pathinfo']['extension']);
	if(!in_array($_ext,['png','jpg','gif','jpeg'])){
		return '';
	}
	
	$save_dir = IMGCDN_PATH . $img_parse_url['pathinfo']['dirname'];
	mkdirs($save_dir);
	
	$save_filepath = IMGCDN_PATH.$img_parse_url['path'];
	if(!file_exists($save_filepath))
	{
		$content = file_get_contents($imgurl);
		$file_size = file_put_contents($save_filepath,$content);
		if(!$file_size){
			return '';
		}
	}
	
	if($is_img_cdn){//cdn直接解析到 imgcdn 目录
		$img_url = $img_domain . $img_parse_url['path'];
	}else{
		$img_url = $img_domain .'/imgcdn'. $img_parse_url['path'];
	}
	
	return $img_url;

}



//创建多级目录
function mkdirs($dirname, $ismkindex=1,$root_dir='') {
    $mkdir = false;
    
    if(is_dir($dirname))
        return true;

	if(!$root_dir){
		$root_dir = IMGCDN_PATH;
	}
	
	$dirname = str_replace($root_dir .'/','',$dirname);
    $arr = explode('/',$dirname);
	
    $dirname= $root_dir.'/';
	$dot = '';
    foreach($arr as $val)
    {
        $dirname .=  $dot .$val;
	
        $dot= '/';
        if(!is_dir($dirname)) {
            if(@mkdir($dirname, 0777)) {
                if($ismkindex) {
                    @fclose(@fopen($dirname.'/index.html', 'w'));
                }
                $mkdir = true;
            }
        } else {
            $mkdir = true;
        }
    }

    return $mkdir;
}