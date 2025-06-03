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

use Boson\WebView\WebViewCreateInfo;
use Boson\Window\WindowCreateInfo;
use Boson\Window\WindowDecoration;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Service provider for the WindowCreateInfo object.
 *
 * This object is used by Boson to create the main application window.
 *
 * @since 1.0.0
 */
class WindowCreateInfoProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple[WindowCreateInfo::class] = fn(Container $c) => new WindowCreateInfo(
			title: $c['windowTitle'] ?? '',
			width: $c['windowWidth'] ?? WindowCreateInfo::DEFAULT_WIDTH,
			height: $c['windowHeight'] ?? WindowCreateInfo::DEFAULT_HEIGHT,
			enableHardwareAcceleration: $c['enableHardwareAcceleration'] ?? true,
			visible: $c['windowVisible'] ?? true,
			resizable: $c['windowResizable'] ?? true,
			alwaysOnTop: $c['windowAlwaysOnTop'] ?? false,
			clickThrough: $c['windowClickThrough'] ?? false,
			decoration: $c['windowDecoration'] ?? WindowDecoration::Default,
			webview: $c[WebViewCreateInfo::class] ?? new WebViewCreateInfo(),
		);
	}
}