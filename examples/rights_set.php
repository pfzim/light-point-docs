<?php
header("Content-Type: text/plain; charset=utf-8");

function set_rights_bit(&$bits, $bit)
{
	$bit--;
	$bits[(int) ($bit / 8)] = chr(ord($bits[(int) ($bit / 8)]) | (0x1 << ($bit % 8)));
}

function unset_rights_bit(&$bits, $bit)
{
	$bit--;
	$bits[(int) ($bit / 8)] = chr(ord($bits[(int) ($bit / 8)]) & ((0x1 << ($bit % 8)) ^ 0xF));
}

require_once("inc.config.php");
require_once('inc.db.php');
require_once('inc.rights.php');
require_once('inc.utils.php');

	$db = new MySQLDB(DB_RW_HOST, NULL, DB_USER, DB_PASSWD, DB_NAME, DB_CPAGE, FALSE);
	
	$bits = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
	$update = 0;
	
	if($db->select(rpv("SELECT bits FROM @access WHERE oid = # AND dn = ! LIMIT 1", 1, "")))
	{
		$bits = $db->data[0][0];
		$update = 1;
	}
	
	set_rights_bit($bits, LPD_ACCESS_READ);
	unset_rights_bit($bits, LPD_ACCESS_WRITE);
	set_rights_bit($bits, 34);
	
	echo rpv("UPDATE @access SET bits = '?' WHERE oid = # AND dn = ! LIMIT 1", sql_escape($bits), 1, "")."\n";
	if($update)
	{
		if($db->put(rpv("UPDATE @access SET bits = '?' WHERE oid = # AND dn = ! LIMIT 1", sql_escape($bits), 1, "")))
		{
			echo "UPDATE OK";
		}
		else
		{
			echo "UPDATE ERROR: ".$db->get_last_error();
		}
	}
	else
	{
		if($db->put(rpv("INSERT INTO @access (bits, oid, dn) VALUES ('?', #, !)", sql_escape($bits), 1, "")))
		{
			echo "INSERT OK";
		}
		else
		{
			echo "INSERT ERROR: ".$db->get_last_error();
		}
	}
