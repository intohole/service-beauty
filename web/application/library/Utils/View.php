<?php
class Utils_View {
	/**
	 * 生成面包屑链接方法
	 * @param array $links
	 * @return string
	 */
	static public function breadcrumb($links) {
		$breadcrumb =<<<html
			<li>
				<i class="icon-home"></i>
				<a href="/">首页</a> 
				<i class="icon-angle-right"></i>
			</li>
html;
		$total = count($links);
		$count = 1;
		foreach ($links as $name=>$link) {
			if ($count != $total)
				$nexTag = '<i class="icon-angle-right"></i>';
			else 
				$nexTag = '';
			$breadcrumb .=<<<html
			<li>
				<i class="icon-home"></i>
				<a href="{$link}">{$name}</a> 
				<i class="icon-angle-right"></i>
			</li>
html;
		}
		
		return $breadcrumb;
	}
}