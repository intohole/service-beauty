<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
namespace Home\Model;

use Think\Model;

/**
 * 分类模型
 */
class MaterialModel extends Model {
	protected $tableName = 'material_news';
	
	/**
	 * 获取导航列表，支持多级导航
	 *
	 * @param boolean $field
	 *        	要列出的字段
	 * @return array 导航树
	 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
	 */
	public function getMediaIdByGroupId($group_id,$from='') {
		$map ['group_id'] = $group_id;
		$list = $this->where ( $map )->order ( 'id asc' )->select ();
		if (! empty ( $list [0] ['media_id'] ))
			return $list [0] ['media_id'];
			
			// 自动同步到微信端
		foreach ( $list as $vo ) {
			$data ['title'] = $vo ['title'];
			$data ['thumb_media_id'] = empty ( $vo ['thumb_media_id'] ) ? $this->_thumb_media_id ( $vo ['cover_id'],$from ) : $vo ['thumb_media_id'];
			$data ['author'] = $vo ['author'];
			$data ['digest'] = $vo ['intro'];
			$data ['show_cover_pic'] = 1;
			$data ['content'] = $this->getNewContent($vo ['content']);
			$data ['content'] = str_replace("\"","'",$data ['content']);
			$data ['content_source_url'] = U ( 'news_detail', array (
					'id' => $vo ['id'] 
			) );
			
			$articles [] = $data;
		}
		if ($from =='sendall'){
			$url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=' . get_access_token ();
		}else{
			$url = 'https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=' . get_access_token ();
		}
	
		$param ['articles'] = $articles;
		
		$res = post_data ( $url, $param );
		if ($res ['errcode'] != 0) {
			return false;
		} else {
			if (empty($from)){
				$this->where ( $map )->setField ( 'media_id', $res ['media_id'] );
			}
			return $res ['media_id'];
		}
	}
	function _thumb_media_id($cover_id,$from='') {
		$cover = get_cover ( $cover_id );
		$driver = C ( 'PICTURE_UPLOAD_DRIVER' );
		if ($driver != 'Local' && ! file_exists ( SITE_PATH . $cover ['path'] )) { // 先把图片下载到本地
			
			$pathinfo = pathinfo ( SITE_PATH . $cover ['path'] );
			mkdirs ( $pathinfo ['dirname'] );
			
			$content = wp_file_get_contents ( $cover ['url'] );
			$res = file_put_contents ( SITE_PATH . $cover ['path'], $content );
			if ($res) {
				return '';
			}
		}
		
		$path = $cover ['path'];
		if (! $path) {
			return '';
		}
		
		$param ['type'] = 'thumb';
		$param ['media'] = '@' . realpath ( SITE_PATH . $path );
		if ($from =='sendall'){
			$param ['type'] = 'image';
		    $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=' . get_access_token ();
		}else{
		    $url = 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token=' . get_access_token ();
		}
		
		$res = post_data ( $url, $param, true );
		
		if (isset ( $res ['errcode'] ) && $res ['errcode'] != 0) {
			return '';
		}
		if (empty($from)){
			$map ['cover_id'] = $cover_id;
			$map ['manager_id'] = $this->mid;
			$this->where ( $map )->setField ( 'thumb_media_id', $res ['media_id'] );
		}
		return $res ['media_id'];
	}
	
	//图文消息的内容图片，上传到微信并获取新的链接覆盖
	function getNewContent($content)
	{
	    if (! $content)
	        return;
	    $newUrl = array();
	    // 获取文章中图片img标签
	    //         $match=$this->getImgSrc($content);
	    preg_match_all('#<img.*?src="([^"]*)"[^>]*>#i', $content, $match);
	    foreach ($match[1] as $mm) {
	        $newUrl[$mm] = uploadimg($mm);
	    }
	    if (count($newUrl)){
	        $content_new = strtr($content, $newUrl);
	    }
	    return empty($content_new) ? $content : $content_new;
	}
	
}
