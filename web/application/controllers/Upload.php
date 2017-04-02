<?php
/**
 * 上传处理
 */
class UploadController extends Yaf_Controller_Abstract {
	public function indexAction() {
		$ele = array_keys($_FILES)[0];
		if (!$ele) {
			echo json_encode(array('error'=>'1', 'errmsg'=>'文件不存在'));
			return FALSE;
		}
		if ($_FILES[$ele]) {
                   
			$config = Yaf_Application::app()->getConfig()->imgupload->toArray();
			$dir = 'fk/'.date('Ymd');
			$upload = new Utils_Upload($dir, $config['root']);
			$upload->file($_FILES[$ele]);
			//仅允许上传gif,jpg,png三种类型图片
			$upload->set_allowed_mime_types(array('image/gif', 'image/jpeg', 'image/png'));
			$results = $upload->upload();
			
			try {
				$thumb = new Utils_Thumbnail($results['full_path']);
				$thumb->buildThumbnail(140, 200, Utils_Thumbnail::SIZE_FIT_LONGEST);	//缩略图最大140 最小200
				list ($prefix, $ext) = explode('.', $results['filename']);
				$thumbfile = $dir.'/'.$prefix.'_thumb.jpg';
				$thumb->toJpegFile($config['root'].$thumbfile);
			} catch (Exception $e) {
				$thumbfile = $results['filename'];	//没有成功生成缩略图  就用原始图片代替
			}
			
			if (!$results['status']) {
				$errmsg = isset($results['errors'])?implode(',', $results['errors']):'';
				echo json_encode(array('error'=>'2', 'errmsg'=>'文件上传失败,'.$errmsg));
			} else {
				$path = str_replace('\\', '/', $results['path']);
				
				$filemodel = new FilesModel();
				$id = $filemodel->addFileThumb($path, $thumbfile);
                                
				if (!$id) {
					echo json_encode(array('error'=>'3', 'errmsg'=>'文件上传失败,'));
				} else {
					$url = $config['host'].$path;
					$thumburl = $config['host'].$thumbfile;
					echo json_encode(array('error'=>'0', 'data'=>array('path'=>$path, 'url'=>$url, 'thumb'=>$thumburl, 'id'=>$id)));
				}
			}
		}
		return FALSE;
	}
	
	
	/* public function rotateimageAction(){
		$filename = $this->getRequest()->getPost('aid', ''); //图片原型
		if(!$filename){
			exit;
		}
		$degrees = 270;
		$conf = Yaf_Registry::get("config")->get('imgupload');
		if ($conf) $imguploadconf = $conf->toArray();
		else $imguploadconf = [];
		
		$filename = str_replace($imguploadconf['host'], $imguploadconf['root'], $filename);//原始图片
		
		//echo $filename = "/home/work/www/dev/dev/public/fk/9906790372c7e2d183d92ec16dac9426317114ab1472657561.png";
		
		
		$filename_thum = str_replace(".", "_thumb.", $filename);//缩略图
		$filename_thum = str_replace(".png", ".jpg", $filename_thum);//缩略图
		
		$status = $this->rotate($filename,$degrees);
		$status = $this->rotate($filename_thum,$degrees);
		
		exit;
		return FALSE;
		
	} */
	
	public function rotateimageAction(){
		$fileid = htmlspecialchars($this->getRequest()->getPost('aid', 0)); //
		$deg = (int)$this->getRequest()->getPost('deg', 0); //
		//$fileid = $this->getRequest()->get('aid', ''); //图片id
		if(!$fileid || !$deg){
			exit;
		}
		
		$fileid = str_replace("fancybox_", "", $fileid);//上传图片的 id替换
		
		$deg = $deg%4;
		$degrees = (4-$deg)*90;
		if(!$degrees) exit;
		
		$fileModel = new FilesModel();
		
		$config = Yaf_Application::app()->getConfig()->imgupload->toArray();
		//$conf = Yaf_Registry::get("config")->get('imgupload');
		
		$dir = 'fk/'.date('Ymd');
		$upload = new Utils_Upload($dir, $config['root']);
		
		$filenamelast = $upload->get_spin_name();
		$filenamelast = $dir."/".$filenamelast;//重新获取图片路径，无后缀
		
		
		$result = $fileModel->getFilePathThumb($fileid);
		if(!$result['path']){
			exit;
		}
		
		$status = $this->rotate($result['path'],$degrees,$filenamelast);//1：修改前图片绝对路径；2：旋转的角度；3：修改的
		
		$data['path'] = $status;
		if($result['thumb']){
			$status = $this->rotate($result['thumb'],$degrees,$filenamelast."_thumb");//1：修改前图片绝对路径；2：旋转的角度；3：修改的
			$data['thumb'] = $status;
		}
		
		$result = $fileModel->where(array(id=>$fileid))->save($data);
		
		if($result){
			$data['data_src'] = $config['host'].$data['path'];
			if($data['thumb']){
				$data['src'] = $config['host'].$data['thumb'];
			}else{
				$data['src'] = $config['host'].$data['path'];
			}
			echo json_encode($data);
		}
		
		die;
		//$status = $this->rotate($filename_thum,$degrees);
		
		
		
		$conf = Yaf_Registry::get("config")->get('imgupload');
		if ($conf) $imguploadconf = $conf->toArray();
		else $imguploadconf = [];
		
		$filename = str_replace($imguploadconf['host'], $imguploadconf['root'], $filename);//原始图片
		
		//echo $filename = "/home/work/www/dev/dev/public/fk/9906790372c7e2d183d92ec16dac9426317114ab1472657561.png";
		
		
		$filename_thum = str_replace(".", "_thumb.", $filename);//缩略图
		$filename_thum = str_replace(".png", ".jpg", $filename_thum);//缩略图
		
		
		
		exit;
		return FALSE;
		
	}
	
	
	public function rotate($filename,$degrees,$filenamelast){
		$conf = Yaf_Registry::get("config")->get('imgupload');
		$filename = $conf['root'].$filename;
		
		
		$result = explode('.',$filename);
		
		
		$filenamelast .= ".".$result[1];
		
		$lastfilename = $conf['root'].$filenamelast;
		
		
		$a = 0;
		if($result[1] == 'jpg' || $result[1] == 'jpeg'){
			//创建图像资源，以jpeg格式为例
			$source = imagecreatefromjpeg($filename);
			//使用imagerotate()函数按指定的角度旋转
			$rotate = imagerotate($source, $degrees, 0);
			//旋转后的图片保存
			$a = imagejpeg($rotate,$lastfilename);
			
		}else if($result[1] == 'png'){
			//创建图像资源，以jpeg格式为例
			$source = imagecreatefrompng($filename);
			//使用imagerotate()函数按指定的角度旋转
			$rotate = imagerotate($source, $degrees, 0);
			//旋转后的图片保存
			$a = imagepng($rotate,$lastfilename);
		}else if($result[1] == 'gif'){
			//创建图像资源，以jpeg格式为例
			$source = imagecreatefromgif($filename);
			//使用imagerotate()函数按指定的角度旋转
			$rotate = imagerotate($source, $degrees, 0);
			//旋转后的图片保存
			$a = imagegif($rotate,$lastfilename);
		}
		$userId = Session_AdminFengkong::instance()->getUid();
		$msg = "操作人：".$userId." 操作图片：".$filename." 成功或失败：1成功：0失败：".$a;
		Utils_Tool::log('upload',$msg);
		return $filenamelast;
    }
    
    
    /**
     * 车贷图片旋转方法
     * @author hgy
     * @since 2016-09-26
     */
    public function rotateimagecdAction(){
        //文件ID
        $fileid = $this->getRequest()->getPost('aid', ''); 
        //旋转次数
        $rotate_num= $this->getRequest()->get('rotate_num', 1); 
        if(!$fileid){
            exit;
        }

        //$fileid = str_replace("fancybox_", "", $fileid);//上传图片的 id替换

        //$degrees = 270;
        $degrees = 360 - (90 * $rotate_num);

        $fileModel = new FilesModel();

        //图片存储目录
        $config = Yaf_Application::app()->getConfig()->imgupload->toArray();
        $dir = 'fk/cdnation/'.date('Ymd');
        $upload = new Utils_Upload($dir, $config['root']);

        //构造新上传图片的文件名
        $filenamelast = $upload->get_spin_name();
        //新图片路径，无后缀
        $filenamelast = $dir."/".$filenamelast;

        //获取原图片路径
        $result = $fileModel->getFilePathThumb($fileid);
        if(!$result['path']){
            exit;
        }

        //替换图片路径
        $status = $this->rotate($result['path'],$degrees,$filenamelast);//1：修改前图片绝对路径；2：旋转的角度；3：修改的

        //替换缩略图路径
        $data['path'] = $status;
        if($result['thumb']){
            $status = $this->rotate($result['thumb'],$degrees,$filenamelast."_thumb");//1：修改前图片绝对路径；2：旋转的角度；3：修改的
            $data['thumb'] = $status;
        }

        //替换fk_files表中图片的路径为新修改的路径
        $result = $fileModel->where(array(id=>$fileid))->save($data);

        //替换成功的话返回新的路径
        if($result){
            $data['data_src'] = $config['host'].$data['path'];
            if($data['thumb']){
                    $data['src'] = $config['host'].$data['thumb'];
            }else{
                    $data['src'] = $config['host'].$data['path'];
            }
        }
        
        //不成功的话返回原路径
        else{
            $data['data_src'] = $config['host'].$result['path'];
            $data['src'] = $config['host'].$result['thumb'];
        }

        echo json_encode($data);
        exit;
    }
    
    
    
}