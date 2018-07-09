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

**生产环境
1. composer install --no-dev
2. app.debug=false
3. php artisan config:cache
4. php artisan router:cache
5. php artisan optimize --force
6. 去除没必要中间件、安装redis可视化、加opcache