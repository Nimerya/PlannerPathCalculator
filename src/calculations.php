<?php

require("include/template2.inc.php");
require("include/Connector.php");

$main = new Template("skins/pooled/dtml/frame_public.html");
$body = new Template("skins/pooled/dtml/calculations.html");

if (!isset($_REQUEST['page'])) {
    $_REQUEST['page'] = 0;
}



switch ($_REQUEST['page']) {
    case 0: // emit form
        foreach($_GET as $key=>$value){
            $body->setContent($key, $value);
        }


        break;
    case 1:
        $body=new Template("skins/pooled/dtml/result.html");

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', -1);
        ini_set('session.gc_maxlifetime', 1440);

        $tree=$neo->getTrees();
        foreach ($tree as $line){
            if($line->name == $_POST['name'] )
                $mytree=$line;
        }

        $result=$neo->sum($mytree, (int)$_POST['node_start'], (int)$_POST['node_end']);
        
        if(count($result[0]) == 0){
            $result=$neo->sum($mytree, (int)$_POST['node_start'], (int)$_POST['node_end'],$undirected='true');
        }

        $sums = $result[0];
        foreach ($sums as $key => $value) {
            if (substr($key, -4) == 'node') {
                $body->setContent('key_sum_node', $key);
                $body->setContent('value_sum_node', $value);
            } else {
                $body->setContent('key_sum_edge', $key);
                $body->setContent('value_sum_edge', $value);
            }
        }

        $path=$result[1];

        foreach($path as $line){
            $body->setContent('vertex',$line['name']);
            foreach ($line as $key=>$value){
                    if ($key=='name') {
                        continue;
                    }else{
                        $body->setContent('key_attr', $key);
                        $body->setContent('value_attr', $value);

                    }
                }
            }

        $body->setContent("time",$result[2]);

        break;


}

$main->setContent("body", $body->get());
$main->close();

?>