<?php
/**
/*
 * This file is part of phpmodgraph, see readme.md
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

require_once 'GraphVizDriver.php';
require_once 'FileModificationHandler.php';
require_once 'Image/GraphVizMarkModifications.php';

/**
 * Marks the nodes that have been modified
 */
class GraphVizMarkModificationsDriver extends GraphVizDriver {

    protected $fileModificationHandler;
    
    /**
     * @return CallgraphDriver
     */
    public function __construct($outputFormat = 'png', $dotCommand = 'dot') {
        parent::__construct($outputFormat, $dotCommand);        
        $this->fileModificationHandler = new FileModificationHandler();
    }

    /**
     * @param integer $line
     * @param string $file
     * @param string $name
     * @return void
     */
    public function startFunction($line, $file, $name, $memberCode) {
        $this->addNode($name, $this->fileModificationHandler->isMethodChanged($file, $name, $line, $line));      
        $this->currentCaller = $name;
    }

    
    public function addCall($line, $file, $name) {      
      $modified = $this->fileModificationHandler->isMethodChanged($file, $name, $line, $line);
	    $this->addNode($name, $modified);
      $this->graph->addEdge(array($this->currentCaller => $name));
    }
    
    /**
     * @param string $name
     * @param boolean $modified flag if this node should be marked as modified or not
     * @return void
     */
    protected function addNode($name, $modified = false) {
        $nameParts = explode('::', $name);
        $cluster = 'default';
        $label = $name;
        $color = 'lavender'; //lightblue2, lightsteelblue2, azure2, slategray2

      	if($modified){
      	  $color=$this->graph->markNode;
      	}

        if (count($nameParts) == 2) { // method call
            if (empty($nameParts[0])) {
                $cluster = 'class is unknown';
            } else {
                $cluster = $nameParts[0];
            }
            // obtain method name
            $label = $nameParts[1];
        }
        // remove parameter list
        $label = substr($label, 0, strpos($label, '('));

        if (count($nameParts) == 1) { // function call
            if (in_array($label, $this->internalFunctions)) { // call to internal function
                $cluster = 'internal PHP functions';
            }
        }
                
        $this->graph->addOrMergeNode(
            $name,
            array(
                'fontname'  => 'Verdana',
                'fontsize'  => 12.0,
                //'fontcolor' => 'gray5',
                'label' => $label,
                //'style' => 'rounded' . ($this->useColor ? ',filled' : ''), // produces errors in rendering
                'style' => ($this->useColor ? 'filled' : 'rounded'),
                'color' => ($this->useColor ? $color : 'black'),
                'shape' => ($this->useColor ? 'ellipse' : 'rectangle'),
                ),
            $cluster
            );
        //*
        $this->graph->addCluster(
            $cluster,
            $cluster,
            array(
//                'style'   => ($this->useColor ? 'filled' : ''),
                'color'   => 'gray20',
//                'bgcolor' => '',
                )
            );
        //*/
    }

   /**
     * @return void
     */
    protected function initializeNewGraph() {
        $this->graph = new Image_GraphVizMarkModifications(
            true,
            array(
                'fontname'  => 'Verdana',
                'fontsize'  => 12.0,
                //'fontcolor' => 'gray5',
                'rankdir' => 'LR', // left-to-right
            )
        );
        $this->graph->dotCommand = $this->dotCommand;
    }
    
    
   /**
     * @return string
     */
    public function __toString() {
      //propagate the impact of marked nodes before returning output
      $this->graph->traverseAndMarkGraph();
      return $this->graph->fetch($this->outputFormat);
    }
    
}
?>
