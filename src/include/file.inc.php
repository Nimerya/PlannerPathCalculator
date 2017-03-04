<?php

Class File{

    function createVertexCSV($split, $depth, $name, $attributes, $ranges){
	    
            $size=count($attributes);
            $file = fopen(dirname(__DIR__).'/Neo4j/import/vertex_'.$name.'.csv','w') or die ('Unable to open/create file!');

            for ($i=0; $i<$size; $i++){
                if($i<($size-1))
                    fwrite($file,'"'. $attributes[$i].'",');
                elseif($i==($size - 1))
                    fwrite($file, '"'.$attributes[$i].'"');
            }
            fwrite($file, "\n");

            if ($split == 1)
            {
                $nodes = $depth + 1;
            }
            else
            {
                $nodes = (int)(($split**($depth+1)-1)/ ($split-1));
            }
            #echo '<br>'.$nodes.'<br>';
            for ($i=1; $i<$nodes+1; $i++){
                $s='"vertex_'.$i.'",';
                for($j=1; $j<$size; $j++) {
                    if($ranges[$attributes[$j]][2]=='true')
                        $s = $s . '"' . $this->frand($ranges[$attributes[$j]][0], $ranges[$attributes[$j]][1], 0) . '",';
                    else
                        $s = $s . '"' . $this->frand($ranges[$attributes[$j]][0], $ranges[$attributes[$j]][1], 2) . '",';
                }
                $s=substr($s, 0, -1);
                fwrite($file, $s."\n");
            }

        }

    function createEdgeCSV($split, $depth, $name, $attributes, $ranges){

            $size=count($attributes);
            $file = fopen(dirname(__DIR__).'/Neo4j/import/edge_'.$name.'.csv','w') or die ('Unable to open/create file!');

            for ($i=0; $i<$size; $i++){
                if($i<($size-1))
                    fwrite($file,'"'. $attributes[$i].'",');
                elseif($i==($size - 1))
                    fwrite($file, '"'.$attributes[$i].'"');
            }
            fwrite($file, "\n");

            if ($split == 1)
            {
                $nodes = $depth + 1;
            }
            else
            {
                $nodes = (int)(($split**($depth+1)-1)/ ($split-1));
            }

            #echo '<br>'.$nodes.'<br>';
            for ($i=1; $i<($nodes-($split**$depth)+1); $i++) {
                for ($j = ((-$split) + 2); $j < 2; $j++) {
                    $s = '"vertex_' . $i . '",' . '"vertex_' . ($split*$i + $j) . '",';
                    for ($k = 2; $k < $size; $k++) {
                        if($ranges[$attributes[$k]][2]=='true')
                            $s = $s . '"' . $this->frand($ranges[$attributes[$k]][0], $ranges[$attributes[$k]][1], 0) . '",';
                        else
                            $s = $s . '"' . $this->frand($ranges[$attributes[$k]][0], $ranges[$attributes[$k]][1], 2) . '",';
                    }
                    $s = substr($s, 0, -1);
                    fwrite($file, $s . "\n");
                }
            }
            

        }

        function fillArray($mode){

            $result=array();
            $attributes=array();
            $ranges=array();

               switch($mode){
                   case 'vertex':

                       $attributes[]='name';

                       for ($i=1;$i<=$_POST['num_attr_vertex'];$i++){

                           $attributes[]=$_POST['name_vattr'.$i];

                           $ranges[$attributes[$i]][0]=$_POST['min_vattr'.$i];
                           $ranges[$attributes[$i]][1]=$_POST['max_vattr'.$i];
                           $ranges[$attributes[$i]][2]= (isset($_POST['int_vattr'.$i])) ? 'true' : 'false';
                       }

                       break;

                   case 'edge':

                       $attributes[]='from';
                       $attributes[]='to';

                       for ($i=2; $i<=$_POST['num_attr_edge']+1; $i++){
                           $j=$i-1;
                           $attributes[]=$_POST['name_eattr'.$j];

                           $ranges[$attributes[$i]][0]=$_POST['min_eattr'.$j];
                           $ranges[$attributes[$i]][1]=$_POST['max_eattr'.$j];
                           $ranges[$attributes[$i]][2]= (isset($_POST['int_eattr'.$j])) ? 'true' : 'false';
                       }
                       break;
                   default:
                       return -1;
                       break;
               }

               $result[]=$attributes;
               $result[]=$ranges;

               return $result;

        }

    function frand($min, $max, $decimals = 0) {
            $scale = pow(10, $decimals);
            return mt_rand($min * $scale, $max * $scale) / $scale;
        }

    function clearFile($name){
        $filename = dirname(__DIR__)."/Neo4j/import/vertex_".$name.".csv";
	unlink($filename);
	$filename = dirname(__DIR__)."/Neo4j/import/edge_".$name.".csv";
	unlink($filename);
    }
}

   # $file=new File();
  
?>
