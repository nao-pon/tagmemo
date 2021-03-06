<?php
/**
* たぐメモのクラス定義
* ページコントローラからはこのファイルのクラスのみインスタンス化すること
*
*
*
* @package Persistence
*/
/**
* Xoops Objectの定義読み込み
*/
//include_once XOOPS_ROOT_PATH."/class/xoopsobject.php";
/**
* タグメモのオブジェクトハンドラ
* @package Persistence
* @author twodash <twodash@twodash.net>
*/
class TagmemoTagmemoHandler {// extends XoopsObjectHandler {
	//public vars
	var $forceignorepages = '';
	
	public $mid;
	
	private $db;
	private $is_utf8;
	private $readonly;
	
	//Nothing
	//public function
	//Constructor
	/**
	* コンストラクタ
	* @param object $db (dummy param which is needed when using xoops_getmodulehandler method. )
	* @access public
	*/
	function TagmemoTagmemoHandler($db=null) {
//		$this->XoopsObjectHandler($db);
		$this->db = & $db;
		$this->_tag_handler = & xoops_getmodulehandler('tag', 'tagmemo');
		$this->_memo_handler = & xoops_getmodulehandler('memo', 'tagmemo');
		$this->_rel_handler = & xoops_getmodulehandler('relation', 'tagmemo');
		
		$this->is_utf8 = (strtoupper(_CHARSET) === 'UTF-8');
		
		$this->setReadonly();
		
		$module_handler =& xoops_gethandler('module');
		$mModule =& $module_handler->getByDirname('tagmemo');
		$this->mid = $mModule->mid();
		
		// For XCL >= 2.2.1.1 (clear cache of modinfo for submenu control)
		// Is it XCL's bug? need check next
		// http://xoopscube.svn.sourceforge.net/viewvc/xoopscube/Package_Legacy/trunk/html/kernel/module.php?view=log
		if (defined('LEGACY_BASE_VERSION') && version_compare(LEGACY_BASE_VERSION, '2.2.1.1', '>=')) {
			$mModule->modinfo = null;
		}
	}
	//Constructor
	/**
	* コンストラクタ
	*/
	public static function & getInstance(& $db) {
		static $instance;
		if (!isset ($instance)) {
			$instance = new TagmemoTagmemoHandler($db);
		}
		return $instance;
	}
	
	function setReadonly($uid = null) {
		$this->readonly = $this->checkGroupPerm($uid)? false : true;
		$GLOBALS['mTagmemoReadonly'] = $this->readonly;
	}
	
	function isReadonly() {
		return $this->readonly;
	}
	
	/**
	* 新規にタグのオブジェクトを取得
	*
	* @param bool 新規に作成
	* @access public
	* @return TagmemoTagObject
	*/
	function & createTag($isNew = true) {
		if ($this->readonly) return false;
		$this->_ready(true, false, false);
		$ret = & $this->_tag_handler->create($isNew);
		return $ret;
	}

	/**
	* 新規にメモのオブジェクトを取得
	* @access public
	* @param bool 新規に作成
	* @return TagmemoMemoObject
	*/
	function & createMemo($isNew = true) {
		if ($this->readonly) return false;
		$this->_ready(false, true, false);
		$ret = & $this->_memo_handler->create($isNew);
		return $ret;
	}

	/**
	* メモオブジェクトとタグを登録
	* @access public
	* @param TagmemoMemoObject
	* @param string タグのリストの文字列
	* @param bool
	* @return integer 挿入したメモのID
	*/
	function insert(&$arg_memo, $arg_tags, $force = false) {
//		$this->_ready();
/*		$this->_memo_handler->insert($arg_memo, $force);
		//		$arg_memo->isnew();
		$wk_memo_id = $arg_memo->getVar("tagmemo_id");
		$arr_tags = & $this->_tag2array($arg_tags);
		$this->_rel_handler->removeRelation($wk_memo_id);
		foreach ($arr_tags as $wk_tag) {
			$wk_tag_id = $this->getTagId($wk_tag, true);
			$this->setRelation($wk_memo_id, $wk_tag_id);
		}
		return $wk_memo_id;*/
		if ($this->readonly) return false;
		$ret = false;
		if ($this->insertMemo($arg_memo, $force)) {
			$wk_memo_id = $arg_memo->getVar("tagmemo_id");
			if ($this->insertTags($wk_memo_id, $arg_tags, $force)) {
				$ret = true;
			} else{
				$this->setError('Some Error occured but memo is saved.');
			}
		}
		return $ret;
	}
	/**
	 * メモオブジェクトを登録
	 */
	function insertMemo(&$arg_memo, $force = false) {
		if ($this->readonly) return false;
		if (!$this->_memo_handler->insert($arg_memo, $force)) {
			$this->setError($this->_memo_handler->getErrors(false));
			return false;
		}
		return true;
	}
	/**
	 * タグを登録
	 */
	function insertTags($arg_memo_id, $arg_tags, $force = false) {
		if ($this->readonly) return false;
		$ret = true;
		$arr_tags = & $this->_tag2array($arg_tags);
		$this->_rel_handler->setUpdateMode($force);
		$this->_rel_handler->removeRelation($arg_memo_id);
		foreach ($arr_tags as $wk_tag) {
			$wk_tag_id = $this->getTagId($wk_tag, true, $force);
			if (!$this->setRelation($arg_memo_id, $wk_tag_id)) {
				$this->setError('tag: '.htmlspecialchars($wk_tag, ENT_COMPAT, _CHARSET).' is not related.');
				$ret = false;
			}
		}
		return $ret;
	}
	/**
	* メモを削除
	* @access public
	* @param TagmemoTagObject
	*/
	function deleteMemo($memoObj) {
		if ($this->readonly) return false;
		$wk_memo_id = $memoObj->getVar("tagmemo_id");
		$this->_rel_handler->removeRelation($wk_memo_id);
		if (!$this->_memo_handler->delete($memoObj)) {
			$this->setError($this->_memo_handler->getErrors(false));
			return false;
		}
		return true;
	}
	/**
	* $tag_varの存在確認
	* @access public
	* @return bool
	*/
	/* $tag_varの存在確認 */
	function isExistTag(&$tag_var) {

		$wk_tag_id = $this->getTagId($tag_var);
		$ret = ($wk_tag_id > 0) ? 1 : 0;
		//		$this->_getTag2Cache();
		//		$ret =& in_array($tag_var, $this->_tags);
		//$ret =& in_array($this->_tags); hey!hey!
		return $ret;
	}

	/**
	* $tag_varのtag_idの取得なければインサート
	* @access public
	* @param string タグのデータ
	* @param bool
	* @return integer tagmemo_id
	*/
	function getTagId(&$tag_var, $force = false, $forceupdate = false) {
		$ret = $this->_tag_handler->getTagId($tag_var);
		if ($ret < 1 and $force) {
			global $xoopsModuleConfig;
			if (defined('XOOPS_TRUST_PATH') && is_file(XOOPS_TRUST_PATH . '/class/hyp_common/hyp_kakasi.php')) {
				include_once XOOPS_TRUST_PATH . '/class/hyp_common/hyp_kakasi.php';
			} else {
				include_once './include/hyp_common/hyp_kakasi.php';
			}
			$wk_obj_tag = & $this->_tag_handler->create(true);
			$wk_obj_tag->setVar("tag", $tag_var);
			$ka = new Hyp_KAKASHI();
			$ka->kakasi_path = $xoopsModuleConfig['kakasi_path'];
			$ka->encoding = _CHARSET;
			$suggest = $tag_var;
			if ($ka->get_hiragana($suggest))
			{
				$wk_obj_tag->setVar("suggest", $suggest);
			}
			$this->_tag_handler->insert($wk_obj_tag, $forceupdate);
			$ret = $wk_obj_tag->getVar("tag_id");
		}
		return $ret;
	}

	/**
	* タグとメモを関連付ける
	* @param integer メモのID
	* @param integer タグのID
	* @access public
	*/
	function setRelation($tagmemo_id, $tag_id) {
		$ret = false;
		if ($tagmemo_id != 0 & $tag_id != 0) {
			$ret = $this->_rel_handler->setRelation($tagmemo_id, $tag_id);
		}
		return $ret;
	}

	/**
	* メモobjectを取得
	* @access public
	* @deprecated
	*/
	//メモobjectを返す
	function &getMemos() {
		$this->_getMemo2Cache();
		$ret = & $this->_memos;
		return $ret;
	}

	/* 1個のメモを返す */
	/**
	* 1個のメモをデータの配列を取得
	* @access public
	* @param integer メモのID
	* @return array
	*/
	function &get($memo_id, $use_autolink = false) {
		$memo_id = intval($memo_id);
		//		$this->_ready();
		$wk_objmemo = & $this->getMemoObj($memo_id);
		if (!is_object($wk_objmemo)) {
			return array('content'=>'No such memo does not exist','uid'=>0);
		}
		$this->_set_condition_memo($memo_id);
		$this->_getTag2Cache();
		$rel_criteria = new Criteria('tagmemo_id', $memo_id);
		$this->_rel_handler->readRelation($rel_criteria);
		$ret = $this->_memoObj2Array($wk_objmemo, 's', $use_autolink);
		return $ret;
	}

	/* 編集用のメモを返す */
	/**
	* 編集用のメモのデータ配列を取得
	* @param integer メモのID
	* @access public
	* @return array
	*/
	function & getMemo4Edit($memo_id) {
		$memo_id = intval($memo_id);
		//		$this->_ready();
		$wk_objmemo = & $this->getMemoObj($memo_id);
		$this->_getTag2Cache();
		$rel_criteria = new Criteria('tagmemo_id', $memo_id);
		$this->_rel_handler->readRelation($rel_criteria);
		$ret = $this->_memoObj2Array($wk_objmemo, 'e');
		return $ret;
	}

	/**
	* メモオブジェクトを取得
	* @param integer メモID >0
	* @access public
	* @return TagmemoMemoObject
	*/
	function & getMemoObj($memo_id) {
		//		$this->_getMemo2Cache();
		$this->_set_condition_memo($memo_id);
		$ret = & $this->_memo_handler->get($memo_id);
		return $ret;
	}

	/**
	* メモの配列を取得
	* @access public
	* @param タグID
	* @return array
	*/
	//
	function & getMemosArray($tag_id = null, $count = 0, $start = 0) {
		$ret = array ();
		//		$this->_ready();
		$this->_set_condition_tag($tag_id);
		$this->_getMemo2Cache($count, $start);
		$this->_set_condition_memo(array_keys($this->_memos));
		$this->_getTag2Cache();
		$this->_rel_handler->readRelation();
		$wk_memo_key = array_keys($this->_memos);
		$wk_memo_list = $wk_memo_key;
		// 		if($tag_id != null){
		// 			$wk_memo_list = $this->_rel_handler->tag2memo[$tag_id];
		// 		}
		// 		arsort($wk_memo_list);
		foreach ($wk_memo_list as $wk_memo_id) {
			$objMemo = & $this->_memos[$wk_memo_id];
			$ret[] = $this->_memoObj2Array($objMemo);
		}
		return $ret;
	}
	/**
	* 全てのタグを取得
	* @access public
	* @return array タグIDをキーとしてデータにタグを持つ配列
	*/
	function & getAllTags($min_length = 1) {
		$wk_tags = & $this->_tag_handler->getTagObjects(true);
		$ret = array();
		foreach ($wk_tags as $wk_obj_tag) {
			$wk_tag_id = $wk_obj_tag->getVar("tag_id");
			$wk_tag = $wk_obj_tag->getVar("tag");
			if (strlen($wk_tag) >= $min_length) {
				$ret[$wk_tag_id] = $wk_tag;
			}
		}
		asort($ret);
		return $ret;
	}

	/**
	* 拡張版全てのタグを取得
	* @access public
	* @return array タグIDをキーとしてデータにタグを持つ配列
	*/
	function & getAllTagsEx() {
		$ret = array ();
		$count = $this->_rel_handler->getCount();
		$ret = $this->_tag_handler->getTagArrayForCloud($count);
		//		$ret = $this->_tag_handler->getAllTagsEx();
		return $ret;
	}

	/**
	* 人気タグを取得
	* @access public
	* @param integer 取得数
	* @return array 順位をキーとしてデータにタグIDとタグを持つ配列
	*/
	function getPopularTag($count = 10, $start = 0) {
		$ret = array ();
		$ret = $this->_tag_handler->getPopularTag($count, $start);
		return $ret;
	}
	/**
	* 最新タグを取得
	* @access public
	* @param integer 取得数
	* @return array 順位をキーとしてデータにタグIDとタグを持つ配列
	*/
	function getResentTag($count = 10, $start = 0) {
		$ret = array ();
		$ret = $this->_tag_handler->getResentTag($count, $start);
		return $ret;
	}

	/**
	* 関連タグを取得
	* @access public
	* @return array
	*/
	function getRelatedTags() {
		$ret = array ();
		if (!($this->_flg_chenge_condition_tag)) {
			$this->_getTag2Cache();
			$wk_tags = $this->_tags;
			$wk_keys = array_keys($wk_tags);
			$wk_keys = array_diff($wk_keys, ($this->_condition_tag));
			foreach ($wk_keys as $wk_key) {
				$ret[$wk_key] = $wk_tags[$wk_key];
			}
		}
		return $ret;
	}

	/**
	* タグの検索対象を設定
	* @access public
	* @param array タグIDの配列
	*/
	//
	function setTagCondition($tags) {
		/**  @todo impliment cirteria */
		$wk_tags = array ();
		foreach ($tags as $wk_tag) {
			if (is_numeric($wk_tag) and $wk_tag > 0) {
				$wk_tags[] = $wk_tag;
			}
		}
		$this->_set_condition_tag($wk_tags);
	}
	/**
	*ユーザーIDをセット
	* @param int Xoops Uid
	*/
	function setUid($uid = 0) {
		$this->_condition_uid = intval($uid);
		$this->setReadonly($uid);
	}
	/**
	* 検索用のキーワード設定関数
	* @access public
	* @param string 検索対象
	*/
	function search($keyword) {
		/**  @todo impliment cirteria */
		$wk_array_kwd = $this->_kwd2array($keyword);
		foreach ($wk_array_kwd as $wk_kwd) {
			$wk_tag_id = $this->getTagId($wk_kwd);
			$wk_kwd_item = array();
			$wk_kwd_item["tag_id"] = $wk_tag_id;
			$wk_kwd_item["text"] = $wk_kwd;
			$this->_keyword[] = $wk_kwd_item;
/*			if ($wk_tag_id > 0) {
				$this->_set_condition_tag($wk_tag_id);
			}*/
		}
		$this->_flg_chenge_condition_memo = true;
	//	$this->_keyword = $wk_array_kwd;
	}

	/**
	* キーワードの抽出条件
	* @access public
	*/
	function getQueryCondition() {
	//	return implode($this->_keyword, " ");
	$ret = "";
		foreach($this->_keyword as $wk_kwd_item){
			$ret .= " " . $wk_kwd_item["text"];
		}
		return $ret;
	}
	/**
	* タグの抽出条件
	* @access public
	*/
	function getTagCondition() {
		$this->_getTag2Cache();
		$ret = array ();
		$simple_condition = implode($this->_condition_tag, ",");
		$ret['simple'] = $simple_condition;
		$ret['url'] = (strlen($simple_condition) > 0) ? ($simple_condition.",") : $simple_condition;
		$wk_tagcondition = ($this->_condition_tag);
		foreach ($wk_tagcondition as $condition) {
			$wk_array = array ();
			$wk_condition = implode(array_diff($wk_tagcondition, array ($condition)), ",");
			$wk_array['url'] = $wk_condition;
			$wk_array['id'] = $condition;
			$wk_array['string'] = $this->_tags[$condition]["tag"];
			$ret['detail'][] = $wk_array;
			unset ($wk_array);
		}
		return $ret;
		//		return implode($this->_condition_tag, ",");
	}

	/**
	 * グループパーミッションのチェック
	 * @access private
	 * @param  $gperm_name
	 * @param  $gperm_itemid
	 * @param  $gperm_groupid
	 * @return boolean
	 */
	function checkGroupPerm($uid = null, $gperm_name = 'tagmemo_submit', $gperm_itemid = 1) {
		global $xoopsUser;
		
		$gperm_modid = $this->mid;
		
		if (is_null($uid)) {
			$user = $xoopsUser;
		} else {
			if ($uid > 0) {
				$member_handler =& xoops_gethandler('member');
				$user = $member_handler->getUser($uid);
			} else {
				$user = null;
			}
		}
		
		if (is_object($user)) {
			$gperm_groupid = $user->getGroups();
			if ($user->isAdmin($gperm_modid)) {
				return true;
			}
		} else {
			$gperm_groupid = XOOPS_GROUP_ANONYMOUS;
		}

		$criteria = new CriteriaCompo(new Criteria('gperm_modid', $gperm_modid));
		$criteria->add(new Criteria('gperm_name', $gperm_name));
		$gperm_itemid = intval($gperm_itemid);
		if ($gperm_itemid > 0) {
			$criteria->add(new Criteria('gperm_itemid', $gperm_itemid));
		}
		if (is_array($gperm_groupid)) {
			if (in_array(XOOPS_GROUP_ADMIN, $gperm_groupid)) {
				return true;
			}
			$criteria2 = new CriteriaCompo();
			foreach ($gperm_groupid as $gid) {
				$criteria2->add(new Criteria('gperm_groupid', $gid), 'OR');
			}
			$criteria->add($criteria2);
		} else {
			if (XOOPS_GROUP_ADMIN == $gperm_groupid) {
				return true;
			}
			$criteria->add(new Criteria('gperm_groupid', $gperm_groupid));
		}
		
		$sql = 'SELECT COUNT(*) FROM '.$this->db->prefix('group_permission');
		if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
			$sql .= ' '.$criteria->renderWhere();
		}
		if ($result = $this->db->query($sql)) {
			list($count) = $this->db->fetchRow($result);
		} else {
			$count = 0;
		}
		
		if ($count > 0) {
			return true;
		}
		return false;
	}
	
	function getMemoCount($tag_id = null) {
		if ($tag_id) {
			$this->_set_condition_tag($tag_id);
			$wk_criteria = new CriteriaCompo;
			$wk_tag_criteria =  new CriteriaCompo;
			foreach ($this->_condition_tag as $wk_tag_id) {
				if ($wk_tag_id > 0) {
					$wk_tagid_criteria = new Criteria('tag_id', $wk_tag_id);
					$wk_tag_criteria->add($wk_tagid_criteria, 'OR');
					unset ($wk_tagid_criteria);
				}
			}
			$wk_criteria->add($wk_tag_criteria, 'AND');
			return $this->_rel_handler->getCount($wk_criteria);
		}
		return $this->_memo_handler->getCount();
	}

	function makeAutolinkData() {
		$tags = $this->getAllTags(3);
		list($pattern, $pattern_a, $forceignorelist) = $this->_get_autolink_pattern($tags);
		$file = XOOPS_ROOT_PATH.'/uploads/tagmemo/tagmemo_autolink'.($this->is_utf8? '_utf8' : '').'.dat';
		$fp = fopen($file, 'wb') or
			die_message('Cannot write autolink file ' .
			$file .
			'<br />Maybe permission is not writable');
		set_file_buffer($fp, 0);
		flock($fp, LOCK_EX);
		rewind($fp);
		fputs($fp, $pattern   . "\n");
		fputs($fp, $pattern_a . "\n");
		fputs($fp, join("\t", $forceignorelist) . "\n");
		flock($fp, LOCK_UN);
		fclose($fp);
		
		if ($this->is_utf8) {
			$pattern = mb_convert_encoding($pattern, 'EUC-JP', 'UTF-8');
			$file = XOOPS_ROOT_PATH."/uploads/tagmemo/tagmemo_autolink.dat";
			$fp = fopen($file, 'wb') or
				die_message('Cannot write autolink file ' .
				$file .
				'<br />Maybe permission is not writable');
			set_file_buffer($fp, 0);
			flock($fp, LOCK_EX);
			rewind($fp);
			fputs($fp, $pattern   . "\n");
			fputs($fp, $pattern_a . "\n");
			fputs($fp, join("\t", $forceignorelist) . "\n");
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}

	function setError($error_str)
	{
		$this->_errors[] = $error_str;
	}
	function getErrors($html=true, $clear=true)
	{
		$error_str = "";
		$delim = $html ? "<br />\n" : "\n";
		if (count($this->_errors)) {
			$error_str = implode($delim, $this->_errors);
		}
		if ($clear) {
			$this->_errors = array();
		}
		return $error_str;
	}

	//private vars
	/**
	* @access private
	* @var $_errors
	*/
		var $_errors;
	/**
	* @access private
	* @var $_tag_handler
	*/
	var $_tag_handler;
	/**
	* @access private
	* @var $_memo_handler
	*/
	var $_memo_handler;
	/**
	* @access private
	* @var $_rel_handler
	*/
	var $_rel_handler;
	/**
	* @access private
	* @var $_tags
	*/
	var $_tags = array ();
	/**
	* @access private
	* @var $_memos
	*/
	var $_memos = array ();
	//	var $_tag2memo = array();
	//	var $_memo2tag = array();

	/**
	* @access private
	* @var $_flg_ready
	*/
	var $_flg_ready = false;
	/**
	* @access private
	* @var $_flg_get_tags
	*/
	var $_flg_get_tags = false;
	/**
	* @access private
	* @var $_flg_get_memos
	*/
	var $_flg_get_memos = false;
	/**
	* @access private
	* @var $_condition_tag
	*/
	var $_condition_tag = array ();
	/**
	* @access private
	* @var $_condition_memo
	*/
	var $_condition_memo;
	/**
	* @access private
	* @var $_condition_uid
	*/
	var $_condition_uid = 0;
	/**
	* @access private
	* @var $_condition_tag
	*/
	var $_flg_chenge_condition_tag = true;
	/**
	* @access private
	* @var $_condition_memo
	*/
	var $_flg_chenge_condition_memo = true;
	/**
	* @access private
	* @var $_keyword
	*/
	var $_keyword = array ();

	//private function
	//
	/**
	* メモハンドラとタグハンドラを必要に応じて作成
	* @see $_tag_handler
	* @see $_memo_handler
	* @see $_rel_handler
	* @deprecated
	* @access protected
	* @param bool タグハンドラを準備する
	* @param bool メモハンドラを準備する
	* @param bool 関連ハンドラを準備する
	*/
	function _ready($use_tag = true, $use_memo = true, $use_rel = true) {
		if ($use_tag = true) {
			if (!isset ($this->_tag_handler)) {
				$this->_tag_handler = & xoops_getmodulehandler('tag');
			}
		}
		if ($use_memo = true) {
			if (!isset ($this->_memo_handler)) {
				$this->_memo_handler = & xoops_getmodulehandler('memo');
			}
		}
		if ($use_rel = true) {
			if (!isset ($this->_rel_handler)) {
				$this->_rel_handler = & xoops_getmodulehandler('relation');
			}
		}
	}
	/**
	* $_memosにメモオブジェクトを読み込み
	* @see $_memos
	* @access protected
	*/
	function _getMemo2Cache($count = 0, $start = 0) {
		if (!($this->_flg_get_memos) or ($this->_flg_chenge_condition_memo)) {
			//		$this->_ready(false, true, false);
			// @todo impliment cirteria
			$wk_having = "";
			$wk_criteria = new CriteriaCompo;
			if (count($this->_keyword) > 0) {
				$wk_kwd_criterias =  new CriteriaCompo;
				foreach ($this->_keyword as $wk_kwd) {
					//echo "wk_tag_id= $wk_tag_id<br>";
					$wk_kwd_criteria = new CriteriaCompo;
					if(intval($wk_kwd["tag_id"])>0){
						$wk_kwd_tag_criteria = new Criteria('tag_id', $wk_kwd["tag_id"], '=', 'rel');
						$wk_kwd_criteria->add($wk_kwd_tag_criteria, 'AND');
						unset($wk_kwd_tag_criteria);
					}
					$wk_kwd_text_criteria = new Criteria('content', '%'.$wk_kwd["text"].'%', 'like');
					$wk_kwd_criteria->add($wk_kwd_text_criteria, 'OR');
					unset($wk_kwd_text_criteria);
					$wk_kwd_criterias->add($wk_kwd_criteria, 'AND');
					unset ($wk_kwd_criteria);
					//echo "<br>" . $wk_criteria->render() . "<br>";
				}
				$wk_criteria->add($wk_kwd_criterias, 'AND');
			} elseif (count($this->_condition_tag) > 0) {
				$wk_tag_criteria =  new CriteriaCompo;
				$criteria_count = 0;
				foreach ($this->_condition_tag as $wk_tag_id) {
					if ($wk_tag_id > 0) {
						//echo "wk_tag_id= $wk_tag_id<br>";
						$wk_tagid_criteria = new Criteria('tag_id', $wk_tag_id, '=', 'rel');
						$wk_tag_criteria->add($wk_tagid_criteria, 'OR');
						unset ($wk_tagid_criteria);
						//echo "<br>" . $wk_criteria->render() . "<br>";
						$criteria_count += 1;
					}
				}
				if ($criteria_count > 1) {
					$wk_having = "count(tag_id) = ".$criteria_count;
				}
				$wk_criteria->add($wk_tag_criteria, 'AND');
			}

			$wk_criteria->setSort('timestamp');
			$wk_criteria->setOrder('DESC');
			$wk_criteria->setLimit($count);
			$wk_criteria->setStart($start);
			//		$this->_memos =& $this->_memo_handler->getObjects(null,true);
			$this->_memos = & $this->_memo_handler->getMemo($wk_criteria, $wk_having);
			$this->_flg_get_memos = true;
			$this->_flg_chenge_condition_memo = false;
			//		echo $this->_memo_handler->getLastSql();
		}
	}
	/**
	* $_tags読み込み
	* @see $_tags
	* @access protected
	*/
	function _getTag2Cache() {
		if (!($this->_flg_get_tags) or ($this->_flg_chenge_condition_tag)) {
			if (count($this->_condition_memo) > 0) {
				$this->_tags = $this->_tag_handler->getTags($this->_condition_memo, "tag asc");
			}
			$this->_flg_get_tags = true;
			$this->_flg_chenge_condition_tag = false;
		}
	}

	/**
	* タグ文字列を処理して配列へ
	* @param string タグをスペースまたはコンマで分けたリスト
	* @access protected
	* @return array タグIDのリスト
	*/
	function & _tag2array($tags) {
		$wk_tagstr = strval($tags);
		if (function_exists('mb_convert_kana')){
			$wk_tagstr = mb_convert_kana($wk_tagstr, "asKHV");
		}
		$pattern[] = "/,/";
		$replacement[] = " ";
		$pattern[] = "/\s+/";
		$replacement[] = " ";
		$pattern[] = "/^\s+/";
		$replacement[] = "";
		$pattern[] = "/\s+$/";
		$replacement[] = "";
		$wk_tagstr = preg_replace($pattern, $replacement, $wk_tagstr);
		$ret = preg_split("/\s/", $wk_tagstr);
		$ret = array_unique($ret);
		return $ret;
	}

	/**
	* キーワード文字列を処理して配列へ
	* @param string タグをスペースまたはコンマで分けたリスト
	* @access protected
	* @return array タグIDのリスト
	*/
	function & _kwd2array($arg_kwd) {
		$wk_kwd = strval($arg_kwd);
		$wk_kwd = mb_convert_kana($arg_kwd, "s");
		$pattern[] = "/,/";
		$replacement[] = " ";
		$pattern[] = "/\s+/";
		$replacement[] = " ";
		$pattern[] = "/^\s+/";
		$replacement[] = "";
		$pattern[] = "/\s+$/";
		$replacement[] = "";
		$wk_kwd = preg_replace($pattern, $replacement, $wk_kwd);
		$ret = preg_split("/\s/", $wk_kwd);
		$ret = array_unique($ret);
		return $ret;
	}

	/**
	* memoに関連するタグの配列を取得
	* @access protected
	*/
	function _parseRelatedTags($memo_id) {
		$this->_getTag2Cache();
		$ret = array ();
		$wk_memo2tag = & $this->_rel_handler->memo2tag;
		$wk_tagids = & $wk_memo2tag[$memo_id];
		if (!is_null($wk_tagids)) {
			foreach ($wk_tagids as $wk_tag_id) {
				$ret[$wk_tag_id] = $this->_tags[$wk_tag_id]['tag'];
			}
		}
		return $ret;
	}

	/**
	* メモオブジェクトを出力用の配列を返す関数
	* @param TagmemoMemoObject
	* @param string 出力フォーマット
	* @access protected
	*/
	function & _memoObj2Array(& $objMemo, $format = 's', $use_autolink = false) {
		$ret = array ();
		$memo_id = $objMemo->getVar("tagmemo_id");
		$ret["tagmemo_id"] = $memo_id;
		$wk_uid = intval($objMemo->getVar("uid", $format));
		$ret["uid"] = $wk_uid;
		if ((intval($this->_condition_uid) > 0) and ($wk_uid == intval($this->_condition_uid))) {
			$ret["owner"] = 1;
		} else {
			$ret["owner"] = 0;
		}
		$ret["title"] = $objMemo->getVar("title", $format);
		$ret["content"] = $objMemo->getVar("content", $format);
		if ($format == 's' && $use_autolink) {
			$this->_tag_auto_link($ret["content"]);
		}
		$ret["timestamp"] = formatTimestamp($objMemo->getVar("timestamp", $format), "mysql");
		$ret["public"] = $objMemo->getVar("public", $format);
		$ret["tags"] = $this->_parseRelatedTags($memo_id);
		// リンクの抽出
		$ret['links'] = array();
		if (preg_match_all('/https?:\/\/[\w\/\@\$()!?&%#:;.,~\'=*+-]+/i',$objMemo->getVar("content", 'n'),$match,PREG_PATTERN_ORDER)) {
			$ret['links'] = $match[0];
		}

		return $ret;
	}
	/**
	* @access protected
	*@param mixed
	*@return void
	*/
	function _set_condition_tag($tag_ids) {
		$wk_array = $this->_condition_tag;
		if (is_array($tag_ids)) {
			foreach ($tag_ids as $wk_tag_id) {
				$wk_id = intval($wk_tag_id);
				if ($wk_id > 0) {
					$wk_array[] = $wk_id;
				}
			}
		} else {
			$wk_id = intval($tag_ids);
			if ($wk_id > 0) {
			}
			$wk_array[] = $wk_id;
		}

		$this->_flg_chenge_condition_memo = true;
		$this->_condition_tag = array_unique($wk_array);
	}
	/**
	* @access protected
	*@param mixed
	*@return void
	*/
	function _set_condition_memo($memo_ids) {
		$this->_condition_memo = $memo_ids;
		$this->_flg_chenge_condition_tag = true;
	}

	function _tag_auto_link(&$str)
	{
		static $auto;
		static $forceignorepages;

		if (!$auto)
		{
			$autofile = XOOPS_ROOT_PATH.'/uploads/tagmemo/tagmemo_autolink'.($this->is_utf8? '_utf8' : '').'.dat';
			@list($auto,$dum,$forceignorepages) = @file($autofile);
			if (!$auto) $auto = "(?!)";
			$auto = explode("\t",trim($auto));
			$forceignorepages = explode("\t",trim($forceignorepages));
		}

		$this->forceignorepages = $forceignorepages;
		
		$utf8 = $this->is_utf8? 'u' : '';

		// ページ数が多い場合は、セパレータ \t で複数パターンに分割されている
		foreach($auto as $pat)
		{
			$pattern = '/(<(?:a).*?<\/(?:a)>|<[^>]*>|&(?:#[0-9]+|#x[0-9a-f]+|[0-9a-zA-Z]+);)|($pat)/is'.$utf8;
			$str = preg_replace_callback($pattern,array(&$this,'_tag_auto_link_replace'),$str);
		}

		return ;
	}

	function _tag_auto_link_replace($match)
	{
		static $tags = null;

		if (is_null($tags))
		{
			$tags = $this->getAllTags(3);
			$tags = array_map("strtolower",$tags);
			$tags = array_flip($tags);
		}

		if (!empty($match[1])) return $match[1];
		$name = strtolower($match[2]);

		// 無視リストに含まれているページを捨てる
		if (in_array($name,$this->forceignorepages)) {return $match[0];}
		if (_MD_TAGMEMO_SHORTURL)
		{
			return "<a href=\"".XOOPS_URL."/modules/tagmemo/".str_replace('%', '%25', rawurlencode($match[2])).".htm\" onClick=\"return(tagmemoList.getTagslist(".$tags[$name].",event))\" title=\"Tags\" class=\"tagmemo_taglink\">".$match[2]."</a>";
		}
		else
		{
			return "<a href=\"".XOOPS_URL."/modules/tagmemo/?tag_id=".$tags[$name]."\" onClick=\"return(tagmemoList.getTagslist(".$tags[$name].",event))\" title=\"Tags\" class=\"tagmemo_taglink\">".$match[2]."</a>";
		}
	}

	// AutoLinkのパターンを生成する
	function _get_autolink_pattern(& $pages)
	{
		//foreach ($pages as $page)
		//{
		//	$auto_pages[] = $page;
		//}
		$auto_pages = array_values($pages);

		if (count($auto_pages) == 0)
		{
			$result = '(?!)';
		}
		else
		{
			$auto_pages = array_unique($auto_pages);
			sort($auto_pages, SORT_STRING);

			$result = $this->_get_autolink_pattern_sub($auto_pages, 0, count($auto_pages), 0);
		}

		return array($result, '(?!)', array());
	}

	function _get_autolink_pattern_sub(& $pages, $start, $end, $pos)
	{
		static $lev = 0;

		if ($end == 0) return '(?!)';

		$lev ++;

		$result = '';
		$count = $i = $j = 0;

		$x = (mb_strlen($pages[$start]) <= $pos);

		if ($x) { ++$start; }

		for ($i = $start; $i < $end; $i = $j) // What is the initial state of $j?
		{
			$char = mb_substr($pages[$i], $pos, 1);
			for ($j = $i; $j < $end; $j++)
			{
				if (mb_substr($pages[$j], $pos, 1) != $char) { break; }
			}
			if ($i != $start)
			{
				if ($lev === 1)
				{
					$result .= "\t";
				}
				else
				{
					$result .= '|';
				}

			}
			if ($i >= ($j - 1))
			{
				$result .= str_replace(' ', '\\ ', preg_quote(mb_substr($pages[$i], $pos), '/'));
			}
			else
			{
				$result .= str_replace(' ', '\\ ', preg_quote($char, '/')) .
					$this->_get_autolink_pattern_sub($pages, $i, $j, $pos + 1);
			}

			++$count;
		}
		if ($lev === 1)
		{
			$limit = 1024 * 30; //マージンを持たせて 30kb で分割
			$_result = "";
			$size = 0;
			foreach(explode("\t",$result) as $key)
			{
				if (strlen($_result.$key) - $size > $limit)
				{
					$_result .= ")\t(?:".$key;
					$size = strlen($_result);
				}
				else
				{
					$_result .= ($_result ? "|" : "").$key;
				}
			}
			$result = '(?:' . $_result . ')';
		}
		else
		{
			if ($x or $count > 1) { $result = '(?:' . $result . ')'; }
			if ($x) { $result .= '?'; }
		}
		$lev --;
		return $result;
	}
} //end of class define of TagmemoHandler
?>