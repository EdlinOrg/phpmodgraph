phpmodgraph
-----------
The goal would be to see which parts of the software has been
modified and are most relevant for the testing of the software.

The dependent and the modified methods will be marked,
e.g. a software contains the functions A, B, C, D
if the call graphs looks like
A -> B -> C -> D
and the function C has been modified, then A, B, C should all be marked,
since they are the ones that might have issues due to the modification.
The function D cannot be broken since it was not dependent on the modification.
C will have a different color to signal that thats the function that have been modified.

At the moment, all methods/functions that belongs to a file
that has been modified are marked (as well as those dependent on those functions).
(i.e. it should really be only the modified method that are marked, and not all methods in that file)

I used phpcallgraph v.0.8.0 which can be downloaded from sourceforge:
<a href="http://phpcallgraph.sourceforge.net/">http://phpcallgraph.sourceforge.net/</a>

In the directory examples/ you will find some php files and the generated png for that code.

Installation
------------

1) Download phpcallgraph v.0.8.0

2) Test that you can generate a png call graph as described in the documentaion for phpcallgraph

3) Download the phpmodgraph files and place them in the src directory
   (and thereby replacing some of the original php files from phpcallgraph)
   
4) Download svnmodfiles.sh

5) Modify svnmodfiles.sh to give back a list of files that have been
   modified lately (i.e. the files whose content should be marked as modified in the graph)
   
6) Modify FileModificationHandler.php to have the correct path to the svnmodfiles.sh file

7) Generate a png call graph again with this modified source, but call it with 'mod' instead of 'png'

8) If the corresponding nodes have not been marked, check in FileModificationHandler.php
   that the filenames in __construct and isMethodChanged are the same
   (e.g. put in the echo statements and verify that they are the same), if not,
   modify extractFileName

TODO:
To get it to mark only the relevant methods/functions (and not all methods in a file), you need to take start/end line number into consideration.
The phpcallgraph code contains that already, so the information only has to be handed over to the driver methods.
(the start line -1, is already handed over).
An alternative is to take the code chunk that the driver recieves and calculate the end line based on that
(under assumption that the code chunk is exactly as in the source file that it has processed)
Subversion, CVS, Git are all giving out which lines that have been changed when doing a diff, so you just
need to calculate and compare that data to see which methods have been modified.

It seems like phpcallgraph is not showing all function calls at the moment, hence this will not be a complete
graph of the code impact, needs to be looked into if that can be fixed.



   