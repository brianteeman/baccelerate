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

namespace Dionysopoulos\BAccelarate\ExceptionHandling;

use Boson\WebView\WebView;
use Dionysopoulos\BAccelarate\Container\Factory;

/**
 * An exception handler which displays a simple HTML page with the exception details.
 *
 * @since  1.0.0
 */
readonly class HtmlExceptionHandler implements ExceptionHandlerInterface
{
	/**
	 * Constructor.
	 *
	 * @param   WebView      $webView  The WebView instance where the error will be displayed.
	 * @param   string|null  $template The path to the HTML error template. NULL for the default template.
	 *
	 * @since   1.0.0
	 */
	public function __construct(private WebView $webView, private ?string $template = null) {}

	/**
	 * Handles the invocation of the object to process an exception and display an error page.
	 *
	 * @param   \Throwable  $exception  The exception to be processed and displayed.
	 *
	 * @return  void
	 */
	public function __invoke(\Throwable $exception): void
	{
		$container = Factory::container();

		@ob_start();
		include $this->template ?? $container['errorPage'];

		$this->webView->html = (@ob_get_clean());
	}
}