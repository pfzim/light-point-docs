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
$g_doc_reg_upr = array("Undefined", "Region 1", "Region 2");
$g_doc_reg_otd = array("Undefined", "Department 1", "Department 2");
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

	$uid = 0;
	$user_login = NULL;
	if(isset($_SESSION['uid']))
	{
		$uid = $_SESSION['uid'];
		$user_login = $_SESSION['login'];
	}

	if(empty($uid))
	{
		if(!empty($_COOKIE['zh']) && !empty($_COOKIE['zl']))
		{
			if($db->select(rpv("SELECT m.`id`, m.`login` FROM @users AS m WHERE m.`login` = ! AND m.`sid` IS NOT NULL AND m.`sid` = ! AND m.`deleted` = 0 LIMIT 1", $_COOKIE['zl'], $_COOKIE['zh'])))
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

	$db = new MySQLDB(DB_RW_HOST, NULL, DB_USER, DB_PASSWD, DB_NAME, DB_CPAGE, TRUE);
	$ldap = new LDAP(LDAP_HOST, LDAP_PORT, LDAP_USER, LDAP_PASSWD, FALSE);
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

			break;
		}

		case 'export':
		{
			header("Content-Type: text/plain; charset=utf-8");
			header("Content-Disposition: attachment; filename=\"base.xml\"; filename*=utf-8''base.xml");

			$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail` FROM `@contacts` AS m WHERE m.`visible` = 1 ORDER BY m.`lname`, m.`fname`"));

			$result = $db->data;

			include('templ/tpl.export.php');
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
				foreach($list as $id)
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

		case 'hide':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `visible` = 0 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "message": "Successful hide (ID '.$id.')"}';
		}
		exit;

		case 'show':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `visible` = 1 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "message": "Successful show (ID '.$id.')"}';
		}
		exit;

		case 'setlocation':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(@$_POST['map'] > PB_MAPS_COUNT)
			{
				echo '{"code": 1, "message": "Invalid map identifier"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `map` = #, `x` = #, `y` = # WHERE `id` = # LIMIT 1", @$_POST['map'], @$_POST['x'], @$_POST['y'], $id));

			echo '{"code": 0, "id": '.$id.', "map": '.json_escape(@$_POST['map']).', "x": '.json_escape(@$_POST['x']).', "y": '.json_escape(@$_POST['y']).', "message": "Location set (ID '.$id.')"}';
		}
		exit;

		case 'upload':
		{
			header("Content-Type: text/plain; charset=utf-8");

			if(!@move_uploaded_file(@$_FILES['file']['tmp_name'], dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'f'.$id))
			{
				echo '{"code": 1, "message": "Failed upload"}';
				exit;
			}

			$db->put(rpv("INSERT INTO `@files` (`pid`, `name`, )", $id));

			echo '{"code": 0, "id": '.$id.', "message": "File added (ID '.$id.')"}';
		}
		exit;

		case 'deletephoto':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			$db->put(rpv("UPDATE `@contacts` SET `photo` = 0 WHERE `id` = # LIMIT 1", $id));

			echo '{"code": 0, "id": '.$id.', "message": "Photo deleted (ID '.$id.')"}';
		}
		exit;

		case 'delete':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$uid)
			{
				echo '{"code": 1, "message": "Please, log in"}';
				exit;
			}
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			$db->put(rpv("DELETE FROM `@contacts` WHERE `id` = # AND `samname` = '' LIMIT 1", $id));

			$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'photos'.DIRECTORY_SEPARATOR.'t'.$id.'.jpg';
			if(file_exists($filename))
			{
				unlink($filename);
			}

			echo '{"code": 0, "message": "Deleted (ID '.$id.')"}';
		}
		exit;

		case 'download':
		{
			if($db->select_ex($file, rpv("SELECT m.`name`, j1.`pid` FROM `@files` AS m LEFT JOIN `@docs` AS j1 ON j1.`id` = m.`pid` WHERE m.`id` = # AND m.`deleted` = 0 LIMIT 1", $id)))
			{
				$db->disconnect(); // release database connection

				if(!$user_perm->check_permission($file[0][1], LPD_ACCESS_READ))
				{
					$error_msg = "Access denied to section ".$file[0][1]." for user ".$uid."!";
					include('templ/tpl.message.php');
					exit;
				}
				
				$filename = dirname(__FILE__).DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.'f'.$id.'';
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

		case 'get':
		{
			header("Content-Type: text/plain; charset=utf-8");
			if(!$id)
			{
				echo '{"code": 1, "message": "Invalid identifier"}';
				exit;
			}

			if(!$db->select(rpv("SELECT m.`id`, m.`samname`, m.`fname`, m.`lname`, m.`dep`, m.`org`, m.`pos`, m.`pint`, m.`pcell`, m.`mail`, m.`photo`, m.`map`, m.`x`, m.`y`, m.`visible` FROM `@contacts` AS m WHERE m.`id` = # LIMIT 1", $id)))
			{
				echo '{"code": 1, "message": "DB error"}';
				exit;
			}

			echo '{"code": 0, "id": '.intval($db->data[0][0]).', "samname": "'.json_escape($db->data[0][1]).'", "firstname": "'.json_escape($db->data[0][2]).'", "lastname": "'.json_escape($db->data[0][3]).'", "department": "'.json_escape($db->data[0][4]).'", "company": "'.json_escape($db->data[0][5]).'", "position": "'.json_escape($db->data[0][6]).'", "phone": "'.json_escape($db->data[0][7]).'", "mobile": "'.json_escape($db->data[0][8]).'", "mail": "'.json_escape($db->data[0][9]).'", "photo": '.intval($db->data[0][10]).', "map": '.intval($db->data[0][11]).', "x": '.intval($db->data[0][12]).', "y": '.intval($db->data[0][13]).', "visible": '.intval($db->data[0][14]).'}';
		}
		exit;

		case 'save':
		{
			header("Content-Type: text/plain; charset=utf-8");
			
			$result_json = array(
				'code' => 0,
				'message' => '',
				'errors' => array()
			);
			
			$v_id = intval(@$_POST['id']);
			$v_pid = intval(@$_POST['pid']);
			$v_name = @$_POST['name'];
			$v_status = intval(@$_POST['status']);
			$v_bis_unit = @$_POST['bis_unit'];
			$v_reg_upr = intval(@$_POST['reg_upr']);
			$v_reg_otd = intval(@$_POST['reg_otd']);
			$v_contr_name = @$_POST['contr_name'];
			$v_order = @$_POST['order'];
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
			if(!$user_perm->check_permission($v_pid, LPD_ACCESS_WRITE))
			{
				//echo '{"code": 1, "message": "Access denied for create/edit document '.$v_id.' in section '.$v_pid.' for user '.$uid.'!"}';
				//exit;
			}
			
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
				
				$db->put(rpv("INSERT INTO `@docs` (`pid`, `uid`, `create_date`, `modify_date`, `name`, `status`, `bis_unit`, `reg_upr`, `reg_otd`, `contr_name`, `order`, `order_date`, `doc_type`, `info`, `deleted`) VALUES (#, #, NOW(), NOW(), !, #, #, #, #, !, !, !, #, !, 0)",
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
				));
				$id = $db->last_id();
				echo '{"code": 0, "id": '.$id.', "message": "Added (ID '.$id.')"}';
			}
			else
			{
				$db->put(rpv("UPDATE `@docs` SET `uid` = #, `modify_date` = NOW(), `name` = !, `status` = #, `bis_unit` = !, `reg_upr` = #, `reg_otd` = #, `contr_name` = !, `order` = !, `order_date` = !, `doc_type` = #, `info` = ! WHERE `id` = # AND `pid` = # LIMIT 1",
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
					$v_info,
					$v_id,
					$v_pid
				));
				echo '{"code": 0, "id": '.$id.',"message": "Updated (ID '.$id.')"}';
			}
		}
		exit;

		case 'doc':
		{
			header("Content-Type: text/html; charset=utf-8");

			$db->select_assoc_ex($doc, rpv("SELECT m.`id`, m.`pid`, m.`uid`, m.`create_date`, m.`modify_date`, m.`name`, m.`status`, m.`bis_unit`, m.`reg_upr`, m.`reg_otd`, m.`contr_name`, m.`order`, m.`order_date`, m.`doc_type` FROM `@docs` AS m WHERE m.`id` = #", $id));

			if(!$user_perm->check_permission($doc[0]['pid'], LPD_ACCESS_READ))
			{
				$error_msg = "Access denied to section ".$doc[0]['pid']." for user ".$uid."!";
				//include('templ/tpl.message.php');
				//exit;
			}

			$db->select_ex($sections, rpv("SELECT m.`id`, m.`name` FROM `@sections` AS m WHERE m.`deleted` = 0 AND m.`pid` = 0 ORDER BY m.`priority`, m.`name`"));
			$db->select_assoc_ex($docs, rpv("SELECT m.`id`, m.`pid`, m.`uid`, m.`create_date`, m.`modify_date`, m.`name` FROM `@files` AS m WHERE m.`pid` = # AND m.`deleted` = 0 ORDER BY m.`name`", $id));

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
			$db->select_assoc_ex($docs, rpv("SELECT m.`id`, m.`pid`, m.`uid`, m.`create_date`, m.`modify_date`, m.`name`, m.`status`, m.`bis_unit`, m.`reg_upr`, m.`reg_otd`, m.`contr_name`, m.`order`, m.`order_date`, m.`doc_type` FROM `@docs` AS m WHERE m.`pid` = # AND m.`deleted` = 0 ORDER BY m.`modify_date`", $id));

			include('templ/tpl.main.php');
		}
		exit;

		default:
		{
			$error_msg = "Unknown action: ".$action."!";
			include('templ/tpl.message.php');
			exit;
		}
	}
