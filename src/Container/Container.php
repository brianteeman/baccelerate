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

use Boson\Application;
use Boson\ApplicationCreateInfo;
use Boson\Bridge\Http\HttpAdapter;
use Boson\Bridge\Static\StaticAdapterInterface;
use Boson\WebView\Api\Schemes\Event\SchemeRequestReceived;
use Boson\WebView\WebViewCreateInfo;
use Boson\Window\WindowCreateInfo;
use Boson\Window\WindowDecoration;
use Dionysopoulos\BAccelarate\DarkMode\DetectDarkMode;
use League\Route\Router;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * The application container.
 *
 * @since  1.0.0
 */
class Container extends \Pimple\Container
{
	/**
	 * The PSR-11 instance of the container.
	 *
	 * @var   ContainerInterface|null
	 * @since 1.0.0
	 */
	private ?ContainerInterface $psr11 = null;

	public function __construct(array $values = [])
	{
		/**
		 * BAccelerator-specific configuration.
		 */
		// Should I automatically detect Dark Mode?
		$values['autoDark'] ??= true;
		// The list of static files directories.
		$values['staticDirs'] ??= [DESKTOPAPP_MEDIA];
		// The default request handling provider.
		$values['defaultRequestHandlingProvider'] ??= SchemeRequestReceivedSplitProvider::class;;
		// The default exception display page.
		$values['errorPage'] ??= __DIR__ . '/../ExceptionHandling/error.php';

		/**
		 * Application creation information.
		 *
		 * These values apply if you do not override the ApplicationCreateInfo service provider.
		 *
		 * @see \Boson\ApplicationCreateInfo::__construct
		 */
		// The name of the application (NOT necessarily the same as the window name).
		$values['name'] ??= 'Boson Quick Start';
		// The schemes to register. If you change this, remember to override the SchemeRequestReceived service.
		$values['schemes'] ??= ['app', 'media'];
		// How many threads to use. Leave NULL to use the platform default.
		$values['threads'] ??= null;
		// Should I show dev tools?
		$values['debug'] ??= false;
		// Quit the application when the last window is closed?
		$values['quitOnClose'] ??= true;
		// Should I automatically launch the application?
		$values['autorun'] ??= true;

		/**
		 * Window creation information.
		 *
		 * These values apply if you do not override the WindowCreateInfo service provider.
		 *
		 * @see \Boson\WindowCreateInfo::__construct
		 */
		// The window title.
		$values['windowTitle'] ??= '';
		// The window width.
		$values['windowWidth'] ??= WindowCreateInfo::DEFAULT_WIDTH;
		// The window height.
		$values['windowHeight'] ??= WindowCreateInfo::DEFAULT_HEIGHT;
		// Should I enable hardware acceleration?
		$values['enableHardwareAcceleration'] ??= true;
		// Should I show the window?
		$values['windowVisible'] ??= true;
		// Should I allow the window to be resized?
		$values['windowResizable'] ??= true;
		// Should I always show the window on top of other windows?
		$values['windowAlwaysOnTop'] ??= false;
		// Should the clicks pass through the window without being handled?
		$values['windowClickThrough'] ??= false;
		// The window decoration style.
		$values['windowDecoration'] ??= $values['autoDark']
			? DetectDarkMode::getPreferredWindowDecoration()
			: WindowDecoration::Default;

		/**
		 * The custom override for the WebViewCreateInfo configuration object.
		 *
		 * This not a very common thing to do, as you can manipulate the WebView directly before running
		 * the application.
		 */
		$values[WebViewCreateInfo::class] ??= null;

		// The application event dispatcher interface. Leave null to use Boson's default implementation.
		$values[EventDispatcherInterface::class] ??= null;

		parent::__construct($values);

		/**
		 * Register the default services. You can override them in your application.
		 *
		 * @link https://github.com/silexphp/Pimple?tab=readme-ov-file#modifying-services-after-definition
		 */
		if (!isset($this[ApplicationCreateInfo::class]))
		{
			$this->register(new ApplicationCreateInfoProvider());
		}

		if (!isset($this[WindowCreateInfo::class]))
		{
			$this->register(new WindowCreateInfoProvider());
		}

		if (!isset($this[Application::class]))
		{
			$this->register(new ApplicationProvider());
		}

		if (!isset($this[Router::class]))
		{
			$this->register(new RouterProvider());
		}

		if (!isset($this[HttpAdapter::class]))
		{
			$this->register(new HttpAdapterProvider());
		}

		if (!isset($this[StaticAdapterInterface::class]))
		{
			$this->register(new FilesystemStaticAdapterProvider());
		}

		if (!isset($this[SchemeRequestReceived::class]))
		{
			$className = $values['defaultRequestHandlingProvider'] ?? SchemeRequestReceivedSplitProvider::class;

			$this->register(new $className());
		}
	}

	/**
	 * Returns the container as a PSR-11 container.
	 *
	 * @return  ContainerInterface
	 * @since   1.0.0
	 */
	final public function asPsr11(): ContainerInterface
	{
		return $this->psr11 ??= new \Pimple\Psr11\Container($this);
	}
}