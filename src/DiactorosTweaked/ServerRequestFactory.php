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

use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Extends Laminas Diactoros' ServerRequestFactory for use with Boson.
 *
 * Laminas Diactoros excepts all URLs to have a scheme of "http" or "https". This is not the case with Boson. We have to
 * use custom schemes to allow for PSR-7 handling of internal URIs. We achieve that by extending the factory and using
 * our customised URI class which is aware of custom schemes.
 */
class ServerRequestFactory extends \Laminas\Diactoros\ServerRequestFactory
{
	public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
	{
		$uploadedFiles = [];

		if (is_string($uri)) {
			$uri = new Uri($uri);
		}

		return new ServerRequest(
			$serverParams,
			$uploadedFiles,
			$uri,
			$method,
			'php://temp'
		);
	}

}