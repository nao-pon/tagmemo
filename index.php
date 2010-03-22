<?php
/**
* @package Page
*/

// 必要なファイルを一気に取り込むおまじない。
/**
* XOOPS用ファイルの取り込み
*/
require_once '../../mainfile.php';
$myts =& MyTextSanitizer::getInstance();

define("_MD_TAGMEMO_SHORTURL", empty($xoopsModuleConfig['tagmemo_shorturl'])? false : true);

$tagmemo_handler =& xoops_getmodulehandler('tagmemo');

$tag_name = empty($_GET["tag_name"]) ? "" :$_GET["tag_name"];
$tag_names = explode(" ",$tag_name);
if ($tag_name)
{
	$tag_id = array();
	foreach($tag_names as $_name)
	{
		$tag_id[] = $tagmemo_handler->getTagId($_name);
	}
	$tag_id = join(',',$tag_id);
	//exit ($tag_id);
}
else
{
	$tag_id = empty($_GET["tag_id"]) ? "" :$_GET["tag_id"];
}
$condition = $tag_id;
$tag_id = explode(",",$tag_id);
$keyword = empty($_GET["query"]) ? "" :$_GET["query"];
//$keyword = $myts->addSlashes($keyword);
$condition .= $keyword;
$count = isset($xoopsModuleConfig['per_page']) ? $xoopsModuleConfig['per_page'] : 10;
$count = (strlen($condition)>0) ? 0 : $count;
//$search_from = empty($_GET["from"]) ? "sub" :$_GET["from"];
$start = empty($_GET["start"]) ? 0 : intval($_GET["start"]);


if(strlen($keyword)>0){
	$tagmemo_handler->search($keyword);
//	if($search_from=="all"){
		$tag_id  = array();
//	}
}

if(is_object($xoopsUser)){
	$uid = $xoopsUser->getVar("uid");
	$uname = $xoopsUser->getVar("uname");
} else {
	$uid = 0;
	$uname = "";
}
	$tagmemo_handler->setUid($uid);
$memo_array =& $tagmemo_handler->getMemosArray($tag_id,$count,$start);
if(strlen($condition)>0){
	$tagmemo_query=true;
	$query_condition = $tagmemo_handler->getQueryCondition();
	$query_condition = stripslashes($query_condition );
	$query_condition = htmlspecialchars($query_condition );
	$tag_condition = $tagmemo_handler->getTagCondition();
	$tagmemo_related_tags = $tagmemo_handler->getRelatedTags();
}else{
	$tagmemo_query=false;
//	$tagmemo_block_recent_hide = true;
}

// favicon_src.
if (is_file(XOOPS_ROOT_PATH . '/class/hyp_common/favicon.php')) {
	$favicon_src = XOOPS_URL . '/class/hyp_common/favicon.php';
} else {
	$favicon_src = '';
}

$xoopsOption['template_main'] = 'tagmemo_list.html';
// echo 'my uid =' . $xoopsUser->getVar("uid");
// ヘッダを書くおまじない。
/**
* XOOPSのテンプレートのヘッダー
*/
include XOOPS_ROOT_PATH.'/header.php';
$xoopsTpl->assign("memos", $memo_array);
$xoopsTpl->assign("favicon_src", $favicon_src);

//version 取得
$xoopsTpl->assign("tagmemo_version", $xoopsModule->getInfo('version'));

// <head>タイトル設定
$_tmp = array();
if (!empty($tag_condition['detail']))
{
	foreach($tag_condition['detail'] as $_val)
	{
		$_tmp[] = $_val['string'];
	}
}
$_tmp = join("+",$_tmp);
$xoopsTpl->assign("xoops_pagetitle",$_tmp."-".$xoopsModule->name());

// タグリストのポップアップ指定
$xoopsTpl->assign("taglist_popup",empty($xoopsModuleConfig['tagpopup_list'])? false : true);

if(strlen($condition)>0){
	$xoopsTpl->assign("query",true);
	$xoopsTpl->assign("rel_tags", $tagmemo_related_tags);
	$xoopsTpl->assign("query_condition", $query_condition);
	$xoopsTpl->assign("query_condition_url", urlencode($query_condition));
	$xoopsTpl->assign("tag_condition", $tag_condition);
}else{

	$xoopsTpl->assign("query",false);
	$maxcount = $tagmemo_handler->getMemoCount();
	include_once './class/pagenavi.php';
	$nav = new TagmemoPageNav($maxcount, $count, $start);
	$xoopsTpl->assign('pagenav', $nav->renderNav());
}

$xoopsTpl->assign('bookmarklet',"javascript:if(document.getElementById('tagmemo_scr'))document.body.removeChild(document.getElementById('tagmemo_scr'));(function(){var s=document.createElement('script');s.id='tagmemo_s';s.charset='EUC-JP';s.src='".XOOPS_URL."/modules/tagmemo/quickform.php?uid=".$uid."';document.body.appendChild(s)})();");
$ff_plugin = isset($xoopsModuleConfig['ff_plugin']) ? $xoopsModuleConfig['ff_plugin'] : 0;
$xoopsTpl->assign('ff_plugin', $ff_plugin);

/**
* XOOPSのテンプレートのフッター
*/
include(XOOPS_ROOT_PATH.'/footer.php');
?>
