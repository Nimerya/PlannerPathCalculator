<?php

require_once "include/Connector.php";
require_once "include/template2.inc.php";

$main = new Template("skins/pooled/dtml/frame_public.html");
$body = new Template("skins/pooled/dtml/list.html");

        $data=$neo->getTrees();

        foreach($data as $line) {

            foreach ($line as $key=>$value) {

                if ($key == 'vertexAttrList' || $key == 'edgeAttrList') {
                    $s="";
                    for($i=0;$i<count($value); $i++)
                        $s = $s.' '.$value[$i].',';
                    $s = substr($s, 0, -1);
                    $body->setContent($key, $s);
                } else {
                    $body->setContent($key, $value);
                }
            }

        }

        switch($_GET['mode']) {
            case 'del':
                $body->setContent('url', 'delete_tree.php');
                $body->setContent('action', "delete");
                $body->setContent('color', "#EB3E28");
                break;
            default:
                $body->setContent('url', 'calculations.php');
                $body->setContent('action', "query");
                $body->setContent('color', "#6852B2");
                break;
        }


$main->setContent("body", $body->get());
$main->close();

?>


