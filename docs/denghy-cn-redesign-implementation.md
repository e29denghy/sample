# denghy.cn 个人网站改版实施方案

> 文档状态：实施基线  
> 版本：v1.0  
> 更新日期：2026-08-03  
> 适用项目：`e29denghy/sample`  
> 目标运行环境：Laravel 13、PHP 8.5

## 实施进度（2026-08-05）

第一轮实现已在 `agent/upgrade-laravel13-php85` 开始，当前完成阶段 1–3 的可运行骨架，并提供阶段 4 的只读 API：

- 已完成：新 SSR 公共布局、Tailwind/Vite 入口、管理员 Gate、后台文章/项目表单、登录限流、关闭公开注册和用户目录；
- 已完成：文章、不可变修订、标签、项目、媒体、重定向、审计、Outbox 迁移和模型关系；
- 已完成：草稿保存、服务端 Markdown 预览、发布事务、修订隔离、301 重定向、首页/文章/项目/Now/About、SEO 元数据和 JSON-LD；
- 已完成：RSS 2.0、Atom、sitemap、ETag/Last-Modified，以及网站正文共用的 `/api/v1` 文章/标签/项目只读接口；
- 已验证：PHP 8.5 下迁移、前端构建、Pint、5 个功能测试通过。
- 迭代完成：接入 Inertia Laravel + Vue 3 + Vite Vue 插件，管理员概览、文章和项目后台已迁移到 Inertia 页面；公共内容页暂保留 Blade，以保持首屏 SEO，待 SSR 运行环境就绪后再迁移。
- 迭代完成：新增可重复执行的 `ContentSeeder`，补充 InterAPI、KAIWU、MyAiWorkFlow、福宝英语角、程序员的个人修养 / 工程现场五个项目档案，以及五篇基于项目记忆知识库的公开工程文章；Seeder 只创建不存在的 slug，不覆盖人工编辑内容。

本批次尚未接入真实媒体上传/封面选择、公众号草稿投递、远端核对和个人小程序客户端；这些能力依赖后续内容录入、公众号权限预检和部署环境配置，不能用本地测试替代。

## 1. 文档目的

本方案用于指导 `denghy.cn` 从 Laravel 教程示例站改造成个人工程内容站，并建立网站、RSS、微信公众号和个人小程序共用的一套内容发布体系。

改版完成后，网站不再以公开注册和用户目录为中心，而以以下内容为核心：

- 技术文章与工程现场笔记；
- 可验证的项目案例；
- RSS / Atom 全文订阅；
- 微信公众号草稿分发；
- 个人小程序文章读取；
- 单站长、可审计、可回滚的内容后台。

本方案只规定改版实施路径。任何生产数据迁移、账号关闭、服务器配置修改和微信公众号发布，都必须在对应阶段完成预检后执行。

## 2. 产品定位

### 2.1 站点名称

**程序员的个人修养 / 工程现场**

英文说明：

> Production notes on AI, content systems and Laravel.

### 2.2 核心主张

> 把复杂的 AI、内容与业务流程，做成可验证、可发布、可维护的系统。

首页介绍文案：

> 我在 AI 教学、内容生产与 Laravel 工程的交叉处工作。这里记录一个系统从问题、约束和决策，到验证、上线与复盘的完整过程。

### 2.3 目标受众

1. AI 应用、教育内容、搜索和 Laravel 生产系统方向的工程师与技术负责人；
2. 潜在合作伙伴、客户和未来团队成员；
3. 公众号、小程序和泛技术读者。

### 2.4 内容支柱

主栏目不按 PHP、Vue、AI 等技术栈划分，而按解决的问题划分：

1. **让 AI 可评估**：检索、重排、权限、成本、候选资格、生成兜底；
2. **让内容可发布**：绘本、音视频、来源、审核、Manifest、Outbox、对账；
3. **让系统可上线**：Laravel 升级、API 契约、多租户、部署、回滚、生产复盘；
4. **让工作可积累**：JIXU、项目记忆、任务执行、Fobo 和个人产品实验。

Laravel、PHP、Vue、SQLite、DashVector 等作为标签使用。

## 3. 当前基线

### 3.1 可复用部分

- Laravel 13、PHP 8.5、Composer、Vite 运行底座；
- 现有 `User`、Session 登录、密码重置和 `is_admin` 字段；
- EasyWeChat 8 配置和现有 `/wechat` 回调入口；
- Laravel Policy、Validation、Mail、Filesystem 等基础能力；
- `/up` 健康检查、测试/Pint 基线和现有部署路径。

### 3.2 需要重建部分

- 首页、About、Help 等仍属于旧教程示例页面；
- 没有文章、项目、标签、媒体、修订和渠道发布模型；
- 没有 RSS、Atom、sitemap 和小程序文章 API；
- 没有内容管理后台和真正的管理员发布门禁；
- 公共页面仍加载旧 Bootstrap/jQuery 编译产物；
- 微信能力目前只有消息回调，没有公众号文章草稿发布。

### 3.3 实施前提

正式开发前必须完成：

- 拉取远端最新代码，确认本地 `master` 与已部署版本一致；
- 记录当前生产 HEAD、PHP 版本、数据库驱动和迁移状态；
- 备份生产数据库、上传文件、`.env` 和当前可运行版本；
- 清点生产用户、管理员和未激活账号；
- 验证 `/`、`/up`、登录和 `/wechat` 当前基线；
- 从最新 `master` 创建独立改版分支。

不得从当前未对齐远端的升级分支直接继续发布改版。

## 4. 已锁定的技术决策

### 4.1 改造方式

保留现有仓库、Laravel 13 运行底座和部署历史，重新建设产品层。当前不创建第二个独立 Laravel 项目。

如果基线同步后发现 Laravel 13 升级代码无法稳定运行，才在同一仓库的迁移分支中引入全新 Laravel 13 骨架，并逐项迁移 `User`、EasyWeChat 配置和部署能力。

### 4.2 前端方案

- 公共页面：Blade + Tailwind CSS 4 + Vite；
- 管理后台：Blade + Tailwind，优先使用服务端表单；
- 首版不引入完整 SPA；
- 首版 Markdown 编辑器采用“文本编辑 + 服务端预览”；
- 只有出现明确交互需求时，才增加 Alpine 或独立编辑器组件。

选择服务端渲染是为了降低复杂度、提高首屏速度，并让 SEO、RSS 和文章分享信息保持一致。

### 4.3 数据库与存储

- 所有新能力只通过新增迁移实现，不修改已经执行过的历史迁移；
- 迁移保持 SQLite/MySQL 可兼容，不依赖数据库专有枚举和 JSON 查询；
- 媒体通过 Laravel Filesystem 管理；
- 生产文件目录必须独立于代码发布目录并纳入备份；
- 后续可通过配置切换到 OSS/S3 兼容存储，不改变业务模型。

### 4.4 发布原则

- 网站数据库是唯一正文事实来源；
- 网站发布、公众号发布是两个独立状态；
- RSS 和小程序只读取网站已发布版本；
- 公众号首版只生成草稿，必须人工确认后发布；
- 外部渠道失败不能回滚网站发布；
- 外部投递采用“至少一次投递 + 幂等消费者”，不宣称严格 exactly-once。

## 5. 信息架构

### 5.1 公共导航

```text
首页 / 文章 / 项目 / Now / About / RSS
```

### 5.2 页面职责

| 页面 | 路径 | 职责 |
|---|---|---|
| 首页 | `/` | 定位、精选案例、最新文章、工作原则、Now、订阅入口 |
| 文章列表 | `/articles` | 按主题、类型、标签浏览已发布文章 |
| 文章详情 | `/articles/{slug}` | 展示稳定正文版本、验证状态和关联项目 |
| 标签 | `/tags/{slug}` | 按技术或主题标签浏览内容 |
| 项目列表 | `/projects` | 展示代表性项目与状态 |
| 项目详情 | `/projects/{slug}` | 展示问题、约束、决策、证据、结果和相关文章 |
| Now | `/now` | 展示当前重点、最近交付和正在验证事项 |
| About | `/about` | 个人叙事、工作原则、经历与合作方式 |
| RSS | `/rss.xml` | RSS 2.0 全文 Feed |
| Atom | `/atom.xml` | Atom 全文 Feed |
| Sitemap | `/sitemap.xml` | 搜索引擎站点地图 |

### 5.3 首页内容顺序

1. 核心主张和工作交叉面；
2. “查看工程案例”和“订阅 RSS”两个主要入口；
3. 三个精选项目案例；
4. 最新工程文章；
5. 三到四条工作原则；
6. 当前正在做什么；
7. 公众号二维码、小程序和 RSS 入口。

### 5.4 文章结构

2026-09-06 编辑调整：以下要素用作内部素材检查表，按题材选择，不要求成为每篇文章的固定章节。公开文案遵循 [写作原则](editorial/voice.md)。

1. 结论摘要；
2. 适用版本和边界；
3. 问题与约束；
4. 方案选择及放弃项；
5. 实现与验证证据；
6. 结果及未验证部分；
7. 可复用清单；
8. 关联项目和后续文章。

文章首部展示首次发布时间与当前更新时间；版本、编辑核对日期放在文末说明。内容管理仍保留以下字段，不把统一“已验证”徽章作为实测证明：

- 首次发布时间；
- 当前版本更新时间；
- 最后验证时间；
- 内容状态：有效、需复核、已过时或归档。

## 6. 系统架构

```text
管理员后台
    │
    ├── 文章草稿 / 项目 / 标签 / 媒体
    │
    └── 发布事务
          ├── 冻结 Article Revision
          ├── 切换 published_revision_id
          └── 写入 Outbox Event
                    │
                    ├── 清理网页、RSS、Atom、Sitemap 缓存
                    ├── 小程序 API 读取新版本
                    └── 创建微信公众号草稿任务
                                │
                                └── 人工检查并发布
```

### 6.1 网站发布事务

发布文章时，在同一个数据库事务内完成：

1. 校验文章标题、摘要、正文、slug、媒体和 SEO 字段；
2. 将 Markdown 渲染成经过白名单清理的 HTML；
3. 生成不可变文章修订版本；
4. 计算内容哈希；
5. 更新文章的 `published_revision_id`；
6. 首次发布时写入 `first_published_at`；
7. 写入 `ArticlePublished` Outbox 事件；
8. 提交事务。

事务提交之后，异步消费者负责缓存和外部渠道操作。

### 6.2 已发布文章再次编辑

- 已发布版本继续对公众可见；
- 新编辑内容保存为新的草稿修订；
- 只有再次点击发布，网站才切换到新修订；
- RSS 和小程序随后读取新修订；
- 已经发布的公众号文章不会被静默覆盖；
- 如需公众号更新，生成与新修订关联的新草稿。

## 7. 数据模型

### 7.1 `articles`

保存文章稳定身份和发布指针，不直接保存可变正文。

| 字段 | 说明 |
|---|---|
| `id` | 内部主键 |
| `public_id` | 稳定 ULID，用于 Feed GUID 和 API |
| `author_id` | 作者用户 ID |
| `slug` | 当前公开地址，唯一 |
| `status` | `draft/review/published/archived` |
| `visibility` | `public/private/unlisted` |
| `current_draft_revision_id` | 当前编辑版本 |
| `published_revision_id` | 当前公开版本 |
| `first_published_at` | 首次发布时间 |
| `published_at` | 当前版本发布时间 |
| `archived_at` | 归档时间 |
| `created_at/updated_at` | 记录时间 |

修订表与文章表存在双向引用，迁移时先建立基础表，再以追加迁移或延迟外键方式增加发布指针。

### 7.2 `article_revisions`

| 字段 | 说明 |
|---|---|
| `id` | 修订主键 |
| `article_id` | 所属文章 |
| `version` | 文章内递增版本号 |
| `title` | 标题 |
| `excerpt` | 摘要 |
| `markdown` | Markdown 原稿 |
| `rendered_html` | 已清理的 HTML |
| `content_hash` | 稳定内容哈希 |
| `seo_title` | SEO 标题 |
| `seo_description` | SEO 描述 |
| `cover_media_id` | 封面媒体 |
| `verification_status` | `valid/review_required/outdated` |
| `verified_at` | 最后验证时间 |
| `created_by` | 创建修订的管理员 |
| `created_at` | 修订创建时间 |

唯一约束：`article_id + version`。

### 7.3 其他核心表

| 表 | 用途 |
|---|---|
| `tags`、`article_tag` | 技术和主题标签 |
| `series` | 系列文章及排序，可在第二轮启用 |
| `projects` | 项目案例、状态、正文、证据和精选顺序 |
| `project_article` | 项目与文章关联 |
| `media_assets` | 文件、哈希、尺寸、alt、来源、版权和衍生图 |
| `article_redirects` | slug 变更后的永久跳转 |
| `channel_deliveries` | 每个文章版本在外部渠道的状态 |
| `external_media` | 本地图片与公众号媒体 ID 映射 |
| `outbox_events` | 可靠异步投递 |
| `audit_logs` | 登录、编辑、发布和渠道操作审计 |

### 7.4 `channel_deliveries`

关键字段：

- `channel`：如 `wechat_official_account`；
- `article_id`、`article_revision_id`；
- `action`：`create_draft/publish/reconcile`；
- `status`；
- `remote_id`；
- `template_version`；
- `idempotency_key`；
- `attempts`、`last_error_code`、`last_error_message`；
- `submitted_at`、`published_at`、`reconciled_at`。

公众号渠道状态：

```text
pending
→ rendering
→ submitted_draft
→ awaiting_human_publish
→ published
```

异常状态：

```text
failed / needs_reconcile / cancelled
```

### 7.5 `outbox_events`

建议字段：

- `event_type`；
- `aggregate_id`、`aggregate_version`；
- `payload`；
- `idempotency_key`；
- `status`、`attempts`；
- `available_at`、`locked_at`；
- `last_error`、`processed_at`。

`idempotency_key` 必须有唯一索引。

公众号草稿幂等键建议：

```text
wechat:draft:{article_public_id}:{revision_id}:{template_version}
```

## 8. 路由与接口

### 8.1 公共网页

```text
GET /articles
GET /articles/{slug}
GET /tags/{slug}
GET /projects
GET /projects/{slug}
GET /now
GET /about
GET /rss.xml
GET /atom.xml
GET /sitemap.xml
```

旧地址变更时使用 301，不直接删除已有可访问地址。

### 8.2 管理后台

```text
GET    /admin
GET    /admin/articles
POST   /admin/articles
GET    /admin/articles/{article}/edit
PATCH  /admin/articles/{article}
POST   /admin/articles/{article}/preview
POST   /admin/articles/{article}/submit-review
POST   /admin/articles/{article}/publish
POST   /admin/articles/{article}/archive
POST   /admin/articles/{article}/channels/wechat/draft
POST   /admin/channel-deliveries/{delivery}/retry
POST   /admin/channel-deliveries/{delivery}/reconcile
```

所有后台路由必须同时通过 `auth` 和管理员 Gate，不得只依赖隐藏菜单。

### 8.3 小程序只读 API

```text
GET /api/v1/articles
GET /api/v1/articles/{public_id-or-slug}
GET /api/v1/tags
GET /api/v1/tags/{slug}/articles
GET /api/v1/projects
GET /api/v1/projects/{slug}
```

文章响应至少包含：

- `id`、`slug`、标题、摘要、封面；
- `published_at`、`updated_at`、`verified_at`；
- `revision_version`、`content_hash`；
- 经过清理、适合小程序 `rich-text` 的 HTML；
- 媒体、标签和关联项目；
- `canonical_url`、分享标题和分享图。

列表使用游标分页。详情响应基于 `content_hash` 输出 ETag，并只返回 `published + public` 内容。

## 9. RSS、Atom 与 SEO

### 9.1 Feed 规则

- 默认输出最近 20 篇公开文章的完整正文；
- RSS GUID 使用 `urn:denghy:article:{public_id}`；
- Atom `published` 保持首次发布时间；
- Atom `updated` 使用当前发布修订时间；
- 所有文章和图片 URL 转换成绝对 HTTPS 地址；
- HTML `<head>` 加入 RSS 和 Atom 自动发现链接；
- Feed 只查询 `published_revision_id` 指向的内容；
- Feed 内容哈希作为 ETag；
- 最近一次发布或更新作为 Last-Modified。

上线前使用 RSS/Atom 校验器验证 XML、日期、时区、HTML 和特殊字符。

### 9.2 页面 SEO

每个文章页必须输出：

- canonical URL；
- title 与 meta description；
- Open Graph；
- Article JSON-LD；
- 发布时间和更新时间；
- 稳定分享图；
- 相关文章和项目关联。

首页输出 WebSite/Person JSON-LD。`sitemap.xml` 仅包含公开、可索引内容。

slug 变化时写入 `article_redirects` 并返回 301，RSS GUID 保持不变。

## 10. 微信公众号发布

### 10.1 首版流程

```text
选择网站已发布版本
→ 渲染微信专用 HTML
→ 上传封面和正文图片
→ 创建公众号草稿
→ awaiting_human_publish
→ 人工检查公众号后台
→ 人工发布并核对结果
```

微信渲染器需要完成：

- Markdown 转换为微信允许的 HTML；
- 套用有版本号的内联样式模板；
- 清理微信不支持的标签和属性；
- 替换正文图片；
- 设置标题、作者、摘要和封面；
- 将“阅读原文”指向网站 canonical URL；
- 记录文章修订、模板版本和远端草稿 ID。

### 10.2 权限门禁

接入前必须在真实公众号后台确认：

- 账号类型与认证状态；
- 草稿接口和发布接口权限；
- IP 白名单、App ID 和 Secret；
- 素材接口能力和额度；
- “阅读原文”与外链限制。

即使账号拥有自动发布权限，第一阶段也不启用自动群发。

### 10.3 失败处理

- 网络错误、5xx、限流：指数退避并增加随机抖动；
- access token 失效：刷新一次后重试；
- 内容校验失败、接口无权限：停止重试并提示人工处理；
- 达到最大重试次数：进入失败队列，可在后台人工重投；
- 请求超时且远端结果未知：进入 `needs_reconcile`，先核对远端，禁止盲目重试；
- 日志记录错误码和关联 ID，但不得记录 Secret 或完整 access token。

## 11. 个人小程序

### 11.1 MVP 范围

- 文章列表；
- 文章详情；
- 标签筛选；
- 项目案例；
- 分享卡片；
- 跳转或复制网站 canonical URL。

首版不包含：

- 登录；
- 评论；
- 收藏；
- 阅读进度；
- 会员系统；
- 单独的小程序 CMS。

### 11.2 一致性要求

同一篇文章在网站、RSS 和小程序中必须具有相同的：

- `article public_id`；
- `revision_version`；
- `content_hash`；
- `canonical_url`。

小程序不得直接连接网站数据库，也不得维护另一份独立正文。

## 12. 管理后台与权限

### 12.1 账号策略

- 关闭公开注册；
- 关闭公开用户列表和用户资料页；
- 关闭公开邮箱和 Gravatar 输出；
- 保留站长管理员账号；
- 关闭前先完成生产账号清点和影响确认。

### 12.2 权限拆分

即使首版只有一个管理员，也定义以下能力：

- 编辑文章；
- 发布网站文章；
- 创建公众号草稿；
- 确认公众号已发布；
- 管理渠道配置；
- 查看、重试和核对失败任务。

### 12.3 安全要求

- 登录启用明确的速率限制；
- 管理员启用 MFA/Passkey 或等效二次验证；
- Cookie 使用 Secure、HttpOnly 和合理 SameSite 设置；
- 发布和渠道操作写入审计日志；
- Markdown 和输出 HTML 都执行白名单清理；
- 上传校验 MIME、扩展名、大小和图片内容；
- `/wechat` 保持签名验证并增加防重放检查；
- App ID、Secret 和 access token 只存在服务器环境或受保护缓存；
- 接入前检查旧仓库历史是否出现过微信凭据，发现后立即轮换。

## 13. 视觉规范

### 13.1 设计方向

整体气质：安静、准确、克制，接近工程档案和长期笔记，而不是产品营销页。

建议色板：

| 用途 | 色值 |
|---|---|
| 页面背景 | `#F6F4EF` |
| 正文 | `#1F2933` |
| 次要文字 | `#64748B` |
| 主强调色 | `#0F766E` |
| 边框 | `#D8D4CA` |
| 已验证/成功 | `#166534` |
| 风险/需复核 | `#B45309` |

### 13.2 页面约束

- 文章正文最大宽度约 720px；
- 首页和项目页内容区最大宽度约 1120px；
- 优先使用系统中文字体，避免首屏依赖境外字体服务；
- 所有交互状态满足键盘操作和可见焦点；
- 正文、链接和状态标签满足 WCAG AA 对比度；
- 图片优先使用真实架构图、评测曲线、脱敏截图和清单；
- 不使用机器人、脑图标、AI 渐变、玻璃拟态和无意义代码背景；
- RSS、公众号二维码和小程序入口不得只放在页脚。

## 14. 实施阶段

### 阶段 0：基线、备份与分支

任务：

- [ ] 同步远端最新 `master`；
- [ ] 确认生产 HEAD、PHP、数据库和迁移状态；
- [ ] 完成数据库、媒体和环境配置备份；
- [ ] 清点用户和管理员；
- [ ] 记录首页、登录、`/up`、`/wechat` 基线；
- [ ] 创建改版分支；
- [ ] 建立发布前后检查表。

验收：

- 基线可重复验证；
- 备份可以读取并有恢复方法；
- 改版开发不会覆盖当前生产分支。

### 阶段 1：安全收缩与新布局

任务：

- [ ] 建立新 Blade/Tailwind 公共布局；
- [ ] 移除公共页面对旧 Bootstrap/jQuery 构建包的依赖；
- [ ] 建立管理员 Gate 和后台布局；
- [ ] 增加登录限流和 Session 安全设置；
- [ ] 在账号清点后关闭注册、用户目录和公开资料；
- [ ] 保持 `/wechat` 和健康检查不变。

验收：

- 未登录用户无法访问任何后台路由；
- 非管理员即使直接访问 URL 也返回拒绝；
- 公共页面不再暴露用户邮箱；
- 旧微信回调和健康检查通过。

### 阶段 2：内容核心与公共网站

任务：

- [ ] 新增文章、修订、标签、项目、媒体和重定向迁移；
- [ ] 实现 Markdown 渲染与 HTML 清理；
- [ ] 实现草稿、预览、审核、发布和归档；
- [ ] 实现首页、文章、标签、项目、Now、About；
- [ ] 实现媒体上传、alt、版权和哈希记录；
- [ ] 增加 canonical、Open Graph、JSON-LD 和 sitemap；
- [ ] 为发布操作增加审计记录。

验收：

- 修改已发布文章不会立即改变线上正文；
- 发布新修订后，旧修订仍可审计；
- slug 变更后旧地址 301 到新地址；
- 未发布内容不会出现在公共页面、sitemap 或接口；
- 首页至少展示三个项目和五篇真实文章后再替换现站首页。

### 阶段 3：RSS 与 Atom

任务：

- [ ] 实现 RSS 2.0；
- [ ] 实现 Atom；
- [ ] 增加 Feed 自动发现；
- [ ] 增加 ETag、Last-Modified 和缓存；
- [ ] 完成绝对媒体 URL 转换；
- [ ] 增加 Feed XML 测试和外部校验。

验收：

- RSS/Atom 只包含公开发布内容；
- 更新文章不会产生新的 GUID；
- Feed 阅读器可正确显示中文、代码和图片；
- 未变化请求能够返回 304。

### 阶段 4：小程序只读发布

任务：

- [ ] 实现 `/api/v1` 文章、标签和项目接口；
- [ ] 增加游标分页、ETag、限流和缓存；
- [ ] 实现小程序文章列表、详情和分享；
- [ ] 配置合法请求域名；
- [ ] 增加网站、Feed、小程序内容一致性检查。

验收：

- 小程序只显示公开发布内容；
- 网站和小程序对应相同修订及内容哈希；
- API 不要求公共读者登录；
- API 不泄露草稿、管理员或渠道配置。

### 阶段 5：微信公众号草稿

任务：

- [ ] 核验公众号资质和接口权限；
- [ ] 增加数据库队列、Scheduler 和进程守护；
- [ ] 实现 Outbox 消费；
- [ ] 实现微信 HTML 模板和媒体上传映射；
- [ ] 实现草稿创建、幂等、有限重试和失败后台；
- [ ] 实现人工发布确认和远端核对；
- [ ] 增加失败任务告警。

验收：

- 重复提交同一修订不会创建重复本地投递记录；
- 外部接口失败不会影响网站正文；
- 结果未知的请求进入核对状态而不是盲目重试；
- 微信草稿“阅读原文”指向网站 canonical URL；
- 未经人工确认不会自动群发。

### 阶段 6：增强能力

按真实需求选择，不进入首版阻塞项：

- [ ] 定时发布；
- [ ] 站内搜索；
- [ ] 系列文章；
- [ ] Markdown 内容导出与恢复；
- [ ] 隐私友好的阅读统计；
- [ ] 邮件订阅；
- [ ] 收藏、评论或阅读进度；
- [ ] 多作者工作流；
- [ ] 有条件的公众号发布状态同步。

## 15. 测试方案

### 15.1 单元测试

- Markdown 渲染和 HTML 白名单；
- slug 生成和重定向；
- 内容哈希稳定性；
- RSS GUID 和 Feed 日期；
- 微信模板转换；
- 幂等键生成和错误分类。

### 15.2 功能测试

- 草稿、审核、发布、归档状态切换；
- 管理员和非管理员权限；
- 私有、未列出和公开内容可见性；
- 已发布版本与草稿版本隔离；
- RSS、Atom、sitemap 只返回已发布内容；
- API 分页、ETag、304 和限流；
- 公众号任务重试、核对和防重复；
- 微信回调签名验证。

### 15.3 发布前质量检查

- `composer` 依赖检查；
- PHP 测试和代码格式检查；
- 前端构建；
- 关键页面浏览器检查；
- 手机、平板和桌面布局；
- 键盘操作、焦点、对比度和图片 alt；
- RSS/Atom 外部验证；
- 生产配置、队列和 Scheduler 检查；
- 数据库和媒体备份可读性检查。

## 16. 部署与回滚

### 16.1 发布前

1. 确认待发布 commit 和变更范围；
2. 备份数据库与媒体；
3. 在与生产一致的 PHP 8.5 环境安装依赖并运行测试；
4. 构建前端资产；
5. 预检迁移 SQL 和受影响表；
6. 确认队列、Scheduler 和环境变量；
7. 记录上一可用版本。

### 16.2 发布

建议使用不可变发布目录和当前版本软链接。最低要求：

- 使用 `--ff-only` 或明确的发布 commit；
- 安装生产依赖；
- 执行前端构建；
- 执行新增迁移；
- 清理并重建 Laravel 缓存；
- 重启队列进程；
- 验证 `/up`、首页、文章页、RSS、后台和微信回调。

### 16.3 回滚原则

- 代码可立即切回上一不可变版本；
- 新迁移尽量保持向后兼容；
- 已产生文章内容后，不通过破坏性 `migrate:rollback` 删除内容表；
- 外部渠道发布失败只停止渠道任务，不回滚网站文章；
- 回滚后重新验证健康检查、页面、Feed、登录和微信回调；
- 所有回滚记录原因、版本、时间和验证结果。

## 17. 首发内容

### 17.1 首批项目案例

1. **InterAPI**：AI 教学资源、检索、权限与组课；
2. **Picturebook Pipeline**：互动绘本与多媒体资源发布；
3. **JIXU**：个人 AI 工作与项目记忆系统；
4. **Fobo**：儿童英语语音产品实验。

### 17.2 首批文章

优先发布：

1. 《一次 Laravel 5.5 到 Laravel 13 的迁移：为什么选择新骨架》；
2. 《一个搜索结果为什么“不对”：连续中文、分数覆盖与弱结果补齐》；
3. 《向量数据库不是权限系统：检索之后为什么仍需要 MySQL 门禁》；
4. 《116 本互动绘本上线之前，我们真正验证了什么》；
5. 《把 AI 组课做成资源优先：成本、候选资格与单槽位生成兜底》；
6. 《RSS、公众号与小程序：为什么正文只保留一个来源》。

首发前至少完成三个项目案例和五篇真实文章，避免上线一个结构完整但内容空白的网站。

## 18. MVP 边界

首版必须包含：

- 单站长后台；
- Markdown 文章与不可变发布版本；
- 标签、项目和图片媒体；
- 首页、文章、项目、Now、About；
- canonical、sitemap、RSS 和 Atom；
- 小程序只读 API；
- 手动触发公众号草稿；
- 队列、幂等、有限重试和错误查看；
- 备份、测试、审计和回滚路径。

首版明确不包含：

- 公开注册和用户社区；
- 评论、会员和付费；
- AI 自动写作；
- 公众号自动群发；
- 复杂 Headless CMS；
- 多语言；
- 高级阅读统计；
- 多作者审批组织。

## 19. 最终验收标准

满足以下条件后，改版才可视为完成：

1. 当前生产数据和媒体有可验证备份；
2. 首页清楚表达“工程现场”定位并展示真实案例；
3. 公开注册、用户目录和邮箱暴露已安全收缩；
4. 已发布文章使用冻结修订，草稿修改不会影响线上版本；
5. 网站、RSS 和小程序返回相同文章身份、版本、哈希与 canonical URL；
6. RSS/Atom 可被主流阅读器正确订阅；
7. 公众号重复任务不会产生重复本地投递，未知结果可以核对；
8. 渠道失败不会影响网站正文；
9. `/up`、登录、文章、Feed、小程序 API 和 `/wechat` 均通过发布后检查；
10. 上一版本可以恢复，且回滚不会删除已经发布的内容。

## 20. 推荐实施顺序

```text
同步生产基线
→ 备份与账号清点
→ 新布局与后台安全
→ 文章/项目内容核心
→ SEO + RSS/Atom
→ 小程序只读 API
→ 公众号草稿发布
→ 内容首发与持续迭代
```

第一轮开发应停在“内容核心 + RSS + 真实首发内容”。小程序和公众号建立在稳定文章版本之上，不应反过来阻塞网站上线。
