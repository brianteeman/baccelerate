# BAccelerate

Accelerated desktop application development with Boson.

_Disclaimer: This project is not affiliated with or endorsed by the developers of Boson._

## Why BAccelerate?

[Boson](https://bosonphp.com/overview.html) is an excellent tool for producing lightweight desktop applications with PHP. It does not use Electron, it does not hog TCP ports for running servers (there is no server running, actually!), and does not prescribe the use of a specific framework.

The downside is that Boson is barebones and low-level. You need some effort to figure out how to get started. It also only supports PSR-7 routing which makes it hard to use with legacy scripts, and frameworks which do not rely on HTTP messages (e.g. Joomla, WordPress, …).

BAccelerate solves these problems by providing the following facilities:

* A Dependency Injection container.
* Automatic Dark Mode detection (cross-platform!).
* Non-PSR-7 application routing for quick'n'dirty appification of legacy scripts.
* Improved exception handling.
* Sensible instructions to create native binaries.

## Getting started

The easiest way to get started is with the Boson Quick Start template project by the same author.

Create a new project with:
```shell
composer create-project nikosdion/boson-quick-start my-path
```

This will set up a new project for PSR-7 applications – with comments telling you how to convert it for use with legacy scripts – using BAccelerate and Boson. It will automatically install all the dependencies.

Using the template project, you also get instructions for compiling your application into a redistributable binary.

If you'd rather go it on your own, follow the instructions below.

### PSR-7 applications

Create the following project structure:

- `media` Your static media files
- `src` Your application's source files
- `views` (optional) The recommended folder for your view template files.
- `app.php` Your application entry point

Copy your application's PHP files into the `src` folder. Any static media files go into the `media` folder.

Run: 
```shell
composer require nikosdion/baccelerate
composer require league/route
```

Use the following `app.php` code:

```php
use Boson\Application;
use Boson\WebView\Api\Schemes\Event\SchemeRequestReceived;
use Dionysopoulos\BAccelarate\Container\Factory;
use Dionysopoulos\BAccelarate\OSInfo\OSInfo;
use Laminas\Diactoros\Response;
use League\Route\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

// These constants are required.
define('DESKTOPAPP_ROOT', __DIR__);
define('DESKTOPAPP_MEDIA', DESKTOPAPP_ROOT . '/media');
define('DESKTOPAPP_PUBLIC', DESKTOPAPP_ROOT . '/public');
define('DESKTOPAPP_VIEWS', DESKTOPAPP_ROOT . '/views');

require DESKTOPAPP_ROOT . '/vendor/autoload.php';

$container = Factory::container();

// Create your routes here using ThePHPLeague Router. Here's an example.
$container[Router::class]->map(
	'GET',
	'/',
	function (ServerRequestInterface $request): ResponseInterface {
		$response = new Response;
		$content = file_get_contents(DESKTOPAPP_VIEWS . '/routed.php');
		$response->getBody()->write($content);

		return $response;
	}
);

$app = $container[Application::class];
$app->on($container[SchemeRequestReceived::class]);
$app->webview->url = 'app://app.internal/';
$app->run();

if (Phar::running())
{
	@unlink(OSInfo::getLibraryPath());
}
```

### Legacy (non-PSR-7) applications

Create the following project structure:

- `static` Your static media files, and legacy scripts
- `app.php` Your application entry point

Copy your application's PHP files into the `static` folder. For the sake of documentation, we'll assume you have a file named `index.php` in there.

Run `composer require nikosdion/baccelerate`.

Use the following `app.php` code:

```php
use Boson\Application;
use Boson\WebView\Api\Schemes\Event\SchemeRequestReceived;
use Dionysopoulos\BAccelarate\Container\Factory;
use Dionysopoulos\BAccelarate\Container\SchemeRequestReceivedLegacyProvider;
use Dionysopoulos\BAccelarate\OSInfo\OSInfo;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

define('DESKTOPAPP_ROOT', __DIR__);
define('DESKTOPAPP_MEDIA', DESKTOPAPP_ROOT . '/media');
define('DESKTOPAPP_PUBLIC', DESKTOPAPP_ROOT . '/public');
define('DESKTOPAPP_VIEWS', DESKTOPAPP_ROOT . '/views');

require DESKTOPAPP_ROOT . '/vendor/autoload.php';

$container = Factory::container([
	'staticDirs'                     => [DESKTOPAPP_PUBLIC],
	'defaultRequestHandlingProvider' => SchemeRequestReceivedLegacyProvider::class,
]);

$app = $container[Application::class];
$app->on($container[SchemeRequestReceived::class]);
$app->webview->url = 'app://app.internal/index.php';
$app->run();

if (Phar::running())
{
	@unlink(OSInfo::getLibraryPath());
}
```

Note: If you need to specify your PHP application's URL in its configuration, it is `app://app.internal/`.

Run `app.php` to start your Boson application!

### Making binaries

The idea is that you will pack your application as a PHAR archive, then make it into a self-executing file with PHPacker and a suitable PHP micro SAPI.

> **IMPORTANT!** Do not use Box to create your PHAR archives. Due to the additional "magic" in a Box-prepared PHAR package the resulting binary _will fail to execute_ without a helpful error message.

Install [PHPacker](https://phpacker.dev) and [Phing](https://www.phing.info). Make sure their executables – `phpacker` and `phing`, respectively – are in your PATH.

Create a directory named `releases` under your repository's root; this is where binaries will be built. 

Create a PHAR stub file named `stub.php` alongside your `app.php` file:

```php
Phar::mapPhar('app.phar');
Phar::interceptFileFuncs();
require_once 'phar://app.phar/app.php';
__HALT_COMPILER();
```

Create a Phing build file named `build.xml` alongside these two files:

```xml
<?xml version="1.0"?>
<project name="MyApp" description="MyBosonDesktopApp" default="build">
    <target name="build" depends="nativephar, phpacker" />
    <target name="nativephar">
        <pharpackage basedir="${phing.dir}" destfile="${phing.dir}/releases/app.phar"
                     compression="gzip" stub="${phing.dir}/stub.php" signature="sha1">
            <fileset dir="${phing.dir}">
                <include name="app.php"/>
                <!-- Customise this, depending on which folders you have in your project -->
                <include name="media/**"/>
                <include name="public/**"/>
                <include name="vendor/**"/>
                <include name="views/**"/>
                <include name="src/**"/>
            </fileset>
            <metadata>
                <element name="version" value="1.0.0"/>
                <element name="authors">
                    <!-- Enter your own name / company name here -->
                    <element name="John Q. Public">
                        <element name="e-mail" value="john@example.com"/>
                    </element>
                </element>
            </metadata>
        </pharpackage>
    </target>
    <target name="phpacker">
        <exec executable="phpacker" dir="${phing.dir}" passthru="true" checkreturn="true">
            <arg value="build" />
        </exec>
    </target>
</project>
```

Create the following `phpacker.json` file which tells PHPacker how to build binaries:

```json
{
	"src": "./releases/app.phar",
	"dest": "./releases",
	"ini": "./phpacker.ini",
	"platform": "all",
	"php": "8.4",
	"repository": "akeeba/php-bin"
}
```

> **IMPORTANT!** The `repository` argument tells PHPacker to use a fork of PHPacker's `php-bin` repository. The official [PHPacker `php-bin` repository](https://github.com/phpacker/php-bin) does not include the `ffi` extension which is required by Boson. If you do not trust random strangers on the Internet to provide you with binaries you can fork the `php-bin` repository yourself, edit `php-extensions.txt` to include `ffi`, and build your own binaries.

Create the following `phpacker.ini` file. This is the `php.ini`-equivalent for the PHP micro SAPI used by PHPacker.

```ini
memory_limit = 256M
display_errors = Off
log_errors = On
error_log = stderr
ignore_repeated_errors = On
ffi.enable=1
```

> **IMPORTANT!** The `ffi.enabled=1` line is _mandatory_ for Boson to work at all.

You can now run `phing` from the repository's root to build your application into a PHAR file, and then convert it into binaries. You will get one binary per platform under the `releases` directory.