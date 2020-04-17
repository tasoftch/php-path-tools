<?php
/**
 * Copyright (c) 2019 TASoft Applications, Th. Abplanalp <info@tasoft.ch>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TASoft\Util;


use Generator;
use RuntimeException;

/**
 * The path tool is an "offline" path manipulator. It does not check, if paths exist.
 * So that why the tool needs some special information in the path:
 * - Path with leading / is a zero path or absolute path.
 * - Path with tailing / is a directory
 * - component .. is parent directory
 * - component . is current directory
 * - empty components are ignored (ex: /path///to/file.txt => /path/to/file.txt)
 *
 * @package TASoft\Util
 */
abstract class PathTool
{
    /** @var int Resolves paths: test/here/.././/file.txt => test/file.txt */
    const OPTION_RESOLVE                = 1<<0;
    /** @var int If set, throws an exception on out of bounds: test/../../file.txt => ../file.txt ==> RuntimeException! */
    const OPTION_DENY_OUT_OF_BOUNDS   = 1<<1;
    /** @var int If absolute path, yields / as root */
    const OPTION_YIELD_ROOT             = 1<<2;
    /** @var int If set, yields PathComponent objects instead of component name */
    const OPTION_YIELD_COMPONENT              = 1<<3;

    const OPTION_ALL = self::OPTION_YIELD_COMPONENT|self::OPTION_DENY_OUT_OF_BOUNDS|self::OPTION_RESOLVE|self::OPTION_YIELD_ROOT;


    /**
     * @param string $path
     * @return string
     */
    public static function normalize(string $path): string {
        $final = "";
        foreach(static::yieldPathComponents($path, static::OPTION_RESOLVE | static::OPTION_YIELD_ROOT | (static::isZeroPath($path) ? static::OPTION_DENY_OUT_OF_BOUNDS : 0)) as $cmp) {
            if($final != DIRECTORY_SEPARATOR && ($final && $cmp != DIRECTORY_SEPARATOR))
                $final .= DIRECTORY_SEPARATOR;
            $final .= $cmp;
        }
        return $final;
    }

    /**
     * Creates a relative path from zero path sourcePath to zero path targetPath
     *
     * @param $sourcePath
     * @param $targetPath
     * @return string
     */
    public static function relative($sourcePath, $targetPath) {
        if ($sourcePath === $targetPath) {
            return '';
        }

        if(!static::isZeroPath($sourcePath) || !static::isZeroPath($targetPath))
            throw new RuntimeException("Can only create relative path from two zero paths");

        $ds = static::isDirectory($sourcePath);
        $dt = static::isDirectory($targetPath);

        $target = iterator_to_array( static::yieldPathComponents($targetPath, static::OPTION_ALL ) );
        $source = iterator_to_array( static::yieldPathComponents($sourcePath, static::OPTION_ALL ) );

        /**
         * @var int $i
         * @var PathComponent $dir
         */
        foreach ($source as $i => $dir) {
            if (isset($target[$i]) && $dir->isEqual( $target[$i] )) {
                if(!$dir->isFinal() && !$dir->isDirectory())
                    break;
                unset($source[$i], $target[$i]);
            } else {
                break;
            }
        }

        if(!$ds) {
            array_shift($source);
        }

        array_walk($source, function($V) use (&$target) {
            if($V == DIRECTORY_SEPARATOR)
                return;
            array_unshift($target, "..");
        });



        if($dt) {
            if(!$target)
                return ".".DIRECTORY_SEPARATOR;
            return static::joinPathComponents(array_values($target)) . DIRECTORY_SEPARATOR;
        }

        return static::joinPathComponents(array_values($target));
    }

    /**
     * Yield path components
     *
     * @param string $path
     * @param int $options
     * @return Generator
     */
    public static function yieldPathComponents(string $path, int $options = self::OPTION_RESOLVE|self::OPTION_YIELD_ROOT): Generator {
        $paths = explode(DIRECTORY_SEPARATOR,  isset($path[0]) && '/' === $path[0] ? substr($path, 1) : $path);
        if($options & static::OPTION_RESOLVE) {
            $stack = [];

            foreach($paths as $p) {
                if($p == '' || $p == '.')
                    continue;
                elseif($p == '..') {
                    if(!$stack) {
                        if($options & static::OPTION_DENY_OUT_OF_BOUNDS)
                            throw new RuntimeException("Path resolution out of bounds");
                        else
                            $stack[] = "..";
                    }
                    else
                        array_pop($stack);
                } else
                    $stack[] = $p;
            }

            $paths = $stack;
        }

        if($options & static::OPTION_YIELD_ROOT && static::isZeroPath($path))
            array_unshift($paths, DIRECTORY_SEPARATOR);

        foreach($paths as $idx => $p) {
            if($options & static::OPTION_YIELD_COMPONENT)
                yield new PathComponent(
                    $idx+1 < count($paths) || static::isDirectory($path),
                    $p,
                    $idx+1 == count($paths)
                );
            else
                yield $p;
        }
    }

    /**
     * Joins components into path
     *
     * @param array $components
     * @return string
     */
    public static function joinPathComponents(array $components): string {
        if(!$components)
            return "";
        if($components[0] == DIRECTORY_SEPARATOR)
            $components[0] = '';
        return implode(DIRECTORY_SEPARATOR, $components);
    }

    /**
     * The Util does not check, if a file or directory exists.
     * So to determine a directory, add a tailing slash:
     * - /path/to/file          => is a file
     * - /path/to/directory/    => is a directory
     *
     * @param string $path
     * @return bool
     */
    public static function isDirectory(string $path): bool {
        return substr($path, -1) == DIRECTORY_SEPARATOR;
    }

    /**
     * Returns true, if the path is an absolute path:
     * /my/path     => TRUE
     * my/path      => FALSE
     * ../path      => FALSE
     *
     * @param string $path
     * @return bool
     */
    public static function isZeroPath(string $path): bool {
        return isset($path[0]) && $path[0] == DIRECTORY_SEPARATOR;
    }

    /**
     * Determines the extension of a path
     *
     * @param string $path
     * @param string|NULL $pathName
     * @return string
     */
    public static function getPathExtension(string $path, string &$pathName = NULL): string {
        $exts = explode(".", basename($path));
        $pathName = array_shift($exts);
        return array_pop($exts);
    }

    /**
     * Determines the name of a path ( last path component minus extension )
     *
     * @param string $path
     * @param string|NULL $extension
     * @return string
     */
    public static function getPathName(string $path, string &$extension = NULL): string {
        $exts = explode(".", basename($path));
        $pathName = array_shift($exts);
        $extension = array_pop($exts);
        return $pathName;
    }
}