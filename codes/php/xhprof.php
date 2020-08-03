<?php

 /** xhprof */
$xhprofDir = '/data/users/august/xhprof';	//输出记录信息目录
ini_set('xhprof.output_dir', $xhprofDir);	
$XHPROF_ROOT = '/data/users/august/xhprof_lib/utils/';	//从github上下载的工具文件目录
include_once $XHPROF_ROOT . "xhprof_lib.php";
include_once $XHPROF_ROOT . "xhprof_runs.php";
//开启xhprof
xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

 /** xhprof */
$xhprof_data = xhprof_disable();
//冲刷(flush)所有响应的数据给客户端
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}
$xhprof_runs = new \XHProfRuns_Default();
//save the run under a namespace "xhprof_foo"
$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");