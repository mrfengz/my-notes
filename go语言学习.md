## go语言结构
	
	基础组成
		1.包声明
		2.引入包
		3.函数
		4.变量
		5.表达式和语句
		6.注释

	数据类型
		布尔型
		数字类型
		字符串类型
		派生类型
			指针类型
			数组类型
			结构化类型
			channel类型
			函数类型
			切片类型
			接口类型
			Map类型	

	变量声明
		1. var 变量名 类型
			var indetifier1, identifier2 type		

		2. var v_name = value # 根据值自动判断变量类型

		3. v_name := value 	# 省略var, 只能在函数体中出现
			intval := 1
			intval, intval2 := 1, 2

	常量
		const identifier [type] = value	

		const LENGTH int = 10
		const a, b, c = 1, false, "str"

		作为枚举
			const (
				Unkonwn = 0
				a = "abc"
				b = len(a)
				c = unsafe.Sizeof(a)
			)

	数组
		var 变量名 [长度] 类型
		var balance [10] float32		

		初始化：
			var balance = [5]float32{1000, 5.0, 2.6, 7.0, 33.0}

		不定长度数组初始化
			var balance = [...]float32{1000, 5.0, 2.6, 7.0, 33.0}	
			
	闭包函数
		后边的 func() int 是返回类型说明
		func getSequence() func() int {
			i := 0
			return func() int {
				i+=1
				return i
			}
		}

		// 闭包使用方法
		func add(x1, x2 int) func(x3 int,x4 int)(int,int,int)  {
		    i := 0
		    return func(x3 int,x4 int) (int,int,int){ 
		       i++
		       return i,x1+x2,x3+x4
		    }
		}

	函数作为方法
		
		/* 定义结构体 */
		type Circle struct {
		  radius float64
		}

		func main() {
		  var c1 Circle
		  c1.radius = 10.00
		  fmt.Println("圆的面积 = ", c1.getArea())
		}

		//该 method 属于 Circle 类型对象中的方法
		func (c Circle) getArea() float64 {
		  //c.radius 即为 Circle 类型对象中的属性
		  return 3.14 * c.radius * c.radius
		}	