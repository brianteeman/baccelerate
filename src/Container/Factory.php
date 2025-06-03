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

/**
 * Container factory.
 *
 * An easy way to always have access to the Singleton container instance.
 *
 * @since  1.0.0
 */
final class Factory
{
	/**
	 * The container instance
	 *
	 * @var Container
	 */
	private static Container $container;

	/**
	 * Returns the container instance.
	 *
	 * IMPORTANT! You can only use the `$config` parameter ONCE, the very first time you call this method. Further calls
	 * will always return the same instance, regardless of the `$config` parameter value. You can still override the
	 * services defined in the container using its `extend()` method.
	 *
	 * @param   array  $config  The container configuration.
	 *
	 * @return  Container
	 */
	public static function container(array $config = []): Container
	{
		if (isset(self::$container) && !empty($config))
		{
			throw new \InvalidArgumentException('You can only pass the $config parameter ONCE to the container() method.');
		}

		return self::$container ??= new Container($config);
	}
}