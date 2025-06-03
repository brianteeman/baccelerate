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

/**
 * This class allows for stacking multiple exception handlers and invoking them sequentially.
 *
 * @since  1.0.0
 */
final readonly class StackableExceptionHandler implements ExceptionHandlerInterface
{
	/**
	 * Constructor.
	 *
	 * @param   ExceptionHandlerInterface[]  $handlerStack  The stack of exception handlers.
	 *
	 * @since   1.0.0
	 */
	public function __construct(private array $handlerStack = []) {}

	/**
	 * Invokes the exception handlers in the stack for the given exception.
	 *
	 * @param   \Throwable  $exception  The exception to be handled.
	 *
	 * @return  void
	 * @since   1.0.0
	 */
	public function __invoke(\Throwable $exception): void
	{
		foreach ($this->handlerStack as $handler)
		{
			$handler($exception);
		}
	}
}