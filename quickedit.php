<?php
/**
* @package Page
*/

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';

//GIJOE さんのワンタイムチケット
include_once "./include/gtickets.php" ;
$myts =& MyTextSanitizer::getInstance();

include_once ("./include/wiki_helper.php");

$memo_id = isset($_GET["tagmemo_id"]) ? intval($_GET["tagmemo_id"]) : "";
//$memo_id = isset($_POST["tagmemo_id"]) ? intval($_POST["tagmemo_id"]) : $memo_id;
$title = empty($_GET["t"]) ? "" : htmlspecialchars(mb_convert_encoding($_GET["t"],"EUC-JP","UTF-8"))."\n";
$url = empty($_GET["u"]) ? "" : $_GET["u"];
$memo_content = $title . (($url)? htmlspecialchars($url)."\n\n":"");

// URL中の & 対策
$url = str_replace("&","%26",$url);

$uid = empty($_GET["uid"]) ? 0 : (int)$_GET["uid"];

//ユーザーIDをもらおう
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
	$uname = htmlspecialchars($xoopsUser->getVar("uname"));
	$isAdmin = $xoopsUser->isAdmin($xoopsModule->getVar('mid'));
	$login = true;
} else {
	if ($uid)
	{
		$member_handler =& xoops_gethandler('member');
		$user =& $member_handler->getUser($uid);
		$uname = htmlspecialchars($user->getVar("uname"));
	}
	$uid = 0;
	$isAdmin = false;
	$login = false;
}

$memo = array();
$tagmemo_handler =& xoops_getmodulehandler('tagmemo');
$tagmemo_handler->setUid($uid);
//echo $tagmemo_handler->_condition_uid ;
if($memo_id != ""){
	$memo_id =intval($memo_id );
	$memo =& $tagmemo_handler->getMemo4Edit($memo_id);
	if (!$memo) {
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, 'Such memo does not exist');
	}
	
	if ($memo['public'] == 0 && !$isAdmin) {
		if ($memo['uid'] == 0) {
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		} elseif ($memo['owner'] == 0) {
			redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
		}
	}
}

$xoopsOption['template_main'] = 'tagmemo_quickedit.html';

require_once XOOPS_ROOT_PATH.'/class/template.php';
$xoopsTpl = new XoopsTpl();

// Wikiヘルパー
$he = & WikiHelper::getInstance();
$xoopsTpl->assign("helper", $he->get());

$xoopsTpl->assign("uname", $uname);
$xoopsTpl->assign("login", $login);

$memo['content'] = $memo_content;
$xoopsTpl->assign("target_url", rawurlencode($url));
$xoopsTpl->assign("memo", $memo);
if($memo_id != ""){
	$xoopsTpl->assign("xoopsGTicket_html", $xoopsGTicket->getTicketHtml( __LINE__ ));
}

$xoopsCachedTemplate = 'db:'.$xoopsOption['template_main'];
header ("Content-Type: text/html; charset=EUC-JP");
$xoopsTpl->display($xoopsCachedTemplate);
?>