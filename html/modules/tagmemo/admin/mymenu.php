<?php
	if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;
	if( ! isset( $module ) || ! is_object( $module ) ) $module = $xoopsModule ;
	else if( ! is_object( $xoopsModule ) ) die( '$xoopsModule is not set' )  ;
	if( file_exists("../language/".$xoopsConfig['language']."/modinfo.php") ) {
		include_once("../language/".$xoopsConfig['language']."/modinfo.php");
	} else {
		include_once("../language/english/modinfo.php");
	}
	include( './menu.php' ) ;
	$menuitem_dirname = $module->getvar('dirname') ;
	$preflink = (defined('XOOPS_CUBE_LEGACY'))? '../legacy/admin/index.php?action=PreferenceEdit&confmod_id='
	                                          : 'admin/admin.php?fct=preferences&op=showmod&mod=';
	array_push( $adminmenu , array( 'title' => _PREFERENCES , 'link' => $preflink . $module->getvar('mid') ) ) ;
	// 管理画面の上部メニュー
	echo "<div style='font-size: 7pt; padding-right: 20px;padding-bottom: 20px;'>
		<font style='font-size: large;color: #2F5376;'>" . _MI_TAGMEMO_NAME . "</font>&nbsp;&nbsp;&nbsp;&nbsp;
		<a href='../index.php'>" . _MI_TAGMEMO_GOMODULES . "</a>&nbsp;&nbsp;|&nbsp;&nbsp;
		<a href='../../system/admin.php?fct=modulesadmin&op=update&module=" .  $module-> getVar('dirname') . "'>" . _MI_TAGMEMO_UPDATE . "</a>
		</div>";
	// ブロックでの選択メニュー
	echo '<div width="90%" align="center" style="text-align: center; color: #2F5376; vertical-align: middle; padding: 2px 6px 2px 6px; font-size: 11px; line-height: 14px; border-top-width: thin; border-bottom-style: solid; border-bottom-width: thin;">' ;
	// メニュー処理
	$menuitem_count = 0 ;
	$mymenu_uri = empty( $mymenu_fake_uri ) ? $_SERVER['REQUEST_URI'] : $mymenu_fake_uri ;
	foreach( $adminmenu as $menuitem ) {
		if( stristr( $mymenu_uri , $menuitem['link'] )) {
			// 選択されたテキスト
			$menuitem_bgcolor = '#C0FFC0;
			border-top-style: solid;border-top-width: thin; border-right-style: solid; border-right-width: thin; border-left-style: solid; border-left-width: thin ' ;
		} else {
			// 選択されていないテキスト
			$menuitem_bgcolor = '#FFFFFF';
		}
		// 下のライン
		echo "<a href='".XOOPS_URL."/modules/$menuitem_dirname/{$menuitem['link']}' style='text-align: center; background-color:$menuitem_bgcolor;
		 vertical-align: middle; padding: 6px 6px 3px 6px; font-size: 11px; line-height: 14px; '>{$menuitem['title']}</a> &nbsp; \n" ;
	}
	echo "</div><br>\n" ;
	$mymenu_link = substr( strstr( $mymenu_uri , '/admin/' ) , 1 ) ;


?>