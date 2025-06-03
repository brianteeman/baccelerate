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

declare(strict_types=1);

namespace Dionysopoulos\BAccelarate\LegacyScript;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use function PHPDesktopApp\LegacyScript\headers_clear;

final class LegacyBridge
{
	/**
	 * Execute a legacy script and return a PSR-7 response.
	 *
	 * @param   ServerRequestInterface  $request  The incoming request.
	 * @param   string|callable         $script   Path to the legacy script or a callable.
	 *
	 * @throws \Throwable Anything thrown by the legacy script will bubble up.
	 */
	public static function run(ServerRequestInterface $request, string|callable $script): ResponseInterface
	{
		/* -------------------------------------------------
		 * 1. Backup super-globals
		 * ------------------------------------------------- */
		$backup = [
			'_SERVER'  => $_SERVER,
			'_GET'     => $_GET,
			'_POST'    => $_POST,
			'_COOKIE'  => $_COOKIE,
			'_FILES'   => $_FILES,
			'_REQUEST' => $_REQUEST,
		];

		try
		{
			/* -------------------------------------------------
			 * 2. Hydrate super-globals from the PSR-7 request
			 * ------------------------------------------------- */
			$_SERVER  = self::buildServer($request);
			$_COOKIE  = $request->getCookieParams();
			$_GET     = $request->getQueryParams();
			$_POST    = $request->getParsedBody() ?? [];
			$_FILES   = self::buildFiles($request->getUploadedFiles());
			$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);

			/* -------------------------------------------------
			 * 3. Execute the legacy script
			 * ------------------------------------------------- */
			ob_start();

			if (\is_callable($script))
			{
				$script();               // invoke the callable
			}
			else
			{
				require $script;         // include the legacy PHP file
			}

			$bodyContent = ob_get_clean() ?: '';

			/* -------------------------------------------------
			 * 4. Collect headers set via header()
			 * ------------------------------------------------- */
			$headerLines = headers_list();
			headers_sent() && headers_clear(); // avoid double-sending later

			[$statusCode, $psrHeaders] = self::parseHeaders($headerLines);

			/* -------------------------------------------------
			 * 5. Build PSR-7 response
			 * ------------------------------------------------- */
			$streamFactory = new StreamFactory;
			$body          = $streamFactory->createStream($bodyContent);
			$response      = (new Response($body, $statusCode));

			foreach ($psrHeaders as $name => $values)
			{
				foreach ($values as $value)
				{
					$response = $response->withAddedHeader($name, $value);
				}
			}

			return $response;
		}
		finally
		{
			/* -------------------------------------------------
			 * 6. Restore global state
			 * ------------------------------------------------- */
			foreach ($backup as $key => $value)
			{
				$GLOBALS[$key] = $value;
			}
		}
	}

	/* -------------------------------------------------
	 * Helpers
	 * ------------------------------------------------- */

	private static function buildServer(ServerRequestInterface $req): array
	{
		$server = $req->getServerParams();

		// populate commonly expected keys
		$server['REQUEST_METHOD'] = $req->getMethod();
		$server['REQUEST_URI']    = (string) $req->getUri()->getPath();
		$server['QUERY_STRING']   = $req->getUri()->getQuery();
		$server['HTTP_HOST']      = $req->getUri()->getHost();
		$server['HTTPS']          = $req->getUri()->getScheme() === 'https';

		// PSR-7 headers  $_SERVER style (HTTP_FOO_BAR)
		foreach ($req->getHeaders() as $name => $values)
		{
			$key          = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
			$server[$key] = implode(', ', $values);
		}

		return $server;
	}

	/**
	 * Transform UploadedFileInterface trees into the array structure used by $_FILES.
	 */
	private static function buildFiles(array $uploaded): array
	{
		$normalize = static function ($file) use (&$normalize) {
			if ($file instanceof \Psr\Http\Message\UploadedFileInterface)
			{
				return [
					'name'     => $file->getClientFilename(),
					'type'     => $file->getClientMediaType(),
					'size'     => $file->getSize(),
					'tmp_name' => $file->getStream()->getMetadata('uri') ?? '',
					'error'    => $file->getError(),
				];
			}
			// Nested array – walk it recursively
			foreach ($file as $idx => $item)
			{
				$file[$idx] = $normalize($item);
			}

			return $file;
		};

		return $normalize($uploaded);
	}

	/**
	 * Turn raw header lines into [statusCode, headers[]].
	 *
	 * Example header line formats:
	 *   "HTTP/1.1 404 Not Found"
	 *   "Content-Type: text/html"
	 */
	private static function parseHeaders(array $headerLines): array
	{
		$statusCode = 200;
		$headers    = [];

		foreach ($headerLines as $line)
		{
			if (\str_starts_with($line, 'HTTP/'))
			{
				// e.g. HTTP/1.1 404 Not Found
				[$protocol, $code] = \array_pad(\explode(' ', $line, 3), 3, null);
				$statusCode = (int) $code;
				continue;
			}

			[$name, $value] = \explode(':', $line, 2);
			$name  = trim($name);
			$value = trim($value);

			$headers[$name][] = $value;
		}

		return [$statusCode, $headers];
	}

}