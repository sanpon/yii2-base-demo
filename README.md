### 环境配置
    配置nginx虚拟域名，添加重写规则
    rewrite ^(.*)$ /index.php?r=$1 last; //前端重写