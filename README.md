# CI-worker
基于workerman翻作的CI框架，与CI框架的函数保持兼容，结构保持一致。目前仅支持CI的model和controll，view并没有做支持。
#如何使用
参考CI框架使用
route由于效率问题被固定为‘test/test’这种类型。并没有引入Route和URL类，
理论上将原来的CI框架中的model，controll，config，help，lib等复制到对应的文件夹即可使用。
部分CI的类库没有默认加入到框架中，可自行放入，但切记涉及到core中未包含的类对象是无法访问的。
Log，Input，Output进行了深度优化和CI原本的不一致
workeman进行了少量的修改，主要为Http这个文件的一些修改
#优化
不要在你的控制器方法中使用load，load应该被移到__construct中，__CI_construct为了保留CI的习惯这个方法在每次调用控制器方法前都会执行，同样这里只能做验证获取数据等操作不要使用load
CI原版代码中大量使用了魔术方法，尽量不要使用魔术方法非常影响效率，在__construct中尽量缓存数据。
#性能
i3 4核 8G php7 event.so 环境下，裸跑workeman http协议请求能达到11W/s
CI-worker简易逻辑，仅route获取get，post，header等数据测试结果在8.5W/s。
秒杀CI框架。
另外为了测试workerman http解析时效率问题，用swoole框架进行的对比发现swoole的http服务器性能为10W/s还略不如workerman。

  
