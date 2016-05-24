<?php

require_once('class/Telegram/TelegramBotCore.php');
require_once('class/Telegram/TelegramBot.php');

/**
 * Example bot implementation
 * 
 * @author larfeus
 */
class ExampleBot extends TelegramBot {

	/**
	 * @var string
	 */
	public $name = 'ExampleBot';
	
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct('<your telegrambot token id>');
	}
}



$telegram = new ExampleBot();

// check request
$request = $telegram->getRequest() or (object)array();

// it`s simple text message
if (isset($request->message)) {

	$requestMessage = $request->message;
	$requestFrom = $request->message->from;
	$requestChat = $request->message->chat;

	if (isset($requestMessage->text)) {
		$requestMessage->text = str_replace('@'.$telegram->name, '', $requestMessage->text);
	}
}

// it`s not supported message
if (empty($requestFrom)) {
	return;
}

if (empty($requestMessage->text)) {
	return;
}

// register hooks
foreach (glob('hooks/*.php') as $includeFile) {
	include_once($includeFile);
}

// hook:hello
if (preg_match('/^(hello|hi|прив|здаров|здравствуй|ку|хай)([^\wа-я]|$)/i', $requestMessage->text)) {
	return $telegram->triggerHook('hello');
}

// hook:datetime
if (preg_match('/^\/?(date|time|дата|время)$/', $requestMessage->text)) {
	return $telegram->triggerHook('datetime');
}