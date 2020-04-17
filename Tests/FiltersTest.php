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
 * FiltersTest.php
 * php-path-tools
 *
 * Created on 17.04.20 16:24 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Util\FileFilter\CallbackFilter;
use TASoft\Util\FileFilter\Compound\ANDCompound;
use TASoft\Util\FileFilter\Compound\NOTCompound;
use TASoft\Util\FileFilter\Compound\ORCompound;
use TASoft\Util\FileFilter\Compound\XORCompound;
use TASoft\Util\FileFilter\MatchGlobFilter;
use TASoft\Util\FileFilter\MatchRegexFilter;
use TASoft\Util\FileFilter\OnlyDirectoryFilter;
use TASoft\Util\FileFilter\OnlyFileFilter;

class FiltersTest extends TestCase
{
	public function testCallbackFilter() {
		$filter = new CallbackFilter(function($f) { return $f == 'test'; });

		$this->assertTrue($filter->acceptsFile("test"));
		$this->assertFalse($filter->acceptsFile("not-test"));
	}

	public function testMatchGlobFilter() {
		$filter = new MatchGlobFilter("*.txt");

		$this->assertTrue($filter->acceptsFile("my-test-file.txt"));
		$this->assertFalse($filter->acceptsFile("my-test-file.php"));
	}

	public function testMatchRegexFilter() {
		$filter = new MatchRegexFilter("/^my\-(file|test)\.tx?t$/i");

		$this->assertTrue($filter->acceptsFile("my-file.txt"));
		$this->assertTrue($filter->acceptsFile("my-test.tt"));
		$this->assertTrue($filter->acceptsFile("my-file.tt"));
		$this->assertTrue($filter->acceptsFile("my-test.txt"));

		$this->assertFalse($filter->acceptsFile("my-file.html"));
		$this->assertFalse($filter->acceptsFile("this-is-my-file.txt"));
		$this->assertFalse($filter->acceptsFile("my-file.tty"));
		$this->assertFalse($filter->acceptsFile("my-file.t"));
	}

	public function testDirectoryFilter() {
		$filter = new OnlyDirectoryFilter();

		$this->assertTrue($filter->acceptsFile(__DIR__));
		$this->assertFalse($filter->acceptsFile(__FILE__));
	}

	public function testFileFilter() {
		$filter = new OnlyFileFilter();

		$this->assertFalse($filter->acceptsFile(__DIR__));
		$this->assertTrue($filter->acceptsFile(__FILE__));
	}

	public function testANDCompound() {
		$filter = new ANDCompound(
			new OnlyFileFilter(),
			new MatchGlobFilter("*.txt")
		);

		$this->assertTrue($filter->acceptsFile(__DIR__ . '/test-file.txt'));

		// GLOB does not match
		$this->assertFalse($filter->acceptsFile(__FILE__));

		// Not a file
		$this->assertFalse($filter->acceptsFile('some-file.txt'));
	}

	public function testORComponent() {
		$filter = new ORCompound(
			new OnlyFileFilter(),
			new OnlyDirectoryFilter()
		);

		$this->assertTrue($filter->acceptsFile(__DIR__));
		$this->assertTrue($filter->acceptsFile(__FILE__));

		$this->assertFalse($filter->acceptsFile('nonexisting-file-or-directory'));
	}

	public function testXORComponent() {
		$filter = new XORCompound(
			new MatchGlobFilter("txt.*"),
			new MatchGlobFilter("*.txt")
		);

		$this->assertTrue($filter->acceptsFile('my-file.txt'));
		$this->assertTrue($filter->acceptsFile('txt.tasoft.ch'));

		$this->assertFalse($filter->acceptsFile('txt.my-file.txt'));
	}

	public function testNOTCompound() {
		$filter = new NOTCompound(
			new XORCompound(
				new MatchGlobFilter("txt.*"),
				new MatchGlobFilter("*.txt")
			)
		);

		$this->assertFalse($filter->acceptsFile('my-file.txt'));
		$this->assertFalse($filter->acceptsFile('txt.tasoft.ch'));

		$this->assertTrue($filter->acceptsFile('txt.my-file.txt'));
	}
}
