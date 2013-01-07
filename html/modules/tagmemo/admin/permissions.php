<?php
/*
 * Created on 2006/04/15
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

require_once( 'mygrouppermform.php' ) ;

// for "Duplicatable"
$mydirname = basename( dirname( dirname( __FILE__ ) ) ) ;
if( ! preg_match( '/^(\D+)(\d*)$/' , $mydirname , $regs ) ) echo ( "invalid dirname: " . htmlspecialchars( $mydirname ) ) ;
$mydirnumber = $regs[2] === '' ? '' : intval( $regs[2] ) ;

require_once( XOOPS_ROOT_PATH."/modules/$mydirname/include/gtickets.php" ) ;

if( ! empty( $_POST['submit'] ) ) {

	// Ticket Check
	if ( ! $xoopsGTicket->check() ) {
		redirect_header(XOOPS_URL.'/',3,$xoopsGTicket->getErrors());
	}

	include( "mygroupperm.php" ) ;

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

echo $form->render() ;

?>