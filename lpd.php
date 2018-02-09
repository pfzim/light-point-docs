<?php
/*
    light-point-docs
    Copyright (C) 2018 Dmitry V. Zimin

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

if(!file_exists('inc.config.php'))
{
	header('Location: install.php');
	exit;
}

require_once("inc.config.php");

$g_doc_status = array("Undefined", "Создан", "Отфактурован", "На доработку", "Доработан", "Замена документов");
$g_doc_reg_upr = array("Undefined", "Донское региональное управление", "Уральское региональное управление", "Приволжское региональное управление");
$g_doc_reg_otd = array("Undefined", "Екатеринбург", "Ростов на Дону", "Ярославль");
$g_doc_types = array("Торг12", "СФ", "1Т", "Доверенность", "Справка А", "Справка Б",);

function doc_type_to_string($doc_type)
{
	global $g_doc_types;

	$result = "";
	for($i = 0; $i < 6; $i++)
	{
		if(($doc_type >> $i) & 0x01)
		{
			$result .= $g_doc_types[$i].';';
		}
	}
	return $result;
}

function set_permission_bit(&$bits, $bit)
{
	$bit--;
	$bits[(int) ($bit / 8)] = chr(ord($bits[(int) ($bit / 8)]) | (0x1 << ($bit % 8)));
}

function unset_permission_bit(&$bits, $bit)
{
	$bit--;
	$bits[(int) ($bit / 8)] = chr(ord($bits[(int) ($bit / 8)]) & ((0x1 << ($bit % 8)) ^ 0xF));
}

function assert_permission_ajax($section_id, $allow_bit)
{
	global $uid;
	global $user_perm;

	if(!$user_perm->check_permission($section_id, $allow_bit))
	{
		//echo '{"code": 1, "message": "Access denied to section '.$section_id.' for user '.$uid.'!"}';
		//exit;
	}
}

function php_mailer($to, $name, $subject, $html, $plain)
{
	require_once 'libs/PHPMailer/PHPMailerAutoload.php';

	$mail = new PHPMailer;

	$mail->isSMTP();
	$mail->Host = MAIL_HOST;
	$mail->SMTPAuth = MAIL_AUTH;
	if(MAIL_AUTH)
	{
		$mail->Username = MAIL_LOGIN;
		$mail->Password = MAIL_PASSWD;
	}

	$mail->SMTPSecure = MAIL_SECURE;
	$mail->Port = MAIL_PORT;

	$mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
	$mail->addAddress($to, $name);
	//$mail->addReplyTo('helpdesk@example.com', 'Information');

	$mail->isHTML(true);

	$mail->Subject = $subject;
	$mail->Body    = $html;
	$mail->AltBody = $plain;

	return $mail->send();
}


	session_name("ZID");
	session_start();
	error_reporting(E_ALL);
	define("Z_PROTECTED", "YES");

	$self = $_SERVER['PHP_SELF'];

	if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = @$_SERVER['REMOTE_ADDR'];
	}

	require_once('inc.db.php');
	require_once('inc.ldap.php');
	require_once('inc.access.php');
	require_once('inc.rights.php');
	require_once('inc.utils.php');

	$action = "";
	if(isset($_GET['action']))
	{
		$action = $_GET['action'];
	}

	$id = 0;
	if(isset($_GET['id']))
	{
		$id = $_GET['id'];
	}

	if($action == "message")
	{
		switch($id)
		{
			case 1:
				$error_msg = "Registration is complete. Wait for the administrator to activate your account.";
				break;
			default:
				$error_msg = "Unknown error";
				break;
		}

		include('templ/tpl.message.php');
		exit;
	}

	$db = new MySQLDB(DB_RW_HOST, NULL, DB_USER, DB_PASSWD, DB_NAME, DB_CPAGE, TRUE);
	$ldap = new LDAP(LDAP_HOST, LDAP_PORT, LDAP_USER, LDAP_PASSWD, FALSE);

	$uid = 0;
	$user_login = NULL;
	if(isset($_SESSION['uid']) && isset($_SESSION['login']))
	{
		$uid = $_SESSION['uid'];
		$user_login = $_SESSION['login'];
	}

	if(empty($uid))
	{
		if(!empty($_COOKIE['zh']) && !empty($_COOKIE['zl']))
		{
			if($db->select(rpv("SELECT m.`id`, m.`login` FROM @users AS m WHERE m.`login` = ! AND m.`sid` IS NOT NULL AND m.`sid` = ! LIMIT 1", $_COOKIE['zl'], $_COOKIE['zh'])))
			{
				$_SESSION['uid'] = $db->data[0][0];
				$_SESSION['login'] = $db->data[0][1];
				$uid = $_SESSION['uid'];
				$user_login = $_SESSION['login'];
				setcookie("zh", $_COOKIE['zh'], time()+2592000, '/');
				setcookie("zl", $_COOKIE['zl'], time()+2592000, '/');
			}
		}
	}

	$user_perm = new UserPermissions($db, $ldap, $user_login);

	if(empty($uid))
	{
		switch($action)
		{
			case 'logon':
			{
				if(empty($_POST['login']) || empty($_POST['passwd']))
				{
					$error_msg = "Неверное имя пользователя или пароль!";
					include('templ/tpl.login.php');
					exit;
				}

				$login = @$_POST['login'];

				if(strpos($login, '\\'))
				{
					list($domain, $login) = explode('\\', $login, 2);
				}
				else if(strpos($login, '@'))
				{
					list($login, $domain) = explode('@', $login, 2);
				}
				else
				{
					$error_msg = "Неверный формат логина (user@domain, domain\\user)!";
					include('templ/tpl.login.php');
					exit;
				}

				if(!$ldap->reset_user($login.'@'.$domain, @$_POST['passwd'], TRUE))
				{
					$error_msg = "Неверное имя пользователя или пароль!";
					include('templ/tpl.login.php');
					exit;
				}

				if($db->select(rpv("SELECT m.`id` FROM `@users` AS m WHERE m.`login` = ! LIMIT 1", $login)))
				{
					$_SESSION['uid'] = $db->data[0][0];
				}
				else // add new LDAP user
				{
					$db->put(rpv("INSERT INTO @users (login) VALUES (!)", $login));
					$_SESSION['uid'] = $db->last_id();
				}

				$_SESSION['login'] = $login;
				$uid = $_SESSION['uid'];
				$user_login = $_SESSION['login'];

				$sid = uniqid();
				setcookie("zh", $sid, time()+2592000, '/');
				setcookie("zl", $login, time()+2592000, '/');

				$db->put(rpv("UPDATE @users SET `sid` = ! WHERE `id` = # LIMIT 1", $sid, $uid));

				header('Location: '.$self);
				exit;
			}
			case 'login':
			{
				include('templ/tpl.login.php'); // show login form
				exit;
			}
		}
	}

	if(!$uid)
	{
		//include('templ/tpl.login.php'); // show login form
		header('Location: '.$self.'?action=login');
		exit;
	}

	switch($action)
	{
		case 'logoff':
		{
			$db->put(rpv("UPDATE @users SET `sid` = NULL WHERE `id` = # LIMIT 1", $uid));
			$_SESSION['uid'] = 0;
			$_SESSION['login'] = NULL;
			$uid = $_SESSION['uid'];
			$user_login = $_SESSION['login'];
			$user_perm->reset_user();
			setcookie("zh", NULL, time()-60, '/');
			setcookie("zl", NULL, time()-60, '/');
			
			header('Location: '.$self);
		}
		exit;

		case 'export_selected':
		{
			header("Content-Type: text/plain; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$result = array();

			if(isset($_POST['list']))
			{
				$j = 0;
				$list_safe = '';
				$list = explode(',', $_POST['list']);
				foreach($list as &$id)
				{
					if($j > 0)
					{
						$list_safe .= ',';
					}

					$list_safe .= intval($id);
					$j++;
				}

				if($j > 0)
				{
					if($db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`id` IN (?) ORDER BY m.`lname`, m.`fname`", $list_safe)))
					{
						$result = $db->data;
					}
				}
			}

			include('templ/tpl.export.php');
		}
		exit;

		case 'upload':
		{
			header("Content-Type: text/plain; charset=utf-8");

			$v_id = intval(@$_POST['id']);		// id of file for replace
			$v_pid = intval(@$_POST['pid']);	// id of parent document

			if(!$db->select_ex($doc, rpv("SELECT m.`pid` FROM `@docs` AS m WHERE m.`id` = # AND m.`deleted` = 0 LIMIT 1", $v_pid))
				|| empty($_FILES['file']['tmp_name'][0])
				|| !file_exists(@$_FILES['file']['tmp_name'][0])
			)
			{
				echo '{"code": 1, "message": "Failed upload"}';
				exit;
			}

			assert_permission_ajax($doc[0][0], LPD_ACCESS_WRITE);

			$files_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR;
			
			$db->start_transaction();

			if($v_id)
			{
				if(count($_FILES['file']['tmp_name']) > 1)
				{
					echo '{"code": 1, "message": "Too many files uploaded, upload one file"}';
					exit;
				}

				if(!$db->select_ex($file, rpv("SELECT m.`name`, m.`modify_date`, m.`uid` FROM `@files` AS m WHERE m.`id` = # AND m.`deleted` = 0 LIMIT 1", $v_id)))
				{
					echo '{"code": 1, "message": "Failed upload. Error #1"}';
					exit;
				}

				if(!$db->put(rpv("INSERT INTO `@files_history` (`pid`, `name`, `modify_date`, `uid`, `deleted`) VALUES (#, !, !, #, 0)", $v_id, $file[0][0], $file[0][1], $file[0][2])))
				{
					echo '{"code": 1, "message": "Failed upload. Error #2"}';
					exit;
				}
				$last_id = $db->last_id();

				rename($files_dir.'f'.$v_id, $files_dir.'f'.$v_id.'_'.$last_id);

				if(!$db->put(rpv("UPDATE `@files` SET `name` = !, `modify_date` = NOW(), `uid` = # WHERE `id` = # LIMIT 1", @$_FILES['file']['name'][0], $uid, $v_id)))
				{
					echo '{"code": 1, "message": "Failed upload. Error #3"}';
					exit;
				}

				if(!@move_uploaded_file(@$_FILES['file']['tmp_name'][0], $files_dir.'f'.$v_id))
				{
					echo '{"code": 1, "message": "Failed upload. Error #4"}';
					exit;
				}
			}
			else
			{
				for($i = 0; $i < count($_FILES['file']['tmp_name']); $i++)
				{
					if(!$db->put(rpv("INSERT INTO `@files` (`pid`, `name`, `create_date`, `modify_date`, `uid`, `deleted`) VALUES (#, !, NOW(), NOW(), #, 0)", $v_pid, @$_FILES['file']['name'][$i], $uid)))
					{
						echo '{"code": 1, "message": "Failed upload. Error #5"}';
						exit;
					}

					$v_id = $db->last_id();

					if(!@move_uploaded_file($_FILES['file']['tmp_name'][$i], $files_dir.'f'.$v_id))
					{
						echo '{"code": 1, "message": "Failed upload. Error #6"}';
						exit;
					}
				}
			}

			$db->commit();

			echo '{"code": 0, "message": "Files added"}';
		}
		exit;

		case 'delete_file':
		{
			if(!$db->select_ex($file, rpv("SELECT j1.`pid` FROM `@files` AS m LEFT JOIN `@docs` AS j1 ON j1.`id` = m.`pid` WHERE m.`id` = # AND m.`deleted` = 0 LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "Failed delete"}';
				exit;
			}

			assert_permission_ajax($file[0][0], LPD_ACCESS_WRITE);

			if(!$db->put(rpv("UPDATE `@files` SET `deleted` = 1 WHERE `id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "Failed delete"}';
				exit;
			}

			if(!$db->put(rpv("UPDATE `@files_history` SET `deleted` = 1 WHERE `pid` = #", $id)))
			{
				echo '{"code": 1, "message": "Failed delete"}';
				exit;
			}

			echo '{"code": 0, "id": '.$id.', "message": "File deleted"}';
		}
		exit;

		case 'delete_doc':
		{
			if(!$db->select_ex($doc, rpv("SELECT m.`pid` FROM `@docs` AS m WHERE m.`id` = # AND m.`deleted` = 0 LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "Failed delete document"}';
				exit;
			}

			assert_permission_ajax($doc[0][0], LPD_ACCESS_WRITE);

			$db->start_transaction();
			
			if(!$db->put(rpv("UPDATE `@docs` SET `deleted` = 1 WHERE `id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "Failed delete. Error #1"}';
				exit;
			}

			if($db->select_ex($files, rpv("SELECT m.`id` FROM `@files` AS m WHERE m.`pid` = # AND m.`deleted` = 0", $id)))
			{
				foreach($files as &$file)
				{
					if(!$db->put(rpv("UPDATE `@files` SET `deleted` = 1 WHERE `id` = # LIMIT 1", $file[0])))
					{
						echo '{"code": 1, "message": "Failed delete. Error #2"}';
						exit;
					}

					if(!$db->put(rpv("UPDATE `@files_history` SET `deleted` = 1 WHERE `pid` = #", $file[0])))
					{
						echo '{"code": 1, "message": "Failed delete. Error #3"}';
						exit;
					}
				}
			}

			$db->commit();

			echo '{"code": 0, "id": '.$id.', "message": "Document deleted"}';
		}
		exit;

		case 'download':
		{
			if($db->select_ex($file, rpv("SELECT m.`name`, j1.`pid` FROM `@files` AS m LEFT JOIN `@docs` AS j1 ON j1.`id` = m.`pid` WHERE m.`id` = # AND m.`deleted` = 0 LIMIT 1", $id)))
			{
				if(!$user_perm->check_permission($file[0][1], LPD_ACCESS_READ))
				{
					$error_msg = "Access denied to section ".$file[0][1]." for user ".$uid."!";
					//include('templ/tpl.message.php');
					//exit;
				}

				$db->disconnect(); // release database connection

				$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'f'.$id;
				if(file_exists($filename))
				{
					header("Content-Type: application/octet-stream");
					header("Content-Length: ".filesize($filename));
					header("Content-Disposition: attachment; filename=\"".rawurlencode($file[0][0])."\"; filename*=utf-8''".rawurlencode($file[0][0]));
					readfile($filename);
					exit;
				}
			}

			$error_msg = "File not found!";
			include('templ/tpl.message.php');
			exit;
		}
		exit;

		case 'get_permission':
		{
			header("Content-Type: text/plain; charset=utf-8");

			assert_permission_ajax(0, LPD_ACCESS_READ);

			if(!$db->select_assoc_ex($permission, rpv("SELECT m.`oid`, m.`dn`, m.`allow_bits` FROM `@rights` AS m WHERE m.`id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "Failed get permissions"}';
				exit;
			}

			$permission[0]['pid'] = &$permission[0]['oid'];
			
			for($i = 0; $i < 2; $i++)
			{
				$permission[0]['allow_'.($i+1)] = ((ord($permission[0]['allow_bits'][(int) ($i / 8)]) >> ($i % 8)) & 0x01)?1:0;
			}
			
			$result_json = array(
				'code' => 0,
				'message' => '',
				'data' => $permission[0]
			);

			echo json_encode($result_json);
		}
		exit;
		
		case 'get_document':
		{
			header("Content-Type: text/plain; charset=utf-8");

			if(!$db->select_assoc_ex($doc, rpv("SELECT m.`id`, m.`pid`, m.`uid`, DATE_FORMAT(m.`create_date`, '%d.%m.%Y') AS create_date, DATE_FORMAT(m.`modify_date`, '%d.%m.%Y') AS modify_date, m.`name`, m.`status`, m.`bis_unit`, m.`reg_upr`, m.`reg_otd`, m.`contr_name`, m.`order`, DATE_FORMAT(m.`order_date`, '%d.%m.%Y') AS order_date, m.`doc_type` FROM `@docs` AS m WHERE m.`id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "Failed get document"}';
				exit;
			}

			assert_permission_ajax($doc[0]['pid'], LPD_ACCESS_READ);

			$doc_type = intval($doc[0]['doc_type']);
			for($i = 0; $i < count($g_doc_types); $i++)
			{
				$doc[0]['doc_type_'.($i+1)] = (($doc_type >> $i) & 0x01)?1:0;
			}
			
			$result_json = array(
				'code' => 0,
				'message' => '',
				'data' => $doc[0]
			);

			echo json_encode($result_json);
		}
		exit;
		
		case 'save_permission':
		{
			header("Content-Type: text/plain; charset=utf-8");

			$result_json = array(
				'code' => 0,
				'message' => '',
				'errors' => array()
			);

			$v_id = intval(@$_POST['id']);
			$v_pid = intval(@$_POST['pid']);
			$v_dn = trim(@$_POST['dn']);
			$v_allow = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

			if(intval(@$_POST['allow_1']))
			{
				set_pemission_bit($v_allow, LPD_ACCESS_READ);
			}

			if(intval(@$_POST['allow_2']))
			{
				set_pemission_bit($v_allow, LPD_ACCESS_WRITE);
			}

			assert_permission_ajax(0, LPD_ACCESS_WRITE);	// level 0 have Write access mean admin

			if(empty($v_dn))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'dn', 'msg' => 'Fill DN!');
			}

			if($result_json['code'])
			{
				$result_json['message'] = 'Not all required field filled!';
				echo json_encode($result_json);
				exit;
			}

			if(!$v_id)
			{
				if($db->put(rpv("INSERT INTO `@access` (`oid`, `dn`, `allow_bits`) VALUES (#, !, !)",
					$v_pid,
					$v_dn,
					$v_allow
				)))
				{
					$id = $db->last_id();
					echo '{"code": 0, "id": '.$id.', "message": "Added (ID '.$id.')"}';
					exit;
				}
			}
			else
			{
				if($db->put(rpv("UPDATE `@access` SET `dn` = !, `allow_bits` = ! WHERE `id` = # AND `oid` = # LIMIT 1",
					$v_dn,
					$v_allow,
					$v_id,
					$v_pid
				)))
				{
					echo '{"code": 0, "id": '.$id.',"message": "Updated (ID '.$id.')"}';
					exit;
				}
			}

			echo '{"code": 1, "id": '.$id.',"message": "Error: '.json_escape($db->get_last_error()).'"}';
		}
		exit;

		case 'save_document':
		{
			header("Content-Type: text/plain; charset=utf-8");

			$result_json = array(
				'code' => 0,
				'message' => '',
				'errors' => array()
			);

			$v_id = intval(@$_POST['id']);
			$v_pid = intval(@$_POST['pid']);
			$v_name = trim(@$_POST['name']);
			$v_status = intval(@$_POST['status']);
			$v_bis_unit = trim(@$_POST['bis_unit']);
			$v_reg_upr = intval(@$_POST['reg_upr']);
			$v_reg_otd = intval(@$_POST['reg_otd']);
			$v_contr_name = trim(@$_POST['contr_name']);
			$v_order = trim(@$_POST['order']);
			$v_order_date = @$_POST['order_date'];
			$v_info = @$_POST['info'];

			$v_doc_type = 0;
			$v_doc_type |= intval(@$_POST['doc_type_1'])?0x01:0;
			$v_doc_type |= intval(@$_POST['doc_type_2'])?0x02:0;
			$v_doc_type |= intval(@$_POST['doc_type_3'])?0x04:0;
			$v_doc_type |= intval(@$_POST['doc_type_4'])?0x08:0;
			$v_doc_type |= intval(@$_POST['doc_type_5'])?0x10:0;
			$v_doc_type |= intval(@$_POST['doc_type_6'])?0x20:0;

			//if(($v_id && !$user_perm->check_permission($v_pid, LPD_ACCESS_CREATE)) || (!$v_id && !$user_perm->check_permission($v_pid, LPD_ACCESS_EDIT)))
			assert_permission_ajax($v_pid, LPD_ACCESS_WRITE);

			if(($v_status < 1) || ($v_status >= count($g_doc_status)))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'status', 'msg' => 'Не выбрано значение!');
			}

			if(empty($v_bis_unit))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'bis_unit', 'msg' => 'Укажите бизнес-юнит!');
			}

			if(empty($v_reg_upr))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'reg_upr', 'msg' => 'Выберите региональное управление!');
			}

			if(empty($v_reg_otd))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'reg_otd', 'msg' => 'Выберите региональное отделение!');
			}

			if(empty($v_contr_name))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'contr_name', 'msg' => 'Укажите наименование контрагента!');
			}

			if(empty($v_order))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'order', 'msg' => 'Укажите номер ордера!');
			}

			$d = explode('.', $v_order_date, 3);
			$nd = intval(@$d[0]);
			$nm = intval(@$d[1]);
			$ny = intval(@$d[2]);
			$v_order_date = sprintf("%04d-%02d-%02d", $ny, $nm, $nd);

			if(!datecheck($nd, $nm, $ny))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'order_date', 'msg' => 'Укажите правильную дату ордера!');
			}

			if(!($v_doc_type & 0x3F))
			{
				$result_json['code'] = 1;
				$result_json['errors'][] = array('name' => 'doc_type_1', 'msg' => 'Выберите хотя бы один тип документа!');
			}

			if($result_json['code'])
			{
				$result_json['message'] = 'Заполнены не все значения!';
				echo json_encode($result_json);
				exit;
			}

			if(!$v_id)
			{
				$v_name = $v_order_date.$v_name;
				if($db->put(rpv("INSERT INTO `@docs` (`pid`, `uid`, `create_date`, `modify_date`, `name`, `status`, `bis_unit`, `reg_upr`, `reg_otd`, `contr_name`, `order`, `order_date`, `doc_type`, `info`, `deleted`) VALUES (#, #, NOW(), NOW(), !, #, #, #, #, !, !, !, #, !, 0)",
					$v_pid,
					$uid,
					$v_name,
					$v_status,
					$v_bis_unit,
					$v_reg_upr,
					$v_reg_otd,
					$v_contr_name,
					$v_order,
					$v_order_date,
					$v_doc_type,
					$v_info
				)))
				{
					$id = $db->last_id();
					echo '{"code": 0, "id": '.$id.', "message": "Added (ID '.$id.')"}';
					exit;
				}
			}
			else
			{
				if($db->put(rpv("UPDATE `@docs` SET `uid` = #, `modify_date` = NOW(), `status` = #, `bis_unit` = !, `reg_upr` = #, `reg_otd` = #, `contr_name` = !, `order` = !, `order_date` = !, `doc_type` = #, `info` = ! WHERE `id` = # AND `pid` = # LIMIT 1",
					$uid,
					//$v_name, , `name` = !
					$v_status,
					$v_bis_unit,
					$v_reg_upr,
					$v_reg_otd,
					$v_contr_name,
					$v_order,
					$v_order_date,
					$v_doc_type,
					$v_info,
					$v_id,
					$v_pid
				)))
				{
					echo '{"code": 0, "id": '.$id.',"message": "Updated (ID '.$id.')"}';
					exit;
				}
			}

			echo '{"code": 1, "id": '.$id.',"message": "Error: '.json_escape($db->get_last_error()).'"}';
		}
		exit;

		case 'permissions':
		{
			header("Content-Type: text/html; charset=utf-8");

			$db->select_assoc_ex($doc, rpv("SELECT m.`id`, m.`pid`, m.`uid`, DATE_FORMAT(m.`create_date`, '%d.%m.%Y') AS create_date, DATE_FORMAT(m.`modify_date`, '%d.%m.%Y') AS modify_date, m.`name`, m.`status`, m.`bis_unit`, m.`reg_upr`, m.`reg_otd`, m.`contr_name`, m.`order`, DATE_FORMAT(m.`order_date`, '%d.%m.%Y') AS order_date, m.`doc_type`, m.`info` FROM `@docs` AS m WHERE m.`id` = #", $id));

			if(!$user_perm->check_permission(0, LPD_ACCESS_READ))
			{
				$error_msg = "Access denied to section ".$doc[0]['pid']." for user ".$uid."!";
				//include('templ/tpl.message.php');
				//exit;
			}

			$db->select_ex($sections, rpv("SELECT m.`id`, m.`name` FROM `@sections` AS m WHERE m.`deleted` = 0 AND m.`pid` = 0 ORDER BY m.`priority`, m.`name`"));
			$db->select_assoc_ex($permissions, rpv("SELECT m.`id`, m.`oid`, m.`dn`, m.`allow_bits` FROM `@rights` AS m WHERE m.`oid` = # ORDER BY m.`dn`", $id));

			include('templ/tpl.admin.php');
		}
		exit;

		case 'doc':
		{
			header("Content-Type: text/html; charset=utf-8");

			$db->select_assoc_ex($doc, rpv("SELECT m.`id`, m.`pid`, m.`uid`, DATE_FORMAT(m.`create_date`, '%d.%m.%Y') AS create_date, DATE_FORMAT(m.`modify_date`, '%d.%m.%Y') AS modify_date, m.`name`, m.`status`, m.`bis_unit`, m.`reg_upr`, m.`reg_otd`, m.`contr_name`, m.`order`, DATE_FORMAT(m.`order_date`, '%d.%m.%Y') AS order_date, m.`doc_type`, m.`info` FROM `@docs` AS m WHERE m.`id` = #", $id));

			if(!$user_perm->check_permission($doc[0]['pid'], LPD_ACCESS_READ))
			{
				$error_msg = "Access denied to section ".$doc[0]['pid']." for user ".$uid."!";
				//include('templ/tpl.message.php');
				//exit;
			}

			$db->select_ex($sections, rpv("SELECT m.`id`, m.`name` FROM `@sections` AS m WHERE m.`deleted` = 0 AND m.`pid` = 0 ORDER BY m.`priority`, m.`name`"));
			$db->select_assoc_ex($files, rpv("SELECT m.`id`, m.`pid`, m.`uid`, DATE_FORMAT(m.`create_date`, '%d.%m.%Y') AS create_date, DATE_FORMAT(m.`modify_date`, '%d.%m.%Y') AS modify_date, m.`name` FROM `@files` AS m WHERE m.`pid` = # AND m.`deleted` = 0 ORDER BY m.`name`", $id));

			include('templ/tpl.doc.php');
		}
		exit;

		case '':
		{
			if(!$user_perm->check_permission($id, LPD_ACCESS_READ))
			{
				$error_msg = "Access denied to section ".$id." for user ".$uid."!";
				//include('templ/tpl.message.php');
				//exit;
			}

			header("Content-Type: text/html; charset=utf-8");

			$db->select_ex($sections, rpv("SELECT m.`id`, m.`name` FROM `@sections` AS m WHERE m.`deleted` = 0 AND m.`pid` = 0 ORDER BY m.`priority`, m.`name`"));
			if($id > 0)
			{
				if(isset($_GET['offset']))
				{
					$offset = intval($_GET['offset']);
				}
				else
				{
					$offset = 0;
				}

				$doc_cols = array('modify_date', 'name', 'bis_unit', 'reg_upr', 'reg_otd', 'order_date');
				if(isset($_GET['sort']) && isset($doc_cols[intval($_GET['sort'])]))
				{
					$sort = intval($_GET['sort']);
					$sort_col = $doc_cols[intval($_GET['sort'])];

					if(isset($_GET['direction']) && intval($_GET['direction']))
					{
						$direction = 1;
					}
					else
					{
						$direction = 0;
					}
				}
				else
				{
					$sort = 0;
					$direction = 1;
					$sort_col = $doc_cols[0];
				}
				
				$db->select_ex($docs, rpv("SELECT COUNT(*) FROM `@docs` AS m WHERE m.`pid` = # AND m.`deleted` = 0", $id));
				$docs_count = intval($docs[0][0]);
				$db->select_assoc_ex($docs, rpv("SELECT m.`id`, m.`pid`, m.`uid`, DATE_FORMAT(m.`create_date`, '%d.%m.%Y') AS create_date, DATE_FORMAT(m.`modify_date`, '%d.%m.%Y') AS modify_date, m.`name`, m.`status`, m.`bis_unit`, m.`reg_upr`, m.`reg_otd`, m.`contr_name`, m.`order`, DATE_FORMAT(m.`order_date`, '%d.%m.%Y') AS order_date, m.`doc_type` FROM `@docs` AS m WHERE m.`pid` = # AND m.`deleted` = 0 ORDER BY m.`?`? LIMIT #,50", $id, $sort_col, $direction?' DESC':'', $offset*50));
				include('templ/tpl.main.php');
			}
			else
			{
				include('templ/tpl.home.php');
			}
		}
		exit;

		default:
		{
			$error_msg = "Unknown action: ".$action."!";
			include('templ/tpl.message.php');
			exit;
		}
	}
