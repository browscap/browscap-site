<?php

$metadata = require_once(__DIR__ . '/../build/metadata.php');

$baseHost = 'http://' . $_SERVER['SERVER_NAME'];

require "../views/header.php"; ?>

<div id="beta">
	<h1>Warning! New beta site...</h1>
	<p>This is a beta site - use with caution and maybe don't use these files on production servers! Please report any issues on <a href="https://github.com/browscap/browscap">our GitHub</a> :)</p>
</div>

<div id="downloads">

	<h1>Downloads</h1>

	<p>The latest version is <strong><?php echo $metadata['version']; ?></strong></p>

	<h2>ASP Versions</h2>
	<ul>
		<li>
			<p><a href="/stream.php?q=BrowsCapINI">browscap.ini</a> <em>(<?php echo $metadata['filesizes']['BrowsCapINI']; ?> KB)</em></p>
			<p>A special version of browscap.ini for PHP users only!</p>
		</li>
		<li>
			<p><a href="/stream.php?q=Full_BrowsCapINI">full_asp_browscap.ini</a> <em>(<?php echo $metadata['filesizes']['Full_BrowsCapINI']; ?> KB)</em></p>
			<p>A larger version of php_browscap.ini with all the new properties.</p>
		</li>
	</ul>

	<h2>PHP Versions</h2>
	<ul>
		<li>
			<p><a href="/stream.php?q=PHP_BrowsCapINI">php_browscap.ini</a> <em>(<?php echo $metadata['filesizes']['PHP_BrowsCapINI']; ?> KB)</em></p>
			<p>A special version of browscap.ini for PHP users only!</p>
		</li>
		<li>
			<p><a href="/stream.php?q=Full_PHP_BrowsCapINI">full_php_browscap.ini</a> <em>(<?php echo $metadata['filesizes']['Full_PHP_BrowsCapINI']; ?> KB)</em></p>
			<p>A larger version of php_browscap.ini with all the new properties.</p>
		</li>
	</ul>

	<h2>Lite versions &amp; other files</h2>
	<p>The lite versions &amp; other files are currently not supported. You may follow the progress of re-instating the lite versions <a href="https://github.com/browscap/browscap/issues/16">here</a>.</p>

</div>

<div id="info">

	<h1>Important Information</h1>

	<h2>Usage with PHP</h2>
	<p>We highly recommend using the <a href="https://github.com/browscap/browscap-php">browscap-php</a> library from Jonathan Stoppani (<a href="https://github.com/GaretJax">GaretJax</a>).</p>

	<h2>Rate Limiting</h2>
	<p>Downloading the INI files here implies you agree to our fair usage policy. <em>Any repeat attempts to download the files will be banned.</em> We highly recommend that you request the version URL and compare your current version against the latest version before requesting the download URL.</p>

	<h2>Download URLs</h2>
	<dl>
		<dt><strong>Version:</strong></dt>
		<dd><span class="monospace"><?php echo $baseHost; ?>/version-date.php</span></dd>
	</dl>
	<dl>
		<dt><strong>Download:</strong></dt>
		<dd><span class="monospace"><?php echo $baseHost; ?>/stream.php?q=BrowsCapINI</span> (replace <em>BrowsCapINI</em> with the appropriate version)</dd>
	</dl>
</div>

<?php require "../views/footer.php"; ?>
