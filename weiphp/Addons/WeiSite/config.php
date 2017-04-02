<?php
return array (
		'title' => array (
				'title' => '封面标题:',
				'type' => 'text',
				'value' => '点击进入首页',
				'tip' => '' 
		),
		'cover' => array (
				'title' => '封面图片:',
				'type' => 'picture',
				'value' => '',
				'tip' => '最佳尺寸为900*500' 
		),
		'info' => array (
				'title' => '封面简介:',
				'type' => 'textarea',
				'value' => '',
				'tip' => '' 
		),
		'show_background' => array (
				'title' => '显示模板背景图',
				'type' => 'radio',
				'value' => '1',
				'options' => array (
						'0' => '不显示',
						'1' => '显示' 
				),
				'tip' => '' 
		),
		'background' => array (
				'title' => '模板背景图:',
				'type' => 'mult_picture',
				'value' => '',
				'tip' => '为空时默认使用模板里的背景图片，最佳尺寸：640X1156' 
		),
		
		'code' => array (
				'title' => '统计代码:',
				'type' => 'textarea',
				'value' => '',
				'tip' => '' 
		),
		'template_index' => array (
				'title' => '首页模板',
				'type' => 'hidden',
				'value' => 'ColorV1',
				'tip' => '' 
		),
		'template_footer' => array (
				'title' => '底部模板',
				'type' => 'hidden',
				'value' => 'V1',
				'tip' => '' 
		),
		'template_lists' => array (
				'title' => '图文列表模板',
				'type' => 'hidden',
				'value' => 'V1',
				'tip' => '' 
		),
		'template_detail' => array (
				'title' => '图文内容模板',
				'type' => 'hidden',
				'value' => 'V1',
				'tip' => '' 
		) 
);
					