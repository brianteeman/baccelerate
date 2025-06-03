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

namespace Dionysopoulos\BAccelarate\OSInfo;

final class OSInfo
{
	public static function getLibraryPath(): string
	{
		// Use the correct native library for the current platform.
		$library = match (OSInfo::type()->value . '/' . OSInfo::architecture()->value)
		{
			'windows/x64', 'windows/arm64' => 'libboson-windows-x86_64.dll',
			'linux/x64' => 'libboson-linux-x86_64.so',
			'linux/arm64' => 'libboson-linux-aarch64.so',
			'macos/x64', 'macos/arm64' => 'libboson-darwin-universal.dylib',
			default => null,
		};

		if ($library === null)
		{
			// TODO Throw
			die('Unsupported platform.');
		}

		if (!\Phar::running())
		{
			return DESKTOPAPP_ROOT . '/vendor/boson-php/runtime/bin/' . $library;
		}

		// We can't load a dynamic library from a PHAR archive. Copy it to a temporary location.
		$tempDir    = sys_get_temp_dir() ?: dirname(substr(DESKTOPAPP_ROOT, 7));
		$sourceFile = DESKTOPAPP_ROOT . '/vendor/boson-php/runtime/bin/' . $library;
		$targetFile = $tempDir . DIRECTORY_SEPARATOR . basename($sourceFile);

		@copy($sourceFile, $targetFile);

		return $targetFile;
	}

	public static function type(): OSType
	{
		return match (strtolower(
			PHP_OS_FAMILY
		))
		{
			'windows' => OSType::WINDOWS,
			'linux' => OSType::LINUX,
			'darwin' => OSType::MACOS,
			default => OSType::OTHER,
		};
	}

	public static function architecture(): OSArchitecture
	{
		try
		{
			$arch = @php_uname('m');
		}
		catch (\Throwable)
		{
			$arch = null;
		}

		if (empty($arch) && self::type() === OSType::WINDOWS)
		{
			$arch = getenv('PROCESSOR_ARCHITECTURE') ?: 'unknown';

			if (strtolower($arch) === 'x86' && getenv('PROCESSOR_ARCHITEW6432'))
			{
				$arch = getenv('PROCESSOR_ARCHITEW6432');
			}
		}

		return match (strtolower($arch))
		{
			'x86_64' => OSArchitecture::X64,
			'arm64', 'aarch64', 'aarch64_be', 'armv8b', 'armv8l' => OSArchitecture::ARM64,
			'arm' => OSArchitecture::ARM,
			'i386', 'i686', 'x64' => OSArchitecture::X86,
			default => OSArchitecture::UNKNOWN,
		};
	}
}