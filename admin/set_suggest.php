<?php
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <http://www.xoops.org/>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
/**
* @package adminPage
*/

/**
* 管理画面用のヘッダファイル読み込み
* s
*/

require_once('../../../include/cp_header.php');

//(DB update section)
// suggest用フィールド存在チェック
tagmemo_admin_set_suggest_dbcheck();
xoops_cp_header();

// add Hiro
include('./mymenu.php');
echo "<h4>Set Suggest</h4>";

if ($_GET['mode'] == "set")
{
	tagmemo_admin_set_suggest();
}
else
{
	tagmemo_admin_set_suggest_init();
}

function tagmemo_admin_set_suggest_dbcheck()
{
	global $xoopsDB;
	//Suggest用DBフィールドのチェック
	$query = "select `suggest` FROM ".$xoopsDB->prefix("tagmemo_tag")." LIMIT 1;";
	if(!$result=$xoopsDB->query($query))
	{
		$query = "ALTER TABLE `".$xoopsDB->prefix("tagmemo_tag")."` ADD `suggest` VARCHAR(60) NOT NULL default '';";
		if(!$result=$xoopsDB->queryF($query)){
			echo "ERROR: '".$xoopsDB->prefix("tagmemo_tag")."' is already processing settled.<br/>";
			echo $query;
		}
	}
}

function tagmemo_admin_set_suggest_init()
{
	echo "<p><a href='?mode=set'>"._AM_SET_SUGGEST."</a></p>";
}

function tagmemo_admin_set_suggest()
{
	include_once("../include/hyp_kakasi.php");
	global $xoopsDB, $xoopsModuleConfig;

	$ka = new Hyp_KAKASHI();
	$ka->kakasi_path = $xoopsModuleConfig['kakasi_path'];
	$query = "SELECT * FROM `".$xoopsDB->prefix("tagmemo_tag")."` WHERE `suggest` = '' OR `suggest` = `tag`";
	$res = $xoopsDB->query($query);
	if ($res)
	{
		while($data = mysql_fetch_row($res))
		{
			$suggest = $data[1];
			$ka->get_hiragana($suggest);
			$query = "UPDATE `".$xoopsDB->prefix("tagmemo_tag")."` SET `suggest` = '".addslashes($suggest)."' WHERE `tag_id` = ".$data[0]." LIMIT 1";
			echo htmlspecialchars($data[1])." -> ".htmlspecialchars($suggest)."<br />";
			$xoopsDB->queryF($query);
		}
		echo "<hr />End of data.<br />";
	}
}

xoops_cp_footer();
?>