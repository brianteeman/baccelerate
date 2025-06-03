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

/**
 * The HTML template for displaying an exception.
 *
 * This template is used by the HtmlExceptionHandler class.
 *
 * @var    \Throwable $exception The exception to display
 * @since  1.0.0
 */
$treatPaths = fn(string $text): string => str_replace(DESKTOPAPP_ROOT, '…', $text);

?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Error</title>
	<style>
        :root {
            color-scheme: light dark;
        }

        body {
            font-family: sans-serif;
        }

        h1 {
            color: red;
            font-size: 2em;
            padding-bottom: 0.25em;
            border-bottom: thick double firebrick;
        }

        h2 {
            font-size: 1.5em;
            font-weight: normal;
        }

        h2.previousexception {
            font-size: 1.25em;
            color: slategray;
        }

        h3 {
            font-size: 1.25em;
            font-weight: normal;
        }

        span.badge {
            background-color: orangered;
            color: white;
            padding: 0.25em;
            border-radius: 0.25em;
        }

        h2.previousexception > span.badge {
            background-color: palevioletred;
            color: whitesmoke;
        }

        p.errorlocation {
            color: dimgray;
        }

        span.filename {
            font-family: 'Menlo', 'Consolas', 'Courier New', monospace;
            color: darkgreen;
        }

        span.linenumber {
            color: dodgerblue;
        }

        pre {
            font-family: 'Menlo', 'Consolas', 'Courier New', monospace;
            border: thin solid gray;
            border-radius: 0.25em;
            background-color: whitesmoke;
            padding: 0.5em;
            overflow-x: scroll;
        }

        hr {
            margin: 1.25em 0;
            border: 2px solid cornflowerblue;
        }

        @media (prefers-color-scheme: dark) {
            html {
                background-color: #121212;
                color: #fefefe;
            }

            p.errorlocation {
                color: darkgray;
            }

            span.filename {
                color: limegreen;
            }

            span.linenumber {
                color: lightskyblue;
            }

            pre {
                border: thin solid lightslategray;
                background-color: dimgray;
            }
        }
	</style>
</head>
<body>
<h1>⁉️ Unhandled Exception</h1>
<h2>
	<?php
	if ($exception->getCode()): ?>
		<span class="badge"><?= $exception->getCode() ?></span>
	<?php
	endif ?>
	<?= htmlentities($treatPaths($exception->getMessage())) ?>
</h2>
<p class="errorlocation">
	Error location: <span class="filename"><?= $treatPaths($exception->getFile()) ?></span>:<span
			class="linenumber"><?= $exception->getLine() ?></span>
</p>

<h3>Debug backtrace</h3>
<pre><?= $treatPaths($exception->getTraceAsString()) ?></pre>

<?php
while ($exception = $exception->getPrevious()) : ?>

	<hr>

	<h2 class="previousexception">
		<?php
		if ($exception->getCode()): ?>
			<span class="badge"><?= $exception->getCode() ?></span>
		<?php
		endif ?>
		<?= htmlentities($treatPaths($exception->getMessage())) ?>
	</h2>
	<p class="errorlocation">
		Error location: <span class="filename"><?= $treatPaths($exception->getFile()) ?></span>:<span
				class="linenumber"><?= $exception->getLine() ?></span>
	</p>

	<h3>Debug backtrace</h3>
	<pre><?= $treatPaths($exception->getTraceAsString()) ?></pre>

<?php
endwhile ?>
</body>
</html>