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

> 容器启动
	docker run 				# 新建并启动
	docker container start 	# 启动已经终止容器		

> 后台运行
	docker run -d

> 终止容器
	docker container stop 
	exit
	ctrl+D

> 进入容器
	docker run -d 会在后台运行容器
	1. docker attach 容器ID 	# 进入容器。如果从这个stdin中退出，导致容器停止
	2. docker exec				# 退出bash时，不会导致容器停止【推荐】

	docker run -dit ubuntu
	docker container ls 
	docker exec -it 容器id bash  

> 导入和导出容器
	docker export # 将容器导出为本地文件
		docker container ls -a
		docker export 容器id > ubuntu.tar
	docker import # 导入为镜像
		cat ubuntu.tar | docker import - test/ubuntu:v1.0  # 后边也可以接url docker import remote/url.tgz example/dir
		docker image ls
		
	docker import和docker load的区别
		docker load 导入镜像存储文件到本地镜像库，保存记录完整，体积也更大。	

> 删除容器
	docker container rm 容器名
		docker container rm trusting_newton

> 清理所有处于中止状态的容器
	docker container prune		

		
> 其他命令	
	docker container logs 容器id或者 名称 	## 显示日志
	docker container ls -a  				## 容器列表
	docker container restart 				## 启动一个停止的容器

## 仓库（repository)
	注册服务器（Registry), 一个服务器，上边可以有很多仓库(一般表示一个单独的项目或者目录)

> Docker Hub
	官方仓库，网址 https://hub.docker.com

	docker login 	# 登录
	docker logout 	# 退出
	docker search 	# 搜索镜像
	docker pull 	# 拉取镜像
	docker push 	# 推送镜像

	自动构建： 指定关联的目标网站，选择自动构建，选择目标中的项目（包含dockerfile）和分支，在docker hub的timeline中查看构建历史和状态

> 私有仓库
	docker registry 工具

	nexus 3.x 	

## 数据管理
	
	数据管理的两种方式
		1.数据卷(volumes)
		2.挂载主机目录(bind mounts)

> 数据卷

	数据卷的特点
		1. 可以在容器之间共享和重用
		2. 对数据卷的修改立马生效
		3. 对数据卷的更新，不会影响镜像
		4. 数据卷会默认一直存在，即便容器被删除

	数据卷相关命令	
		docker volume create my-vol 	# 创建数据卷
		docker volume ls 				# 查看所有数据卷
		docker volume inspect my-vol 	# 查看指定数据卷的信息
		docker run -d -P \				# 创建一个名为 web 的容器，并加载一个 数据卷 到容器的 /webapp目录中
			--name web \
			# -v my-vol:/webapp \
			--mount source=my-vol,target=/webapp \
			training/webapp \
			python app.py

		docker inspect web 				# （容器内）查看web容器的信息	
		docker volume rm my-vol 		# 删除数据卷
		docker rm -v 					# 删除容器时移除数据卷
		docker volume prune 			# 删除无主的数据卷

> 主机目录
	
	加载主机目录中的 /src/webapp目录到容器的 /opt/webapp目录
	docker run -d -P \
		--name web \
		# -v /src/webapp:/opt/webapp \
		--mount type=bind,source=/src/webapp,target=/opt/webapp \
		training/webapp \
		python app.py	

	默认权限时读写，只读可以如下方式制定
		--mount type=bind,source=/src/webapp,target=/opt/webapp, readonly

	挂在一个本地主机文件作为数据卷
		docker run --rm -it \
			# -v $HOME/.bash_history:/root/.bash_history \
			--mount type=bind,source=$HOME/.bash_history,target=/root/.bash_history \
			ubuntu:18.04 \
			bash #可以记录在容器中输入过的命令


## 网络

	允许通过 访问外部网路 或者 容器互联的方式 提供网络服务

> 外部访问容器
	
	docker run -d -P training/webapp python app.py 	# —P 随机映射主机端口（49000-49900）到内部容器开放的网络端口	
	docker container ls -l  	# 查看容器 0.0.0.0:49115->5000	
	docker logs -f nostalgic_morse # 查看应用请求信息
	docker port nostalgic_morse 5000 # 查看端口映射信息

	-p 指定要映射的端口的格式
		ip地址:主机端口:容器端口 | ip地址::容器端口 | 主机端口: 容器端口

	可以使用多次
		docker run -d \
			-p 5000:5000 \
			-p 3000:80 \
			training/webapp \
			python app.py	

> 容器互联

	docker network create -d bridge my-net # 新建网络 	-d:指定网络类型：bridge overlay
	docker run -it --rm --name busybox1 --network my-net busybox sh # 运行一个容器并连接到新建的my-net网络
	docker run -it --rm --name busybox2 --network my-net busybox sh

	docker container ls

	ping busybox2 # 在busybox1中输入

	docker compose # 多个容器之间需要互相连接，推荐使用

> 配置容器DNS

	在容器中使用 mount命令查看挂载信息

	全部容器的DNS配置，也可以用 /etc/docker/daemon.json配置

	启动容器时，手动指定相关参数


#### Docker安装elasticsearch

	1. docker pull docker.elastic.co/elasticsearch/elasticsearch:7.6.2
	2. docker run -p 9200:9200 -p 9300:9300 -e "discovery.type=single-node" docker.elastic.co/elasticsearch/elasticsearch:7.6.2