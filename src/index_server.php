<?php

require_once 'include/vendor/autoload.php';

use GraphAware\Neo4j\Client\ClientBuilder;

class IndexServer {

    private $sock;

    function __construct() {
        if(!($this->sock = socket_create(AF_INET, SOCK_STREAM, 0)))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Couldn't create socket: [$errorcode] $errormsg \n");
        }

        echo "Socket created \n";

        // Bind the source address
        if( !socket_bind($this->sock, "127.0.0.1" , 5000) )
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not bind socket : [$errorcode] $errormsg \n");
        }

        echo "Socket bind OK \n";

        if(!socket_listen ($this->sock , 100))
        {
            $errorcode = socket_last_error();
            $errormsg = socket_strerror($errorcode);

            die("Could not listen on socket : [$errorcode] $errormsg \n");
        }

        echo "Socket listen OK \n";
    }

    function run(){
        echo "Waiting for incoming connections... \n";

        //start loop to listen for incoming connections
        while (true) {
        //Accept incoming connection - This is a blocking call
            $client =  socket_accept($this->sock);

        //display information about the client who is connected
            if(socket_getpeername($client , $address , $port))
            {
                echo "Client $address : $port is now connected to us. \n";
            }

        //read data from the incoming socket
            $input = socket_read($client, 2045);

        //Start connection to the DB
            $database = ClientBuilder::create()
                ->addConnection('server', 'bolt://localhost:7687')
                ->setDefaultTimeout(100)
                ->build();

        //Try to run the Query recived from the client

            $database->run($input);

            $response = "OK .. $input";

            echo $response;
            echo "\n";

        //If the string matches 'Drop' then don't await for the Index to be online
            if( !preg_match('(Drop)',$input,$matches) ){

                preg_match("(:Vertex_[a-zA-Z0-9]*)",$input,$res);

                $result = $database->run("CALL db.awaitIndex('".$res[0]."(name)')");

                echo "Index is ONLINE";
                echo "\n";
            }

            //Send back an acknowledgement to the client
            if( ! socket_send ( $client , "OK\n" , strlen("OK\n") , 0))
            {
                $errorcode = socket_last_error();
                $errormsg = socket_strerror($errorcode);

                die("Could not send data: [$errorcode] $errormsg \n");
            }

            }
    }

}

$index = new IndexServer();
$index->run();

