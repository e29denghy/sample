![开物适配 DeepSeek Harness：受控闸门连接 Quest、Event 与 AI 执行](https://denghy.cn/images/articles/kaiwu-deepseek-harness-adapter/00-cover.png)

开物 KAIWU 是一个本地优先、人工批准、跨 Agent Harness 的工作台。它保存项目、Quest、审批和审计记录，但不会把自己变成另一个拥有任意 Shell 权限的执行器。

当 DeepSeek Harness 发布 Developer Preview 后，真正需要解决的问题并不是“能不能从 PHP 启动一个命令”，而是：怎样让已经批准的任务进入 DSH，又不让一次 Web 请求静默获得机器写入权限；怎样把执行结果带回开物，同时保留版本、重试和失败证据。

![kaiwu.denghy.cn 线上开物工作台：首页展示本地优先、人工批准与跨 Harness 协调](https://denghy.cn/images/articles/kaiwu-deepseek-harness-adapter/01-kaiwu-home.jpg)

## 先固定职责，而不是直接嵌入 SDK

开物负责控制面，DeepSeek Harness 负责执行面。

控制面回答这些问题：任务目标是什么，约束和验收标准是什么，是否需要写入，谁批准了它，应该交给哪个 Harness，以及结果是否已经被确认。

执行面负责在自己的权限模型和沙箱里读取仓库、调用工具、运行命令并返回结果。开物的 Web 应用不直接执行 DSH，也不持有它的 API Key。

因此适配被放在 `bridges/deepseek-harness` 独立进程中，而不是 Laravel 控制器里。Bridge 消费经过批准的 Outbox 消息，调用 DSH 自动化入口，再把生命周期和结果写回 Inbox。Web 请求与机器执行之间多了一道清晰、可监管的进程边界。

## 两份协议把工作台和 Harness 解耦

开物向外发出 `kaiwu.quest/v1`，Harness 向内写回 `kaiwu.event/v1`。

Quest 不只包含一句 Prompt，还带有稳定 ID、项目、目标、约束、风险等级、是否需要写入、批准证据和验收标准。Event 则记录启动、完成或失败状态、发生时间、执行会话和可核对的结果摘要。

```text
人工创建或 AI 生成 Quest
        ↓
开物保存目标、约束与风险
        ↓
写入型任务等待人工批准
        ↓
kaiwu.quest/v1 Outbox
        ↓
DeepSeek Harness Bridge
        ↓
DSH 在独立进程与沙箱中执行
        ↓
kaiwu.event/v1 Inbox
        ↓
开物投影执行状态并等待结果确认
```

这意味着以后更换 Harness 时，不需要重写项目、审批和审计模型。新的 Adapter 只负责翻译稳定协议，不能绕过批准门禁。

![开物线上协议边界与已固定 0.1.1-rc.2 的 DSH Preview Adapter](https://denghy.cn/images/articles/kaiwu-deepseek-harness-adapter/02-protocol-and-dsh.jpg)

## 权限映射必须默认保守

Bridge 会重新检查每一份 Quest，而不是相信“它出现在目录里，所以一定批准过”。没有批准证据的任务拒绝执行；写入型任务也不能伪装成只读任务。

权限映射保持简单：`requires_write=false` 对应 DSH 的 `read-only`，需要写入且已经批准的任务才进入 `workspace-write`。API Key 只存在于进程环境中，不写入 Git、Quest 或事件日志。

这条规则的价值在于，开物可以规划高权限工作，但规划本身不会自动获得高权限。真正的授权仍由人给出，而且证据会跟随 Quest 一起进入审计链路。

## 重试不能覆盖第一次失败

Bridge 对 dispatch 加锁，原子写入状态，并用稳定的事件 ID 记录生命周期。相同事件重复同步时不会制造第二份业务记录，但投影器仍会重放状态更新。

这个细节解决了一类容易忽略的问题：事件可能已经成功落库，但执行状态投影在中途失败。如果同步器只因为“事件已存在”就跳过后续处理，Quest 会永久停在旧状态。允许幂等重放，才能让下一次同步自愈。

失败后的重试也会保留新的 attempt 和 dispatch 身份，不覆盖第一次执行。审计时间线因此能够回答：哪一次获得批准，哪一次开始执行，失败发生在哪里，第二次尝试是否使用了不同上下文。

## Developer Preview 不能只改版本号

截至 2026-08-29，DeepSeek Harness 的 GitHub 最新源码标签是 `0.1.2-alpha.1`，但没有对应的 npm/PyPI 可安装主包；当前可安装版本分别是 npm CLI `0.1.1-rc.2` 和 Python SDK `0.1.1rc1`。开物因此把生产 Adapter 固定到经过验证的 CLI `0.1.1-rc.2`，而不是追逐一个只有源码标签的版本。

这次升级通过 PR #6 合并到 KAIWU `main`，生产提交为 `8458504c7b34c5e85f00fe79fd78205fbe9391dc`。`kaiwu.denghy.cn` 已公开显示 `RC.2 PINNED`，项目源代码、生产 release 和页面版本文案保持一致。

兼容性测试发现，SDK wheel 虽然补上了 macOS arm64 runtime，却无法加载开物要求的 Bash 沙箱插件；npm CLI 也需要显式补足官方 peer 依赖。开物选择失败关闭：自动模式只选择经过验证、带完整沙箱配置的 CLI，SDK 保留为显式诊断路径，不会静默降级成无沙箱执行。

这说明适配预览软件时，版本锁定不是保守过度，而是工程事实。每次升级都要重新验证安装包、启动配置、权限插件、协议和项目测试，不能把 GitHub 上的新标签直接等同于可部署版本。

## 这次验证了什么，也没有验证什么

开物 DSH 适配的验证覆盖 Bridge 单测、Quest/Event 协议、权限映射、幂等同步、Laravel 测试、前端构建、网站服务端渲染、静态导出和依赖审计。升级过程实际读回了 CLI `0.1.1-rc.2` 和完整沙箱配置；GitHub 两套 CI 通过后才合并并重新构建生产站。

验证过程没有调用真实模型，也没有使用或保存 API Key。因此可以确认的是：协议、门禁、进程边界和无密钥执行路径能够按设计工作；不能据此声称已经完成真实模型任务的端到端质量评测。

## 开物适配 DSH 的核心，不是“接上了”

如果只是让 Web 应用启动一个 AI 命令，几行代码就能完成。但一个可长期使用的 Harness 工作台必须继续回答：谁批准、允许写什么、如何失败、怎样重试、结果如何回到项目，以及更换 Harness 后哪些状态仍然成立。

开物选择把这些问题固定在 DSH 之外：用人工批准控制授权，用 Quest/Event 固定协议，用独立 Bridge 隔离执行，用追加式事件保留证据。DeepSeek Harness 可以持续演进，而工作台仍然掌握决定权。

项目站：[kaiwu.denghy.cn](https://kaiwu.denghy.cn)

源代码：[github.com/e29denghy/kaiwu](https://github.com/e29denghy/kaiwu)
