<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty auto version modifier plugin<br>
 * 提供欲使用的静态文件和当前静态文件所在项目的路径<br>
 * 得到带有版本号的静态文件使用路径
 *
 * Type:     modifier<br>
 * Name:     static_version<br>
 * Purpose:  static file version
 * @link http://wiki.joyport.com/Tec:静态文件版本号控制
 * 
 * @author   naodai <liumingzhao@joyport.com>
 * @param string $staticFile 静态文件
 * @param string $dir 版本号控制配置文件所在目录，默认值为全局配置配件做所在路径
 * @return string 带有版本号参数的静态文件路径
 */
function smarty_modifier_static_version($staticFile,$dir = 'Libraries')
{
    /** 为了兼容空路径的情况 */
    $dir = trim($dir) ? trim($dir) : 'Libraries';
    /** 定义静态版本号控制文件路径和名称 */
    $versionFile = '/Configs/version.config.php';
    /** 是否是全局 */
	if($dir == 'Libraries'){
        /** 
         * 组装静态文件版本号控制文件全部路径 
         * 此处的 LIB_PATH 是在项目入口处定义的
         */
		$configFile = LIB_PATH . $versionFile; 
	}
	else{
	/** 
         * 全局未读取到,读项目自己的配置 
         * 此处的 BASE_PATH 是在项目入口处定义的
         */
        $configFile = APP_PATH . $versionFile;
	}
	/** 获取/写入版本号，并返回带有版本号的静态文件路径 */
    return getVersion($staticFile,$configFile,$dir);
}

/**
 * 抽离出来的获取/写入静态文件版本号控制函数。
 * 对传入的静态文件到静态版本号控制配置文件(version.config.php)里获取版本号
 * 如果没有这个静态文件的版本号，用当前时间作为该静态文件的版本号，追加到版本号配置文件中。
 * 如果有，则返回静态文件加版本号的url供使用(示例: http://image.ledu.com/a.js?v=2013010101)。
 * @param string $staticFile 静态文件
 * @param string $configFile 版本号控制配置文件
 * @param string $dir 当前项目的目录
 * @return string 带有版本号的静态文件路径
 */
function getVersion($staticFile,$configFile,$dir){
	/** 判断版本控制配置文件是否存在 */
	if(file_exists($configFile)){
		/** 得到配置文件内容，并赋值 */
		$config = include($configFile);
		/** 该静态文件是否存在于版本号控制配置中 */
		if(array_key_exists($staticFile, $config)){
			/** 存在。直接返回，程序退出 */
			return $staticFile.'?v='.$config[$staticFile];
		}
                /**
		 * 不存在于配置中
		 * 追加到配置中
		 * { 
		 */
		/** 读取配置文件内容 */
		$confileContents = file_get_contents($configFile);
		/** 判断是否读取成功 */
        if(false === $confileContents){
            /**
             * 读取配置文件内容失败
             * e=1
             */
            return $staticFile.'?e=1&d='.$dir;
        }
        /** 判断配置文件内容是否为空 */
		if(strlen(trim($confileContents)) == 0){
			/** 为空，填入预制内容 */
			$confileContents = '<?php
return array(
);';
		}
		/** 用当前时间作为该静态文件的版本号 */
		$versionDate = date('YmdHis');
		/** 匹配字符串，为将原配置文件内容最后的数组结束去掉，以便添加新内容 */
		$pattern = "/\);/";
		/** 新增静态文件的版本号信息 */
		$fileVersion = "'$staticFile'=>'$versionDate',";
		/** 用新静态文件的版本信息替换原静态配置文件内容尾部的数组结束符号，完成新信息的追加 */
        $confileContents = preg_replace($pattern, $fileVersion, $confileContents);
        /** 增加数组结束符号 */
		$confileContents .= '
);';
        /**
         *  新的配置信息写入配置文件 
         *  这块使用了抑制错误@符号，防止不能正常输出。
         */
		if(@file_put_contents($configFile, $confileContents)){
			/** 完成写入，返回该静态文件带有静态版本号的使用路径 */
			return $staticFile.'?v='.$versionDate;
		}
		else{
			/** 
			 * 写入配置文件失败
			 * e=2 
			 * 请确认版本号配置文件version.config.php有可写权限
			 */
			return $staticFile.'?e=2&d='.$dir;
		}
		/**
		 * }
		 */
	}
	else{
		/**
		 * 版本号配置文件version.config.php不存在
		 * e=3 
		 * 请在项目的Configs目录下创建版本号配置文件version.config.php.并将其权限设置为666
		 */
		return $staticFile.'?e=3&d='.$dir;
	}
}
/* vim: set expandtab: */
