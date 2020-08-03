#### 函数
	extension_loaded('swoole');	# 扩展是否已加载
	get_loaded_extensions();	# 获取已加载的所有扩展
	get_extension_funcs('swoole');	# 获取扩展的函数
	function_exists('func_name');	# 检查函数是否存在

#### array_merge和 array1+array2

数字下标：
	1. array_merge()会合并
	2. + 会使用前边的序号的值，忽略后边的序号的值

字母下标：
	1. array_merge()会使用array2的值
	2. +会使用array1中值
