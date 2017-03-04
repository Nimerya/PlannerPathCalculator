<?php

require("include/template2.inc.php");
shell_exec("php index_server.php");
ini_set('memory_limit',-1);
ini_set('max_execution_time', 0);
ini_set('session.gc_maxlifetime',1440);

$main = new Template("skins/pooled/dtml/frame_public.html");
$body = new Template("skins/pooled/dtml/home.html");


$main->setContent("body", $body->get());
$main->close();



?>
