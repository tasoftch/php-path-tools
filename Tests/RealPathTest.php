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

/**
 * RealPathTest.php
 * php-path-tools
 *
 * Created on 17.04.20 15:58 by thomas
 */

use PHPUnit\Framework\TestCase;

use TASoft\Util\FileFilter\MatchGlobFilter;
use TASoft\Util\FileFilter\OnlyDirectoryFilter;
use TASoft\Util\FileFilter\OnlyFileFilter;
use TASoft\Util\RealPathTool as Tool;

class RealPathTest extends TestCase
{
	public function testEmptyDirectory() {
		// If this test fails, check if the directory ./Tests/RealDirTest/Empty exists.
		$this->assertSame([],  Tool::getDirectoryContents(__DIR__ . "/RealDirTest/Empty"));
	}

	public function testNonexistingDirectory() {
		$this->assertNull(Tool::getDirectoryContents(__DIR__ . "/does-not-exist"));
	}

	public function testPlainDirectory() {
		$items = Tool::getDirectoryContents("Tests/RealDirTest");
		sort($items);

		$this->assertEquals([
			'Tests/RealDirTest/Empty',
			'Tests/RealDirTest/PhpFiles',
			'Tests/RealDirTest/TextFiles',
			'Tests/RealDirTest/global.php',
			'Tests/RealDirTest/global.txt'
		], $items, "", 0.0, 10, true);
	}

	public function testPlainDirectoryWithDirectoryFilter() {
		$items = Tool::getDirectoryContents("Tests/RealDirTest", false, [new OnlyDirectoryFilter()]);
		sort($items);

		$this->assertEquals([
			'Tests/RealDirTest/Empty',
			'Tests/RealDirTest/PhpFiles',
			'Tests/RealDirTest/TextFiles'
		], $items, "", 0.0, 10, true);
	}

	public function testPlainDirectoryWithFilesFilter() {
		$items = Tool::getDirectoryContents("Tests/RealDirTest", false, [new OnlyFileFilter()]);
		sort($items);

		$this->assertEquals([
			'Tests/RealDirTest/global.php',
			'Tests/RealDirTest/global.txt'
		], $items, "", 0.0, 10, true);
	}

	public function testRecursiveDirectory() {
		$items = Tool::getDirectoryContents("Tests/RealDirTest", true);
		sort($items);

		$this->assertEquals([
			'Tests/RealDirTest/Empty',

			'Tests/RealDirTest/PhpFiles',
			'Tests/RealDirTest/PhpFiles/file1.php',
			'Tests/RealDirTest/PhpFiles/file2.php',
			'Tests/RealDirTest/PhpFiles/file3.php',

			'Tests/RealDirTest/TextFiles',
			'Tests/RealDirTest/TextFiles/Last',
			'Tests/RealDirTest/TextFiles/Last/file4.txt',

			'Tests/RealDirTest/TextFiles/file1.txt',
			'Tests/RealDirTest/TextFiles/file2.txt',
			'Tests/RealDirTest/TextFiles/file3.txt',

			'Tests/RealDirTest/global.php',
			'Tests/RealDirTest/global.txt'
		], $items, "", 0.0, 10, true);
	}

	public function testRecursiveDirectoryWithPHPOnly() {
		$items = Tool::getDirectoryContents("Tests/RealDirTest", true, [new MatchGlobFilter("*.php")]);
		sort($items);

		$this->assertEquals([
			'Tests/RealDirTest/PhpFiles/file1.php',
			'Tests/RealDirTest/PhpFiles/file2.php',
			'Tests/RealDirTest/PhpFiles/file3.php',

			'Tests/RealDirTest/global.php'
		], $items, "", 0.0, 10, true);
	}
}
