<?php
set_time_limit(0);
include "cmdBootstraping.php";
$conf = Zend_Registry::get('config');

$server = new HM_Websocket_Library_Server($conf->websocket->host, $conf->websocket->port, $conf->websocket->ssl);

$server->setMaxClients($conf->websocket->maxClients);
$server->setMaxConnectionsPerIp($conf->websocket->maxConnectionsPerIp);
$server->setMaxRequestsPerMinute($conf->websocket->maxRequestsPerMinute);
$server->registerApplication('message', HM_Websocket_Message::getInstance());
$server->run();
