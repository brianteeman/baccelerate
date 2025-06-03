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

namespace Dionysopoulos\BAccelarate\Container;

use Boson\Bridge\Http\HttpAdapter;
use Boson\Bridge\Static\StaticAdapterInterface;
use Boson\Contracts\Http\RequestInterface;
use Boson\WebView\Api\Schemes\Event\SchemeRequestReceived;
use Dionysopoulos\BAccelarate\DiactorosTweaked\Uri;
use Dionysopoulos\BAccelarate\ExceptionHandling\RoutingExceptionHandler;
use Dionysopoulos\BAccelarate\LegacyScript\LegacyBridge;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Service provider for the SchemeRequestReceived event handler.
 *
 * This implementation allows you to map a folder to the app:// scheme. It will allow you to execute PHP script from the
 * folder, as well as serve static media files. This is the quick and dirty approach to get started by dropping your
 * web application files into the public folder.
 *
 * IMPORTANT! Your app.php needs to configure the container like so:
 *
 * ```php
 * $container = Factory::container([
 *   'schemes' => ['app'],
 *   'staticDirs' => [DESKTOPAPP_PUBLIC],
 *   'defaultRequestHandlingProvider' => \PHPDesktopApp\Container\SchemeRequestReceivedLegacyProvider::class,
 * );
 * ```
 * TODO
 *
 * @since  1.0.0
 */
class SchemeRequestReceivedLegacyProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple[SchemeRequestReceived::class] = fn(Container $c) => function (SchemeRequestReceived $e): void {
			try
			{
				$container = Factory::container();
				/** @var HttpAdapter $psr7 */
				$psr7 = $container[HttpAdapter::class];
				/** @var StaticAdapterInterface $mediaFilesRouter */
				$mediaFilesRouter = $container[StaticAdapterInterface::class];

				if (!str_starts_with($e->request->url, 'app://'))
				{
					throw new \RuntimeException('Not Found', 404);
				}

				$uri   = new Uri($e->request->url);
				$isPhp = str_ends_with(strtolower($uri->getPath()), '.php');

				if ($isPhp)
				{
					$script   = $this->findPathnameForExistingFile($e->request);
					$response = null;

					if (!empty($script))
					{
						$request      = $psr7->createRequest($e->request);
						$psr7response = LegacyBridge::run($request, $script);
						$response     = $psr7->createResponse($psr7response);
					}
				}
				else
				{
					$response = $mediaFilesRouter->lookup($e->request);
				}

				if (empty($response))
				{
					throw new \RuntimeException('Not Found', 404);
				}

				$e->response = $response;
			}
			catch (\Throwable $exception)
			{
				$handler   = new RoutingExceptionHandler();
				$errorCode = $exception->getCode() ?? 500;

				if ($errorCode < 400 || $errorCode >= 600)
				{
					$errorCode = 500;
				}

				$streamFactory = new StreamFactory;
				$bodyStream    = $streamFactory->createStream($handler($exception));
				$response      = new Response($bodyStream, $errorCode);
				$container     = Factory::container();
				$e->response   = $container[HttpAdapter::class]->createResponse($response);
			}
		};
	}

	private function findPathnameForExistingFile(RequestInterface $request): ?string
	{
		$path = \parse_url($request->url, \PHP_URL_PATH);

		if (!\is_string($path) || $path === '')
		{
			return null;
		}

		$container = Factory::container();
		$roots     = $container['staticDirs'] ?? [DESKTOPAPP_PUBLIC];

		foreach ($roots as $root)
		{
			$pathname = $root . '/' . $path;

			if (!\is_file($pathname) || !\is_readable($pathname))
			{
				continue;
			}

			return $pathname;
		}

		return null;
	}

}