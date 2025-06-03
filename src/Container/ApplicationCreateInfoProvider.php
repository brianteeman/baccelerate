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

use Boson\ApplicationCreateInfo;
use Boson\Window\WindowCreateInfo;
use Dionysopoulos\BAccelarate\OSInfo\OSInfo;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Service provider for the Boson ApplicationCreateInfo object.
 *
 * Boson uses this object to configure the application.
 *
 * IMPORTANT! If you choose to customise this provider, you are recommended to leave the library path as-is. It takes
 * into account when the application is running under PHAR, or as a native application, in which case it extracts the
 * native binary to a temporary path.
 *
 * IMPORTANT! If you customise the schemes you also need to override the SchemeRequestReceived provider.
 *
 * @since  1.0.0
 */
class ApplicationCreateInfoProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple[ApplicationCreateInfo::class] = fn(Container $c) => new ApplicationCreateInfo(
			name: $c['name'],
			schemes: ['app', 'media'],
			threads: $c['threads'],
			debug: $c['debug'],
			library: OSInfo::getLibraryPath(),
			quitOnClose: $c['quitOnClose'],
			autorun: $c['autorun'],
			window: $c[WindowCreateInfo::class] ?? null,
		);
	}
}