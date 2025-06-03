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
use Boson\WebView\Api\Schemes\Event\SchemeRequestReceived;
use Dionysopoulos\BAccelarate\ExceptionHandling\RoutingExceptionHandler;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\StreamFactory;
use League\Route\Router;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Service provider for the SchemeRequestReceived event handler.
 *
 * The default implementation of this event handler is a very simple one which handles the media:// and app:// schemes.
 * The media:// scheme is mapped to static files, whereas the app:// scheme is mapped to the PSR-7 router by
 * ThePHPLeague.
 *
 * IMPORTANT! If you redefine the available application schemes, or if you want to use a more traditional routing under
 * a single scheme, you should override this service provider and provide your own implementation.
 *
 * @since  1.0.0
 */
class SchemeRequestReceivedSplitProvider implements ServiceProviderInterface
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
				/** @var Router $router */
				$router = $container[Router::class];

				// Handle the media:// scheme
				if (str_starts_with($e->request->url, 'media://'))
				{
					$e->response = $mediaFilesRouter->lookup($e->request);
				}

				// Handle the app:// scheme
				if (str_starts_with($e->request->url, 'app://'))
				{
					$request     = $psr7->createRequest($e->request);
					$response    = $router->dispatch($request);
					$e->response = $psr7->createResponse($response);
				}

				if ($e->response === null)
				{
					throw new \RuntimeException('Not Found', 404);
				}
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
}