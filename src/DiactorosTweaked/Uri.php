<?php
/**
 * @package   BAccelerate
 * @copyright Copyright (c)2025-2025 Nicholas K. Dionysopoulos
 * @license   MIT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace Dionysopoulos\BAccelarate\DiactorosTweaked;

use Psr\Http\Message\UriInterface;

/**
 * Extends Laminas Diactoros' Uri class to allow for custom schemes.
 *
 * Unlike the regular Laminas Diactoros' Uri you can use _any_ scheme in your URI strings.
 *
 * @since  1.0.0
 */
class Uri extends \Laminas\Diactoros\Uri
{
	/**
	 * Constructor method to initialize the object with the specified URI and configure allowed schemes.
	 *
	 * @param   string  $uri  The URI to be parsed and used for initializing the object.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function __construct(string $uri = '')
	{
		$parts = parse_url($uri);

		if ($parts !== false && isset($parts['scheme']) && !isset($this->allowedSchemes[$parts['scheme']]))
		{
			$this->allowedSchemes[$parts['scheme']] = 80;
		}

		parent::__construct($uri);
	}

	/**
	 * Updates the URI with the specified scheme and configures it as an allowed scheme.
	 *
	 * @param   string  $scheme  The scheme to be set in the URI.
	 *
	 * @return  UriInterface  A new instance of the URI with the updated scheme.
	 * @since   1.0.0
	 */
	public function withScheme(string $scheme): UriInterface
	{
		if (!isset($this->allowedSchemes[$scheme]))
		{
			$this->allowedSchemes[$scheme] = 80;
		}

		return parent::withScheme($scheme);
	}
}