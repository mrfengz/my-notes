#### JWT
    
    JWT是json web token缩写。它将用户信息加密到token里，服务器不保存任何用户信息。服务器通过使用保存的密钥验证token的正确性，只要正确即通过验证。基于token的身份验证可以替代传统的cookie+session身份验证方法。

    JWT由三个部分组成：header.payload.signature

#### 示例

    以下示例以JWT官网为例
    header部分：
    {
      "alg": "HS256",
      "typ": "JWT"
    }
    对应base64UrlEncode编码为：eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9
    说明：该字段为json格式。alg字段指定了生成signature的算法，默认值为 HS256，typ默认值为JWT

    payload部分：
    {
      "sub": "1234567890",
      "name": "John Doe",
      "iat": 1516239022
    }
    对应base64UrlEncode编码为：eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ
    说明：该字段为json格式，表明用户身份的数据，可以自己自定义字段，很灵活。sub 面向的用户，name 姓名 ,iat 签发时间。例如可自定义示例如下：

    {
        "iss": "admin",          //该JWT的签发者
        "iat": 1535967430,        //签发时间
        "exp": 1535974630,        //过期时间
        "nbf": 1535967430,         //该时间之前不接收处理该Token
        "sub": "www.admin.com",   //面向的用户
        "jti": "9f10e796726e332cec401c569969e13e"   //该Token唯一标识
    }
    signature部分：
    HMACSHA256(
      base64UrlEncode(header) + "." +
      base64UrlEncode(payload),
      123456
    ) 
    对应的签名为：keH6T3x1z7mmhKL1T3r9sQdAxxdzB6siemGMr_6ZOwU

    最终得到的JWT的Token为(header.payload.signature)：eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.keH6T3x1z7mmhKL1T3r9sQdAxxdzB6siemGMr_6ZOwU
    说明：对header和payload进行base64UrlEncode编码后进行拼接。通过key（这里是123456）进行HS256算法签名。


#### JWT使用流程

    初次登录：用户初次登录，输入用户名密码
    密码验证：服务器从数据库取出用户名和密码进行验证
    生成JWT：服务器端验证通过，根据从数据库返回的信息，以及预设规则，生成JWT
    返还JWT：服务器的HTTP RESPONSE中将JWT返还
    带JWT的请求：以后客户端发起请求，HTTP REQUEST
    HEADER中的Authorizatio字段都要有值，为JWT
    服务器验证JWT

#### 代码
    参考目录codes/php/jwt.php