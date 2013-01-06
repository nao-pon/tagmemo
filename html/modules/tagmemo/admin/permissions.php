<?php
/*
 * Created on 2006/04/15
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once( '../../../include/cp_header.php' ) ;
require_once( 'mygrouppermform.php' ) ;

// for "Duplicatable"
$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;
if( ! preg_match( '/^(\D+)(\d*)$/' , $mydirname , $regs ) ) echo ( "invalid dirname: " . htmlspecialchars( $mydirname ) ) ;
$mydirnumber = $regs[2] === '' ? '' : intval( $regs[2] ) ;

require_once( XOOPS_ROOT_PATH."/modules/$mydirname/include/gtickets.php" ) ;

// language files
$language = $xoopsConfig['language'] ;
if( ! file_exists( XOOPS_ROOT_PATH . "/modules/system/language/$language/admin/blocksadmin.php") ) $language = 'english' ;
include_once( XOOPS_ROOT_PATH . "/modules/system/language/$language/admin.php" ) ;

if( ! empty( $_POST['submit'] ) ) {

	// Ticket Check
	if ( ! $xoopsGTicket->check() ) {
		redirect_header(XOOPS_URL.'/',3,$xoopsGTicket->getErrors());
	}

	include( "mygroupperm.php" ) ;
	redirect_header( XOOPS_URL."/modules/$mydirname/admin/permissions.php" , 1 , _MD_AM_DBUPDATED );
	exit ;
}


$item_list = array(
	'1' => _AM_TAGMEMO_GPERM_SUBMIT
	) ;


/*$item_list = array(
	'1' => _AM_GPERM_G_INSERTABLE ,
	'2' => _AM_GPERM_G_SUPERINSERT ,
	'4' => _AM_GPERM_G_EDITABLE ,
	'8' => _AM_GPERM_G_SUPEREDIT ,
//	'16' => _AM_GPERM_G_DELETABLE ,
	'32' => _AM_GPERM_G_SUPERDELETE
//	'64' => _AM_GPERM_G_TOUCHOTHERS
	) ;*/

$form = new MyXoopsGroupPermForm( _AM_GROUPPERM , $xoopsModule->mid() , 'tagmemo_submit' , _AM_GROUPPERMDESC ) ;
foreach( $item_list as $item_id => $item_name) {
	$form->addItem( $item_id , $item_name ) ;
}

xoops_cp_header();
include( './mymenu.php' ) ;
echo $form->render() ;
xoops_cp_footer();

?>