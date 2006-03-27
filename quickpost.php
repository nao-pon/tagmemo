<?php

/**
* @package Page
*/

/**
* XOOPS用ファイルの取り込み
*/

require_once '../../mainfile.php';
//GIJOE さんのワンタイムチケット
//include_once "./include/gtickets.php" ;

// 前処理
//mb_string ini_set
@ini_set("mbstring.substitute_character"," ");
@ini_set("mbstring.http_input","pass");
@ini_set("mbstring.http_output","pass");
@ini_set("mbstring.internal_encoding",_CHARSET);

// NULL バイト除去
$_POST = tagmemo_input_filter($_POST);

// 文字コード判定 & 変換
if (!empty($_POST['encode']))
{
	if (_CHARSET != strtoupper($_POST['encode']))
	{
		mb_convert_variables(_CHARSET, $encode, $_POST);
	}
}

//@todo check for CSRF;
$uname = empty($_POST["uname"]) ? '': $_POST["uname"];
$token = $_POST['tagmemo_quickform_token'];
$content = $_POST["tagmemo_quickform_memo"];
$public = isset($_POST["public"]) ? intval($_POST["public"]) : 0;
$tags =  $_POST["tagmemo_quickform_tags"];

$title="";
if(preg_match("/^([^\n]{0,120})/i", $content, $matches)){
	$title = $matches[0];
}
$title = (strlen($title) > 0) ? $title : "NO TITLE";
$tagmemo_handler =& xoops_getmodulehandler("tagmemo");

$uid = 0;
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	// uname, pass があれば uid を Get!
	if(isset($_POST["uname"]) && isset($_POST["pass"])) {
		$myts =& MyTextSanitizer::getInstance();
		$uname = $myts->stripSlashesGPC($_POST["uname"]);
		$pass = $myts->stripSlashesGPC($_POST["pass"]);
		$member_handler =& xoops_gethandler('member');
		$user =& $member_handler->loginUser(addslashes($uname), addslashes($pass));
		$uid = $user->getVar("uid");
	}
}

$memo_obj =& $tagmemo_handler->createMemo();

$memo_obj->setVar('uid', $uid);
$memo_obj->setVar('title', $title);
$memo_obj->setVar('content', $content);
$memo_obj->setVar('timestamp', time());
$memo_obj->setVar('public', $public);

$tagmemo_handler->insert($memo_obj, $tags, true);

// redirect (post request)
// @todo check if valid url.
$gobackurl = $_POST['tagmemo_quickform_gobackurl'];
header("Location: ".strtr($gobackurl, array("\r"=>'',"\n"=>'')));
exit();

function tagmemo_input_filter($param)
{
	static $magic_quotes_gpc = NULL;
	if ($magic_quotes_gpc === NULL)
	    $magic_quotes_gpc = get_magic_quotes_gpc();

	if (is_array($param)) {
		return array_map('tagmemo_input_filter', $param);
	} else {
		$result = str_replace("\0", '', $param);
		if ($magic_quotes_gpc) $result = stripslashes($result);
		return $result;
	}
}
?>