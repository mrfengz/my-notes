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