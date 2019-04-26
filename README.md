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

The core of `PathTool` is the method `yieldPathComponents`. It feeds other methods with path components. You can also use it directly with options described here:

- OPTION_RESOLVE: Resolves empty directories, parent and current directories.
- OPTION_DENY_OUT_OF_BOUNDS: Does not allow to choose parent directory of root directory (ex: `/root/path/../../../`)
- OPTION_YIELD_ROOT: If a zero path, yields `/` as first component.
- OPTION_YIELD_COMPONENT: Yields PathComponent object instead of strings.
- OPTION_ALL: Include all options described before.

```php
// Use bitwise operators to join options:
$options = PathTool::OPTION_RESOLVE | PathTool::OPTION_YIELD_ROOT;

// Or subtract them
$options = PathTool::OPTION_ALL & ~PathTool::OPTION_DENY_OUT_OF_BOUNDS & ~PathTool::OPTION_YIELD_ROOT;
```
