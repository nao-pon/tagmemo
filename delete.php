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

//値を受けてみるよ。
$memo_id = isset($_POST["tagmemo_id"]) ? $_POST["tagmemo_id"] :0;
//ハンドラをつくってみるよ。
$tagmemo_handler =& xoops_getmodulehandler("tagmemo");
//空のオブジェクトを作ってみるよ
// echo "checkpoint 3.5 <br>\n";

$module_id = $xoopsModule->mid();

//ユーザーIDをもらおう
if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
} else {
	$uid = 0;
}
if($memo_id != 0){
	$memo_obj =& $tagmemo_handler->getMemoObj($memo_id);
	$memo_owner = $memo_obj->getVar('uid');

 	if(($uid == 0) or (($memo_owner!= $uid) and !($xoopsUser->isAdmin($module_id)))){
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);
	}
}else{
		redirect_header(XOOPS_URL."/modules/tagmemo/index.php", 3, _NOPERM);

}
	if ( ! $xoopsGTicket->check() ) {
		redirect_header(XOOPS_URL.'/',3,$xoopsGTicket->getErrors());
	}


//オブジェクトに値を設定してみるよ。

//放り込め！
$tagmemo_handler->deleteMemo($memo_obj);
// echo "checkpoint 7 <br>\n";

// echo "OK";

// ヘッダを書くおまじない。
//  include XOOPS_ROOT_PATH.'/header.php';
//  include(XOOPS_ROOT_PATH.'/footer.php');

redirect_header(XOOPS_URL.'/modules/tagmemo/', 1, _MD_TAGMEMO_MESSAGE_DELETE);
?>