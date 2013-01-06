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
include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
/**
 * Class to facilitate navigation in a multi page document/list
 *
 * @package     discuss
 */
class MyPageNav extends XoopsPageNav
{
    /**
     * @access  private
     */
    var $_start;
    var $_extra;
    var $_perpage_arr = array(15,30,50,100);

    /**
     * Constructor
     *
     * @param   int     $total_items    Total number of items
     * @param   int     $items_perpage  Number of items per page
     * @param   int     $current_start  First item on the current page
     * @param   string  $start_name     Name for "start" or "offset"
     * @param   string  $extra_arg      Additional arguments to pass in the URL
     **/
    function MyPageNav($total_items, $items_perpage, $current_start, $start_name="start", $extra_arg="")
    {
        $this->XoopsPageNav($total_items, $items_perpage, $current_start, $start_name, 'perpage='.intval($items_perpage).'&amp;'.$extra_arg);
        $this->_start = trim($start_name);
        $this->_extra = $extra_arg;
    }

    /**
     * Set perpage array
     *
     * @param   array $perpages
     * @return  void
     **/
    function setPerpageArray($perpages)
    {
	$this->_perpage_arr = $perpages;
    }

    /**
     * Create navigation
     *
     * @param   integer $offset
     * @return  string
     **/
    function renderAuto($offset = 4)
    {
        if ( !$this->perpage ) {
            return '';
        }
	$ret = $this->renderNav($offset);
        $total_pages = ceil($this->total / $this->perpage);
	if ($ret != '' && $total_pages <= $offset ) {
	    $extra_arg = $this->_extra;
	    if ( $extra_arg != '' && ( substr($extra_arg, -5) != '&amp;' || substr($extra_arg, -1) != '&' ) ) {
		$extra_arg .= '&amp;';
	    }
	    $ret .= '&nbsp;<a href="'.xoops_getenv('PHP_SELF').'?perpage='.$this->total.'&amp;'.$extra_arg.$this->_start.'=1">All</a>';
	}
        if ( $this->total > 0 ) {
	    $ret .= $this->renderSelectStart($total_pages);
        }
	return $ret;
    }

    /**
     * Create a navigational dropdown list
     *
     * @return  string
     **/
    function renderSelectStart($total_pages)
    {
	$extra_arg = $this->_extra;
	if ( $extra_arg != '' ) {
	    $extra_arg = preg_replace('/&amp;/', '&', $extra_arg);
	    if ( substr($extra_arg, -1) != '&' ) {
		$extra_arg .= '&';
	    }
	}
        $ret = '<script type="text/javascript">
            function navigate() {
                document.location=\''.xoops_getenv('PHP_SELF').'?perpage=\' + document.forms.pagenavform.perpage[document.forms.pagenavform.perpage.selectedIndex].value + \'&'.$extra_arg.$this->_start.'=\' + document.forms.pagenavform.'.$this->_start.'.options[document.forms.pagenavform.'.$this->_start.'.options.selectedIndex].value;
            }';
        $ret .= '</script>';

        $ret .= '<form name="pagenavform" action="#">';
        $ret .= '<select name="perpage">';
	$perpages = $this->_perpage_arr;
	if (!in_array($this->perpage, $perpages)) {
	    array_unshift($perpages, $this->perpage);
	}
        foreach ($perpages as $perpage) {
            $selected = ($perpage == $this->perpage) ? '" selected="selected">' : '">';
            $ret .= '<option value="'.$perpage.$selected.$perpage.' results</option>';
        }
        $ret .= '</select>';
        $ret .= '<select name="'.$this->_start.'" onchange="navigate();">';
        $counter = 1;
        $current_page = intval(floor(($this->current + $this->perpage) / $this->perpage));
        while ( $counter <= $total_pages ) {
            if ( $counter == $current_page ) {
                $ret .= '<option value="'.(($counter - 1) * $this->perpage).'" selected="selected">from '.(($counter - 1) * $this->perpage + 1).'</option>';
            } else {
                $ret .= '<option value="'.(($counter - 1) * $this->perpage).'">from '.(($counter - 1) * $this->perpage + 1).'</option>';
            }
            $counter++;
        }
        $ret .= '</select>';
        $ret .= '&nbsp;<input type="button" value="'._GO.'" onClick="navigate();" />';
        $ret .= '</form>';
        return $ret;
    }
}
?>