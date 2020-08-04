## 链接
	(github)[https://github.com/airbnb/javascript]

## 短写法
	//bad
	const a={
		name: 'jack',
		addValue:function(value) {
			return value+1;
		}
	}	

	//good
	const name='jack';
	const a={
		jack,
		addValue(value){
			return value+1;
		}
	}

## copy
	不要使用Object.assign 
	//bad
	const original={a:1, b:2}
	const copy=Object.assign(original, {c: 3});
	
	//bad
	const copy = Object.assign({}, original, { c: 3 }); // copy => { a: 1, b: 2, c: 3 }	

	//good
	const copy={...original, c:3}
	const {a, ...noA} = copy; //noA = {b:2, c:3}

## array

	copy ... 
	const a = [xxx];
	const b = [...a];

	类数组对象->数组 Array.form
	const arrLike = {0: 'a', 1: 'b', 2: 'c', length: 3};
	//bad
	const c=Array.prototype.slice.call(arrLike)
	// good
	const c = Array.from(arrLike);

	对象转为数组
	var a = document.QuerySelectorAll('.div');
	//good
	var b = Array.from(a);
	// best.可以避免循环
	var b = [...a];

	添加元素
	//bad
	arr[arr.length] = 1;
	//good
	arr.push(1)

	回调函数中使用return
	a.map((x)=>x+1); a.filter/reduce...


	解构
		var arr = [1, 2, 3, 4];
		var [a, b] = arr; //a=1, b=2

	添加元素
		arr.push('xxx')	

## object 

	//good
	function getFullName(user) {
		const {firstName, lastName} = user;
		return `${firstName} ${lastName}`;
	}

	//best
	function getFullName({firstName, lastName}) {
		return `${firstName} ${lastName}`;
	}


	尽量不要直接调用prototype上的方法
		object.hasOwnProperty('xxx') 
		//good 或者封装成一个函数
		Object.prototype.hasOwnProperty.call(object, 'xxx')


## string 
	
	使用''定义

	模板拼接
		`How are you, ${name}?`

## function

	使用命名函数表达式定义函数
		const short = function longUniqueMoreDescriptiveLexicalFoo(){

		}		

	立即执行函数用括号包裹起来
		(function(){
			console.log('this is just a test.');	
		}())	

	函数参数，不要使用 arguments
		function concatenateAll(...args) {
			return args.join(' ');
		}	

	使用默认值
		//bad
		function handleThings(opt) {
			opts = opts || {};
			xxx
		}
		
		//good
		function handleThings(opts = {}){

		}	

	使用箭头函数
		1.参数用()括起来
		2.如果函数复杂，用括号括起来，并用return返回，如果简单且无副作用，可以省略。

## classes && constructors
	
	// 类和构造方法
	class Queue {
		constructor(content = []) {
			this.queue = [...content];
		}
		pop(){
			const value=this.queue[0];
			this.queue.splice(0, 1);
			return value;
		}
	}		

	//继承
	class PeekQueue extends Queue{
		//继承父类的构造方法
		constructor(...args) {
			super(...args);
		}

		peek(){
			return this.queue[0];
		}
	}

	// 链式操作
	class Jedi {
		jump(){
			this.jump=true;
			return this;
		}

		setHeight(height) {
			this.height=height;
			return this;
		} 
	}

	//toString
	class Jedu{
		constructor(options = {}){
			this.name = options.name || 'no name';
		}

		getName() {
			return this.name;
		}

		// `` 中间可以执行方法，与变量写法类似
		toString() {
			return `Jedu - ${this.getName()}`;
		}

		//静态方法
		static getAge() {
			return (new Date()).getFullYear() - 1999;
		}

		//非静态方法，尽量用this来调用其中的参数
	}

## modules
	
	尽量使用 import/export，这个是未来啊
		//good 比require好
		import AirbnbStyleGuide from './AirbnbStyleGuide';
		export default AirbnbStyleGuide.es6;	

		//best
		import { es6 } from './AirbnbStyleGuide'
		export default es6;

		//不要直接从导入导出
		//bad 
		import {es6 as default} from './AirbnbStyleGuide'

		//good
		import { es6 } from './AribnbStyleGuide'
		export default {es6};

	//export import简介用法
		//a.js
		var sex='boy';
		var echo=function(value) {
			console.log(value);
		}

		export {sex, echo}; 	//导出多个变量

		//b.js
		import {sex, echo} from './a';	//导入a.js文件
		console.log(sex);	//boy
		echo(sex);	//boy

		import default sex

## 循环和迭代
	
	优先使用高级函数，不适用循环(for for..in for-of)

	高阶函数
		循环数组： map() /every() / find() / findIndex() / reduce() / some() / forEach() ...
		循环对象： Object.keys() / Object.values() / object.entries() 来生成数组以便循环

		const numbers = [1, 2, 3, 4, 5];
		//sum
		const sum = numbers.reduce((total,num) => total+num, 0);

		// 每个元素+1
		const increasedByOne = numbers.map((num) => num+1);

## 对象属性
	
	使用 . object.key
	如果key是一个变量，使用[]

	function getProp(key) {
		return Jack[key];
	}		

	Jack.name

## 变量

	尽量使用 const 和 let

## 注释

	单行 //
	多行 /**

	注释内容与符号之间，留一个空格，便于阅读

	使用 FIXME 和 TODO 注释要解决和做的			

