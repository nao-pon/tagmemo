<?php
/*
 * Created on 2006/03/20
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

$xoopsOption['nocommon'] = 1;
require '../../mainfile.php';

error_reporting(0);

$q = (isset($_GET['q']))? $_GET['q'] : "";

$dats = array();
$oq = $q = str_replace("\0","",$q);

if ($q !== "")
{
	// mbstring のチェック
	if (!extension_loaded('mbstring'))
	{
		include_once('./include/mbstring.php');
	}
	
	$q = addslashes(mb_convert_encoding($q,"EUC-JP","UTF-8"));
	
	if ($q == ":" || $q == "：")
	{
		$oq = "";
		$where = "";
		$order = " ORDER BY `tag_id` DESC";
	}
	else
	{
		
		$where = " WHERE `tag` LIKE '%".$q."%' OR `suggest` LIKE '%".$q."%'";
		$order = "";
	}
		
	mysql_connect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS) or die(mysql_error());
	mysql_select_db(XOOPS_DB_NAME); 
	
	$query = "SELECT `tag`, `suggest` FROM `".XOOPS_DB_PREFIX."_tagmemo_tag`".$where.$order." LIMIT 30";
	
	$suggests = $tags = array();
	if ($result = mysql_query($query))
	{
		while($dat = mysql_fetch_array($result))
		{
			$tags[] = '"'.str_replace('"','\"',$dat[0]).'"';
			$suggests[] = '"'.str_replace('"','\"',$dat[1]).'"';
		}
	}
}

$oq = '"'.str_replace('"','\"',$oq).'"';
$ret = "this.setSuggest($oq,new Array(".mb_convert_encoding(join(", ",$tags),"UTF-8","EUC-JP")."),new Array(".mb_convert_encoding(join(", ",$suggests),"UTF-8","EUC-JP")."));";

header ("Content-Type: text/html; charset=UTF-8");
echo $ret;

?>
