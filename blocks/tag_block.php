<?php
/**
* @package block
*/

/**
*人気タグブロック
*@return array
*/
function b_tagmemo_poulartag(){
	global $tagmemo_block_popular_hide;
	if($tagmemo_block_popular_hide == true){
		return false;
	}
	$tagmemo_tag_handeler =& xoops_getmodulehandler("tag", 'tagmemo');
	$ret = $tagmemo_tag_handeler->getPopularTag();
	return $ret;
}

/**
*最近のタグブロック
*@return array
*/
function b_tagmemo_recenttag(){
	global $tagmemo_block_recent_hide;
	if($tagmemo_block_recent_hide == true){
		return false;
	}
	$tagmemo_tag_handeler =& xoops_getmodulehandler("tag", 'tagmemo');
	$ret = $tagmemo_tag_handeler->getResentTag();
	return $ret;
}

function b_tagmemo_relatedtag(){
	global $tagmemo_related_tags;
	$ret["reltags"] = $tagmemo_related_tags;
	global $tag_condition;
	$ret["tag_condition"]=$tag_condition;
	global $tagmemo_query;
	$ret["query"]=$tagmemo_query;
	global $query_condition;
	$ret["query_condition"]=$query_condition;
	$ret["query_condition_url"]=urlencode($query_condition);
	return $ret;
}
function b_tagmemo_id_serch(){
return true;
}
?>