<?php

/**
 * Register datetime hook
 */
$telegram->registerHook('datetime', function() use ($telegram, $requestChat) {
	
	$telegram->sendTextMessage($requestChat->id, date('d.m.Y H:i'));
});