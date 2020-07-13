<?php
namespace app\index\controller;
use \think\Controller;
use \think\Cache;
use \think\Request;
class Miniqrcode extends Controller
{


	public function getQrcode(Request $request)
	{
		$path = '/index/page/';
		$res = $this -> miniQrCode($path);

		if( is_array($res) ){
			return objReturn($res['errcode'],$res['errmsg']);
		}else{
			$filePath = $request -> domain() . $res;
			$data = ['filePath'=>$filePath];
			return objReturn(0,'success',$data);
		}
	}

	/**
	 * 获取小程序二维码  成功返回字符串,错误返回数组
	 * @param  string $path 页面路径
	 * @return string $qrcoPath 小程序二维码路径
     */
	function miniQrCode($path)
	{
		//获取token
		$accessToken = Cache::get('wx_accessToken');
		if( !$accessToken ){
			//小程序App
			$AppId = 'wxb297388e77602c0d';
			$AppSecret = 'd593c84211128de073fb73d5568b2dbb';
			//获取Token
			$url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppId.'&secret='.$AppSecret;
			$accessToken = file_get_contents($url);
			$accessToken = json_decode($accessToken);
			$accessToken = get_object_vars($accessToken);
			if( empty($accessToken['access_token']) ){
				//错误返回,返回的是微信的错误码与错误信息
				return $accessToken;
			}else{
				Cache::set('wx_accessToken',$accessToken['access_token'],$accessToken['expires_in']);
				$accessToken = $accessToken['access_token'];
			}
		}

		//post数据
		$postData = array('path'=>$path,'width'=>1080);
		$postData = json_encode($postData);
		//请求QRcode
		$url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$accessToken;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postData);
        $data = curl_exec($ch);
        if( $data ){
        	curl_close($ch);	
        }else{
        	$error = curl_errno($ch);
        	curl_close($ch);
        	return ['errcode'=>401,'errmsg'=>'请求QRcode发生错误'];
        }
        //错误返回,返回的是微信的错误码与错误信息
        if( !empty($data['errcode']) ){
        	return $data;
		}

		//QRcode文件夹
		$targetDir = './static/img/qrcode';
		//QRcode文件名
		$date = date('Ymd',time());
		$fileName = $date.md5($path);
		//创建QRcode目录
		if( !file_exists($targetDir) ){
			mkdir($targetDir);
		}
		//QRcode文件路径
		$filePath = $targetDir . '/' . $fileName . '.png';
		//写入
		$result = file_put_contents($filePath,$data);
		if( $result ){
			// 处理qrcodePath字符
			$filePath = substr( $filePath,1,strlen($filePath) );
			return $filePath;
		}else{
			return ['errcode'=>402,'errmsg'=>'图片写入失败'];
		}
	}
}