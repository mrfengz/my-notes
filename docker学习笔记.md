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

	volume ["路径1", "路径2"] or volume 路径 保持存储层不发生写操作，比如数据库文件，代码
		运行时指定挂载卷 docker run -d -v mydata:/data xxxx mydata命名卷挂载到了/data

	expose <端口1> [..<端口>] 声明容器时提供的端口
		docker run -P <宿主端口>:<容器端口>	

	workdir <工作目录路径>，不存在会自动创建
		RUN cd /app					# 容器层1
		RUN echo "hello">world.txt 	# 容器层2 /app/world.txt不存在，这是另一个容器层了
		** 每一个 RUN 都是启动一个容器、执行命令、然后提交存储层文件变更

	user 指定当前用户
		user <用户名>[:<用户组>]

	healthcheck [选项] exec <命令>
		检查容器是否正常运行

	onbuild 可用于构建基础镜像，其他依赖的镜像在运行时，会生效，对当前镜像不生效		

## Docker构建
>	多阶段构建 
	1个dockerfile, 多个dockerfile, 多阶段构建
	构建某阶段镜像 as关键字和 --target=aliasName	
	从其他镜像复制文件 copy --from=imageName file target_file		

> manifest 构建多种系统支持的docker镜像

> 其他方式制作镜像
	docker import
	docker save
	docker load 
	这些都是旧的，如无必要，使用新的即可	

## 镜像的实现原理
	如何实现增量的修改和维护？
		使用Union FS将不同层结合到一个镜像中

## 操作docker容器
	容器是独立运行的一个或一组应用，以及它们的运行态环境。
	对应的，虚拟机可以理解为模拟运行的一整套操作系统（提供了运行态环境和其他系统环境）和跑在上面的应用			