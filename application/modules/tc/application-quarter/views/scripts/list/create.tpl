<?php

$this->headLink()->appendStylesheet($this->serverUrl('/css/application/tc/style.css'));
$this->headScript()->appendFile($this->serverUrl('/js/application/tc/script.js'));

echo $this->form;
