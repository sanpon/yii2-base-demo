### 环境配置
    配置nginx虚拟域名，添加重写规则
    rewrite ^(.*)$ /index.php?r=$1 last; //前端重写
### 项目目录介绍
    backend 后端模块
        controllers 同步请求模块
        entry api接口
            v1.0 api版本
                xxxx API模块
        models 后端数据库模型 继承自基础数据库模型(后端数据服务层)
        views 后端视图模块
    common 公共模块
        components 自定义组件
        config 公共配置
        cores 业务框架核心
        entry 公共服务入口
        language i18N语言配置
        library 业务核心类库
        models 数据库模型基类
        override yii2类重写
        plugins 第三方插件
        services 公共服务层
    config 系统配置项目
    console 控制台模块
    entry 业务框架统一入口
    frontend 前端模块
        controllers 同步请求模块
        entry api接口
            v1.0 api版本
                xxxx API模块
        models 前端数据库模型 继承自基础数据库模型(前端数据服务层)
        views 前端视图模块
    runtime 运行环境日志
    vendor yii2框架
    web 项目入口
    