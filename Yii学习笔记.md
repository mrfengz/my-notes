backend/index.php 入口文件

	加载环境配置文件 environments/config.php 
	composer/autoload.php
	Yii.php 
		1.继承BaseYii.php
			定义了一些基础操作方法 Yii::configure()/Yii::t()/Yii::getAlias()/Yii::getRootAlias()/Yii::error()/Yii::getObjectVars()/Yii::autoload()
			/Yii::getVersion()/Yii::createObject()/Yii::getLogger()/Yii::setLogger()/Yii::beginProfile()/Yii::endProfile() 等
		2. Yii::autoload()自动加载类
		2.引入 Yii::$classMap = classes.php 【yii\base\xxx => Yii_PATH.'/base/xxx.php'】
		3. Yii::$container = new yii\di\Container();

	加载当前目录的 config/main.php
	
	(new yii\web\Application($config))->run();
		继承关系： yii\web\Application 
			extends yii\base\Application 
			extends yii\base\Module 
			extends yii\di\ServiceLocator 
			extends yii\base\Component
			extends yii\base\BaseObject 
			extends yii\base\Configurable
		
		1. yii\base\Configurable 接口，无任何方法
		
		2. yii\base\BaseObject 
		 	BaseObject::className()/hasMethod()
		 	BaseObject::__construct($config = [])
		 	BaseObject::__get()/__set()/__isset()/__unset()/__call()
		 	BaseObject::hasProperty()/canGetProperty()/canSetProperty()
		
		3. yii\base\Component 实现了 property/event/behavior 组件的基础类
			1. event: 通过注入的方式实现
				eventHandler： 通过 on()方法绑定。
					类型为 
						1.[$obj, 'method']
						2.['staticClass', 'method']
						3. 匿名函数 function($event){...}
						4. 命名函数
					传参：
						$post->on('update', function ($event) {
					       // the data can be accessed via $event->data
					    }, $data);	

				触发： trigger()函数触发
			2. property: 在baseObject中已实现
			3. behavior 是Behavior或者其子类的一个实例，一个组件可以有一个或者多个行为。
					行为的属性和方法，可以被组件获取和使用

			event和behavior都可以通过配置文件进行配置

			Component::__get()/__set()/__isset()/__unset()/__call() 同时处理自身和behavior的值
			Component::__clonse() 克隆时，会将$_events/$_eventWildcards/$_behaivors置空
			Component::hasProperty()/canGetProperty()/canSetProperty()/hasMethod() 同时还会判断$behaivor

			Component::on()/off()/trigger() 	# 事件处理相关方法
			Component::getBehavior()/getBehaviors()/attachBehavior()/detachBehavior()/detachBehaviors() # 行为相关处理方法

		4. yii\di\ServiceLocator 服务定位
			需要通过set()或者setComponent()注册 componentId(), 然后通过get()获取指定ID的组件
			服务定位器会自动实例化并配置相应组件

			__get()/__isset()/has()/
			ServiceLocator::get()/getComponents()
			ServiceLocator::set()/SetComponents()
			ServiceLocator::clear()	

		5. 	extends yii\base\Module 是module和Application的基类
			module代表一个包含mvc
			$module->id
			$module->module 父module
			$layout
			$controllerMaps ['Account' => ['class' => xxx]] controllerId => configureArray
			$controllerNamespace
			$defaultRoute
			$_basePath
			$_viewPath
			$_layoutPath

			$_modules 子modules
			$_version
			
			methods: 

			Module::__construct($id, $parent = null, $config) {
				$this->id = $id;
				$this->parent = $parent;
				parent::__construct($config);
			}
			Module::setInstance($class) => Yii::$app->loadedModules[get_class($instance)]; 
			Module::getInstance() => $class = get_called_class()
			Module::init()  命名空间赋值  controllerNamespace
			Module::getUniqueId()
			Module::getBasePath()/setBasePath()
			Module::getControllerPath()/getViewPath()/setViewPath()
			Module::getLayoutPath()/setLayoutPath()
			Module::setVersion()/getVersion()
			Module::defaultVersion()
			Module::setAlias()
			Module::getModule()/setModule()/hasModule()
			Module::getModules()/setModules()
			Module::createController()
			Module::createControllerById()
			Module::runAction()
			Module::beforeAction()/afterAction()
			Module::get()
			Module::has()

		6. yii\base\Application	
			properties:
				name
				charset
				language
				sourceLanguage
				controller
				requestRoute
				requestAction
				layout
				requestedParams

			methods:
					