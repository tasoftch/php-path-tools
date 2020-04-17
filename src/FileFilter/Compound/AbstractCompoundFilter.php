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

namespace TASoft\Util\FileFilter\Compound;

use TASoft\Util\FileFilter\FilterInterface;

abstract class AbstractCompoundFilter implements FilterInterface
{
	/** @var FilterInterface|null */
	public $filter1, $filter2;

	/**
	 * AbstractCompountFilter constructor.
	 * @param FilterInterface|null $filter1
	 * @param FilterInterface|null $filter2
	 */
	public function __construct(FilterInterface $filter1, FilterInterface $filter2 = NULL)
	{
		$this->filter1 = $filter1;
		$this->filter2 = $filter2;
	}

	/**
	 * @return FilterInterface
	 */
	public function getFilter1(): FilterInterface
	{
		return $this->filter1;
	}

	/**
	 * @return FilterInterface|null
	 */
	public function getFilter2(): ?FilterInterface
	{
		return $this->filter2;
	}
}