<?php
/* 	--------------------------------------------------------|
	API Handler for Metadmin by thescs						|
	HowTo: Путь к скрипту прописать в server.lua метадмина.	|
	=================== Комментарий ========================|
	- Меня учили комментировать код, хотя бы для себя.		|
	- Я для себя и откомментировал. А то нажрусь вдрызг		|
	и хрен пойму чо за хероту я тут написал.				|
	--------------------------------------------------------|
*/
file_put_contents('api.log', print_r($_REQUEST, TRUE) . print_r($_GET, TRUE), FILE_APPEND);
// DEBUG: Закомментировать для отладки
if ($_SERVER['REMOTE_ADDR'] != 'gmod ip') die("<img src=\"https://lh3.googleusercontent.com/proxy/hfbtL365B_HlJvpezIxV7oF8mcmoy3l_kckLjV8Tvd0Qb1ia5bBmhbxVVc2oVfKXXPIMOVjlA045njuT_Hj8bJQ3_3U\" alt=\"Poshel haxui\" />");
//Если POST пустой, выйти.
if (!isset($_POST['SID'])) die();
// Подключение к БД, директивы, импорты, хуйня нужная всякая крч.
define('MITRASTROI_ROOT', dirname(__FILE__).'/');
header('Content-type: text/json'); // Это JSON, детка!
require ("config.php");
require ("classes/base.class.php");
Mitrastroi::TakeClass('db');
$db = new DB($config['db_base'],$config['db_host'],$config['db_user'], $config['db_pass'], $config['db_port']);
$db->connect();
// чтоб не ебаться
$sid = $_POST['SID'];

// Выборки: основная инфа
$general = $db->execute("SELECT `nick`,`group`,`SID`,`status`,`icon` FROM `ma_players` WHERE SID='".$db->safe($sid)."'") or die($db->error());
$general = $db->fetch_assoc($general);
// читабельные ранги
$rank = $db->execute("SELECT `name` FROM `groups` WHERE txtid='$general[group]'");
$rank = $db->fetch_assoc($rank);
// нарушения
$violations = $db->execute("SELECT * FROM `ma_violations` WHERE SID='" . $db->safe($sid) . "'");
$v = array();
while ($violation = $db->fetch_assoc($violations)){
	$v[] = $violation;
}
// результаты экзаменов
$exam = $db->execute("SELECT * FROM `ma_examinfo` WHERE SID='" . $db->safe($sid) . "'");
$ex = array();
while ($ex_row = $db->fetch_assoc($exam)){
	$ex[] = $ex_row;
}

//костыль с картинками в профиле
if($general['icon'] == 0) $general['icon'] = '[{"id":"0"}]';

/*
	TODO:
	- mag_banned
	- icon
	- icons
	- rights
	- count_mag_reports
	- tests_site
*/
// Составляем массив данных
$r = array(
	"nick" 				=> $general['nick'],
	"rank" 				=> $general['group'],
	"SID" 				=> $general['SID'],
	"rank_name" 		=> $rank['name'],
	"mag_banned"		=> array('reason' => false, 'date' => null),
	"status" 			=> json_decode($general['status']),
	"violations"		=> $v,
	"exam"				=> $ex,
	"icon"				=> 0,
	"icons"				=> json_decode($general['icon']),
	"rights"			=> [],
	"count_mag_reports"	=> 0,
	"tests_site"		=> []
);

// отправляем данные серверу в JSON
echo json_encode($r);
