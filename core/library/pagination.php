<?php
class Pagination
{
	var $base_url			= '';
	var $prefix				= '';
	var $suffix				= '';
	var $total_rows			=  0;
	var $per_page			= 10;
	var $num_links			=  2;
	var $cur_page			=  0;
	var $use_page_numbers	= FALSE;

	var $display_pages		= TRUE;
	var $anchor_class		= '';
	var $num_pages;

	var $page_range = 3;

	public function __construct($params = array())
	{
		$attributs = $_GET;
		$this->suffix = ( $attributs ) ? '?'.http_build_query( $attributs ) : '';
	}

	public function get_cur_page() {
		return ( $this->cur_page < 1 || $this->cur_page > $this->num_pages ) ? 1 : $this->cur_page;
	}

	public function get_cur_offset() {
		return ( $this->get_cur_page() - 1 ) * $this->per_page;
	}

	/**
	 * Generate the pagination links
	 *
	 * @access	public
	 * @return	string
	 */
	function create_links()
	{
		$this->cur_page = intval($this->cur_page);
		$this->cur_page = empty($this->cur_page) ? 1 : $this->cur_page;
		$this->num_pages = ceil($this->total_rows/$this->per_page);
		$this->cur_page = ($this->cur_page > $this->num_pages) ? $this->num_pages : $this->cur_page;

		if ( isset($_SERVER['QUERY_STRING']) ) {
			parse_str($_SERVER['QUERY_STRING'], $args);
			unset($args['p']);
			unset($args['limit']);
			$this->base_url .= '?'.http_build_query($args).'&';
		} else {
			$this->base_url .= '?';
		}

		$html = "<div class=\"fixed-table-pagination\"><div class=\"pull-left pagination-detail\"><span class=\"pagination-info\">总共{$this->total_rows}条记录，第{$this->cur_page}页/共{$this->num_pages}页</span><span class=\"page-list\"><div class=\"dropdown\"><a href=\"#\" id=\"perpage_option\" class=\"btn btn-default dropdown-toggle\" data-toggle=\"dropdown\">{$this->per_page}条 <span class=\"caret\"></span></a><ul class=\"dropdown-menu\" role=\"menu\" aria-labelledby=\"perpage_option\"><li><a href=\"{$this->base_url}p=1&limit=10\">10条</a></li><li><a href=\"{$this->base_url}p=1&limit=20\">20条</a></li><li><a href=\"{$this->base_url}p=1&limit=30\">30条</a></li></ul></div></span></div><div class=\"pull-right pagination\"><ul class=\"pagination\">";
		if ( $this->num_pages > 6 ) {
			if ( $this->cur_page == 1 ) {
				for($i=1;$i<=$this->page_range;$i++) {
					$active = ($this->cur_page == $i) ? 'active' : '';
					$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
				}
				$html .= "<li class=\"page-last-separator disabled\"><a href=\"javascript:void(0)\">...</a></li><li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$this->num_pages}</a></li>";
			} else if( $this->cur_page == $this->num_pages ) {
				$html .= "<li class=\"page-number\"><a href=\"{$this->base_url}p=1&limit={$this->per_page}\">1</a></li><li class=\"page-last-separator disabled\"><a href=\"javascript:void(0)\">...</a></li>";
				for($i=$this->num_pages-$this->page_range+1;$i<=$this->num_pages;$i++) {
					$active = ($this->cur_page == $i) ? 'active' : '';
					$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
				}
			} else {
				$min = $this->cur_page-$this->page_range+1;
				$max = $this->cur_page+$this->page_range-1;
				if ( $min < 1 ) $min = 1;
				if ( $max > $this->num_pages ) $max = $this->num_pages;
				if ( $min <= 1 ) {
					for($i=1;$i<=$this->cur_page;$i++) {
						$active = ($this->cur_page == $i) ? 'active' : '';
						$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
					}
					for($i=$this->cur_page+1;$i<=$max;$i++) {
						$active = ($this->cur_page == $i) ? 'active' : '';
						$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
					}
					$html .= "<li class=\"page-last-separator disabled\"><a href=\"javascript:void(0)\">...</a></li><li class=\"page-number\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$this->num_pages}</a></li>";
				} else if ( $max >= $this->num_pages ) {
					$html .= "<li class=\"page-number\"><a href=\"{$this->base_url}p=1&limit={$this->per_page}\">1</a></li><li class=\"page-last-separator disabled\"><a href=\"javascript:void(0)\">...</a></li>";
					for($i=$this->cur_page-$this->page_range+1;$i<$this->cur_page;$i++) {
						$active = ($this->cur_page == $i) ? 'active' : '';
						$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
					}
					for($i=$this->num_pages-$this->page_range+1;$i<=$this->num_pages;$i++) {
						$active = ($this->cur_page == $i) ? 'active' : '';
						$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
					}
				} else {
					$html .= "<li class=\"page-number\"><a href=\"{$this->base_url}p=1&limit={$this->per_page}\">1</a></li><li class=\"page-last-separator disabled\"><a href=\"javascript:void(0)\">...</a></li>";
					for($i=$min;$i<=$max;$i++) {
						$active = ($this->cur_page == $i) ? 'active' : '';
						$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
					}
					$html .= "<li class=\"page-last-separator disabled\"><a href=\"javascript:void(0)\">...</a></li><li class=\"page-number\"><a href=\"{$this->base_url}p={$this->num_pages}&limit={$this->per_page}\">{$this->num_pages}</a></li>";
				}
			}
		} else {
			for($i=1;$i<=$this->num_pages;$i++) {
				$active = ($this->cur_page == $i) ? 'active' : '';
				$html .= "<li class=\"page-number {$active}\"><a href=\"{$this->base_url}p={$i}&limit={$this->per_page}\">{$i}</a></li>";
			}
		}
		$html .= "</ul></div></div>";
		return $html;
	}

	function initialize($params = array())
	{
		if (count($params) > 0)
		{
			foreach ($params as $key => $val)
			{
				if (isset($this->$key))
				{
					$this->$key = $val;
				}
			}
		}
	}
}
