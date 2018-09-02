<?php
namespace app\index\controller;

use think\Controller;
use think\facade\Env;

use qrcpu\qrcpuCOM;

class Index extends Controller
{


	public function index()
    {
		
		return 'qrcpu php demo';
	}
}
