<?php

require("include/TreeCreator.php");
require("include/template2.inc.php");

$main = new Template("skins/pooled/dtml/frame_public.html");
$body = new Template("skins/pooled/dtml/create_tree.html");

if (!isset($_REQUEST['page'])) {
    $_REQUEST['page'] = 0;
}

$main->setContent("body", $body->get());

switch ($_REQUEST['page']) {
    case 0: // emit form

        break;
    case 1: // wait

        ini_set('max_execution_time', 0);
        ini_set('memory_limit',-1);
        ini_set('session.gc_maxlifetime',1440);
        $tree = new TreeCreator();
        $time = $tree->GenerateTree();
        $main->setContent("message","OK");
        $main->setContent("time", $time);

        break;

}

$main->close();

?>


