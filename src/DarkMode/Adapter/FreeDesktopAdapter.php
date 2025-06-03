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

namespace Dionysopoulos\BAccelarate\DarkMode\Adapter;

use Dionysopoulos\BAccelarate\DarkMode\DarkModeAdapterInterface;

/**
 * Linux FreeDesktop Dark Mode detection adapter.
 *
 * @since  1.0.0
 */
final class FreeDesktopAdapter implements DarkModeAdapterInterface
{
	/** @inheritDoc */
	public function isCompatible(): bool
	{
		return strtolower(PHP_OS) === 'linux';
	}

	/** @inheritDoc */
	public function isDarkMode(): bool
	{
		$output = shell_exec('gdbus call --session --dest org.freedesktop.portal.Desktop --object-path /org/freedesktop/portal/desktop --method org.freedesktop.portal.Settings.Read \'org.freedesktop.appearance\' \'color-scheme\'') ?? '';

		if (strtolower(trim($output)) === '(uint32 1,)')
		{
			return true;
		}
		elseif (strtolower(trim($output)) === '(uint32 0,)')
		{
			return false;
		}

		$output = shell_exec('gsettings get org.gnome.desktop.interface color-scheme') ?? '';

		return str_contains(strtolower($output), 'prefer-dark');
	}
}