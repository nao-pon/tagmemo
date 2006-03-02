<?php
/*
 * Created on 2006/03/02
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
include_once XOOPS_ROOT_PATH.'/class/pagenav.php';
class TagmemoPageNav extends XoopsPageNav
{
	/**#@+
	 * @access	private
	 */
	var $total;
	var $perpage;
	var $current;
	var $url;
	var $asjs = "";
	/**#@-*/

	/**
	 * Constructor
	 *
	 * @param	int		$total_items	Total number of items
	 * @param	int		$items_perpage	Number of items per page
	 * @param	int		$current_start	First item on the current page
	 * @param	string	$start_name		Name for "start" or "offset"
	 * @param	string	$extra_arg		Additional arguments to pass in the URL
	 **/
	function TagmemoPageNav($total_items, $items_perpage, $current_start, $start_name="start", $extra_arg="")
	{
		$this->total = intval($total_items);
		$this->perpage = intval($items_perpage);
		$this->current = intval($current_start);
		if ( $extra_arg != '' && ( substr($extra_arg, -5) != '&amp;' || substr($extra_arg, -1) != '&' ) ) {
			$extra_arg .= '&amp;';
		}
		$this->url = xoops_getenv('PHP_SELF').'?'.$extra_arg.trim($start_name).'=';
	}

	/**
	 * Create text navigation
	 *
	 * @param	integer $offset
	 * @return	string
	 **/
	function renderNav($offset = 4)
	{
		$ret = '';
		if ( $this->total <= $this->perpage ) {
			return $ret;
		}
		$total_pages = ceil($this->total / $this->perpage);
		if ( $total_pages > 1 ) {
			$prev = $this->current - $this->perpage;
			if ( $prev >= 0 ) {
				if ($this->asjs)
				{
					$js = str_replace("_NAV_",$prev,$this->asjs);
					$ret .= '<a href="#" '.$js.'><u>&laquo;</u></a> ';
				}
				else
				{
					$ret .= '<a href="'.$this->url.$prev.'"><u>&laquo;</u></a> ';
				}
			}
			$counter = 1;
			$current_page = intval(floor(($this->current + $this->perpage) / $this->perpage));
			while ( $counter <= $total_pages ) {
				if ( $counter == $current_page ) {
					$ret .= '<b>('.$counter.')</b> ';
				} elseif ( ($counter > $current_page-$offset && $counter < $current_page + $offset ) || $counter == 1 || $counter == $total_pages ) {
					if ( $counter == $total_pages && $current_page < $total_pages - $offset ) {
						$ret .= '... ';
					}
					if ($this->asjs)
					{
						$js = str_replace("_NAV_",(($counter - 1) * $this->perpage),$this->asjs);
						$ret .= '<a href="#" '.$js.'>'.$counter.'</a> ';
					}
					else
					{
						$ret .= '<a href="'.$this->url.(($counter - 1) * $this->perpage).'">'.$counter.'</a> ';
					}
					if ( $counter == 1 && $current_page > 1 + $offset ) {
						$ret .= '... ';
					}
				}
				$counter++;
			}
			$next = $this->current + $this->perpage;
			if ( $this->total > $next ) {
				if ($this->asjs)
				{
					$js = str_replace("_NAV_",$next,$this->asjs);
					$ret .= '<a href="#" '.$js.'><u>&raquo;</u></a> ';
				}
				else
				{
					$ret .= '<a href="'.$this->url.$next.'"><u>&raquo;</u></a> ';
				}
			}
		}
		return $ret;
	}
}
?>