<?php

namespace app\Api\tools;

trait Send
{
	/**
	 * 返回消息
	 * @param int $code 20000 => ok 50008 => 非法令牌 50012 => 其他客户端的错误 0014 => Token 过期
	 * @param string $message
	 * @param array $data
	 * @param array $header
	 */
	public static function returnMsg($code = 20000, $message = '', $data = [], $header = [])
	{
		http_response_code(200);    //设置返回头部
		$msgs = [
			'20000' => 'ok',
			'50008' => '非法令牌',
			'50012' => '服务器错误',
			'50014' => 'Token已过期'
		];

		$return['code'] = $code;
		$return['message'] = empty($message) ? $msgs[$code] : $message;
		$return['data'] =  $data;

		header('Access-Control-Allow-Origin:*');
		header('Access-Control-Allow-Methods:*');
		header('Access-Control-Allow-Headers:*');
		header('Set-Cookie:SameSite=None; Secure');
		//允许ajax异步请求带cookie信息
		header('Access-Control-Allow-Credentials:true');

		// 发送头部信息
		foreach ($header as $name => $val) {
			if (is_null($val)) {
				header($name);
			} else {
				header($name.':'.$val);
			}
		}
		exit(json_encode($return, JSON_UNESCAPED_UNICODE));
	}
}