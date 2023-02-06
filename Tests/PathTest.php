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
 * PathTest.php
 * php-path-tools
 *
 * Created on 2019-04-25 22:33 by thomas
 */

use PHPUnit\Framework\TestCase;
use TASoft\Util\PathTool as Tool;

class PathTest extends TestCase
{
    public function testZeroPaths() {
        $this->assertTrue(Tool::isZeroPath("/my/app/data.db"));
        $this->assertTrue(Tool::isZeroPath("/my/../data.db"));

        $this->assertFalse(Tool::isZeroPath("my/app/data.db"));
        $this->assertFalse(Tool::isZeroPath("../../data.db"));

        $this->assertFalse(Tool::isZeroPath(""));
    }

    public function testDirectories() {
        $this->assertTrue(Tool::isDirectory("/my/path/to/dir/"));
        $this->assertTrue(Tool::isDirectory("../to/dir.txt/"));
        $this->assertTrue(Tool::isDirectory("../"));
        $this->assertTrue(Tool::isDirectory("./"));

        $this->assertFalse(Tool::isDirectory("/my/path/to/dir/file.txt"));
        $this->assertFalse(Tool::isDirectory("../to/dir.txt"));
        $this->assertFalse(Tool::isDirectory("file"));
        $this->assertFalse(Tool::isDirectory("t.txt"));

        $this->assertFalse(Tool::isDirectory(""));
    }

    public function testUnfilteredPathComponents() {
        $cmps = ["/", "my", ".", "tasoft", "..", "apps", "", "..", "file.txt"];

        foreach(Tool::yieldPathComponents("/my/./tasoft/../apps//../file.txt", Tool::OPTION_YIELD_ROOT) as $cmp) {
            $this->assertEquals(array_shift($cmps), $cmp);
        }
    }
    public function testFilteredPathComponents() {
        $cmps = ["/", "my", "file.txt"];

        foreach(Tool::yieldPathComponents("/my/./tasoft/../apps//../file.txt") as $cmp) {
            $this->assertEquals(array_shift($cmps), $cmp);
        }
    }

    public function testRelativePath() {
        $this->assertEquals("", Tool::relative("/my/path/to/file.txt", "/my/path/to/file.txt"));
        $this->assertEquals("file.txt", Tool::relative("/my/path/to/", "/my/path/to/file.txt"));
        $this->assertEquals("to/file.txt", Tool::relative("/my/path/to", "/my/path/to/file.txt"));
        $this->assertEquals("../file.txt", Tool::relative("/my/path/to/different/file.txt", "/my/path/to/file.txt"));

        $this->assertEquals("../file.txt", Tool::relative("/my/path/../path/to/different/file.txt", "/my/./path/to/file.txt"));


        $this->assertEquals("directory", Tool::relative("/my/path/to/file.txt", "/my/path/to/directory"));
        $this->assertEquals("directory", Tool::relative("/my/path/to/", "/my/path/to/directory"));
        $this->assertEquals("my/path/to/directory/", Tool::relative("/my", "/my/path/to/directory/"));
        $this->assertEquals("path/to/directory/", Tool::relative("/my/test.txt", "/my/path/to/directory/"));

        $this->assertEquals("", Tool::relative("/my/path/to/directory", "/my/path/to/directory"));
        $this->assertEquals("../directory", Tool::relative("/my/path/to/dir/", "/my/path/to/directory"));
        $this->assertEquals("./", Tool::relative("/my/path/to/directory/file.txt", "/my/path/to/directory/"));

        $this->assertEquals("../", Tool::relative("/my/path/to/directory/file.txt/", "/my/path/to/directory/"));

        $this->assertEquals("2", Tool::relative("/my/dir/1", "/my/dir/2"));
        $this->assertEquals("../2", Tool::relative("/my/dir/1/", "/my/dir/2"));

        $this->assertEquals("2/", Tool::relative("/my/dir/1", "/my/dir/2/"));
        $this->assertEquals("../2/", Tool::relative("/my/dir/1/", "/my/dir/2/"));
    }


    /**
     * @expectedException \RuntimeException
     */
    public function testRelativePathFailed() {
		$this->expectException(RuntimeException::class);
        Tool::relative("/my/path/to/file.txt", "path/to/file.txt");
    }



    public function testNormalizePath() {
        $this->assertEquals("/my/path/to/file.txt", Tool::normalize("/my/path/to/file.txt"));
        $this->assertEquals("/my/path/to/file.txt", Tool::normalize("/my/path/to/other/../file.txt"));
        $this->assertEquals("/my/path/to/file.txt", Tool::normalize("/my/path//////to/file.txt"));
        $this->assertEquals("/my/path/to/file.txt", Tool::normalize("/my/.././my/path/to/file.txt"));

        $this->assertEquals("my/rel/file.txt", Tool::normalize("test/../my///rel/./file.txt"));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNormalizeFailedPath() {
        $this->assertEquals("../path/to/file.txt", Tool::normalize("my/../../path/to/file.txt"));
		$this->expectException(RuntimeException::class);
        echo Tool::normalize("/my/../../path/to/file.txt");
        //      Out of range!              ^^
    }

    public function testExtension() {
    	$this->assertEquals("txt", Tool::getPathExtension("/my/path/to/target.txt"));
		$this->assertEquals("php", Tool::getPathExtension("/my/path/to/target.php", $name));

		$this->assertEquals("target", $name);
	}

	public function testPathName() {
		$this->assertEquals("target", Tool::getPathName("/my/path/to/target.txt"));
		$this->assertEquals("my-file", Tool::getPathName("/my/path/to/my-file.php", $ext));

		$this->assertEquals("php", $ext);
	}
}
