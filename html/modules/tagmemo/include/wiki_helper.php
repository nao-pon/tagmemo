<?php
/*
 * Created on 2006/03/15
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

class WikiHelper
{
	var $helper = null;
	var $enable;
	
	function WikiHelper()
	{
		$this->enable = (file_exists(XOOPS_ROOT_PATH."/modules/pukiwiki/skin/default.ja.js")
			&& file_exists(XOOPS_ROOT_PATH."/class/modPukiWiki/PukiWiki.php")
		);
	}

	function &getInstance()
	{
		static $instance;
		if (!isset($instance)) {
			$instance = new WikiHelper();
		}
		return $instance;
	}
		
	function get()
	{
		if (!$this->enable) return array();
		if (is_null($this->helper))
		{
			$this->helper = $this->build();
		}
		return $this->helper;
	}
	
	function build()
	{
		// Wiki¥Ø¥ë¥Ñ¡¼
		// for PukiWiki helper.
		$helper = array();
		$url = XOOPS_URL."/modules/pukiwiki";
		$helper['form'] = ' onmouseup=pukiwiki_pos() onkeyup=pukiwiki_pos()';
		$helper['init'] = '<script type="text/javascript">
		<!--
		pukiwiki_initTexts();
		//-->
		</script>';
		$helper['map'] = <<<EOD
<map name="map_button">
<area shape="rect" coords="0,0,22,16" alt="URL" href="#" onClick="javascript:pukiwiki_linkPrompt('url'); return false;">
<area shape="rect" coords="24,0,40,16" alt="B" href="#" onClick="javascript:pukiwiki_tag('b'); return false;">
<area shape="rect" coords="43,0,59,16" alt="I" href="#" onClick="javascript:pukiwiki_tag('i'); return false;">
<area shape="rect" coords="62,0,79,16" alt="U" href="#" onClick="javascript:pukiwiki_tag('u'); return false;">
<area shape="rect" coords="81,0,103,16" alt="SIZE" href="#" onClick="javascript:pukiwiki_tag('size'); return false;">
</map>
<map name="map_color">
<area shape="rect" coords="0,0,8,8" alt="Black" href="#" onClick="javascript:pukiwiki_tag('Black'); return false;">
<area shape="rect" coords="8,0,16,8" alt="Maroon" href="#" onClick="javascript:pukiwiki_tag('Maroon'); return false;">
<area shape="rect" coords="16,0,24,8" alt="Green" href="#" onClick="javascript:pukiwiki_tag('Green'); return false;">
<area shape="rect" coords="24,0,32,8" alt="Olive" href="#" onClick="javascript:pukiwiki_tag('Olive'); return false;">
<area shape="rect" coords="32,0,40,8" alt="Navy" href="#" onClick="javascript:pukiwiki_tag('Navy'); return false;">
<area shape="rect" coords="40,0,48,8" alt="Purple" href="#" onClick="javascript:pukiwiki_tag('Purple'); return false;">
<area shape="rect" coords="48,0,55,8" alt="Teal" href="#" onClick="javascript:pukiwiki_tag('Teal'); return false;">
<area shape="rect" coords="56,0,64,8" alt="Gray" href="#" onClick="javascript:pukiwiki_tag('Gray'); return false;">
<area shape="rect" coords="0,8,8,16" alt="Silver" href="#" onClick="javascript:pukiwiki_tag('Silver'); return false;">
<area shape="rect" coords="8,8,16,16" alt="Red" href="#" onClick="javascript:pukiwiki_tag('Red'); return false;">
<area shape="rect" coords="16,8,24,16" alt="Lime" href="#" onClick="javascript:pukiwiki_tag('Lime'); return false;">
<area shape="rect" coords="24,8,32,16" alt="Yellow" href="#" onClick="javascript:pukiwiki_tag('Yellow'); return false;">
<area shape="rect" coords="32,8,40,16" alt="Blue" href="#" onClick="javascript:pukiwiki_tag('Blue'); return false;">
<area shape="rect" coords="40,8,48,16" alt="Fuchsia" href="#" onClick="javascript:pukiwiki_tag('Fuchsia'); return false;">
<area shape="rect" coords="48,8,56,16" alt="Aqua" href="#" onClick="javascript:pukiwiki_tag('Aqua'); return false;">
<area shape="rect" coords="56,8,64,16" alt="White" href="#" onClick="javascript:pukiwiki_tag('White'); return false;">
</map>
EOD;

		$helper['js'] = <<<EOD
<script type="text/javascript">
<!--
var pukiwiki_root_url = "{$url}/";
-->
</script>
<script type="text/javascript" src="{$url}/skin/default.ja.js"></script>
<script type="text/javascript">
<!--
	if (pukiwiki_WinIE || pukiwiki_Gecko)
	{
		document.write('<div>');
		pukiwiki_show_fontset_img();
		document.write('<'+'/'+'div>');
	}
-->
</script>
EOD;
		return $helper;
	}
}
?>
