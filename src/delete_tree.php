<?php


require("include/Connector.php");
require("include/template2.inc.php");

$main = new Template("skins/pooled/dtml/frame_public.html");
$body = new Template("skins/pooled/dtml/delete_tree.html");

if (!isset($_REQUEST['proceed']) || $_REQUEST['proceed']!='y') {
    $body->setContent("name", $_REQUEST['name']);
}else{


        ini_set('max_execution_time', 0);
        ini_set('memory_limit',-1);
        ini_set('session.gc_maxlifetime',1440);

        $result=$neo->delete($_REQUEST['name']);

        $body = new Template("skins/pooled/dtml/home.html");

        $main->setContent("message","OK");
        $main->setContent("time",$result);


}

$main->setContent("body", $body->get());
$main->close();


?>


