<?php
xhprof_enable(XHPROF_FLAGS_MEMORY | XHPROF_FLAGS_CPU);

register_shutdown_function(function() {
	$xhprofDir = '/data/users/august/xhprof';	//输出记录信息目录
	ini_set('xhprof.output_dir', $xhprofDir);	
	$XHPROF_ROOT = '/data/users/august/xhprof_lib/utils/';	//从github上下载的工具文件目录
	include_once $XHPROF_ROOT . "xhprof_lib.php";
	include_once $XHPROF_ROOT . "xhprof_runs.php";

    $xhprof_data = xhprof_disable();
    if (function_exists('fastcgi_finish_request')){
        fastcgi_finish_request();
    }
    $xhprof_runs = new \XHProfRuns_Default();
	//save the run under a namespace "xhprof_foo"
	$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
});