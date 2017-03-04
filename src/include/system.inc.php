<?php

    Class System {
        var
            $table,
            $operation;
        
        function System() {
            
            if (preg_match("+^([a-z]{1,})_([a-z]{1,})\.php$+", basename($_SERVER['SCRIPT_NAME']), $matches)) {
                $this->table = $matches[1];
                $this->operation = $matches[2];
  
            } else {
                $this->table = "";
                $this->operation = "";
            }
        }
        
        function getTable() {
            return $this->table;
        }
        
        function getOperation() {
            return $this->operation;
        }

        function redirect($url){
            echo "<script type=\"text/javascript\">
                           window.location = \"$url\"
                  </script>";
        }



        function listDirs($dir){

            // Open a directory, and read its contents
            $i=0;
            if (is_dir($dir)){
                if ($dh = opendir($dir)){
                    while (($item = readdir($dh)) !== false){
                        if($i>2){
                            $result[$i]= $item;
                        }
                        $i++;

                    }
                    closedir($dh);
                    return $result;
                }
            }
        }


    }
    
    $system = new System();


?>