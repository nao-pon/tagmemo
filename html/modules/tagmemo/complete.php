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
	$link = mysqli_connect(XOOPS_DB_HOST, XOOPS_DB_USER, XOOPS_DB_PASS, XOOPS_DB_NAME) or die(mysql_error());
	
	if (function_exists('mysqli_set_charset')) {
		mysqli_set_charset($link, 'utf8');
	} else {
		mysqli_query($link, 'SET NAMES utf8');
	}
	
	$q = mysqli_real_escape_string($link, $q);
	
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
		
	
	$query = "SELECT `tag`, `suggest` FROM `".XOOPS_DB_PREFIX."_tagmemo_tag`".$where.$order." LIMIT 30";
	
	$suggests = $tags = array();
	if ($result = mysqli_query($link, $query))
	{
		while($dat = mysqli_fetch_array($result))
		{
			$tags[] = '"'.str_replace('"','\"',$dat[0]).'"';
			$suggests[] = '"'.str_replace('"','\"',$dat[1]).'"';
		}
	}
}

$oq = '"'.str_replace('"','\"',$oq).'"';
$ret = "this.setSuggest($oq,new Array(".join(", ",$tags)."),new Array(".join(", ",$suggests)."));";

header ("Content-Type: text/html; charset=UTF-8");
header ("Content-Length: ".strlen($ret));
echo $ret;

?>
