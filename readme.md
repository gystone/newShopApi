```
git clone
```
```
composer install
```
配置数据库 
```
php artisan migrate --seed
```

缓存说明
https://github.com/GeneaLabs/laravel-model-caching

功能说明：
1. 分页、搜索、排序
2. 缓存
3. 微信相关封装
4. jwt
5. 七牛
7. cors跨域支持
8. lang语言包


生产环境
1. composer install --no-dev
2. app.debug=false
3. php artisan config:cache
4. php artisan router:cache
5. php artisan optimize --force
6. 去除没必要中间件、安装redis可视化、加opcache
https://segmentfault.com/a/1190000011569012