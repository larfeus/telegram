<?php

/**
 * Register hello hook
 */
$telegram->registerHook('hello', function() use ($telegram, $requestChat) {

	$telegram->sendTextMessage($requestChat->id, "Привет!");
});