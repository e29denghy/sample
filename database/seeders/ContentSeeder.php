<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleRevision;
use App\Models\AuditLog;
use App\Models\OutboxEvent;
use App\Models\Project;
use App\Models\Tag;
use App\Services\MarkdownRenderer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(MarkdownRenderer $renderer): void
    {
        $projects = $this->seedProjects();

        foreach ($this->articles() as $data) {
            $this->seedArticle($data, $projects, $renderer);
        }
    }

    /** @return array<string, Project> */
    private function seedProjects(): array
    {
        $projects = [];

        foreach ($this->projectData() as $data) {
            $syncExisting = (bool) ($data['sync_existing'] ?? false);
            unset($data['sync_existing']);

            $project = Project::where('slug', $data['slug'])->first();

            if ($project && $syncExisting) {
                $project->fill($data)->save();
            } else {
                $project ??= Project::create([...$data, 'published_at' => now()]);
            }

            $projects[$data['slug']] = $project;
        }

        return $projects;
    }

    /** @param array<string, mixed> $data @param array<string, Project> $projects */
    private function seedArticle(array $data, array $projects, MarkdownRenderer $renderer): void
    {
        if (Article::where('slug', $data['slug'])->exists()) {
            return;
        }

        DB::transaction(function () use ($data, $projects, $renderer): void {
            $now = now();
            $markdown = $data['markdown'];
            $article = Article::create([
                'slug' => $data['slug'],
                'status' => 'published',
                'visibility' => 'public',
                'first_published_at' => $now,
                'published_at' => $now,
            ]);
            $revision = ArticleRevision::create([
                'article_id' => $article->id,
                'version' => 1,
                'title' => $data['title'],
                'excerpt' => $data['excerpt'],
                'markdown' => $markdown,
                'rendered_html' => $renderer->render($markdown),
                'content_hash' => hash('sha256', $markdown),
                'seo_title' => $data['seo_title'],
                'seo_description' => $data['seo_description'],
                'verification_status' => $data['verification_status'],
                'verified_at' => $data['verification_status'] === 'valid' ? $now : null,
            ]);
            $article->forceFill([
                'current_draft_revision_id' => $revision->id,
                'published_revision_id' => $revision->id,
            ])->save();

            $tagIds = collect($data['tags'])
                ->map(fn (string $name): array => ['name' => $name, 'slug' => Str::slug($name)])
                ->filter(fn (array $tag): bool => $tag['slug'] !== '')
                ->map(fn (array $tag): int => Tag::firstOrCreate(['slug' => $tag['slug']], ['name' => $tag['name']])->id)
                ->all();
            $article->tags()->sync($tagIds);

            $article->projects()->sync(collect($data['projects'])->map(fn (string $slug): int => $projects[$slug]->id)->all());
            OutboxEvent::create([
                'event_type' => 'ArticlePublished',
                'aggregate_type' => Article::class,
                'aggregate_id' => $article->id,
                'aggregate_version' => 1,
                'payload' => [
                    'article_public_id' => $article->public_id,
                    'article_revision_id' => $revision->id,
                    'content_hash' => $revision->content_hash,
                    'source' => 'content-seeder',
                ],
                'idempotency_key' => "article:published:{$article->public_id}:{$revision->id}",
                'status' => 'pending',
                'available_at' => $now,
            ]);
            AuditLog::create([
                'action' => 'article.seeded',
                'auditable_type' => Article::class,
                'auditable_id' => $article->id,
                'metadata' => ['source' => 'ContentSeeder'],
            ]);
        });
    }

    /** @return list<array<string, mixed>> */
    private function projectData(): array
    {
        return [
            [
                'name' => 'InterAPI / 知阅录',
                'slug' => 'interapi',
                'status' => 'active',
                'summary' => '管理课程和教学资源，提供搜索、播放与使用权限。',
                'problem' => '教学资源同时涉及内容身份、租户权限、搜索质量、媒体来源和持续发布，单一的 CRUD 页面无法解释完整链路。',
                'decisions' => '以数据库权限为事实来源；用评测闭环拆分召回、词法校验和重排；向量采用不可变 release、Outbox、tombstone 和 reconcile；把 AI 教学、资源多媒体、向量搜索、课程资源、安全质量、成本运营分成六个模块。',
                'evidence' => '已有生产发布记录覆盖资源身份、检索评测、封面 URL 契约、关系上下文继承和发布后对账。',
                'outcome' => '系统把一次搜索或资源发布拆成可回读的证据链，并明确哪些数据是权限事实、哪些只是检索上下文。',
                'is_public' => true,
                'is_featured' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'KAIWU / 开物',
                'slug' => 'kaiwu',
                'status' => 'released',
                'summary' => '集中管理交给 AI 的任务，记录批准、执行结果和失败后的重试。',
                'problem' => '不同 Harness 的执行方式各不相同，项目状态、人工批准和执行结果容易散落在工具日志里。',
                'decisions' => '用 kaiwu.event/v1 JSONL Inbox 接收事件，用 kaiwu.quest/v1 Outbox 发出经批准的任务；Harness 通过 Adapter 接入，系统不执行任意 Shell；重试产生新的 attempt 和 dispatch ID。',
                'evidence' => '独立仓库 e29denghy/kaiwu 已完成 v0.1.0 发布、双语 README、Linux CI 和 provenance 检查。',
                'outcome' => '把“谁批准了什么、何时发出、由哪个 Harness 执行、失败后如何重试”变成稳定的事件和审计边界。',
                'is_public' => true,
                'is_featured' => true,
                'sort_order' => 20,
            ],
            [
                'name' => 'MyAiWorkFlow / 迹序',
                'slug' => 'myaiworkflow',
                'status' => 'active',
                'summary' => '整理项目记录和待办，选出今天要做的几件事，方便下次接着做。',
                'problem' => '项目活动记录很多，但活动不等于今天的承诺；如果只有一张扁平任务表，历史、待办和当前焦点会互相干扰。',
                'decisions' => '只从本地记忆知识库同步；按项目模块归档历史记录，区分 backlog、next、today、waiting 和 archive；每日焦点最多三个，并由用户选择，不自动替用户排程。',
                'evidence' => '记忆同步模型包含 Project、Todo、TodoStep、Reminder 和 project_modules；InterAPI 已有六个模块键和 focus_rank 约束。',
                'outcome' => '工作流从“我做过什么”进一步分离出“接下来可以选什么”，但仍保留来源文件、内容哈希和同步时间。',
                'is_public' => true,
                'is_featured' => true,
                'sort_order' => 30,
            ],
            [
                'name' => '福宝英语角 / Fobo',
                'slug' => 'fobo-english-corner',
                'sync_existing' => true,
                'status' => 'released',
                'summary' => '面向儿童的英语口语练习站，保留快捷句型练习，并新增邀请码准入的实时语音、字幕、打断和场景对话。',
                'problem' => '儿童练习需要简单交互和即时语音反馈；实时模型上线后，还必须同时控制访问、费用、密钥、隐私、并发和最长会话。',
                'decisions' => '保留原五主题英语角，在 /talk 增加实时对话；采用设备通行证、原子配额、单次 Relay 票据、同域 Nginx 分流和服务隔离；第一版默认不录音。',
                'evidence' => '生产验证覆盖 24 个 PHP 测试、141 个断言、30 个前端测试、真实 Qwen session.created、票据重放拒绝、60 秒硬上限结算、TLS 和日志审计。',
                'outcome' => 'voice.denghy.cn 已上线邀请测试；原英语角继续保留，实时链路独立运行，浏览器不接触 Qwen API Key。',
                'is_public' => true,
                'is_featured' => false,
                'sort_order' => 40,
            ],
            [
                'name' => 'Learn Harness Engineering',
                'slug' => 'learn-harness-engineering',
                'status' => 'open-source',
                'summary' => '一套面向 AI 编程 Agent 的开源项目型课程，通过 14 讲、8 个递进实践和可复用模板，学习如何用环境、状态、验证与控制机制提升真实工程任务的可靠性。',
                'problem' => '模型能生成代码，不等于它能在真实仓库里跨会话完成任务。缺少项目规则、持久状态、执行反馈和验收门槛时，Agent 容易越界修改、遗漏步骤、重复返工或提前宣布完成。',
                'decisions' => '课程用讲义、实战项目、资源模板和真实产品拆解四条路径组织内容；把可操作的 Harness 拆成 Instructions、State、Verification、Scope 和 Session Lifecycle，并通过 baseline 对比、跨会话接力、Maker-Checker Loop、Graph 与人工审批逐步加固。',
                'evidence' => '截至 2026-08-20，主分支 README 标注 14 Lectures、8 Projects、15 Languages 和 MIT License；中文项目目录提供 starter/solution 实践，资源库包含 AGENTS.md、feature_list、init、交接与验证模板。',
                'outcome' => '项目提供了一条可测量的 Harness 学习路径：先用相同任务建立无 Harness 基线，再逐步比较完成率、人工介入、错误完成、返工量和跨会话恢复时间。课程本身是学习材料，不等同于生产可靠性保证。',
                'source_url' => 'https://github.com/walkinglabs/learn-harness-engineering',
                'is_public' => true,
                'is_featured' => false,
                'sort_order' => 45,
            ],
            [
                'name' => '程序员的个人修养 / 工程现场',
                'slug' => 'denghy-engineering-site',
                'status' => 'active',
                'summary' => '记录 AI、内容系统、Laravel、部署和工作流实践的个人工程内容站。',
                'problem' => '项目经验如果只停留在聊天和临时文档里，很难复用，也无法区分已验证结论和仍在计划中的想法。',
                'decisions' => '网站数据库作为正文事实来源；文章修订不可变；发布后分别服务网站、RSS、Atom 和小程序只读 API；公众号从人工审核草稿开始；Outbox 和审计保留发布证据。',
                'evidence' => '当前 Laravel 13/PHP 8.5 改版已经包含文章、修订、项目、标签、Feed、API、管理员门禁和部署备份链路。',
                'outcome' => '把一个个人站点先做成可维护的内容发布系统，再逐步增加媒体、渠道投递和 Inertia 交互。',
                'is_public' => true,
                'is_featured' => false,
                'sort_order' => 50,
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function articles(): array
    {
        $articles = [
            [
                'title' => '把工程现场写成可验证的发布链路',
                'slug' => 'engineering-notes-as-verifiable-release-chain',
                'excerpt' => '个人技术站真正需要沉淀的，不只是文章正文，而是正文版本、验证状态、关联项目和发布后的可回读证据。',
                'seo_title' => '把工程现场写成可验证的发布链路',
                'seo_description' => '从文章修订、项目档案、RSS/API 到 Outbox 和审计，搭建一个可验证的个人工程内容发布链路。',
                'verification_status' => 'valid',
                'tags' => ['内容系统', 'Laravel', '发布流程'],
                'projects' => ['denghy-engineering-site'],
                'markdown' => <<<'MD'
## 先区分正文和发布

一个工程站点最容易犯的错误，是把“编辑器里当前的文本”直接当成“读者看到的正文”。这两个状态应该分开：草稿可以反复修改，公开版本必须冻结，发布动作才负责切换读者看到的版本。

因此文章表只保存稳定身份和发布指针，正文放在不可变的 `article_revisions` 中。每次发布都产生版本号和内容哈希，读者、RSS 和只读 API 读取同一个 `published_revision_id`。

## 渠道状态不能反过来影响网站

网站发布成功后，RSS、Atom、小程序和公众号是不同的下游渠道。公众号投递失败，不应该回滚网站已经公开的正文；外部渠道未知时，要保留 `needs_reconcile` 一类状态，而不是用一个“失败”覆盖所有情况。

这也是 Outbox 存在的原因：事务里记录“应该发生什么”，消费者再处理缓存清理和渠道投递。这样一次发布至少能回答三个问题：发布了哪个版本、下游处理到哪一步、失败后是否可以安全重试。

## 内容也要有验证边界

文章应明确首次发布时间、当前修订更新时间和最后验证时间。对仍在计划中的功能，不把设计稿写成已经上线；对只在本地验证过的实验，写清楚环境边界；对已过时的结论，保留历史版本但改变验证状态。

个人站不需要一开始就做成大型 CMS，但需要从第一篇文章开始保留这些边界。可验证的内容，才有机会成为下一次项目的输入。
MD,
            ],
            [
                'title' => 'KAIWU：把跨 Agent 工作拆成事件、任务与审批',
                'slug' => 'kaiwu-agent-workbench-events-quests-approval',
                'excerpt' => 'KAIWU 的核心不是再造一个 Shell 执行器，而是把项目、审批、调度和 Harness 执行之间的边界固定下来。',
                'seo_title' => 'KAIWU：把跨 Agent 工作拆成事件、任务与审批',
                'seo_description' => '介绍 KAIWU v0.1 的 event Inbox、quest Outbox、Harness Adapter、人工审批和可审计重试设计。',
                'verification_status' => 'valid',
                'tags' => ['KAIWU', 'Agent', '工作流', '审计'],
                'projects' => ['kaiwu'],
                'markdown' => <<<'MD'
## 为什么要有一个工作台

不同 Agent Harness 擅长的事情不同：有的负责代码，有的负责浏览器，有的负责文档或数据处理。如果每个 Harness 都直接修改项目状态，系统很快会失去统一的审批和审计边界。

KAIWU 把自己放在 Harness 之上：它管理项目、审批、调度和审计，Harness 负责执行已经被批准的工作。这个分层让“任务是什么”和“由谁执行”可以独立变化。

## 两条稳定边界

事件通过 `kaiwu.event/v1` JSONL Inbox 进入系统，任务通过 `kaiwu.quest/v1` 版本化 Outbox 发出。两者都是数据边界，不是任意 Shell 的入口。

Harness 通过 Adapter 接入。Adapter 可以把外部执行器的结果转成统一事件，也可以把 Quest 转成某个 Harness 能理解的请求，但 KAIWU 不因此宣称拥有每个 Harness 的原生能力。

## 审批和重试必须可见

未批准的 Quest 不能 dispatch。执行失败后，重试要产生新的 attempt 和 dispatch ID，而不是覆盖上一条历史。这样审计记录可以说明：原任务何时批准、第一次执行发生了什么、第二次尝试是否使用了新的参数。

KAIWU v0.1 的价值，正是把这些边界做成一个可被其他 Harness 复用的工作台，而不是把某一个 Agent 的实现细节包装成平台能力。
MD,
            ],
            [
                'title' => 'InterAPI 向量发布：为什么要经过 Outbox、tombstone 与 reconcile',
                'slug' => 'interapi-vector-release-outbox-tombstone-reconcile',
                'excerpt' => '向量检索系统最危险的不是一次写入失败，而是数据库、向量库和发布队列之间悄悄产生漂移。',
                'seo_title' => 'InterAPI 向量发布：为什么要经过 Outbox、tombstone 与 reconcile',
                'seo_description' => '从 InterAPI 的资源身份、不可变向量发布、Outbox、tombstone 和 reconcile 看生产检索系统如何控制漂移。',
                'verification_status' => 'valid',
                'tags' => ['InterAPI', '向量搜索', 'Outbox', '生产'],
                'projects' => ['interapi'],
                'markdown' => <<<'MD'
## 向量库不是权限事实

搜索向量可以帮助召回候选，但它不应该决定一个租户是否能看到资源。权限和可见性仍然由数据库事实和业务查询决定，向量库只是检索加速层。

这条边界很重要：如果把“向量存在”误当成“资源可见”，一次迟到的删除、一次租户范围错误或一次索引漂移，就可能变成权限问题。

## 发布不是循环写入

InterAPI 的生产发布采用不可变 release。发布前先生成 manifest，经过 dry-run、评测和必要的 canary，再把数据库变化和向量变化拆成可追踪的 Outbox 工作。旧文档需要 tombstone，最终通过 reconcile 回读数据库和向量库，确认没有开放队列、失败任务或内容漂移。

这比“遍历所有记录然后直接 upsert”多了几个步骤，却换来了可重试、可对账和可回滚的边界。任何一次发布都应该能回答：这一批资源属于哪个 release，哪些文档已经送达，哪些文档仍需核对。

## 评测要早于扩容

搜索质量改善先看可复现查询、召回链路和权限过滤，再决定是否增加数据。密集召回、词法验证、重排、强匹配和多样性应该分开统计；向量数量增加，并不自动意味着检索质量提升。

生产系统需要的是一条发布和评测闭环，而不是一个“写入成功”的单点指标。
MD,
            ],
            [
                'title' => 'MyAiWorkFlow：把项目记忆变成可选择的每日焦点',
                'slug' => 'myaiworkflow-memory-to-daily-focus',
                'excerpt' => '活动记录很多，不代表今天应该做很多事；项目记忆、历史归档、待办和用户选择的焦点需要分层。',
                'seo_title' => 'MyAiWorkFlow：把项目记忆变成可选择的每日焦点',
                'seo_description' => '介绍 MyAiWorkFlow 如何从本地记忆知识库同步项目状态，区分历史、backlog 和每天最多三个焦点任务。',
                'verification_status' => 'valid',
                'tags' => ['MyAiWorkFlow', '项目管理', '记忆', 'Inertia'],
                'projects' => ['myaiworkflow'],
                'markdown' => <<<'MD'
## 活动不是承诺

一个项目昨天有很多更新，只能说明它发生过变化，不能直接推导出今天必须处理它。把所有活动都塞进“今日任务”，会让仪表盘越来越热闹，却越来越难做选择。

MyAiWorkFlow 使用本地记忆知识库作为业务来源，按项目的 `applies_to` 和 Git 根目录匹配项目，再保存来源文件、标题、内容指纹和同步时间。这样同步是可解释的，也不会退回去读取聊天会话当业务数据。

## 三层状态

历史快照进入 archive，尚未安排的工作进入 backlog 或 next，用户明确选择的事项才进入 today。waiting 表示等待确认或外部条件，不应该伪装成可以立即执行的任务。

每日焦点最多三个，并且由用户选择。系统可以发现候选、提示依赖和显示证据，但不自动替用户安排一天。

## 模块化比标签堆积更有用

InterAPI 的工作被拆成 AI 教学运行时、资源多媒体、向量搜索、课程资源、安全质量、成本运营六个模块。模块不是装饰性标签，而是让一个项目可以回答“这条工作属于哪种能力边界”。

当历史和当前选择被分开，仪表盘才从活动流水账变成一个可核对的决策界面。
MD,
            ],
            [
                'title' => '福宝英语角：一个小而可控的语音练习 MVP',
                'slug' => 'fobo-english-corner-small-controlled-speech-mvp',
                'excerpt' => '儿童英语练习先解决一次“听见、说出、得到反馈”的闭环，再决定是否增加账号、积分和复杂的学习记录。',
                'seo_title' => '福宝英语角：一个小而可控的语音练习 MVP',
                'seo_description' => '记录福宝英语角使用 Laravel、Inertia、Vue 3 和语音服务构建儿童口语练习 MVP 的取舍。',
                'verification_status' => 'review_required',
                'tags' => ['福宝', 'Vue 3', 'Inertia', '语音'],
                'projects' => ['fobo-english-corner'],
                'markdown' => <<<'MD'
## 先把练习做成一个闭环

福宝英语角的第一版不追求完整课程系统，而是让孩子在一个主题里选择短句，听到示范，再说一遍并得到反馈。这个闭环足够小，才能在浏览器和语音服务之间快速发现真正的问题。

技术上使用 Laravel 13、Inertia、Vue 3、SQLite 和 Vite。后端保留清晰的服务边界，前端负责较轻的交互，不为一个没有明确需求的 MVP 提前引入登录、多人房间、游戏化或 WebSocket。

## 语音体验需要明确的失败路径

浏览器可能阻止异步音频自动播放，所以界面需要保留明确的“再听一遍”操作。ASR 或 TTS 失败时，用户仍应该知道下一步可以怎么做，而不是停在一个没有解释的 loading 状态。

当前验证覆盖了真实 TTS 音频和基本的语音识别路径；本地 Valet HTTPS 仍需独立确认，不能把本地冒烟测试写成已经完成的正式部署。

先把一次练习做得简单、可听见、可重复，再根据真实使用反馈决定是否增加账号、历史记录和更多教学内容。
MD,
            ],
        ];

        $articles[] = $this->loadArticlePackage('learn-harness-engineering-guide');
        $articles[] = $this->loadArticlePackage('deepseek-v4-flash-vision-harness-rc8-rc1');
        $articles[] = $this->loadArticlePackage('fobo-realtime-voice-release');
        $articles[] = $this->loadArticlePackage('kaiwu-deepseek-harness-adapter');
        $articles[] = $this->loadArticlePackage('agi-is-coming');

        return $articles;
    }

    /** @return array<string, mixed> */
    private function loadArticlePackage(string $name): array
    {
        $manifest = json_decode(
            file_get_contents(database_path("content/{$name}.json")),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
        $manifest['markdown'] = file_get_contents(base_path($manifest['markdown_file']));
        unset($manifest['markdown_file']);

        return $manifest;
    }
}
