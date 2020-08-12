TSMD base module for Yii2
=========================

TSMD 是基于 Yii2 的模块化设计的接口二次开发系统，基础模块包含以下子模块：

- 配置模块
- 用户模块
- 权限模块
- 日志模块
- 验证码模块
- 文档生成模块

## 安装

1. 安装 yii2-app-advanced
1. 安装以下 Codeception 相关模块
    - codeception/codeception
    - codeception/module-asserts
    - codeception/module-yii2
    - codeception/module-filesystem
    - codeception/module-rest
    - codeception/module-phpbrowser
    - codeception/verify
1. 安装 yii2-tsmd-base，安装完完成后路径应为 ../yii2-app-advanced/thirsight/yii2-tsmd-base
1. 将 yii2-tsmd-base 下的 api-sample 目录复制至 ../yii2-app-advanced 根目录，同时重命名为 api

## 扩展模块数据迁移

1. 命令行进入 ../thirsight/yii2-tsmd-base/console 目录
1. 执行 ./yii migrate-base/up

## 扩展模块测试

1. 命令行进入 ../yii2-app-advanced/api 目录
1. 执行 ./codecept run api -g mygroup -d

## 扩展模块配置文件

