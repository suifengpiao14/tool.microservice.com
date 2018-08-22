天气预报项目
========================
通过采集中国天气网数据，提供获取实时天气数据和全天天气数据接口。
项目采用slim微框架开发、redis做缓存。通过自主计算日出日落、增加各省市区联动关系，解决中国天气网数据不全情况

注意事项
--------
 1. 使用 php 7.1.3以上版本
 2. 安装php redis 扩展
 3. 开发、测试环境需要设置php 环境变量 APP_ENV 分别为 dev、test 正式环境默认为prod
  # tool.microservice.com
