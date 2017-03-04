<?php

/**
 * Created by PhpStorm.
 * User: Federico
 * Date: 13/01/17
 * Time: 14:59
 */


require("Tree.php");


require_once 'vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;

class Connector
{
    /**
     * Create the connection between the PHP Client and the Neo4j DB, returns a connection handler
     *
     * @return \GraphAware\Neo4j\Client\ClientInterface
     */
    function connect(){

        $conn_name = 'default';

        $client = ClientBuilder::create()
            ->addConnection($conn_name, 'bolt://localhost:7687')
            ->setDefaultTimeout(9999)
            ->build();
        return $client;
    }

    /**
     * Create the Tree on the DB, throws Neo4jException on query run error
     *
     * @param String $name
     * @param Integer $split
     * @param Integer $depth
     * @param String[] $attributes_v
     * @param String[] $attributes_e
     */
    function create($name, $split, $depth, $attributes_v, $attributes_e){
        $client = $this->connect();

        $file_v = "vertex_".$name;
        $file_e = "edge_".$name;
        $size_v = count($attributes_v);
        $size_e = count($attributes_e);

	//Create a socket and connect to the index_server to assure the right creation of the Index
        //before the edges creation

        if(!($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }

        if(!socket_connect($socket , '127.0.0.1' , 5000))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not connect: [$errorcode] $errormsg \n");
        }

        $s1= "Create index on :Vertex_".$name."(name) \n";

        //Send the message to the server that is the query to commit to the DB
        if( ! socket_send ( $socket , $s1 , strlen($s1) , 0))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not send data: [$errorcode] $errormsg \n");
        }

        //Wait synchronously a response from the server
        $response = socket_read($socket, 2045);

        if( $response != "OK\n")
        {
            die("Could not create index \n");
        }

        //Build the query for the Nodes Creation

        $s = "Using periodic commit 10000 LOAD CSV WITH HEADERS FROM 'file:///".$file_v.".csv' AS line Create( :Vertex_".$name." { name:line.name";
        for($i=1;$i<$size_v;$i++){
            $s = $s.", ".$attributes_v[$i].": toFloat(line.".$attributes_v[$i].")";
        }
        $s=$s."}) return distinct 0 as success";

        //Run the query
        try {
            $result = $client->run($s);
        } catch (\GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            echo sprintf('Catched exception in nodes, message is "%s"', $e->getMessage());

        }

        //Build the string for the creation of the Edges

        $s2 = "Using periodic commit 10000 LOAD CSV WITH HEADERS FROM 'file:///".$file_e.".csv' AS line".
            " Match (v_from:Vertex_".$name." { name:line.from })".
            " Match (v_to:Vertex_".$name." { name:line.to })".
            " Create (v_from)<-[:EDGE_".$name." {";

        for($i=2;$i<$size_e;$i++) {
            $s2= $s2.$attributes_e[$i].": toFloat(line.".$attributes_e[$i]."),";
        }

        if ($size_e > 2){
            $s2 = substr($s2, 0, -1);
        }

        $s2 = $s2."}]-(v_to)";

        //Run the query

        try {
            $result = $client->run($s2);
        } catch (\GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            echo sprintf('Catched exception in edges, message is "%s"', $e->getMessage());
        }

        //Now that the tree is fully on the DB, build the Tree Details and create the node :Tree on the DB
        //Compute the number of nodes
        if ($split == 1)
        {
            $nodes = $depth + 1;
        }
        else
        {
            $nodes = (int)(($split**($depth+1)-1)/ ($split-1));
        }

        //Build the query

        $s3 = "Create (t:Tree {name: '".$name."', split_size: ".$split.", depth: ".$depth.",tot_nodes: ".$nodes.", vertexAttrList:[";

        for($i=1;$i<$size_v;$i++){
            $s3 .= "'".$attributes_v[$i]."',";
        }

        $s3 = substr($s3,0,-1). "], edgeAttrList:[";


        for($i=2;$i<$size_e;$i++) {
            $s3 .= "'".$attributes_e[$i]."'," ;
        }

        if ($size_e > 2){
            $s3 = substr($s3,0,-1);
        }

        $s3 .= "]})";

        //Run the query

        try {
            $result = $client->run($s3);
        } catch (\GraphAware\Neo4j\Client\Exception\Neo4jException $e) {
            echo sprintf('Catched exception in tree, message is "%s"', $e->getMessage());
        }
    }

    /**
     * Retrieve the list of the Trees actually on the DB
     *
     * @return array
     */
    function getTrees(){
        $client = $this->connect();

        $query = "Match (t:Tree) return t";

        $result = $client->run($query);
        $treeList = array();
        # Spacchetto i risultati
        foreach ($result->records() as $record) {
            #echo "<br><br>";
            # Effettuo append per ogni $record (che è di tipo Node), property($key)
            # ritorna il valore dell'attributo $key
            $tree = new Tree($record->get('t')->property('name'),
                $record->get('t')->property('split_size'),
                $record->get('t')->property('depth'),
                $record->get('t')->property('tot_nodes'),
                $record->get('t')->property('vertexAttrList'),
                $record->get('t')->property('edgeAttrList'));
            array_push($treeList, $tree);
        }
        # $treeList conterrà tutti gli alberi (con le loro informazioni principali) attualmente sul db
        #print_r($treeList);
        return $treeList;

    }

    /**
     * Request the path/sum on a specific tree between two specific nodes
     *
     * @param Tree $tree
     * @param Integer $start
     * @param Integer $end
     * @return array
     */
    function sum($tree, $start, $end, $undirected=''){

        # Start deve essere il vertice di numero più basso
        if ($start < $end) {
            $temp = $end;
            $end = $start;
            $start = $temp;
        }

        # Se end è un numero che va oltre il numero massimo di nodi, calcola il path fino all'ultimo nodo
        if ($end > $tree->tot_nodes){
            $end = $tree->tot_nodes;
        }

        $name = $tree->name;
        $vertexAttrList = $tree->vertexAttrList;
        $edgeAttrList = $tree->edgeAttrList;

        $client = $this->connect();

        //Build the query for the Path/Sum

        if(!isset($undirected))
        {
            if(!isset($_POST['directed']))
            {
                $undirected = "true";
            }
            else
            {
                $undirected = "false";
            }
        }

        if($undirected == "true"){
            $query = "Match p=(from:Vertex_". $name ." {name:'vertex_".$start."'})-[:EDGE_".$name."*]-";
        }
        else {
            $query = "Match p=(from:Vertex_". $name ." {name:'vertex_".$start."'})-[:EDGE_".$name."*]->";
        }
        $query .= "(to:Vertex_".$name." {name:'vertex_".$end."'})
             using index from:Vertex_".$name."(name)
             using index to:Vertex_".$name."(name) 
             with nodes(p) as nod, relationships(p) as rels
             return
             nod, ";

        foreach($vertexAttrList as $attr){
            $query .= "reduce(sum = 0, n IN nod| sum + n.".$attr.") as tot_".$attr."_node, ";
        }
        foreach($edgeAttrList as $attr){
            $query .= "reduce(sum = 0, n IN rels| sum + n.".$attr.") as tot_".$attr."_edge, ";
        }

        $query = substr($query,0,-2);
        // Start time of the operation
        $time = -microtime(true);
        // Run query
        $result = $client->run($query);
        // End time of the operation
        $time += microtime(true);
        $path = array();
        $sum = array();
        # Spacchetto i risultati
        foreach ($result->records() as $record){
            foreach($record->keys() as $key){
                # Riempio l'array sum con tutti le somme sugli attributi (indicizzando $nome_attr => $valore_attr)
                if ($key == 'nod'){
                    continue;
                }
                $sum[$key] = $record->get($key);
            }

            foreach($record->get("nod") as $node){
                # Riempio l'array path con la lista di vertici (indicizzando $intero_progressivo => [name, attr1, attr2...])
                array_push($path,$node->properties());
            }

        }
        return [$sum,$path,substr($time, 0, 5)];

    }

    /**
     * Request the Deletion of a Tree on the DB
     *
     * @param $tree
     * @return string
     */
    function delete($tree){

        $client = $this->connect();

        $query = "Match (t:Tree) where t.name = '".$tree."' delete t return count(t)";
        $time = -microtime(true);
        $result = $client->run($query);
        $count = $result->firstRecord()->get('count(t)');

        if($count == 0){
            $time += microtime(true);
            return substr($time,0,5);
        }

        $query = "Match (v:Vertex_".$tree.") with v limit 50000 detach delete v return count(v)";

        $result = $client->run($query);

        $count = $result->firstRecord()->get('count(v)');

        while ($count > 0) {
            $result = $client->run($query);
            $count = $result->firstRecord()->get('count(v)');
        }
        $time += microtime(true);
        if(!($socket = socket_create(AF_INET, SOCK_STREAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }

        if(!socket_connect($socket , '127.0.0.1' , 5000))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not connect: [$errorcode] $errormsg \n");
        }

        $s1= "Drop index on :Vertex_".$tree."(name) \n";

        //Send the message to the server
        if( ! socket_send ( $socket , $s1 , strlen($s1) , 0))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not send data: [$errorcode] $errormsg \n");
        }

        $response = socket_read($socket, 2045);

        if( $response != "OK\n")
        {
            die("Could not create index \n");
        }

        return substr($time,0,5);


    }

    /**
     * Sanitizing the $_POST params
     */
    function sanitize() {

        foreach($_POST as $key => $value) {
            $_POST[$key] = addslashes($value);
        }

    }
}

$neo = new Connector();
#$result = $neo->getTrees();
#print_r($result);
