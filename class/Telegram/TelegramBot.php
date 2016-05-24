<?php

namespace Telegram;

/**
 * Telegram bot with user-friendly methods
 *
 * @author dest
 */
class TelegramBot extends TelegramBotCore {

	/**
	 * @var array
	 */
	protected $hooks = array();

	/**
	 * Register hook handler
	 *
	 * @param string $name
	 * @param function $call
	*/
	public function registerHook($name, $call) {

		if (!is_callable($call)) {
			return;
		}

		if (!isset($this->hooks[$name])) {
			$this->hooks[$name] = array();
		}

		$this->hooks[$name][] = $call;
	}

	/**
	 * Call hook handler
	 *
	 * @param string $name
	 */
	public function triggerHook($name) {

		if (isset($this->hooks[$name])) {
				
			$args = func_get_args();
			$args = array_slice($args, 1);
				
			foreach ($this->hooks[$name] as $hook) {
				call_user_func_array($hook, $args);
			}
		}
	}
	
	/**
	 * Create callback button array
	 * 
	 * @param string $text
	 * @param array $callback_data
	 * @return array
	 */
	public function createCallbackButton($text, $callback_data) {
	
		return array(
			'text' => $text,
			'callback_data' => json_encode($callback_data)
		);
	}

	/**
	 * Send text message
	 *
	 * @param int $chat_id
	 * @param string $text
	 * @param object $markup
	 * @return bool
	 */
	public function sendTextMessage($chat_id, $text, $markup = null) {

		$message = array(
			'chat_id' => $chat_id,
			'text' => str_replace('_', '\_', $text),
			'disable_web_page_preview' => true,
			'parse_mode' => 'Markdown',
		);

		if (is_object($markup)) {
			$message['reply_markup'] = $markup;
		}

		$result = $this->sendMessage((object)$message);

		$this->triggerHook('sendMessage', $chat_id, $text, $markup);

		return isset($result->ok) ? (bool)$result->ok : false;
	}
	
	/**
	 * Send photo
	 * 
	 * @param unknown $chat_id
	 * @param unknown $url
	 */
	public function sendPhoto($chat_id, $url) {
		
		$filePath = '/tmp/telegram.file_'.md5($chat_id.'#'.$url).'.'.file_extension($url);
		
		if ($this->file_download($url, $filePath)) {
			
			$message = array(
				'chat_id' => $chat_id,
				'photo' => '@'.realpath($filePath)
			);
			
			$result = parent::sendPhoto((object)$message);
			
			@unlink($filePath);
			
			return isset($result->ok) ? (bool)$result->ok : false;
		}
		
		return false;
	}
	
	/**
	 * Download file from url to specified path
	 * 
	 * @param string $url
	 * @param string $pathto
	 * @return bool
	 */
	protected function file_download($url, $pathto) {
		
		@mkdir(dirname($pathto), 0777, true);
		
		if (ini_get('allow_url_fopen')) {
			@copy($url, $pathto);
		} else {
			$f = @fopen($pathto, 'w');
		
			if ($f === false) {
				return;
			}
		
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_FILE, $f);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_exec($ch);
			curl_close($ch);
		
			fclose($f);
		}
		
		return file_exists($pathto);
	}
	
	/**
	 * File extension
	 * 
	 * @param string $path
	 * @return string
	 */
	protected function file_extension($path) {
		return mb_substr(strrchr($path, '.'), 1);
	}
	
	/**
	 * File info
	 * 
	 * @param string $file_id
	 * @return object
	 */
	public function getFile($file_id) {
		
		$response = parent::getFile($file_id);
		
		if ($response->ok) {
			
			$fileInfo = $response->result;
			$fileInfo->file_url = $this->getFileUrl($fileInfo->file_path);
			$fileInfo->file_extension = $this->file_extension($fileInfo->file_path);
			
			return $fileInfo;
		}
		
		return (object)array();
	}
	
	/**
	 * Parsing php input
	 * 
	 * @return object
	 */
	public function getRequest() {
		
		if ($content = @file_get_contents('php://input')) {
			if ($request = json_decode($content)) {
				return $request;
			}
		}
		
		return null;
	}
}