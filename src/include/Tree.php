<?php

/**
 * Created by PhpStorm.
 * User: Federico
 * Date: 13/01/17
 * Time: 15:02
 */
Class Tree{
    var $name,
        $split_size,
        $depth,
        $tot_nodes,
        $vertexAttrList,
        $edgeAttrList;

    function Tree($name, $split_size, $depth, $tot_nodes, $vertexAttrList, $edgeAttrList)
    {
        $this->name = $name;
        $this->split_size = $split_size;
        $this->depth = $depth;
        $this->tot_nodes = $tot_nodes;
        $this->vertexAttrList = $vertexAttrList;
        $this->edgeAttrList = $edgeAttrList;
    }

}