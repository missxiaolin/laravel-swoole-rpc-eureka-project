### laravel-swoole-rpc-eureka-project

### go rpc

[goRpc连接](https://github.com/missxiaolin/go-rpc)

### elk

[日志服务](https://github.com/missxiaolin/laravel-elk)

### 机器学习
[链接](https://github.com/missxiaolin/laravel-swoole-ml)

### Kong网关
[链接](https://github.com/missxiaolin/laravel-kong)

### 安装

~~~
docker pull logimethods/eureka
~~~

### 启动

~~~
docker run --name erueka -p 5000:5000 -e FLASK_DEBUG=1 logimethods/eureka
~~~

### 使用说明

安装
~~~
docker pull limingxinleo/docker-eureka:1.1.147
~~~

启动
~~~
docker run --name eureka -p 8081:8080 -d limingxinleo/docker-eureka:1.1.147
~~~

配置
~~~
# .env文件
APP_NAME=laravel  # 服务名
APP_URL=eureka.laravel.xiao  # 服务接口调用地址
EUREKA_BASE_URI=http://127.0.0.1:8081/  # Eureka地址
~~~
