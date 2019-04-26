# PHP Path Tools
This PHP library is a small tool to manage virtual paths.  
Virtual paths are paths that don't have to exist on file system.

That leads to the problem of recognize if a path is a directory or a file.  
To solve this problem, the library declares virtual paths using the following notations:
 - Path with leading `/` is a zero path or absolute path.
 - Path with tailing `/` is a directory
 - component `..` is parent directory
 - component `.` is current directory
 - empty components are ignored (ex: `/path///to/file.txt` => `/path/to/file.txt`)
 
 So the Path Tool provides two methods to determine if a path is a zero path or a directory
 ```php
<?php
use TASoft\Util\PathTool;
PathTool::isZeroPath("/my/path");       // TRUE
PathTool::isZeroPath("../path/");       // FALSE

PathTool::isDirectory("my/path/to/");   // TRUE
PathTool::isDirectory("/my/path.txt");  // FALSE
PathTool::isDirectory("/my/path.txt/"); // TRUE
 ```
