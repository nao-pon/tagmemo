<?php
// add Hiro
$adminmenu[] = array(
	'title' => _MI_TAGMEMO_ADMENU1 ,
	'link' => "admin/index.php"
	);
// add end

$adminmenu[] = array(
	'title' => 'firefox search plugin',
	'link' => "admin/index.php?page=ff_plugin"
	);

$adminmenu[] = array(
	'title' => _MI_TAGMEMO_ADMENU3 ,
	'link' => "admin/index.php?page=permissions"
	);

$adminmenu4altsys = array(
	array(
		'title' => _MI_TAGMEMO_ADMENU_MYLANGADMIN ,
		'link' => 'admin/index.php?mode=admin&lib=altsys&page=mylangadmin' ,
	) ,
	array(
		'title' => _MI_TAGMEMO_ADMENU_MYTPLSADMIN ,
		'link' => 'admin/index.php?mode=admin&lib=altsys&page=mytplsadmin' ,
	) ,
	array(
		'title' => _MI_TAGMEMO_ADMENU_MYBLOCKSADMIN ,
		'link' => 'admin/index.php?mode=admin&lib=altsys&page=myblocksadmin' ,
	) ,
	array(
		'title' => _MI_TAGMEMO_ADMENU_MYPREFERENCES ,
		'link' => 'admin/index.php?mode=admin&lib=altsys&page=mypreferences' ,
	) ,
) ;

?>