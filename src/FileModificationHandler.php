<?php

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
class FileModificationHandler {

  protected $modifiedFiles;

  /**
   * Initialize
   * Save a hash with info which files that have been modified
   */
  public function __construct() {

    //replace with proper path
    $command = '../examples/examplemodfiles.sh';
    exec($command,$modfiles);

    foreach ($modfiles as $value) {
      //echo "Modified file $value\n";
      $this->modifiedFiles[$value] = true;
    }
  }

  /**
   * At the moment it just checks if the method is placed in a file that has been modified
   */
  public function isMethodChanged($fileName, $methodName, $startLine, $endLine){

    //echo "isMethodChanged: $fileName, $methodName, $startLine, $endLine\n";

    $tmpF = $this->extractFileName($fileName);
    //echo "tmpF = $tmpF\n";

    return array_key_exists($tmpF, $this->modifiedFiles);
  }

  /**
   * You might need to take away leading path here from the file
   */
  public function extractFileName($f){    
    return str_replace("/some/path/", "", $f);
    
//    $tmp = explode("/", $f);
//    return array_pop($tmp);
  }

}


?>