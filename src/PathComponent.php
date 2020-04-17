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

/**
 * Simple path component representation to work while manipulating paths
 * Components of this class are yielded by PathTool::yieldPathComponents() with option OPTION_YIELD_COMPONENT
 *
 * @package TASoft\Util
 * @see PathTool::yieldPathComponents()
 * @see PathTool::OPTION_YIELD_COMPONENT
 */
class PathComponent
{
    /** @var bool */
    private $directory;

    /** @var string */
    private $name;

    /** @var bool  */
    private $_final;

	/**
	 * PathComponent constructor.
	 * @param bool $directory
	 * @param string $name
	 * @param bool $final
	 */
    public function __construct(bool $directory, string $name, bool $final = false)
    {
        $this->directory = $directory;
        $this->name = $name;
        $this->_final = $final;
    }

    /**
     * @return bool
     */
    public function isDirectory(): bool
    {
        return $this->directory;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isFinal(): bool
    {
        return $this->_final;
    }

    /**
     * Determine if two path components are equal
     *
     * @param $otherComponent
     * @return bool
     */
    public function isEqual($otherComponent): bool {
        if($otherComponent instanceof self) {
            return $this->name == $otherComponent->name && $this->directory == $otherComponent->directory;
        }
        return false;
    }
}