<?php

require '../mcassoc.php';

define('MCASSOC_SITE_ID', 'test'); // this is public
define('MCASSOC_SHARED_SECRET', 'shared'); // this is shared between you and MCAssoc
define('MCASSOC_INSTANCE_SECRET', 'instance'); // this is private and should not be shared! treat it like a password.

$mcassoc = new MCAssoc(MCASSOC_SITE_ID, MCASSOC_SHARED_SECRET, MCASSOC_INSTANCE_SECRET);
$mcassoc->enableInsecureMode();

?>
<!DOCTYPE html>
<html>
<head>
	<title>MCAssoc Tester for PHP</title>
	<script src="static/client.js"></script>
</head>
<body>
<?php
if (!$_GET['stage'] || $_GET['stage'] == '1') {
?>
<h1>Stage 1: Provide a site username</h1>
<p>Usually you'd do this actually within your site, by using some sort of login mechanism. Here I just ask you for a username. Simples!</p>
<form action="" method="GET">
	<input type="hidden" name="stage" value="2">
	<label>Username: <input type="text" name="username"></label>
	<br>
	<input type="submit" value="Next stage">
</form>
<?php
} else if ($_GET['stage'] == '2') {
	$return_link = "http://$_SERVER[HTTP_HOST]". strtok($_SERVER['REQUEST_URI'], '?') . '?' . http_build_query(array(
		'stage' => '3',
		'username' => $_GET['username']
	));
	$key = $mcassoc->generateKey($_GET['username']);
?>
<h1>Stage 2: Perform authentication</h1>
<p>This is quite simple: just include an iframe and make the appropriate calls.</p>
<iframe id="mcassoc" width="600" height="400" frameBorder="0" seamless scrolling="no"></iframe>
<script>
MCAssoc.init("<?php echo MCASSOC_SITE_ID; ?>", "<?php echo $key; ?>", "<?php echo $return_link; ?>");
</script>
<?php
} else if ($_GET['stage'] == '3') {
	try {
		$data = $mcassoc->unwrapData($_POST['data']);
		$username = $mcassoc->unwrapKey($data->key);
?>
<h1>Stage 3: Confirm authentication</h1>
<dl>
	<dt>Site username given:</dt>
		<dd><?php echo htmlentities($_GET['username']); ?></dd>
	<dt>Site username checked:</dt>
		<dd><?php echo htmlentities($username); ?></dd>
	<dt>Site username OK?</dt>
		<dd><?php echo ($_GET['username'] == $username) ? 'yes' : 'no'; ?></dd>
	<br>
	<dt>Minecraft username:</dt>
		<dd><?php echo htmlentities($data->username); ?></dd>
	<dt>Minecraft UUID:</dt>
		<dd><?php echo htmlentities($data->uuid); ?></dd>
	<dt></dt>
		<dd><img src="http://minotar.net/avatar/<?php echo urlencode($data->username); ?>"></dd>
</dl>
<?php
	} catch (Exception $e) {
?>
<h1>Stage 3: Confirm authentication (FAILED)</h1>
<p>Something went wrong whilst verifying the data sent back.</p>
<p><?php echo $e; ?>
<?php
	}
}