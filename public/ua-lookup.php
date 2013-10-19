<?php

$baseHost = 'http://' . $_SERVER['SERVER_NAME'];

$ua = $_SERVER['HTTP_USER_AGENT'];

session_start();

if (isset($_POST['ua']))
{
	$csrfToken = $_SESSION['csrfToken'];
	unset($_SESSION['csrfToken']);

	if (!isset($_POST['csrfToken']) || !$csrfToken || ($_POST['csrfToken'] != $csrfToken))
	{
		die("CSRF token not correct...");
	}

	$ua = $_POST['ua'];

	require_once "../vendor/autoload.php";

	$browscap = new phpbrowscap\Browscap(__DIR__ . '/../cache/');
	$browscap->remoteIniUrl = $baseHost  . '/stream.php?q=Full_PHP_BrowsCapINI';
	$browscap->remoteVerUrl = $baseHost . '/version-date.php';

	$uaInfo = $browscap->getBrowser($ua, true);
}

$csrfToken = hash('sha256', uniqid() . microtime());
$_SESSION['csrfToken'] = $csrfToken;

function escape($str)
{
	if (is_bool($str))
	{
		return $str ? 'true' : 'false';
	}

	return htmlentities($str);
}

require "../views/header.php"; ?>

<div id="beta">
	<h1>Warning! New beta site...</h1>
	<p>This is a beta site - use with caution and maybe don't use these files on production servers! Please report any issues on <a href="https://github.com/browscap/browscap">our GitHub</a> :)</p>
</div>

<div id="ua-lookup">

	<h1>User Agent Lookup</h1>

	<p>This tool allows you to check what the latest <em>browscap.ini</em> will identify any User Agent as.</p>

	<form action="" method="POST" style="width: 680px; margin: 0 auto;">
		<label>User Agent:
			<input type="text" name="ua" value="<?php echo $ua; ?>" style="width: 500px;" />
		</label>

		<input type="hidden" name="csrfToken" value="<?php echo $csrfToken; ?>" />
		<input type="submit" value="Look up &raquo;" />
	</form>

	<?php if (isset($uaInfo) && is_array($uaInfo)): ?>

	<table>
		<thead>
			<tr>
				<th>Key</th>
				<th>Value</th>
			</tr>
		</thead>
		<tbody>
	<?php foreach ($uaInfo as $key => $value): ?>
			<tr>
				<td><?php echo escape($key); ?></td>
				<td><?php echo escape($value); ?></td>
			</tr>
	<?php endforeach; ?>
		</tbody>
	</table>

	<?php endif; ?>

</div>

<?php require "../views/footer.php"; ?>
