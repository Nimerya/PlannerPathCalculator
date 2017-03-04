<?php

require("file.inc.php");
#require("template2.inc.php");
require("Connector.php");


class TreeCreator {
    var
        $file,
        $db;

    function TreeCreator(){
        $this->file = new File();
        $this->db = new Connector();
    }

    function GenerateTree(){

        $this->db->sanitize();

        $result_v = $this->file->fillArray('vertex');
        $result_e = $this->file->fillArray('edge');
        $ok = false;
        $i = 0;
        $unused_name = $_POST['name'];
        while(!$ok){

            $i += 1;
            $trees = $this->db->getTrees();


            if (count($trees) == 0){
                $ok = true;
            }
            foreach ($trees as $tree){
                if(strcasecmp($tree->name, $unused_name) == 0){
                    $unused_name = $_POST['name'] . $i;
                    $ok = false;
                    break;
                }
                $ok = true;
            }
        }

        $attributes_v = $result_v[0];
        $ranges_v = $result_v[1];

        $attributes_e = $result_e[0];
        $ranges_e = $result_e[1];

        $time = -microtime(true);

        $this->file->createVertexCSV($_POST['split'], $_POST['depth'], $unused_name, $attributes_v, $ranges_v);
	
        $this->file->createEdgeCSV($_POST['split'], $_POST['depth'], $unused_name, $attributes_e, $ranges_e);

        $this->db->create($unused_name, $_POST['split'], $_POST['depth'], $attributes_v, $attributes_e);

        $time += microtime(true);
        $this->file->clearFile($unused_name);
        return substr($time, 0, 5);

    }
}
?>
