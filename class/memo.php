<?php
/**
*「メモ」オブジェクトのクラス定義
* @package Persistence
*/
/**
* XoopsTableObject継承
*/
include_once dirname(__FILE__).'/xoopstableobject.php';

/**
* メモのデータオブジェクト
* @package Persistence
* @author twodash <twodash@twodash.net>
*/
class TagmemoMemo extends XoopsTableObject
{
/**
* コンストラクタ
*/
	function TagmemoMemo()
	{
		$this->XoopsObject();
		$this->initVar('tagmemo_id', XOBJ_DTYPE_INT, null, true);
		$this->initVar('uid', XOBJ_DTYPE_INT, null, true);
		$this->initVar('title', XOBJ_DTYPE_TXTBOX, null, true, 120);
		$this->initVar('content', XOBJ_DTYPE_TXTAREA, null, true);
		$this->initVar('timestamp', XOBJ_DTYPE_INT, null, true);
		$this->initVar('public', XOBJ_DTYPE_INT, null, true);
		//プライマリーキーの定義
		$this->setKeyFields(array('tagmemo_id'));

		//AUTO_INCREMENT属性のフィールド定義
		// - 一つのテーブル内には、AUTO_INCREMENT属性を持つフィールドは
		//   一つしかない前提です。
		$this->setAutoIncrementField('tagmemo_id');
	} 
}

/**
* メモのオブジェクトハンドラ
* @package Persistence
* @author twodash <twodash@twodash.net>
*/
class TagmemoMemoHandler extends XoopsTableObjectHandler
{
/**
* コンストラクタ
*/
    function TagmemoMemoHandler(&$db)
    {
    ////////////////////////////////////////
    // 各クラス共通部分(書換不要)
    ////////////////////////////////////////

        //親クラスのコンストラクタ呼出
        $this->XoopsTableObjectHandler($db);
        
    ////////////////////////////////////////
    // 派生クラス固有の定義部分
    ////////////////////////////////////////

        //ハンドラの対象テーブル名定義
        $this->tableName = $this->db->prefix('tagmemo');

        //関連クラス名称を小文字で定義
        // - 標準のネーミングに準拠している場合には設定不要
     $this->objectClassName = 'tagmemomemo';
    }
	//互換性のため残してみる
    function &getInstance(&$db)
    {
        static $instance;
        if (!isset($instance)) {
            $instance = new TagmemoMemoHandler($db);
        }
        return $instance;
    }
/**
*メモ取得用に特化
* @param CriteriaElement
* @return array
*/
	function &getMemo($criteria=null,$having=""){
		$fieldlist=" main.tagmemo_id, uid, title, content, public, timestamp";
		if($criteria == null){
			$criteria = new CriteriaCompo;
		}
		$criteria->setGroupby($fieldlist);
		$joindef = new XoopsJoinCriteria($this->db->prefix('tagmemo_rel'), 'tagmemo_id','tagmemo_id', 'LEFT','main','rel');
		return parent::getObjects($criteria, true, $fieldlist, false, $joindef,$having);	
	}
}
?>