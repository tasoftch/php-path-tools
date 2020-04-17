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
use TASoft\Util\FileFilter\FilterInterface;
use TASoft\Util\FileFilter\MatchGlobFilter;
use TASoft\Util\FileFilter\MatchRegexFilter;

/**
 * The real path tool works with real paths.
 *
 * @package TASoft\Util
 */
abstract class RealPathTool
{
	/**
	 * This method iterates over a directory and yields all files/directories passing the filters list.
	 *
	 * @param string $directory
	 * @param bool $recursive
	 * @param string[]|FilterInterface[] $filters
	 */
	public static function yieldDirectoryContents(string $directory, bool $recursive = false, array $filters = []) {
		if($recursive) {
			$iteratorQ = function($dir) use (&$iteratorQ) {
				foreach(new \DirectoryIterator($dir) as $f) {
					if($f == '.' || $f == '..')
						continue;
					yield $f;

					if($f->isDir())
						yield from $iteratorQ($f->getPathname());
				}
			};
			$iterator = (function() use ($directory, $iteratorQ) {
				yield from $iteratorQ($directory);
			})();
		} else {
			$iterator = new \DirectoryIterator($directory);
		}

		if($filters)
			$iterator = static::applyFilters($iterator, $filters);

		foreach($iterator as $file) {
			if($file == '.' || $file == '..')
				continue;
			if($file instanceof \SplFileInfo)
				$file = $file->getPathname();
			yield $file;
		}
	}

	/**
	 * Same method as yielding the directory contents but will return an array instead of a generator.
	 *
	 * @param string $directory
	 * @param bool $recursive
	 * @param array $filters
	 * @return array|null
	 */
	public static function getDirectoryContents(string $directory, bool $recursive = false, array $filters = []): ?array {
		if(!is_dir($directory))
			return NULL;

		return iterator_to_array(static::yieldDirectoryContents($directory, $recursive, $filters));
	}

	/**
	 * Interact with a generator chain filtering incoming files/directories from a generator.
	 * If the file/directory passes all filters, the generator chain continues.
	 *
	 * As a filter, the following arguments are taken:
	 * - Any instance implementing FilterInterface.
	 * - A string: passed to fnmatch. So it may contain glob patterns
	 * - A string: Beginning with % will be used as regex (ex. %.+\.txt$%im)
	 *
	 * This method yields every found path with an empty filter list.
	 *
	 * @param Iterable $filesGenerator
	 * @param array $filters
	 * @return Generator
	 */
	public static function applyFilters(Iterable $filesGenerator, array $filters) {
		$filters = array_filter(
			array_map(function($f) {
				if(is_string($f)) {
					if(isset($f[0]) && $f[0] == '%')
						$f = new MatchRegexFilter($f);
					else
						$f = new MatchGlobFilter($f);
				}

				if($f instanceof FilterInterface)
					return $f;

				return NULL;
			}, $filters)
		);

		foreach($filesGenerator as $file) {
			/** @var FilterInterface $filter */
			foreach($filters as $filter) {
				if(!$filter->acceptsFile($file))
					continue 2;
			}

			yield $file;
		}
	}
}