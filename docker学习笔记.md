## 基础操作

	区分镜像：id和摘要
	常识：一个镜像可有多个标签

	1.拉取镜像

	2.运行镜像

	3.列出镜像

	4.显示占用磁盘空间大小

	5.删除镜像 untagged和delete 
		结合docker image ls 删除
		docker image rm $(docker image ls -q redis)
		docker image rm $(docker image ls -q -f before=mongo:3.2)
	
## Dockerfile配置指令

	from
	run
	copy
		COPY [--chown=<user>:<group>] <源路径>... <目标路径>
	add  
		与copy指令功能差不多，但是会自动解压某些格式的文件，一般不适用
	cmd
		shell格式：cmd 命令
		exec格式：cmd ["可执行文件", "参数1", "参数2"]
	entrypoint 格式命令与run一样，分为exec和shell格式
		目的和cmd一样，都是指定容器启动程序和参数
		区别：cmd命令会作为参数传递给 entrypoint
	arg 参数名[=<参数值>] --build-arg <参数名>=<参数值> 1.13之前必须在dockerfile中先定义好
	
	
	env 	key value | key=value key2=value2