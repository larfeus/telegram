<?php

namespace Telegram;

/**
 * Telegram bot implement for send same messages
 *
 * @author dest
 */
class TelegramBotCore {

	/**
	 * Token id of api
	 *
	 * @var string
	 */
	public $token = '';

	/**
	 * Constructor
	 *
	 * @param string $token
	 */
	public function __construct($token) {
		$this->token = $token;
	}

	/**
	 * Get testing information about this bot
	 *
	 * @return object
	 */
	public function getMe() {
		return $this->sendRequest('getMe');
	}
	
	/**
	 * Get file info
	 * 
	 * @param string $file_id
	 * @return object
	 */
	public function getFile($file_id) {
		return $this->sendRequest('getFile', (object)array('file_id' => $file_id));
	}
	
	/**
	 * Get file url for download
	 * 
	 * @param string $file_path
	 * @return string
	 */
	public function getFileUrl($file_path) {
		return 'https://api.telegram.org/file/bot'.$this->token.'/'.$file_path;
	}

	/**
	 * Send message to Chat or User
	 *
	 * @param object $message
	 * @return object
	 */
	public function sendMessage($message) {
		return $this->sendRequest('sendMessage', $message);
	}
	
	/**
	 * Send message to Chat or User
	 * 
	 * @param object $message
	 * @return object
	 */
	public function sendPhoto($message) {
		return $this->sendRequest('sendPhoto', $message, 'multipart');
	}

	/**
	 * Send answer of callback
	 *
	 * @param object $message
	 * @return object
	 */
	public function answerCallbackQuery($message) {
		return $this->sendRequest('answerCallbackQuery', $message);
	}

	/**
	 * Edit message text
	 *
	 * @param object $message
	 * @return object
	 */
	public function editMessageText($message) {
		return $this->sendRequest('editMessageText', $message);
	}
	
	/**
	 * Send method url
	 * 
	 * @param string $method
	 * @return string
	 */
	protected function sendUrl($method) {
		return 'https://api.telegram.org/bot'.$this->token.'/'.$method;
	}

	/**
	 * Send naked HTTP request to api.telegram.org
	 *
	 * @param string $url
	 * @param object $request
	 * @param bool $post
	 * @return object
	 */
	protected function sendRequest($method, $request, $type = 'json') {

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $this->sendUrl($method));
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_UPLOAD, 0);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_TIMEOUT, 60);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
		
		if ($request) {
			
			curl_setopt($curl, CURLOPT_POST, 1);
			
			if ($type == 'json') {
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($request));
			} else {
				curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
				curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
			}
		}
		
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		$result = curl_exec($curl);
		curl_close($curl);

		return $result ? json_decode($result) : new \stdClass();
	}
}