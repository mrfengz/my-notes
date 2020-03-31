<?php
declare(strict_types=1);

function sum(int ...$ints)
{
	return array_sum($ints);
}

// echo sum(12, 6);

function returnIntValue(int $value): int
{
	return $value + 1.5;
}

// print(returnIntValue(2));

// 定义严格模式 declare(strict_types=1);
// 1.参数类型 int float bool string interfaces array callable 
// 2.函数返回值类型  function sum(int ...$ints): int (){...}
// 
// 3. Null合并运算符 ??
// 4.飞船运算符 <=> 第一个表达式比较第二个表达式，分别小于、等于、大于时返回 -1,0,1
// 5. 常量数组。可以通过define()定义了。  define('animals', ['dog', 'cat', 'bird'])
// 6. 匿名类 new class implements Logger
// 7. Closure::call() 加入到临时绑定的对象范围
// 8. 国际字符 IntlChar类，需要安装Intl扩展
// 9. SCPRNG， 跨平台的方式加密安全整数和字符串 random_bytes(), random_int()
// 10. assert
// 11. use   use name1\class1; use name1\class2;  use name1\{class1, class2};
// 12. 错误处理程序。 
// 		现在大多数错误将通过抛出异常错误处理。
// 		类似于冒泡，直到找到第一个catch,如果找不到，调用set_exception_handler()处理，还没有，异常会被转化为致命错误，并像传统错误那样处理。
// 	13. intdiv() 整数除法	
// 	14. Session选项 session_start()函数接受数组覆盖在php.ini中设置的会话配置指令	
// 		

class MathOperators
{
	protected $n = 10;

	public function doOpeartion(): string
	{
		try {
			$value = $this->n % 0;
			return $value;
		} catch(DivisionByZeroError $e) {
			return $e->getMessage();
		}
	}
}
$math = new MathOperators();

echo $math->doOpeartion();

// bindTo加入到绑定对象范围
class A
{
	private $x = 1;
}

// -php7
$getValue = function(){
	return $this->x;
};

// 绑定
$value = $getValue->bindTo(new A, 'A');
print($value());

// php7+
print("<br> php7+<br>");
echo $getValue->call(new A());

// 匿名类
interface Logger
{
	public function log(string $msg);
}

class Application 
{
	private $logger;

	public function getLogger(): Logger
	{
		return $this->logger;
	}

	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}
}

$app = new Application();
$app->setLogger(new class implements Logger{
	public function log(string $msg) {
		print($msg);
	}
});

$app->getLogger()->log("my first log message");


// 常量数组
define('animals', ['bird', 'dog', 'cat']);
print(animals[2]);

// 太空船运算符
print(1.5 <=> 1.5); print("<br>");
print(1.5 <=> 1.6); print("<br>");
print(1.5 <=> 1.4); print("<br>");

