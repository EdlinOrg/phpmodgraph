<?php

/*
 * This file is part of phpmodgraph, see readme.md
 * Needed some more functionality in Image_GraphViz to be able to deal
 * with marked/dependent nodes
 * 
 * phpmodgraph is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpmodgraph is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @license    http://www.gnu.org/licenses/gpl.txt GNU General Public License
 */
class Image_GraphVizMarkModifications extends Image_GraphViz 
{
    /**
     * @var string color that dependent nodes should have
     */
    private $markDependentNode = 'pink';
  
    /**
     * @var string color that marked nodes should have
     */
    public $markNode = 'red';
    
    /**
     * Add or update a node in the graph.
     *
     * @param  string  Name of the node.
     * @param  array   Attributes of the node (will be merged by update, marked nodes remains marked)
     * @param  string  Group of the node.
     * @access public
     */
    function addOrMergeNode($name, $attributes = array(), $group = 'default')
    {
      if (! isset($this->graph['nodes'][$group][$name])) {      
        if($this->isMarkedAttributeSet($this->graph['nodes'][$group][$name])){
          $attributes['color'] = $this->graph['nodes'][$group][$name]['color'];
        }
      }
      $this->graph['nodes'][$group][$name] = $attributes;
      if($this->isMarkedAttributeSet($attributes)){
        $this->markNodesRecursive(array($name));
      }
    }
    
    
    /**
     * Marks a node as dependent on a modfied node
     *
     * @param  string  Name of the node.
     * @param  array   Attributes of the node.
     * @param  string  Group of the node.
     * @access public
     */
    function markNode($name, $group = 'default')
    {
      //if node does not exist, simply return
      if (! isset($this->graph['nodes'][$group][$name])) {
       return;
      }
      $attributes = $this->graph['nodes'][$group][$name];
      $attributes['color'] = $this->markDependentNode;
      $this->graph['nodes'][$group][$name]= $attributes;
    }

    function isNodeMarked($name, $group = 'default'){
      if (! isset($this->graph['nodes'][$group][$name])) {
        //three state boolean
        return null;
      }

      $attributes = $this->graph['nodes'][$group][$name];

      return $this->isMarkedAttributeSet($attributes);
    }
    
    function isMarkedAttributeSet($attributes){
      //TODO: use proper mark flag instead of colors
      return ($attributes['color'] == $this->markDependentNode || $attributes['color'] == $this->markNode);
    }
    
    /**
     * Find which nodes are pointing to this node
     * @param string $nodeName
     * @return array array of node names
     */
    function findCallersToNode($nodeName)
    {
      $edgeIds = array_keys($this->graph['edges']);
      $callers = array();
      
      $test = '_' . $nodeName;
      foreach($edgeIds as $edgeId){
        $sl = strlen($test);
        if(substr_compare($edgeId, $test, - $sl, $sl) === 0){
          //found an edge pointing to the node $name
          array_push($callers, key($this->graph['edges'][$edgeId]));
        }
      }
      return $callers;
    }
      
    /**
     * Since the graph is changing randomly, we need to call this
     * method at the end to make sure all nodes are marked as supposed
     */
    function traverseAndMarkGraph(){
      if (isset($this->graph['nodes'])) {
        foreach($this->graph['nodes'] as $group => $nodes) {
        
          $nodeNames = array();
          foreach($nodes as $nodeName => $attribuges) {
            if($this->isNodeMarked($nodeName,$group)){
              //find callers, they should be marked                
              $nodeNames = array_merge($nodeNames,$this->findCallersToNode($nodeName)); 
            }
          }
          
          $this->markNodesRecursive($nodeNames);
        }
      }
    }
    
   /**
    * Mark the nodes, and all nodes that point to them recursively
    * @param array $nodeNames
    */
   function markNodesRecursive($nodeNames){
      
      foreach($nodeNames as $node){
        
        $group = "default";
        
        //hack: extract group name from the nodeName
        //XXX: seems like the group name info really should be available on the edges, then
        //this hack would not be necessary
        $a = explode("::",$node);
        if(sizeof($a) == 2){
          $group = $a[0];
        }        
        
        //determine if the $node already is marked
        if($this->isNodeMarked($node,$group)){
          continue;
        }
        if(null === $this->isNodeMarked($node,$group)){
          //TODO: should not take place, add error handling
          echo "Error: node $node, group $group not defined";
        }
        
        //not marked, mark it
        $this->markNode($node,$group);
        
        $nodeNames = $this->findCallersToNode($node);
        $this->markNodesRecursive($nodeNames);
      }
    }

}

?>
