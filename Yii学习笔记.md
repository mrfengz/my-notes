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
				extensions 		已安装的yii扩展，启动过程中会初始化，没有配置的话，会使用 @vendor/yiisoft/extensions.php
				bootstrap 		组件数组，在bootstrap时运行的。 数组、闭包、类名、module Id、component Id
				state 			application当前所处请求的state
				loadedModules 	类名为键
			methods:
				__construct($config)
					1. Yii::$app = $this, static::setInstance($this)
						if ($instance!== null) $loadedModules[get_class($instance)]
						else Yii::$app->loadModules[get_class_called()];

					2. $this->state = begin
					
					3. $this->preInit(&$config) 参数引用传递
						setBasePath()/setVendorPath()/setRuntimePath()/setTimeZone()
						setContainer()
						coreComponents() -> $config['components'][$id]
					
					4. $this->registerErrorHandler($config)
						
					5. Component::__construct($config);
						Yii::configure($this, $config); //把config中的参数，设置为application的属性

				Application::bootstrap()

				Application::init()
					1. $this->state = START_INIT;
					2. $this->bootstrap()
						加载extensions 
							extensions['alias']
							extensions['bootstrap']
						$this->bootstrap 组件实例化
				
				Application::registerErrorHandler(&$config)

				Application::getUniqueId()

				Application::setBasePath()
				Application::getRuntimePath()
				Application::setRuntimePath()
				Application::getVendorPath()
				Application::setVendorPath()
				Application::getTimeZone()
				Application::setTimeZone()

				Application::getDb()
				Application::getLog()
				Application::getErrorHandler()
				Application::getCache()
				Application::getFormatter()
				Application::getRequest()
				Application::getResponse()
				Application::getView()
				Application::getUrlManager()
				Application::getI18n()		
				Application::getMailer()
				Application::getAuthManager()
				Application::getAssetManager()
				Application::getSecurity()

				Application::coreComponents
					return [
			            'log' => ['class' => 'yii\log\Dispatcher'],
			            'view' => ['class' => 'yii\web\View'],
			            'formatter' => ['class' => 'yii\i18n\Formatter'],
			            'i18n' => ['class' => 'yii\i18n\I18N'],
			            'mailer' => ['class' => 'yii\swiftmailer\Mailer'],
			            'urlManager' => ['class' => 'yii\web\UrlManager'],
			            'assetManager' => ['class' => 'yii\web\AssetManager'],
			            'security' => ['class' => 'yii\base\Security'],
			        ];

				Application::setContainer($config)
					Yii::configure(Yii::$container, $config);

				Application::run()
					<!-- 请求前事件处理 -->
					$this->state = self::STATE_BEFORE_REQUEST;
		            $this->trigger(self::EVENT_BEFORE_REQUEST);
					<!-- 请求处理 -->
		            $this->state = self::STATE_HANDLING_REQUEST;
		            $response = $this->handleRequest($this->getRequest());
					<!-- 请求后事件处理 -->
		            $this->state = self::STATE_AFTER_REQUEST;
		            $this->trigger(self::EVENT_AFTER_REQUEST);
					<!-- 发送相应 -->
		            $this->state = self::STATE_SENDING_RESPONSE;
		            $response->send();
					<!-- 请求结束 -->
		            $this->state = self::STATE_END;

		            return $response->exitStatus;

				abstract public function handlerRequest($request)
	


				Application::end($status = 0, $response = null)
					 if ($this->state === self::STATE_BEFORE_REQUEST || $this->state === self::STATE_HANDLING_REQUEST) {
			            $this->state = self::STATE_AFTER_REQUEST;
			            $this->trigger(self::EVENT_AFTER_REQUEST);
			        }

			        if ($this->state !== self::STATE_SENDING_RESPONSE && $this->state !== self::STATE_END) {
			            $this->state = self::STATE_END;
			            $response = $response ?: $this->getResponse();
			            $response->send();
			        }

			        if (YII_ENV_TEST) {
			            throw new ExitException($status);
			        }

			        exit($status);

		7. yii\web\Application 		        
			properties:
				$errorHandler
				$homeUrl
				$request
				$response
				$session
				$user

				$defaultRoute 		# 默认路由
				$catchAll 			# 捕捉全部请求，用于网站维护
				$controller 		# 当前控制器实例

			methods:
				bootstrap()
					$request = $this->getRequest();
			        Yii::setAlias('@webroot', dirname($request->getScriptFile()));
			        Yii::setAlias('@web', $request->getBaseUrl());

			        parent::bootstrap();

			    handlerRequest()
			    	

			    getHomeUrl()
			    	if ($this->_homeUrl === null) {
			            if ($this->getUrlManager()->showScriptName) {
			                return $this->getRequest()->getScriptUrl();
			            }

			            return $this->getRequest()->getBaseUrl() . '/';
			        }

			        return $this->_homeUrl;    
			    setHomeUrl()    

			    getReqeust()
			    getResponse()
			    getErrorHandler()
			    getSession()
			    getUser()

			    coreComponents()
			    	return array_merge(parent::coreComponents(), [
			            'request' => ['class' => 'yii\web\Request'],
			            'response' => ['class' => 'yii\web\Response'],
			            'session' => ['class' => 'yii\web\Session'],
			            'user' => ['class' => 'yii\web\User'],
			            'errorHandler' => ['class' => 'yii\web\ErrorHandler'],
			        ]);
			
