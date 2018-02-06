<?php if(!defined("Z_PROTECTED")) exit; ?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<title>Light Point Docs</title>
		<link type="text/css" href="templ/style.css" rel="stylesheet" />
		<script src="lpd.js"></script>
	</head>
	<body>
		<ul class="menu-bar">
			<li><a href="<?php eh("$self"); ?>">Home</a></li>
			<ul style="float:right;list-style-type:none;">
				<?php if($uid) { ?>
				<li><a href="?action=logoff">Log Out</a></li>
				<?php } else { ?>
				<li><a href="?action=login">Log In</a></li>
				<?php } ?>
			</ul>
		</ul>