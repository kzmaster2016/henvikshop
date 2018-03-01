<?php
/**
 * 
 */

namespace Admin\Controller;
use Think\Upload;

class ToolsController extends BaseController {
    public function index(){
        $dbtables = M()->query('SHOW TABLE STATUS');
        $total = 0;
        foreach ($dbtables as $k => $v) {
            $dbtables[$k]['size'] = format_bytes($v['data_length'] + $v['index_length']);
            $total += $v['data_length'] + $v['index_length'];
        }
        $this->assign('list', $dbtables);
        $this->assign('total', format_bytes($total));
        $this->assign('tableNum', count($dbtables));
        $this->display();
    }
    
    public function backup(){
        $M = M();
        //防止备份数据过程超时
        function_exists('set_time_limit') && set_time_limit(0);
        send_http_status('310');
        $tables = I('tables', array());
        if (empty($tables)) {
            $this->error('请选择要备份的数据表');
        }

        $time = time();//开始时间
        if(!file_exists(UPLOAD_PATH.'sqldata')){
        	mkdir(UPLOAD_PATH.'sqldata');
        }
        $path = UPLOAD_PATH."sqldata/tpshop_tables_" . date("Ymd").get_rand_str(3,0);

        $pre = "# -----------------------------------------------------------\n";
        //取得表结构信息
        //1，表示表名和字段名会用``包着的,0 则不用``
     
        M()->execute("SET SQL_QUOTE_SHOW_CREATE = 1"); 
        $outstr = '';
        foreach ($tables as $table) {
            $outstr.="# 表的结构 {$table} \n";
            $outstr .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $tmp = $M->query("SHOW CREATE TABLE {$table}");
            $outstr .= $tmp[0]['create table'] . " ;\n\n";
        }
        
        $sqlTable = $outstr;
        $outstr = "";
        $file_n = 1;
        $backedTable = array();
        //表中的数据
        foreach ($tables as $table) {
            $backedTable[] = $table;
            $outstr.="\n\n# 转存表中的数据：{$table} \n";
            $tableInfo = $M->query("SHOW TABLE STATUS LIKE '{$table}'");
            $page = ceil($tableInfo[0]['rows'] / 10000) - 1;
            for ($i = 0; $i <= $page; $i++) {
                $query = $M->query("SELECT * FROM {$table} LIMIT " . ($i * 10000) . ", 10000");
                foreach ($query as $val) {
                    $temSql = "";
                    $tn = 0;
                    $temSql = '';
                    foreach ($val as $v) {
                        $temSql.=$tn == 0 ? "" : ",";
                        $temSql.=$v == '' ? "''" : "'{$v}'";
                        $tn++;
                    }
                    $temSql = "INSERT INTO `{$table}` VALUES ({$temSql});\n";

                    $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                            "# -----------------------------------------------------------\n" .
                            "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
                       if ($file_n == 1) {
                        $sqlNo = "# Description:备份的数据表[结构]：" . implode(",", $tables) . "\n".
                                 "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                    } else {
                        $sqlNo = "# Description:备份的数据表[数据]：" . implode(",", $backedTable) . $sqlNo;
                    }

                    if (strlen($pre) + strlen($sqlNo) + strlen($sqlTable) + strlen($outstr) + strlen($temSql) > C("CFG_SQL_FILESIZE")) {
                        $file = $path . "_" . $file_n . ".sql";
                        $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
                       
                        if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                            $this->error("备份文件写入失败！", U('Tools/index'));
                        }
    
                        $sqlTable = $outstr = "";
                        $backedTable = array();
                        $backedTable[] = $table;
                        $file_n++;
                        dump($file_n);
                        exit;
                    }
                    $outstr.=$temSql;
                }
            }
        }
        if (strlen($sqlTable . $outstr) > 0) {
            $sqlNo = "\n# Time: " . date("Y-m-d H:i:s") . "\n" .
                    "# -----------------------------------------------------------\n" .
                    "# SQLFile Label：#{$file_n}\n# -----------------------------------------------------------\n\n\n";
            if ($file_n == 1) {
                $sqlNo = "# Description:备份的数据表[结构] " . implode(",", $tables) . "\n".
                         "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            } else {
                $sqlNo = "# Description:备份的数据表[数据] " . implode(",", $backedTable) . $sqlNo;
            }
            $file = $path . "_" . $file_n . ".sql";
            $outstr = $file_n == 1 ? $pre . $sqlNo . $sqlTable . $outstr : $pre . $sqlNo . $outstr;
//			exit($file);
            if (!file_put_contents($file, $outstr, FILE_APPEND)) {
                $this->error("备份文件写入失败！" ,U('Tools/index'));
            }
            $file_n++;
        }
        
        $time = time() - $time;
        exit(json_encode(array('stat'=>'ok','msg'=>"成功备份数据表，本次备份共生成了" . ($file_n-1) . "个SQL文件。耗时：{$time} 秒")));
    }
    
    public function restore(){
    	$size = 0;
    	$pattern = "*.sql";
    	$filelist = glob(UPLOAD_PATH."sqldata/".$pattern);
    	$fileArray = array();
    	foreach ($filelist  as $i => $file) {
    		//只读取文件
    		if (is_file($file)) {
    			$_size = filesize($file);
    			$size += $_size;
    			$name = basename($file);
    			$pre = substr($name, 0, strrpos($name, '_'));
    			$number = str_replace(array($pre. '_', '.sql'), array('', ''), $name);
    			$fileArray[] = array(
    				'name' => $name,
    				'pre' => $pre,
    				'time' => filemtime($file),
    				'size' => $_size,
    				'number' => $number,
    			);
    		}
    	}
    	
    	if(empty($fileArray)) $fileArray = array();
    	krsort($fileArray); //按备份时间倒序排列    	
    	$this->assign('vlist', $fileArray);
    	$this->assign('total', format_bytes($size));
    	$this->assign('filenum', count($fileArray));
    	$this->display();
    }
    
    
    /**
     * 读取要导入的sql文件列表并排序后插入SESSION中
     */
    private function getRestoreFiles() {
    	$sqlfilepre = I('sqlfilepre','');//获取sql文件前缀
    	if (empty($sqlfilepre)) {
    		$this->error('请选择要还原的数据文件！');
    	}
    	$pattern = $sqlfilepre. "*.sql";
    	$sqlFiles = glob(UPLOAD_PATH."sqldata/".$pattern);
    	if (empty($sqlFiles)) {
    		$this->error('不存在对应的SQL文件！');
    	}
    
    	//将要还原的sql文件按顺序组成数组，防止先导入不带表结构的sql文件
    	$files = array();
    	foreach ($sqlFiles as $sqlFile) {
    		$sqlFile = basename($sqlFile);
    		$k = str_replace(".sql", "", str_replace($sqlfilepre . "_", "", $sqlFile));
    		$files[$k] = $sqlFile;
    	}
    	unset($sqlFiles, $sqlfilepre);
    	ksort($files);
    	return $files;
    }
    
    /**
     * 执行还原数据库操作
     */
    public function restoreData() {
    	//ini_set("memory_limit", "16M");
    	function_exists('set_time_limit') && set_time_limit(0); //防止备份数据过程超时
    	//取得需要导入的sql文件
    	if (!isset($_SESSION['cacheRestore']['files'])) {
    		$_SESSION['cacheRestore']['starttime'] = time();
    		$_SESSION['cacheRestore']['files'] = $this->getRestoreFiles();
    	}

    	$files = $_SESSION['cacheRestore']['files'];
    	if (empty($files)) {
    		unset($_SESSION['cacheRestore']);
    		$this->error('不存在对应的SQL文件');
    	}
    
    	//取得上次文件导入到sql的句柄位置
    	$position = isset($_SESSION['cacheRestore']['position']) ? $_SESSION['cacheRestore']['position'] : 0;
    	$execute = 0;
    	foreach ($files as $fileKey => $sqlFile) {
    		$file = UPLOAD_PATH."sqldata/". $sqlFile;
    		if (!file_exists($file))
    			continue;
    		$file = fopen($file, "r");
    		$sql = "";
    		fseek($file, $position); //将文件指针指向上次位置
    		while (!feof($file)) {
    			$tem = trim(fgets($file));
    			//过滤,去掉空行、注释行(#,--)
    			if (empty($tem) || $tem[0] == '#' || ($tem[0] == '-' && $tem[1] == '-'))
    				continue;
    			//统计一行字符串的长度
    			$end = (int) (strlen($tem) - 1);
    			//检测一行字符串最后有个字符是否是分号，是分号则一条sql语句结束，否则sql还有一部分在下一行中  
	    	   if ($tem[$end] == ";") {
	    	   $sql .= $tem;
	    	   M()->execute($sql);
	    	   $sql = "";
	    	   $execute++;
	    	   		if ($execute > 500) {
			    		$_SESSION['cacheRestore']['position'] = ftell($file);
			    		$imported = isset($_SESSION['cacheRestore']['imported']) ? $_SESSION['cacheRestore']['imported'] : 0;
			    		$imported += $execute;
			    		$_SESSION['cacheRestore']['imported'] = $imported;
			    		//echo json_encode(array("status" => 1, "info" => '如果导入SQL文件卷较大(多)导入时间可能需要几分钟甚至更久，请耐心等待导入完成，导入期间请勿刷新本页，当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>', "url" => U('Database/restoreData', array(get_randomstr(5) => get_randomstr(5)))));
			    		$this->success('如果SQL文件卷较大(多),则可能需要几分钟甚至更久,<br/>请耐心等待完成，<font color="red">请勿刷新本页</font>，<br/>当前导入进度：<font color="red">已经导入' . $imported . '条Sql</font>', U('Tools/restoreData', array(get_rand_str(5,0) => get_rand_str(5,0))));
			    		exit();
					}
	    		} else {
	    			$sql .= $tem;
	    		}
    		}
    		//错误位置结束
    		fclose($file);
    		unset($_SESSION['cacheRestore']['files'][$fileKey]);
    		$position = 0;
    	}
    	$time = time() - $_SESSION['cacheRestore']['starttime'];
    	unset($_SESSION['cacheRestore']);
    	$this->success("导入成功，耗时：{$time} 秒钟", U('Tools/restore'));
    }
        
    /**
     * 优化
     */
    public function optimize() {
    	$batchFlag = I('get.batchFlag', 0, 'intval');
    	//批量删除
    	if ($batchFlag) {
    		$table = I('key', array());
    	}else {
    		$table[] = I('tablename' , '');
    	}
    
    	if (empty($table)) {
    		$this->error('请选择要优化的表');
    	}
    
    	$strTable = implode(',', $table);
    	if (!M()->query("OPTIMIZE TABLE {$strTable} ")) {
    		$strTable = '';
    	}
    	$this->success("优化表成功" . $strTable, U('Tools/index'));
    
    }
    
    /**
     * 修复
     */
    public function repair() {
    	$batchFlag = I('get.batchFlag', 0, 'intval');
    	//批量删除
    	if ($batchFlag) {
    		$table = I('key', array());
    	}else {
    		$table[] = I('tablename' , '');
    	}
    
    	if (empty($table)) {
    		$this->error('请选择修复的表');
    	}
    
    	$strTable = implode(',', $table);
    	if (!M()->query("REPAIR TABLE {$strTable} ")) {
    		$strTable = '';
    	}
    
    	$this->success("修复表成功" . $strTable, U('Tools/index'));
  
    }
    
    
    public function restoreUpload()
    {
    	$config = array(
    			"savePath" => 'sqldata/',
    			"maxSize" => 100000000, // 单位B
    			"exts" => array('sql'),
    			"subName" => array(),
    	);
    	
    	$upload = new Upload($config);   	
    	$info = $upload->upload();
    	if (!$info) { // 上传错误提示错误信息
    		$this->error($upload->getError());
    	} else { // 上传成功 获取上传文件信息
    		$file_path_full = '.'.$info['sqlfile']['urlpath'];
    		if (file_exists($file_path_full)) {
    			$this->success("上传成功", U('Tools/restore'));
    		} else {
    			$this->error('文件不存在');   
    		}
    	}
    
    }
    
    /**
     * 下载
     */
    public function downFile() {
    	if (empty($_GET['file']) || empty($_GET['type']) || !in_array($_GET['type'], array("zip", "sql"))) {
    		$this->error("下载地址不存在");
    	}
    	$path = array("zip" => UPLOAD_PATH."zipdata/", "sql" => UPLOAD_PATH."sqldata/");
    	$filePath = $path[$_GET['type']] . $_GET['file'].".sql";
    	if (!file_exists($filePath)) {
    		$this->error("该文件不存在，可能是被删除");
    	}
    	$filename = basename($filePath);
    	header("Content-type: application/octet-stream");
    	header('Content-Disposition: attachment; filename="' . $filename . '"');
    	header("Content-Length: " . filesize($filePath));
    	readfile($filePath);
    }
    

    /**
     * 删除sql文件
     */
    public function delSqlFiles() {
    	$batchFlag = I('get.batchFlag', 0, 'intval');
    	//批量删除
    	if ($batchFlag) {
    		$files = I('key', array());
    	}else {
    		$files[] = I('sqlfilename' , '');
    	}
    	if (empty($files)) {
    		$this->error('请选择要删除的sql文件');
    	}
    
    	foreach ($files as $file) {
    		$a = unlink(UPLOAD_PATH."sqldata". '/' . $file.".sql");
    	}
    	if($a){
    		$this->success("已删除：" . implode(",", $files), U('Tools/restore'));
    	}else{
    		$this->error("删除失败：" . implode(",", $files), U('Tools/restore'));
    	}	
    }
    
    public function region(){
    	$parent_id = I('parent_id',0);
    	if($parent_id == 0){
    		$parent = array('id'=>0,'name'=>"中国省份地区",'level'=>0);
    	}else{
    		$parent = M('region')->where("id=$parent_id")->find();
    	}
    	$region = M('region')->where("parent_id=$parent_id")->select();
    	$this->assign('parent',$parent);
    	$this->assign('region',$region);
    	$this->display();
    }
    
    public function regionHandle(){
    	$data = I('post.');
    	$id = I('id');
    	$referurl =  isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : U("Tools/region");
    	if(empty($id)){
    		$data['level'] = $data['level']+1;
    		if(empty($data['name'])){
    			$this->error("请填写地区名称", $referurl);
    		}else{
    			$res = M('region')->where("parent_id = ".$data['parent_id']." and name='".$data['name']."'")->find();
    			if(empty($res)){
    				M('region')->add($data);
    				$this->success("操作成功", $referurl);
    			}else{
    				$this->error("该区域下已有该地区,请不要重复添加", $referurl);
    			}
    		}
    	}else{
    		M('region')->where("id=$id or parent_id=$id")->delete();
    		$this->success("操作成功", $referurl);
    	}
    }
}