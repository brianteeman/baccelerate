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

namespace Dionysopoulos\BAccelarate\DarkMode;

use Boson\Window\WindowDecoration;
use Dionysopoulos\BAccelarate\DarkMode\Adapter\FreeDesktopAdapter;
use Dionysopoulos\BAccelarate\DarkMode\Adapter\macOSAdapter;
use Dionysopoulos\BAccelarate\DarkMode\Adapter\WindowsAdapter;

/**
 * Detects the user's Dark Mode setting.
 *
 * @since  1.0.0
 */
final class DetectDarkMode
{
	/**
	 * The list of available Dark Mode detection adapters (OS-specific).
	 *
	 * @var   string[]
	 * @since 1.0.0
	 */
	private const array ADAPTERS = [
		macOSAdapter::class,
		FreeDesktopAdapter::class,
		WindowsAdapter::class,
	];

	/**
	 * Determines if the application is currently in dark mode.
	 *
	 * This method iterates through the available adapters to check if any of them
	 * are compatible and indicate dark mode is enabled.
	 *
	 * @return  bool  True if dark mode is enabled, false otherwise.
	 * @since   1.0.0
	 */
	public function isDarkMode(): bool
	{
		foreach (self::ADAPTERS as $adapter)
		{
			$adapter = new $adapter();

			if ($adapter->isCompatible() && $adapter->isDarkMode())
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns the preferred window decoration for the current environment.
	 *
	 * This method iterates through the available adapters to check if any of them
	 * are compatible and indicate dark mode is enabled.
	 *
	 * If dark mode is enabled, the window decoration will be set to dark mode.
	 * If dark mode is not enabled, the window decoration will be set to default.
	 *
	 * @return  WindowDecoration
	 * @since   1.0.0
	 */
	public static function getPreferredWindowDecoration(): WindowDecoration
	{
		$detect = new self();

		return $detect->isDarkMode() ? WindowDecoration::DarkMode : WindowDecoration::Default;
	}
}