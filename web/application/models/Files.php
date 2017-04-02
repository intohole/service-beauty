<?php
class FilesModel extends TK_M {
	protected $tableName = 'xmcd_files';
	
	public function addfile($path, $user=0) {
		$data = array(
			'path'=>$path,
			'creator'=>$user,
			'created'=>time()
		);
		return $this->data($data)->add();
	}
        
	public function addFileThumb($path, $thumb, $user=0) {
		$data = array(
			'path'=>$path,
			'thumb'=>$thumb,
			'creator'=>$user,
			'created'=>time()
		);
		return $this->data($data)->add();
	}
	
	public function getFiles($ids) {
		$files = $this->where(array('id'=>array('in', $ids)))->select();
		$ret = [];
		$conf = Yaf_Registry::get("config")->get('imgupload');
		if ($conf) $imguploadconf = $conf->toArray();
		else $imguploadconf = [];
		
		foreach ($files as $file) {
			$ret[$file['id']] = array(
				'path'=>$file['path'],
				'url'=>$imguploadconf['host'].$file['path'],
				'thumb'=>$imguploadconf['host'].$file['thumb'],
                                'source_name'=>$file['source_name'],
			);
		}
		return $ret;
	}

    public function getFilePath($fileid){
        $files = $this->where(array('id'=>$fileid))->field('id,path')->find();
        $conf = Yaf_Registry::get("config")->get('imgupload');
        if ($conf) $imguploadconf = $conf->toArray();
        else $imguploadconf = [];
        $files['url'] = $imguploadconf['host'].$files['path'];
        return $files;
    }

}