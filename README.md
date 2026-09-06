# Sample

这是 `e29denghy/sample` 的 Laravel 13 / PHP 8.5 迁移版本。

## 技术基线

- Laravel 13.23
- PHP 8.5+
- EasyWeChat / `overtrue/laravel-wechat` 8.x
- Vite 8

旧项目的用户注册、邮箱激活、登录、密码重置、用户管理、授权策略和微信公众号回调已迁移到 Laravel 13 的应用结构。原有数据库迁移文件名保留，用于让已有生产数据库的 `migrations` 记录继续可识别。

## 本地运行

```bash
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan serve
```

运行验证：

```bash
php artisan test
php artisan about
```

微信配置只从 `.env` 读取：`WECHAT_OFFICIAL_ACCOUNT_APPID`、`WECHAT_OFFICIAL_ACCOUNT_SECRET`、`WECHAT_OFFICIAL_ACCOUNT_TOKEN` 和 `WECHAT_OFFICIAL_ACCOUNT_AES_KEY`。不要把实际密钥提交到仓库。

## 服务器发布顺序

服务器应先准备 PHP 8.5、Composer 2、Node.js 20+ 和 Web 服务器，并把站点根目录指向 `public/`。在项目目录执行：

```bash
git fetch origin
git checkout master
git pull --ff-only origin master
/path/to/php8.5 /path/to/composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
/path/to/php8.5 artisan migrate --force
/path/to/php8.5 artisan optimize:clear
/path/to/php8.5 artisan config:cache
/path/to/php8.5 artisan route:cache
/path/to/php8.5 artisan view:cache
```

先在预发布目录执行迁移预检和健康检查，再切换 Web 服务器或进程管理器到新发布目录。生产 `.env`、数据库备份、队列进程和 PHP-FPM 重载必须沿用服务器现有的受控配置，不从 Git 覆盖。

## 内容编辑

网页文案和文章素材整理见 [内容编辑第一、二阶段](docs/editorial/README.md)，写作口吻以 [写作原则](docs/editorial/voice.md) 为准。
