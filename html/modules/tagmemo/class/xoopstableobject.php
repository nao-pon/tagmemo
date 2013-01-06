<?php
include_once XOOPS_ROOT_PATH."/class/xoopsobject.php";

if (!defined('XOBJ_DTYPE_FLOAT')) define('XOBJ_DTYPE_FLOAT', 101);
if (!defined('XOBJ_DTYPE_CUSTOM')) define('XOBJ_DTYPE_CUSTOM', 102);
if (!defined('XOBJ_VCLASS_TFIELD')) define('XOBJ_VCLASS_TFIELD', 1);
if (!defined('XOBJ_VCLASS_ATTRIB')) define('XOBJ_VCLASS_ATTRIB', 2);
if (!defined('XOBJ_VCLASS_EXTRA')) define('XOBJ_VCLASS_EXTRA', 3);
/**
* Generic Table Manupulation XoopsObject class
 * 
 * @copyright copyright (c) 2004-2006 Kowa.ORG
 * @author Nobuki Kowa <Nobuki@Kowa.ORG> 
 * @package XoopsTableObject
 */
if( ! class_exists( 'XoopsTableObject' ) ) {
	class XoopsTableObject  extends XoopsObject
	{
		var $_extra_vars = array();
		var $_keys;
		var $_nameField;
		var $_autoIncrement;
		var $_listTableElements;
		var $_handler;
		var $_form;
		var $_list;
		
	    function initVar($key, $data_type, $value = null, $required = false, $maxlength = null, $options = '')
	    {
	    	parent::initVar($key, $data_type, $value, $required, $maxlength, $options);
	    	$this->vars[$key]['var_class'] = XOBJ_VCLASS_TFIELD;
	    }

		function setAttribute($key, $value)
		{
	        $this->vars[$key] = array('value' => $value, 'required' => false, 'data_type' => XOBJ_DTYPE_OTHER, 'maxlength' => null, 'changed' => false, 'options' => '');
			$this->vars[$key]['var_class'] = XOBJ_VCLASS_ATTRIB;
		}

		function XoopsTableObject()
		{
			//親クラスのコンストラクタ呼出
			$this->XoopsObject();
			$this->_handler = null;
		}

		function setKeyFields($keys)
		{
			$this->_keys = $keys;
		}
		
		function getKeyFields()
		{
			return $this->_keys;
		}
		
		function isKey($field)
		{
			return in_array($field,$this->_keys);
		}

		function cacheKey()
		{
			$recordKeys = $this->getKeyFields();
			$recordVars = $this->getVars();
			$cacheKey = array();
			foreach ($this->getKeyFields() as $k => $v) {
				$cacheKey[$v] = $this->getVar($v);
			}
			return(serialize($cacheKey));
		}

		function getKey($format = 's') {
			if (!array($this->_keys)) {
				return false;
			} else {
				return $this->getVar($this->_keys[0], $format);;
			}
		}

		//AUTO_INCREMENT属性のフィールドはテーブルに一つしかない前提
		function setAutoIncrementField($fieldName)
		{
			$this->_autoIncrement = $fieldName;
		}
		
		function &getAutoIncrementField()
		{
			return $this->_autoIncrement;
		}

		function isAutoIncrement($fieldName)
		{
			return ($fieldName == $this->_autoIncrement);
		}
		
		function resetChenged()
		{
			foreach($this->vars as $k=>$v) {
				$this->vars[$k]['changed'] = false;
			}
		}
		
	    function assignVar($key, $value)
	    {
	        if (isset($value) && isset($this->vars[$key])) {
	            $this->vars[$key]['value'] =& $value;
	        } else {
	            $this->setExtraVar($key, $value);
	        }
	    }

		function &getExtraVar($key)
		{
			return $this->_extra_vars[$key];
		}
		
		function setExtraVar($key, $value)
		{
			$this->_extra_vars[$key] =& $value;
		}

		function setNameField($fieldname) {
			$this->nameField = $fieldname;
		}

		function getName($format = 's') {
			if ($this->nameField) {
				return $this->getVar($this->nameField, $format);
			} else {
				return false;
			}
		}
		
	    /**
	    * assign a value to a variable
	    * 
	    * @access public
	    * @param string $key name of the variable to assign
	    * @param mixed $value value to assign
	    * @param bool $not_gpc
	    */
	    function setVar($key, $value, $not_gpc = false)
	    {
	        if (!empty($key) && isset($this->vars[$key])) {
	            $this->vars[$key]['value'] =& $value;
	            $this->vars[$key]['not_gpc'] = $not_gpc;
	            $this->vars[$key]['changed'] = true;
	            $this->setDirty();
	        }
	    }

	    /**
	    * returns a specific variable for the object in a proper format
	    * 
	    * @access public
	    * @param string $key key of the object's variable to be returned
	    * @param string $format format to use for the output
	    * @return mixed formatted value of the variable
	    */
	    function &getVar($key, $format = 's')
	    {
			if (($this->vars[$key]['data_type'] == XOBJ_DTYPE_CUSTOM)) {
				//個別の変数Getがあれば実行;
				$getMethod = 'getVar_'.$key;
				if(method_exists($this, $getMethod)) {
					$this->$getMethod($this->vars[$key]['value'],$format);
				} else {
					$this->vars[$key]['data_type'] == XOBJ_DTYPE_TXTBOX;
					$ret =& parent::getVar($key, $format);
					$this->vars[$key]['data_type'] == XOBJ_DTYPE_CUSTOM;
				}
			} else {
				$ret =& parent::getVar($key, $format);
			}
			if ($this->vars[$key]['data_type'] == XOBJ_DTYPE_TXTAREA && ($format=='e' || $format=='edit')) {
				$ret = preg_replace("/&amp;(#[0-9]+;)/i", '&$1', $ret);
			}
			return $ret;
		}

		function cleanVars() {
			$iret =parent::cleanVars();
			foreach ($this->vars as $k => $v) {
				$cleanv = $v['value'];
				if (!$v['changed']) {
				} else {
					$cleanv = is_string($cleanv) ? trim($cleanv) : $cleanv;
					switch ($v['data_type']) {
					case XOBJ_DTYPE_FLOAT:
						$cleanv = (float)($cleanv);
						$this->cleanVars[$k] =& $cleanv;
						break;
					default:
						break;
					}
					//個別の変数チェックがあれば実行;
					$checkMethod = 'checkVar_'.$k;
					if(method_exists($this, $checkMethod)) {
						$this->$checkMethod($cleanv);
					}
				}
				unset($cleanv);
			}
			if (count($this->_errors) > 0) {
				return false;
			}
			$this->unsetDirty();
			return true;
		}
			
		function &getVarArray($type='s') {
			$varArray=array();
	        foreach ($this->vars as $k => $v) {
				$varArray[$k]=$this->getVar($k,$type);
			}
			return $varArray;
		}
		//Following two functions are only for WordPress Module.
		function &exportWpObject() {
			$wp_object = (object) null;
	        foreach ($this->vars as $k => $v) {
	        	$wp_object->$k = $v['value'];
			}
	        foreach ($this->_extra_vars as $k => $v) {
	        	$wp_object->$k = $v;
			}
			return $wp_object;
		}
		function importWpObject(&$wp_object) {
	        foreach ($this->vars as $k => $v) {
	        	$this->setVar($k, $wp_object->$k, true);
			}
		}

	    /**#@+
	     * @deprecated
	     */
		function assignEditFormElement($name,$class,$params)
		{
			if (!$this->_form) $this->_form =& new XoopsTableObjectForm;
			$className = "XoopsForm". $class;
			$callstr = '$this->_form->addElement("'.$name.'", new XoopsForm'.$class.'(';
			$delim = '';
			for ($i=0;$i<count($params);$i++) {
				if (gettype($params[$i]) == "string") {
					$callstr .= $delim. '"'. $params[$i].'"';
				} else {
					$callstr .= $delim. $params[$i];
				}
				$delim = ', ';
			}
			$callstr .= '));';
			eval($callstr);
		}
		
	    /**#@+
	     * @deprecated
	     */
		function assignEditFormOptionArray($name,$options) {
			$this->_form->addOptionArray($name,$options);
		}
		
	    /**#@+
	     * @deprecated
	     */
		function &buildEditForm($caption,$name,$action,$token=0) {
			$this->_form->_caption = $caption;
			$this->_form->_name = $name;
			$this->_form->_action = $action;
			$this->_form->_token = $token;
			$this->_form->buildEditForm($this);
		}

	    /**#@+
	     * @deprecated
	     */
		function renderEditForm($caption, $name, $action, $token=0) {
			$this->_form->_caption = $caption;
			$this->_form->_name = $name;
			$this->_form->_action = $action;
			$this->_form->_token = $token;
			return $this->_form->render($this);
		}

	    /**#@+
	     * @deprecated
	     */
		function assignEditForm2Tpl(&$tpl, $caption, $name, $action, $token=0) {
			$this->_form->_caption = $caption;
			$this->_form->_name = $name;
			$this->_form->_action = $action;
			$this->_form->_token = $token;
			$this->_form->assign($this, $tpl);
		}

	    /**#@+
	     * @deprecated
	     */
		function assignListTableElement($name, $type, $caption, $width) {
			if (!$this->_list) $this->_list =& new XoopsTableObjectList;
			$this->_list->addElement($name, $type, $caption, $width);
		}
		
	    /**#@+
	     * @deprecated
	     */
		function &getListItems() {
			return $this->_list->getListItems($this);
		}

	    /**#@+
	     * @deprecated
	     */
		function &getListHeaders() {
			return $this->_list->getListHeaders();
		}
	}
}
if( ! class_exists( 'XoopsTableObjectHandler' ) ) {
	class XoopsTableObjectHandler  extends XoopsObjectHandler
	{
		var $tableName = null;
		var $tableAlias = null;
		var $useFullCache;
		var $cacheLimit;
		var $_entityClassName;
		var $_errors;
		var $_fullCached;
		var $_sql;
		
		function XoopsTableObjectHandler($db)
		{
			$this->_entityClassName = preg_replace("/handler$/i","", get_class($this));
			parent::XoopsObjectHandler($db);
			if ($this->tableName) {
				$this->tableName = $this->db->prefix($this->tableName);
			}
			$this->_errors = array();
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
		function setError($error_str)
		{
			$this->_errors[] = $error_str;
		}
		
		function setAlias($alias) {
			$this->tableAlias = $alias;
		}
		
		function getAlias() {
			return($this->tableAlias);
		}
		
		/**
		 * レコードオブジェクトの生成
		 * 
		 * @param	boolean $isNew 新規レコード設定フラグ
		 * 
		 * @return	object  {@link XoopsTableObject}
		 */
		function &create($isNew = true)
		{
			$record = new $this->_entityClassName;
			if ($isNew) {
				$record->setNew();
			}
			$record->_handler =& $this;
			return $record;
		}

		/**
		 * レコードの取得(プライマリーキーによる一意検索）
		 * 
		 * @param	mixed $key 検索キー
		 * 
		 * @return	object  {@link XoopsTableObject}, FALSE on fail
		 */
		function &get($keys)
		{
			$record =& $this->create(false);
			$recordKeys = $record->getKeyFields();
			$recordVars = $record->getVars();

			if ($whereStr = $this->_key2where($keys)) {
				$sql = sprintf("SELECT * FROM %s WHERE %s",$this->tableName, $whereStr);

				if ( !$result =& $this->query($sql) ) {
					return false;
				}

				$numrows = $this->db->getRowsNum($result);
				if ( $numrows == 1 ) {
					$row = $this->db->fetchArray($result);
					$record->assignVars($row);
					$this->db->freeRecordSet($result);
					return $record;
				}
			}
			unset($record);
			return false;
		}

		function _key2where($keys) {
			$record =& $this->create(false);
			$recordKeys = $record->getKeyFields();
			$recordVars = $record->getVars();
			if (gettype($keys) != 'array') {
				if (count($recordKeys) == 1) {
					$keys = array($recordKeys[0] => $keys);
				} else {
					return false;
				}
			}
			$whereStr = "";
			$whereAnd = "";
			foreach ($record->getKeyFields() as $k => $v) {
				if (array_key_exists($v, $keys)) {
					$whereStr .= $whereAnd . "`$v` = ";
					if (($recordVars[$v]['data_type'] == XOBJ_DTYPE_INT) || ($recordVars[$v]['data_type'] == XOBJ_DTYPE_FLOAT)) {
						$whereStr .= $keys[$v];
					} else {
						$whereStr .= $this->db->quoteString($keys[$v]);
					}
					$whereAnd = " AND ";
				} else {
					unset($record);
					return false;
				}
			}
			unset($record);
			return $whereStr;
		}
		
	    /**
	     * レコードの保存
	     * 
	     * @param	object	&$record	{@link XoopsTableObject} object
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
	     */
		function insert(&$record,$force=false,$updateOnlyChanged=false)
		{
			if ( get_class($record) != $this->_entityClassName ) {
				return false;
			}
			if ( !$record->isDirty() ) {
				return true;
			}
			if (!$record->cleanVars()) {
				$this->_errors += $record->getErrors();
				return false;
			}
			$vars = $record->getVars();
			if ($record->isNew()) {
				$fieldList = "(";
				$valueList = "(";
				$delim = "";
				foreach ($record->cleanVars as $k => $v) {
					if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
						continue;
					}
					$fieldList .= $delim ."`$k`";
					if ($record->isAutoIncrement($k)) {
						$v = $this->getAutoIncrementValue();
					}
					if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
						$value = preg_replace('/^__MySqlFunc__/', '', $v);
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
						if (!is_null($v)) {
							$v = intval($v);
							$v = ($v) ? $v : 0;
							$valueList .= $delim . $v;
						} else {
							$valueList .= $delim . 'null';
						}
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
						if (!is_null($v)) {
							$v = (float)($v);
							$v = ($v) ? $v : 0;
							$valueList .= $delim . $v;
						} else {
							$valueList .= $delim . 'null';
						}
					} else {
						if (!is_null($v)) {
							$valueList .= $delim . $this->db->quoteString($v);
						} else {
							$valueList .= $delim . $this->db->quoteString('');;
						}
					}
					$delim = ", ";
				}
				$fieldList .= ")";
				$valueList .= ")";
				$sql = sprintf("INSERT INTO %s %s VALUES %s", $this->tableName,$fieldList,$valueList);
			} else {
				$setList = "";
				$setDelim = "";
				$whereList = "";
				$whereDelim = "";
				foreach ($record->cleanVars as $k => $v) {
					if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
						continue;
					}
					if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
						$value = preg_replace('/^__MySqlFunc__/', '', $v);
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
						$v = intval($v);
						$value = ($v) ? $v : 0;
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
						$v = (float)($v);
						$value = ($v) ? $v : 0;
					} else {
						$value = $this->db->quoteString($v);
					}

					if ($record->isKey($k)) {
						$whereList .= $whereDelim . "`$k` = ". $value;
						$whereDelim = " AND ";
					} else {
						if ($updateOnlyChanged && !$vars[$k]['changed']) {
							continue;
						}
						$setList .= $setDelim . "`$k` = ". $value . " ";
						$setDelim = ", ";
					}
				}
				if (!$setList) {
					$record->resetChenged();
					return true;
				}
				$sql = sprintf("UPDATE %s SET %s WHERE %s", $this->tableName, $setList, $whereList);
			}
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
			if ($record->isNew()) {
				$idField=$record->getAutoIncrementField();
				$idValue=$this->db->getInsertId();
				$record->assignVar($idField,$idValue);
			}
			$record->resetChenged();
			return true;
		}

	    function updateByField(&$record, $fieldName, $fieldValue, $not_gpc=false)
	    {
	        $record->setVar($fieldName, $fieldValue, $not_gpc);
	        return $this->insert($record, true, true);
	    }

		/**
		 * レコードの削除
		 * 
	     * @param	object  &$record  {@link XoopsTableObject} object
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
		 */
		function delete(&$record,$force=false)
		{
			if ( get_class($record) != $this->_entityClassName ) {
				return false;
			}
			if (!$record->cleanVars()) {
				$this->_errors[] = $this->db->error();
				return false;
			}
			$vars = $record->getVars();
			$whereList = "";
			$whereDelim = "";
			foreach ($record->cleanVars as $k => $v) {
				if ($record->isKey($k)) {
					if (($vars[$k]['data_type'] == XOBJ_DTYPE_INT)||($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT)) {
						$value = $v;
					} else {
						$value = $this->db->quoteString($v);
					}
					$whereList .= $whereDelim . "`$k` = ". $value;
					$whereDelim = " AND ";
				}
			}
			$sql = sprintf("DELETE FROM %s WHERE %s", $this->tableName, $whereList);
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
			return true;
		}

		/**
		 * テーブルの条件検索による複数レコード取得
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
		 * @param	bool $id_as_key		プライマリーキーを、戻り配列のキーにする場合はtrue
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
		function &getObjects($criteria = null, $id_as_key = false, $fieldlist="", $distinct = false, $joindef = false, $having="")
		{
			$records = array();

			if ($result =& $this->open($criteria, $fieldlist, $distinct, $joindef, $having)) {
				while ($myrow = $this->db->fetchArray($result)) {
					$record =& $this->create(false);
					$record->assignVars($myrow);
					if (!$id_as_key) {
						$records[] =& $record;
					} else {
						$ids = $record->getKeyFields();
						$r =& $records;
						$count_ids = count($ids);
						for ($i=0; $i<$count_ids; $i++) {
							if ($i == $count_ids-1) {
								$r[$myrow[$ids[$i]]] =& $record;
							} else {
								if (!isset($r[$myrow[$ids[$i]]])) {
									$r[$myrow[$ids[$i]]] = array();
								}
								$r =& $r[$myrow[$ids[$i]]];
							}
						}
					}
					unset($record);
				}
				$this->db->freeRecordSet($result);
			}
			return $records;
		}

		/**
		 * テーブルの条件検索による複数レコード取得用のOpen （一度には取得しない）
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
		function &open($criteria = null, $fieldlist="", $distinct = false, $joindef = false, $having='')
		{
			$limit = $start = 0;
			$whereStr = '';
			$orderStr = '';
			if ($distinct) {
				$distinct = "DISTINCT ";
			} else {
				$distinct = "";
			}
			if ($fieldlist) {
				if ($this->getAlias() != '') {
					$sql = 'SELECT '.$distinct.$fieldlist.' FROM '.$this->tableName.' AS '.$this->getAlias();
				} else {
					$sql = 'SELECT '.$distinct.$fieldlist.' FROM '.$this->tableName;
				}
			} else {
				if ($this->getAlias() != '') {
					$sql = 'SELECT '.$distinct.'* FROM '.$this->tableName.' AS '.$this->getAlias();
				} else {
					$sql = 'SELECT '.$distinct.'* FROM '.$this->tableName;
			}
			}
			if ($joindef) {
				if ($this->getAlias() != '') {
					$sql .= $joindef->render($this->getAlias());
				} else {
					$sql .= $joindef->render($this->tableName);
				}
			}
			if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
				$whereStr = $criteria->renderWhere();
				$sql .= ' '.$whereStr;
			}
			if (isset($criteria) && (is_subclass_of($criteria, 'criteriaelement')||get_class($criteria)=='criteriaelement')) {
				if ($criteria->getGroupby() != ' GROUP BY ') {
					$sql .= ' '.$criteria->getGroupby();
					if(strlen($having) > 0){
						$sql .= ' HAVING '.$having;
					}
				}
				if ((is_array($criteria->getSort()) && count($criteria->getSort()) > 0)) {
					$orderStr = 'ORDER BY ';
					$orderDelim = "";
					foreach ($criteria->getSort() as $sortVar) {
						$orderStr .= $orderDelim . $sortVar.' '.$criteria->getOrder();
						$orderDelim = ",";
					}
					$sql .= ' '.$orderStr;
				} elseif ($criteria->getSort() != '') {
					$orderStr = 'ORDER BY '.$criteria->getSort().' '.$criteria->getOrder();
					$sql .= ' '.$orderStr;
				}
				$limit = $criteria->getLimit();
				$start = $criteria->getStart();
			}
			$resultSet =& $this->query($sql, false ,$limit, $start);
			return $resultSet;
		}

		function &getNext(&$resultSet)
		{
			if ($myrow = $this->db->fetchArray($resultSet)) {
				$record =& $this->create(false);
				$record->assignVars($myrow);
				return $record;
			} else {
				$result = false;
				return $result;
			}
		}

		/**
		 * テーブルの条件検索による対象レコード件数
		 * 
		 * @param	object	$criteria 		{@link XoopsTableObject} 検索条件
		 * 
		 * @return	integer					検索結果レコードの件数
		 */
	    function getCount($criteria = null)
	    {
	        $sql = 'SELECT COUNT(*) FROM '.$this->tableName;
	        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
	            $sql .= ' '.$criteria->renderWhere();
	        }
	        $result =& $this->query($sql);
	        if (!$result) {
	            return 0;
	        }
	        list($count) = $this->db->fetchRow($result);
	        return $count;
	    }
	    
		/**
		 * テーブルの条件検索による複数レコード一括更新(対象フィールドは一つのみ)
		 * 
		 * @param	string	$fieldname 	更新フィールド名
		 * @param	mixed	$fieldvalue	更新値
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
	    function updateAll($fieldname, $fieldvalue, $criteria = null, $force=false)
	    {
	    	$record = $this->create();
	    	if ($record->vars[$fieldname]['data_type'] == XOBJ_DTYPE_INT) {
				$fieldvalue = intval($fieldvalue);
				$fieldvalue = ($fieldvalue) ? $fieldvalue : 0;
			} elseif ($record->vars[$fieldname]['data_type'] == XOBJ_DTYPE_FLOAT) {
				$fieldvalue = (float)($fieldvalue);
				$fieldvalue = ($fieldvalue) ? $fieldvalue : 0;
			} else {
				$fieldvalue = $this->db->quoteString($fieldvalue);
			}
	        $set_clause = $fieldname.' = '.$fieldvalue;
	        $sql = 'UPDATE '.$this->tableName.' SET '.$set_clause;
	        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
	            $sql .= ' '.$criteria->renderWhere();
	        }
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
	        return true;
	    }

		/**
		 * テーブルの条件検索による複数レコード削除
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
		 */
	    function deleteAll($criteria = null, $force=false)
	    {
	        $sql = 'DELETE FROM '.$this->tableName;
	        if (isset($criteria) && is_subclass_of($criteria, 'criteriaelement')) {
	            $sql .= ' '.$criteria->renderWhere();
	        }
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
	        return true;
	    }

		function getName($keys, $format='s'){
			if ($GLOBALS['_xoopsTableCache']->exists($this->tableName.'__getName_'.$format, serialize($keys))) {
				return $GLOBALS['_xoopsTableCache']->get($this->tableName.'__getName_'.$format, serialize($keys));
			}
			if ($record =& $this->get($keys)) {
				$value = $record->getName($format);
				$GLOBALS['_xoopsTableCache']->set($this->tableName.'__getName_'.$format, serialize($keys), $value);
				return $value;
			} else {
				return false;
			}
		}

		function &getSelectOptionArray() {
			$objects =& $this->getObjects();
			$optionArray = array();
			foreach($objects as $object) {
				$optionArray[$object->getKey()] = $object->getName();
			}
			return $optionArray;
		}

		function getAutoIncrementValue()
		{
			return $this->db->genId(get_class($this).'_id_seq');
		}

		function &query($sql, $force=false, $limit=0, $start=0) {
			if (empty($GLOBALS['_xoopsTableQueryCount'])) {
				$GLOBALS['_xoopsTableQueryCount'] = 1;
			} else {
				$GLOBALS['_xoopsTableQueryCount']++;
			}
			if (!empty($GLOBALS['wpdb'])) {
				$GLOBALS['wpdb']->querycount++;
			}
			if ($force) {
				$result =& $this->db->queryF($sql, $limit, $start);
			} else {
				$result =& $this->db->query($sql, $limit, $start);
			}
			$this->_sql = $sql;
			$this->_start = $start;
			$this->_limit = $limit;

			if (!$result) {
				$this->_errors[] = $this->db->error();
				return false;
			}
			return $result;
		}
		
		function getLastSQL()
		{
			return $this->_sql;
		}
	}
	
	class XoopsCachedTableObjectHandler  extends XoopsTableObjectHandler
	{
		var $tableName;
		var $useFullCache;
		var $cacheLimit;
		var $_entityClassName;
		var $_errors;
		var $_fullCached;
		var $_sql;
		
		function XoopsTableObjectHandler($db)
		{
			$this->_entityClassName = preg_replace("/handler$/i","", get_class($this));
			$this->XoopsObjectHandler($db);
			$this->_errors = array();
			$this->useFullCache = true;
			$this->cacheLimit = 0;
			$this->_fullCached = false;
		}

		/**
		 * レコードの取得(プライマリーキーによる一意検索）
		 * 
		 * @param	mixed $key 検索キー
		 * 
		 * @return	object  {@link XoopsTableObject}, FALSE on fail
		 */
		function &get($keys)
		{
			$record =& $this->create(false);
			$recordKeys = $record->getKeyFields();
			$recordVars = $record->getVars();
			if (gettype($keys) != 'array') {
				if (count($recordKeys) == 1) {
					$keys = array($recordKeys[0] => $keys);
				} else {
					return false;
				}
			}
			$whereStr = "";
			$whereAnd = "";
			$cacheKey = array();
			foreach ($record->getKeyFields() as $k => $v) {
				if (array_key_exists($v, $keys)) {
					$whereStr .= $whereAnd . "`$v` = ";
					if (($recordVars[$v]['data_type'] == XOBJ_DTYPE_INT) || ($recordVars[$v]['data_type'] == XOBJ_DTYPE_FLOAT)) {
						$whereStr .= $keys[$v];
					} else {
						$whereStr .= $this->db->quoteString($keys[$v]);
					}
					$whereAnd = " AND ";
					$cacheKey[$v] = $keys[$v];
				} else {
					return false;
				}
			}
			$cacheKey = serialize($cacheKey);
			if ($GLOBALS['_xoopsTableCache']->exists($this->tableName, $cacheKey)) {
				$record->assignVars($GLOBALS['_xoopsTableCache']->get($this->tableName, $cacheKey));
				return $record;
			}
			$sql = sprintf("SELECT * FROM %s WHERE %s",$this->tableName, $whereStr);

			if ( !$result =& $this->query($sql) ) {
				return false;
			}
			$numrows = $this->db->getRowsNum($result);
//		echo $numrows."<br/>";
			if ( $numrows == 1 ) {
				$row = $this->db->fetchArray($result);
				$record->assignVars($row);
				$GLOBALS['_xoopsTableCache']->set($this->tableName, $cacheKey, $row, $this->cacheLimit);
				$this->db->freeRecordSet($result);
				return $record;
			}
			unset($record);
			return false;
		}
	    /**
	     * レコードの保存
	     * 
	     * @param	object	&$record	{@link XoopsTableObject} object
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
	     */
		function insert(&$record,$force=false,$updateOnlyChanged=false)
		{
			if ( get_class($record) != $this->_entityClassName ) {
				return false;
			}
			if ( !$record->isDirty() ) {
				return true;
			}
			if (!$record->cleanVars()) {
				$this->_errors += $record->getErrors();
				return false;
			}
			$vars = $record->getVars();
			$cacheRow = array();
			if ($record->isNew()) {
				$fieldList = "(";
				$valueList = "(";
				$delim = "";
				foreach ($record->cleanVars as $k => $v) {
					if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
						continue;
					}
					$fieldList .= $delim ."`$k`";
					if ($record->isAutoIncrement($k)) {
						$v = $this->getAutoIncrementValue();
					}
					if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
						$value = preg_replace('/^__MySqlFunc__/', '', $v);
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
						if (!is_null($v)) {
							$v = intval($v);
							$v = ($v) ? $v : 0;
							$valueList .= $delim . $v;
						} else {
							$valueList .= $delim . 'null';
						}
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
						if (!is_null($v)) {
							$v = (float)($v);
							$v = ($v) ? $v : 0;
							$valueList .= $delim . $v;
						} else {
							$valueList .= $delim . 'null';
						}
					} else {
						if (!is_null($v)) {
							$valueList .= $delim . $this->db->quoteString($v);
						} else {
							$valueList .= $delim . $this->db->quoteString('');;
						}
					}
					$cacheRow[$k] = $v;
					$delim = ", ";
				}
				$fieldList .= ")";
				$valueList .= ")";
				$sql = sprintf("INSERT INTO %s %s VALUES %s", $this->tableName,$fieldList,$valueList);
			} else {
				$setList = "";
				$setDelim = "";
				$whereList = "";
				$whereDelim = "";
				foreach ($record->cleanVars as $k => $v) {
					if ($vars[$k]['var_class'] != XOBJ_VCLASS_TFIELD) {
						continue;
					}
					if (preg_match("/^__MySqlFunc__/", $v)) {  // for value using MySQL function.
						$value = preg_replace('/^__MySqlFunc__/', '', $v);
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_INT) {
						$v = intval($v);
						$value = ($v) ? $v : 0;
					} elseif ($vars[$k]['data_type'] == XOBJ_DTYPE_FLOAT) {
						$v = (float)($v);
						$value = ($v) ? $v : 0;
					} else {
						$value = $this->db->quoteString($v);
					}

					if ($record->isKey($k)) {
						$whereList .= $whereDelim . "`$k` = ". $value;
						$whereDelim = " AND ";
					} else {
						if ($updateOnlyChanged && !$vars[$k]['changed']) {
							continue;
						}
						$setList .= $setDelim . "`$k` = ". $value . " ";
						$setDelim = ", ";
					}
					$cacheRow[$k] = $v;
				}
				if (!$setList) {
					$record->resetChenged();
					return true;
				}
				$sql = sprintf("UPDATE %s SET %s WHERE %s", $this->tableName, $setList, $whereList);
			}
			if (!$result =& $this->query($sql, $force)) {
				return false;
			}
			if ($record->isNew()) {
				$idField=$record->getAutoIncrementField();
				$idValue=$this->db->getInsertId();
				$record->assignVar($idField,$idValue);
				$cacheRow[$idField] = $idValue;
			}
			if (!$updateOnlyChanged) {
				$GLOBALS['_xoopsTableCache']->set($this->tableName, $record->cacheKey() ,$cacheRow, $this->cacheLimit);
			} else {
				$GLOBALS['_xoopsTableCache']->reset($this->tableName, $record->cacheKey());
				$this->_fullCached = false;
			}
			$record->resetChenged();
			return true;
		}

		/**
		 * レコードの削除
		 * 
	     * @param	object  &$record  {@link XoopsTableObject} object
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
		 */
		function delete(&$record,$force=false)
		{
			$GLOBALS['_xoopsTableCache']->reset($this->tableName, $record->cacheKey());
			return parent::delete($record,$force);
		}

		/**
		 * テーブルの条件検索による複数レコード取得
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
		 * @param	bool $id_as_key		プライマリーキーを、戻り配列のキーにする場合はtrue
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
		function &getObjects($criteria = null, $id_as_key = false, $fieldlist="", $distinct = false, $joindef = false)
		{
			$records = array();
			//今のところは、非常に限定された条件でしかキャッシュを使えない
			if (($this->useFullCache) && ($this->_fullCached) && (empty($criteria))&& (!$fieldlist) && (!$distinct) && (!$joindef)) {
				foreach ($GLOBALS['_xoopsTableCache']->getFull($this->tableName) as $myrow) {
					$record =& $this->create(false);
					$record->assignVars($myrow);
					if (!$id_as_key) {
						$records[] =& $record;
					} else {
						$ids = $record->getKeyFields();
						$r =& $records;
						$count_ids = count($ids);
						for ($i=0; $i<$count_ids; $i++) {
							if ($i == $count_ids-1) {
								$r[$myrow[$ids[$i]]] =& $record;
							} else {
								$r[$myrow[$ids[$i]]] = array();
								$r =& $r[$myrow[$ids[$i]]];
							}
						}
					}
					unset($record);
				}
				return $records;
			}

			if ($result =& $this->open($criteria, $fieldlist, $distinct, $joindef)) {
				if (($this->useFullCache) && (empty($criteria)) && (!$fieldlist) && (!$distinct) && (!$joindef)) {
					$this->_fullCached = true;
				}
				while ($myrow = $this->db->fetchArray($result)) {
					$record =& $this->create(false);
					$record->assignVars($myrow);
					if (!$id_as_key) {
						$records[] =& $record;
					} else {
						$ids = $record->getKeyFields();
						$r =& $records;
						$count_ids = count($ids);
						for ($i=0; $i<$count_ids; $i++) {
							if ($i == $count_ids-1) {
								$r[$myrow[$ids[$i]]] =& $record;
							} else {
								if (!isset($r[$myrow[$ids[$i]]])) {
									$r[$myrow[$ids[$i]]] = array();
								}
								$r =& $r[$myrow[$ids[$i]]];
							}
						}
					}
					if (!$fieldlist) {
						$GLOBALS['_xoopsTableCache']->set($this->tableName, $record->cacheKey(), $myrow, $this->cacheLimit);
					}
					unset($record);
				}
				$this->db->freeRecordSet($result);
			}
			return $records;
		}

		function &getNext(&$resultSet, $setCache=true)
		{
			if ($myrow = $this->db->fetchArray($resultSet)) {
				$record =& $this->create(false);
				$record->assignVars($myrow);
				if ($setCache) {
					$GLOBALS['_xoopsTableCache']->set($this->tableName, $record->cacheKey(), $myrow, $this->cacheLimit);				}
				return $record;
			} else {
				$result = false;
				return $result;
			}
		}

		/**
		 * テーブルの条件検索による複数レコード一括更新(対象フィールドは一つのみ)
		 * 
		 * @param	string	$fieldname 	更新フィールド名
		 * @param	mixed	$fieldvalue	更新値
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
		 * 
		 * @return	mixed Array			検索結果レコードの配列
		 */
	    function updateAll($fieldname, $fieldvalue, $criteria = null, $force=false)
	    {
	        $GLOBALS['_xoopsTableCache']->clear($this->tableName);
			$this->_fullCached = false;
			return parent::updateAll($fieldname, $fieldvalue, $criteria, $force);
	    }

		/**
		 * テーブルの条件検索による複数レコード削除
		 * 
		 * @param	object	$criteria 	{@link XoopsTableObject} 検索条件
	     * @param	bool	$force		POSTメソッド以外で強制更新する場合はture
	     * 
	     * @return	bool    成功の時は TRUE
		 */
	    function deleteAll($criteria = null, $force=false)
	    {
	        $GLOBALS['_xoopsTableCache']->clear($this->tableName);
			$this->_fullCached = false;
			return parent::deleteAll($criteria, $force);
	    }

		function getAutoIncrementValue()
		{
			return $this->db->genId(get_class($this).'_id_seq');
		}
	}

	class XoopsTableCache
	{
		var $cache;
		
		function set($table, $key, $row, $limit=0) {
			$this->cache[$table][$key] = $row;
			$cache_size = count($this->cache[$table]);
			if (($limit != 0) && $cache_size >$limit) {
				array_splice($this->cache[$table],1, $cache_size-$limit);
			}
		}
		function reset($table, $key) {
			unset($this->cache[$table][$key]);
		}
		function exists($table, $key) {
			return (!empty($this->cache[$table][$key]));
		}
		function &get($table,$key) {
			return $this->cache[$table][$key];
		}
		function &getFull($table) {
			return $this->cache[$table];
		}
		function clear($table) {
			$this->cache[$table] = array();
		}
	}

	class XoopsJoinCriteria
	{
		var $_table_name;
		var $_main_field;
		var $_sub_field;
		var $_join_type;
		var $_next_join;
		var $_table_alias; // thanks towdash
		
		function XoopsJoinCriteria($table_name, $main_field, $sub_field, $join_type='LEFT', $table_alias="")
		{
			$this->_table_name = $table_name;
			$this->_main_field = $main_field;
			$this->_sub_field = $sub_field;
			$this->_join_type = $join_type;
			$this->_next_join = false;
			$this->_table_alias = $table_alias;
		}
		
		function cascade(&$joinCriteria) {
			$this->_next_join =& $joinCriteria;
		}
		
		function render($main_table)
		{
			if($this->_table_alias == ""){
				$table_alias = $this->_table_name;
				$alias_def = "";
			} else {
				$table_alias = $this->_table_alias;
				$alias_def = " AS ".$table_alias;
			}
			$join_str = " ".$this->_join_type." JOIN ".$this->_table_name . $alias_def." ON ".$main_table.".".$this->_main_field."=".$table_alias.".".$this->_sub_field." ";
			if ($this->_next_join) {
				$join_str .= $this->_next_join->render($table_alias);
			}
			return $join_str;
		}
		
		function getMainAlias() {
			return $this->_main_alias;
		}

	}
	
	$GLOBALS['_xoopsTableCache'] = new XoopsTableCache;
}

if( ! class_exists( 'XoopsTableObjectList' ) ) {
	class XoopsTableObjectList {
		var $_elements;

		function addElement($name, $caption, $width, $ext='') {
			$this->_elements[$name]['caption'] = $caption;
			$this->_elements[$name]['width'] = $width;
			$this->_elements[$name]['ext'] = $ext;
		}
		
		function &getListItems(&$object) {
			$items = array();
			foreach($this->_elements as $key=>$value) {
				$extItemMethod = 'extraItem_'.$key;
				$formatMethod = 'formatItem_'.$key;
				$item = array();
				$item['name'] = $key;
				$item['link'] ='';
				$item['linktitle'] ='';
				if(method_exists($this, $extItemMethod)) {
					$item['align'] = 'left';
					$item = array_merge($item, $this->$extItemMethod($object,$value));
				} else if(method_exists($this, $formatMethod)) {
					$item['value'] = $this->$formatMethod($object->getVar($key));
					$item['align'] = 'left';
				} else {
					$item['value'] = $object->getVar($key);
					switch ($object->vars[$key]['data_type']) {
						case XOBJ_DTYPE_INT:
						case XOBJ_DTYPE_FLOAT:
							$item['align'] = 'right';
							break;
						default:
							$item['align'] = 'left';
							break;
					}
				}
				$items[] = $item;
			}
			return $items;
		}

		function &getListHeaders($sort='', $order='') {
			$header = array();
			foreach($this->_elements as $key=>$value) {
				$head = array();
				$head['name'] = $key;
				if (isset($value['ext']['sort'])&&($value['ext']['sort']===true)) {
					$head['link'] = xoops_getenv('PHP_SELF').'?sort='.$key;
					if (($sort==$key)&&(strtolower($order)=='asc')) {
						$head['link'] .= '&amp;order=desc';
						$head['linktitle'] = 'Descending Sort';
						$head['style']='style="color:#00FF00"';
					} else if (($sort==$key)&&(strtolower($order)=='desc')) {
						$head['link'] .= '&amp;order=asc';
						$head['linktitle'] = 'Ascending Sort';
						$head['style']='style="color:#FFFF00"';
					} else {
						$head['link'] .= '&amp;order=asc';
						$head['linktitle'] = 'Ascending Sort';
						$head['style']='style="color:#DDFFDD"';
					}
				} else {
					$head['link'] ='';
				}
				$head['caption'] = $value['caption'];
				$head['width'] = $value['width'];
				$header[] = $head;
			}
			return $header;
		}
		
		function inkey($string) {
			return array_key_exists($string, $this->_elements);
		}
		
		function showList(&$objects, &$tpl, $name, $sort='', $order='') {
			$headers = $this->getListHeaders($sort, $order);
			
			foreach ($objects as $object) {
				$rec['items'] =& $this->getListItems($object);
				$records[] = $rec;
			}
		    $tpl->assign('title',$name);
		    $tpl->assign('headers',$headers);
		    $tpl->assign('records',$records);
		}

		// Special List Item '__SimpleEditLink__'
		function extraItem___SimpleEditLink__(&$object,$element) {
			$objectKey = $object->getKeyFields();
			$objectKey = $objectKey[0];
			$key = $object->getVar($objectKey);
			$item['link'] = xoops_getenv('PHP_SELF').'?op=edit&amp;'.$objectKey.'='.$key;
			$item['linktitle'] = 'Edit this record';
			$item['value'] = $element['ext']['caption'];
			$item['align'] = 'center';
			return $item;
		}
		
		// Special List Item '__SimpleDeleteLink__'
		function extraItem___SimpleDeleteLink__(&$object,$element) {
			$objectKey = $object->getKeyFields();
			$objectKey = $objectKey[0];
			$key = $object->getVar($objectKey);
			$item['link'] = xoops_getenv('PHP_SELF').'?op=delete&amp;'.$objectKey.'='.$key;
			$item['linktitle'] = 'Delete this record';
			$item['value'] = $element['ext']['caption'];
			$item['align'] = 'center';
			return $item;
		}

	}
}

if( ! class_exists( 'XoopsTableObjectForm' ) ) {
	class XoopsTableObjectForm {
		var $_elements;
		var $_name;
		var $_caption;
		var $_action;
		var $_token = 0;

		function XoopsTableObjectForm($caption='', $name='', $action='', $token=0) {
			include_once XOOPS_ROOT_PATH.'/class/xoopsformloader.php';
			$this->_caption = $caption;
			$this->_name = $name;
			$this->_action = $action;
			$this->_token = $token;
			$this->_elements = array();
		}
		function setCaption($caption) {
			$this->_caption = $caption;
		}
		function addElement($name, &$formElement) {
			$this->_elements[$name] =& $formElement;
		}
		
		function addOptionArray($name,$options) {
			if (method_exists($this->_elements[$name], 'addOptionArray')) {
				$this->_elements[$name]->addOptionArray($options);
			}
		}
		
		function &buildEditForm(&$object) {
			if (!file_exists(XOOPS_ROOT_PATH.'/class/xoopsform/formtoken.php')) {
				$this->_token=0;
			}
			
			$formEdit =& new XoopsThemeForm($this->_caption, $this->_name, $this->_action);
			foreach ($this->_elements as $key=>$formElement) {
				$formElement->setValue($object->getVar($key,'e'));
				$formEdit->addElement($formElement,$object->vars[$key]['required']);
				unset($formElement);
			}
			if ($object->isNew()) {
				if ($this->_token) {
					$formEdit->addElement(new XoopsFormToken(XoopsMultiTokenHandler::quickCreate($this->_name.'_insert')));
				}
				$formEdit->addElement(new XoopsFormHidden('op','insert'));
			} else {
				if ($this->_token) {
					$formEdit->addElement(new XoopsFormToken(XoopsMultiTokenHandler::quickCreate($this->_name.'_save')));
				}
				$formEdit->addElement(new XoopsFormHidden('op','save'));
			}
			$formEdit->addElement(new XoopsFormButton('', 'submit', 'OK', 'submit'));

			return $formEdit;
		}

		function render(&$object) {
			$formEdit =& $this->buildEditForm($object);
			$str = $formEdit->render();
			return $str;
		}

		function assign(&$object, &$tpl) {
			$formEdit =& $this->buildEditForm($object);
			$formEdit->assign($tpl);
		}
		

		function showForm(&$object, &$tpl, $errmsg='') {
			$form =& $this->buildEditForm($object);
		    $form->assign($tpl);
		    $tpl->assign('title',$this->_caption);
		    $tpl->assign('formhtml',$this->render($object));
		    $tpl->assign('errmsg',$errmsg);
		}
	}
}
?>