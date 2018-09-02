<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Env;
use think\facade\Request;
use think\Db;


use qrcpu\qrcpuCOM;

class Qrcpuapi extends Controller
{	
	public $qrcpu;
	public $cache_sec = 3600;//默认缓存时间（秒）  减少接口请求次数
	

	

	// 初始化
    protected function initialize()
    {
		
		$token_keys = config('custom.token_keys');

		$qrcpu_time = Request::post('qrcpu_time/d',0);
		$qrcpu_token = Request::post('qrcpu_token/s','');
		
		$timestamp = time();

		if($qrcpu_time<=0 || $timestamp-$qrcpu_time > 300 || $qrcpu_time-$timestamp>300){
			echo json_encode([
				'status'=>0,
				'data'=>'',
				'msg'=>'请求过期',
			]);
			exit;
		}

		//验证token
		$is_token = false;
		foreach($token_keys as $value)
		{
			$new_token = md5($value.$qrcpu_time);
			if($new_token == $qrcpu_token){
				$is_token = true;
				break;
			}
		}
		
		if(!$is_token)
		{
			echo json_encode([
				'status'=>0,
				'data'=>'',
				'msg'=>'请求token验证失败',
			]);
			exit;
		}



    }


	public function qrcpuCOM(){

		if($this->qrcpu){
			return $this->qrcpu;
		}

		//config
		$config = config('custom.qrcpu_config');//在配置文件中
		if(!$config)
		{
			//test
			$config = array(
				'appcode'=> '7a11d5482f274*************',
				'cpu_id'=>'cpu16*************',
				'cpu_key'=>'afVv*************',
			);
		}
		$this->qrcpu = new qrcpuCOM($config);

		return $this->qrcpu;
	}


	public function index()
    {

		return 'qrcpu php api';

	}

	/*
		彩色二维码
		color_array: ['高级黑', '中国红', '微信绿', '武藤蓝'],
		xt_array: ['液态', '直角', '圆圈'],
	*/
	public function easy_generator(){
		
		$qrsize = Request::post('qrsize/d',0);
		$color_index = Request::post('color_index/d',0);
		$xt_index = Request::post('xt_index/d',0);
		$qrdata = Request::post('qrdata/s','','trim');
		
		$color_array = ['#353536','#CE3C39','#179B16','#0d79d1'];
		$qrcolor = isset($color_array[$color_index]) ? $color_array[$color_index] : $color_array[0]; 
	
		if(empty($qrdata)){
			return json([
				'status'=>0,
				'data'=>'',
				'msg'=>'请输入二维码内容',
			]);
		}
		

		$cache_key = 'easy_g'.$qrsize.$color_index.$xt_index. md5($qrdata);
		$api_data = cache($cache_key);
		if(!$api_data)
		{
			$api_data = $this->qrcpuCOM()->qrcustom([
				'size'=>$qrsize,
				'qrdata'=>$qrdata,
				'xt'=>$xt_index,
				//码眼 +　前景
				'p_color'=>$qrcolor,
				'i_color'=>$qrcolor,
				'fore_color'=>$qrcolor,
			]);
		
			if($api_data['status'] ==1){
				//图片缓存到本地
				$api_data['data'] = get_img2local($api_data['data']);
				
				cache($cache_key,$api_data,$this->cache_sec);
			}

		}
		
		return json($api_data);

	}

	public function category($is_getarr = false){
		
		$api_data = cache('qrcpu_category');
		if(!$api_data)
		{
			$api_data = $this->qrcpuCOM()->category();
			/*
				debug

			if($api_data['status'] !=1){
				$this->error($api_data['msg']);
			}
			*/

			if($api_data['status'] ==1){
				cache('qrcpu_category',$api_data,$this->cache_sec);
			}

		}
		if($is_getarr){
			if($api_data['status'] !=1){
				return array();
			}else{
				return $api_data['data'];
			}
		}
		
		return json($api_data);

	}



	public function template(){
		
		

		$cat_count = Request::post('cat_count/d',0);
		$cat_id = Request::post('cat_id/d',0);
		$page_index = Request::post('page_index/d',0);
		$kwd = '';//暂时无搜索功能
		$page_size = 10;//每页几条
		
		/*
		//start  分页js代码bug，在这暂时兼容一下，小程序下个版本就删除
		$page_index_array = [
			1=>1,
			11=>2,
			21=>3,
			31=>4,
			41=>5,
			51=>6,
			61=>7,
			71=>8,
			81=>9,
			91=>10,
			101=>11,
			111=>12,
			121=>13,
		];
		$page_index = isset($page_index_array[$page_index]) ? $page_index_array[$page_index] : $page_index;
		//end  分页js代码bug
		*/


		$cache_key ='qrcpu_template'.$cat_id. $page_index.( $kwd ? md5($kwd) : '');

		$api_data = cache($cache_key);
		if(!$api_data)
		{
			$api_data = $this->qrcpuCOM()->template($cat_id , $kwd ,$page_index,$page_size);

			if($api_data['status'] ==1){ //无数据时 也缓存减少接口无用请求 不要 && !empty($api_data['data'])
				

				foreach($api_data['data'] as $key=>$value){
					
					//图片缓存到本地
					$value['template_qrcode']['cover'] = get_img2local($value['template_qrcode']['cover']);
					$value['template_qrcode']['thumb'] = get_img2local($value['template_qrcode']['thumb']);
					$api_data['data'][$key] = $value;
				}
	

				cache($cache_key,$api_data,$this->cache_sec);
			}

		}

		if($cat_count<=1){
			$api_data['cat_list'] = $this->category(true);
		}else{
			$api_data['cat_list'] =[];
		}
		
		return json($api_data);

	}



	public function template_view(){
		

		$template_id = Request::post('template_id/d',0);

		$cache_key = 'temp_view'.$template_id;
		$api_data = cache($cache_key);
		if(!$api_data)
		{
			$api_data = $this->qrcpuCOM()->template_view($template_id);
	

			if($api_data['status'] ==1){
				//图片缓存到本地
				$api_data['data']['template_qrcode']['cover'] = get_img2local($api_data['data']['template_qrcode']['cover']);
				$api_data['data']['template_qrcode']['thumb'] = get_img2local($api_data['data']['template_qrcode']['thumb']);


				cache($cache_key,$api_data,$this->cache_sec);
			}

		}
		
		return json($api_data);

	}

	/*
		生成
	*/
	public function generator(){
		$template_id = Request::post('template_id/d',0);
		$qrdata = Request::post('qrdata/s','','trim');

	
		if(empty($qrdata)){
			return json([
				'status'=>0,
				'data'=>'',
				'msg'=>'请输入二维码内容',
			]);
		}
		

		$cache_key = 'generator'.$template_id. md5($qrdata);
		$api_data =  cache($cache_key);
		if(!$api_data)
		{
			$api_data = $this->qrcpuCOM()->qrencode($template_id,$qrdata);
	
			if($api_data['status'] ==1){
				//图片缓存到本地
				$api_data['data'] = get_img2local($api_data['data']);
				
				cache($cache_key,$api_data,$this->cache_sec);
			}

		}
		
		return json($api_data);

	}



	
}
