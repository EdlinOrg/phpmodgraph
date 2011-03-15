#!/bin/sh

# Should echo all files that have been modified since revision 1 in svn

cd /some/src/dir

# you would need to do a better filtering here to be sure that you get the filename and not some content of the file
svn diff -r1|grep ".php" |grep "Index: "|cut -c8-255 

exit 0;
