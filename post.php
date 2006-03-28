<?php
/**
* @package Page
*/

// ブックマークレット投稿時の戻り ブラウザのセキュリティ設定により自動的に閉じない可能性あり
if (!empty($_GET['quick_edit_close'])) exit('<script>parent.Windows.close("tagmemo_qp_container");</script>');

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';

//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;

// echo "checkpoint 1 <br>\n";

// mbstring のチェック
if (!extension_loaded('mbstring'))
{
	include_once('./include/mbstring.php');
}

// 前処理
//mb_string ini_set
@ini_set("mbstring.substitute_character"," ");
@ini_set("mbstring.http_input","pass");
@ini_set("mbstring.http_output","pass");
@ini_set("mbstring.internal_encoding",_CHARSET);

// NULL バイト除去
$_POST = tagmemo_input_filter($_POST);

// 文字コード判定 & 変換
if (!empty($_POST['encode_hint']))
{
	$encode = mb_detect_encoding($_POST['encode_hint']);
	if (_CHARSET != strtoupper($encode))
	{
		mb_convert_variables(_CHARSET, $encode, $_POST);
	}
}

//値を受けてみるよ。

$left_tags = empty($_POST["tagmemo_tag_input"]) ? '': $_POST["tagmemo_tag_input"] ;
$memo_id = empty($_POST["tagmemo_id"]) ? 0 : $_POST["tagmemo_id"] ;
$content = $_POST["tagmemo_memo"];
$public = isset($_POST["public"]) ? intval($_POST["public"]) : 0;
$tags =  $_POST["tagmemo_tag_hidden"].' '.$left_tags;
$is_quickedit = !empty($_POST["quick_edit"]);

if(is_array($tags)){
	$tags = implode(' ', $tags);
}
// echo "checkpoint 2 <br>\n";

$title = trim($content);
if(preg_match("/^([^\r\n]{0,120})/i", $title, $matches)){
	$title = $matches[0];
}

// タイトル用に装飾タグ(BBコードなど)を除去
$myts =& MyTextSanitizer::getInstance();
$title = strip_tags($myts->displayTarea($title));

$title = (strlen($title) > 0) ? $title : "NO TITLE";
//ハンドラをつくってみるよ。
// echo "checkpoint 3 <br>\n";
$tagmemo_handler =& xoops_getmodulehandler("tagmemo");

$module_id = $xoopsModule->mid();

//ユーザーIDをもらおう
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
	$isAdmin = $xoopsUser->isAdmin($module_id);
} else {
	// uname, pass があれば uid を Get!
	if(!empty($_POST["uname"]) && !empty($_POST["pass"])) {
		include_once(XOOPS_ROOT_PATH."/class/module.textsanitizer.php");
		$myts =& MyTextSanitizer::getInstance();
		$uname = $myts->stripSlashesGPC($_POST["uname"]);
		$pass = $myts->stripSlashesGPC($_POST["pass"]);
		$member_handler =& xoops_gethandler('member');
		$user =& $member_handler->loginUser(addslashes($uname), addslashes($pass));
		$uid = $user->getVar("uid");
	}
	if (!$uid)
	{
		$uid = 0;
		$isAdmin = false;
	}
}

$changed = true;
if($memo_id != 0){
	if ( ! $xoopsGTicket->check() ) {
		redirect_header(XOOPS_URL.'/',3,$xoopsGTicket->getErrors());
	}
	$memo_obj =& $tagmemo_handler->getMemoObj($memo_id);
	if (!is_object($memo_obj)) {
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, 'Such memo does not exist');
	}
	$memo_owner = $memo_obj->getVar('uid');
	$ts =& MyTextSanitizer::getInstance();
	if ($memo_obj->getVar('public') == 0 && !$isAdmin) {
		if ($memo_owner == 0) {
			//@future password check
			//$password = isset($_POST["password"]) ? $_POST["password"] : "";
			//if ($ts->stripSlashesGPC($password) != $memo_obj->getVar('password', 'n')) {
				redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
			//}
			$memo_id = 0;
		} elseif ($memo_owner != $uid) {
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		}
	}
	if($memo_obj->getVar('content','e') == $ts->stripSlashesGPC($content)){
		$changed = false;
	}
}else{
	$memo_obj =& $tagmemo_handler->createMemo();
}
// echo "checkpoint 4 <br>\n";

// echo "checkpoint 5 <br>\n";

//オブジェクトに値を設定してみるよ。
//$memo_obj->setVar('tagmemo_id', $tagmemo_id);
if($memo_id == 0){
	$memo_obj->setVar('uid', $uid);
	$memo_owner = $uid;
}
if($changed){
	$memo_obj->setVar('title', $title);
	$memo_obj->setVar('timestamp', time());
	$memo_obj->setVar('content', $content);
}
// change public memo to private only by memo owner or admin.
if($memo_owner == $uid || $isAdmin) {
	$memo_obj->setVar('public', $public);
}
// echo "checkpoint 6 <br>\n";

$ret_url = "post.php?quick_edit_close=1";
//放り込め！
if ($tagmemo_handler->insert($memo_obj, $tags)) {
	//memo_id is set in memo_obj by xoopstableobject->insert method.
	$memo_id = $memo_obj->getVar("tagmemo_id");
	// make autolink data
	$tagmemo_handler->makeAutolinkData();
	//redirect to created/updated memo ditail.
	if (!$is_quickedit) $ret_url = '?tagmemo_id='.$memo_id;
	redirect_header(XOOPS_URL.'/modules/tagmemo/'.$ret_url, 1, _MD_TAGMEMO_MESSAGE_SAVE);
} else {
	//get error message.
	$message = $tagmemo_handler->getErrors(false);
	$message = ($message == '') ? 'Your memo is not saved.' : $message; 
	//show error messsage if insert fail.
	if (!$is_quickedit) $ret_url = '';
	redirect_header(XOOPS_URL.'/modules/tagmemo/'.$ret_url, 3, $message);
}

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