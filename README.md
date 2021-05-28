# socket-server
###socket服务
* 启动 register、gateway、business 服务
* gateway、business 向register 注册自己的服务信息
* business 连接所有gateway 服务
* gateway接受客户端请求
* gateway把客户端请求send到随机的business进程服务
* business进程服务动用event进行事件处理
* business将处理好的数据send给gateway 进程
* gateway 理由 client-business 绑定关系将数据send给client
