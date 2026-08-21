> 2026 年 8 月 19 日到 21 日，DeepSeek Harness 连续发布 v0.1.0-rc.8 与 v0.1.1-rc.1，DeepSeek API 同期上线实验性视觉理解模型 DeepSeek-V4-Flash-Vision-Exp。两次更新连起来看，重点不是“聊天框终于能上传图片”，而是 Coding Agent 开始拥有从看见、理解到执行、验证的完整视觉工作链路。

![能够理解截图并连接代码、工具与验证结果的视觉 Coding Agent](https://denghy.cn/images/articles/deepseek-v4-flash-vision-harness/00-cover.jpg)

过去的 Coding Agent 主要处理文本：需求、代码、终端输出、测试日志。遇到页面错位、设计稿、流程图或图表，通常还需要人先把视觉信息翻译成文字。

DeepSeek-V4-Flash-Vision-Exp 与 DeepSeek Harness 最近两次更新改变的，正是这层输入边界。

模型可以理解图片，并不等于 Agent 已经能可靠完成视觉任务。图片怎样进入会话、怎样被命令和子代理引用、历史图片如何控制载荷、修改完成后怎样验证、什么时候必须让人确认，这些仍然需要 Harness 承担。

## 两次更新其实是一条能力接力

如果只看版本号，rc.8 和 rc.1 像是两次普通的候选版更新。把功能放在同一条链路上看，它们的分工很清楚。

rc.8 先把多模态通道铺好：

- DeepSeek 模型适配器可以配置原生图片请求；
- `/goal`、`/plan` 等命令可以接收图文输入；
- `@` 菜单可以引用文件和会话；
- MCP 与 ACP 的图片附件能够持久化，PTC Mode 可以转发嵌套图片；
- 过大图片和历史图片累计载荷导致的请求失败得到修复。

同一个版本还扩展了执行侧：Claude Code 与 Codex 子代理可以作为 Profile Bundle 按需安装，支持非交互权限模式和多个命名实例；Windows PTY 增加持久 PowerShell 会话；`web_search` 支持并发查询；子代理交付结果可以及时唤醒父任务。

rc.1 随后把 DeepSeek-V4-Flash-Vision-Exp 加入 DeepSeek 适配器。换句话说，rc.8 解决“图片怎样在 Harness 里流动”，rc.1 解决“由哪个 DeepSeek 模型真正理解图片”。

![rc.8 建立多模态输入与编排通道，rc.1 接入视觉模型并加强安全](https://denghy.cn/images/articles/deepseek-v4-flash-vision-harness/01-release-handoff.jpg)

rc.1 的另一项更新值得单独强调：它修复了 Bubblewrap 沙箱内受限进程可能通过 `/proc/<pid>/root` 绕过限制的问题。对于能够读取图片、调用工具、修改代码的 Agent，安全边界不是附属体验，而是视觉能力真正进入工程环境的前提。

其余改进更偏向日常使用，包括 `ask_user_question` 支持多行输入和 `Shift+Enter` 换行、Markdown 表格自适应、99.x% 缓存命中率精度显示、子代理会话标题切换，以及编辑 `@` 引用前方文本时的布局修复。

## DeepSeek-V4-Flash-Vision-Exp 能做什么

DeepSeek 将它定义为实验性质的多模态视觉理解模型。通过设置：

```text
model="deepseek-v4-flash-vision-exp"
```

模型可以描述图片、识别截图文字、分析图表，也可以在 Agent 工作流里读取页面截图、设计参考和视觉执行结果。

DeepSeek 官方文章称，它的纯文本能力与 DeepSeek-V4-Flash 正式版持平，在需要视觉理解的 Agent Benchmark 上相较纯文本版本有明显提升，多模态 Agent 能力接近 Opus 4.8。这里需要保留一个重要边界：这是 DeepSeek 官方披露的测试结论，不是本文完成的独立复测。

官方展示的案例包括生成高端定制旅行 PPT、重构开发者网站，以及制作带动态效果的前端 Demo。它们说明模型已经可以把视觉理解与内容生成、前端实现结合起来，但展示结果仍不能替代真实项目里的代码审查、浏览器回读、性能检查和可访问性验证。

## API 已经覆盖三种常见协议

视觉模型目前可以通过以下接口使用：

- OpenAI 兼容的 Chat Completions；
- Anthropic 兼容的 Messages；
- OpenAI 兼容的 Responses API。

图片有三种传入方式：Base64 内联、外部 HTTP 或 HTTPS URL、Files API 的 `file_id`。下面是最小的 Chat Completions 示例：

```python
from openai import OpenAI

client = OpenAI(
    api_key="<DeepSeek API Key>",
    base_url="https://api.deepseek.com",
)

response = client.chat.completions.create(
    model="deepseek-v4-flash-vision-exp",
    messages=[
        {
            "role": "user",
            "content": [
                {"type": "text", "text": "检查这个页面截图，指出布局问题。"},
                {
                    "type": "image_url",
                    "image_url": {
                        "url": "https://example.com/screenshot.jpg",
                        "detail": "low",
                    },
                },
            ],
        }
    ],
)

print(response.choices[0].message.content)
```

官方文档列出的支持格式是 JPEG、PNG、GIF 和 WebP。外部 URL 图片最大 32 MiB，Files API 的 `file_id` 图片最大 64 MiB；内联请求体上限为 48 MiB。单次请求最多可以包含 600 张图片，但工程上不应把“允许很多”理解为“应该一次塞满”。

`detail` 可以选择 `low`、`high`、`original` 或 `auto`。其中 `low` 会在推理前把图片缩放到 512×512，更适合不依赖细节的快速检查。每张图片在模型处理前会自动缩放，图片 token 上限为 384；官方文章说明图片按 token 计费，价格与 V4-Flash 相同。

Files API 本身不收费，适合多次复用同一张图片或避免重复上传大文件，但模型读取图片产生的 token 仍会计费。

## 视觉 Agent 最值得落地的不是“看图聊天”

真正有工程价值的场景，是让图片成为任务证据的一部分。

例如修复一个前端页面，可以把流程设计成：

1. 输入问题截图、目标参考图和明确验收标准；
2. Agent 结合仓库代码、DOM、网络请求和截图形成假设；
3. 在受控权限范围内修改代码并运行工具；
4. 执行测试、构建、DOM 或 API 检查；
5. 重新截图，与目标状态做视觉比较；
6. 高风险修改进入人工审批，失败则回到上一轮修正。

![视觉任务从图片输入到代码执行、机器验证、截图比较和人工审批的闭环](https://denghy.cn/images/articles/deepseek-v4-flash-vision-harness/02-verification-loop.jpg)

这个闭环里，视觉模型负责提出判断，确定性工具负责提供证据。截图可以告诉你“看起来哪里不对”，但不能单独证明 DOM 语义、权限、接口响应、键盘操作和移动端断点都正确。

## 从 rc.6 或 rc.7 升级前，先做六件事

第一，确认它仍是预发布版本。两个 Release 都标记为 prerelease，仓库 README 也明确说明当前处于开发者预览阶段，未来仍可能出现破坏兼容性的变化。

第二，固定确切版本，不要让生产工作流自动追随 latest：

```bash
npx @deepseek-ai/dsh@0.1.1-rc.1 web
```

第三，检查 Node.js。v0.1.1-rc.1 仓库根配置要求 Node.js `^22.19.0 || >=24.0.0`。

第四，升级前备份会话数据。rc.8 的 Release 明确说明 SQLite 后端的数据结构不兼容；即使新实现改善了读写、分叉性能和存储体积，也不能跳过备份与回滚验证。

第五，不要跳过安全版本。rc.1 包含 Bubblewrap 沙箱绕过修复；如果工作流允许 Agent 执行命令或写入文件，应把沙箱、工作区范围、凭据隔离和人工审批一起复测。

第六，为图片建立预算和验证规则。限制图片数量与尺寸，按任务选择 `detail`，不要把视觉判断直接当作完成证据，并在长会话中监控历史图片载荷。

## 最后

DeepSeek-V4-Flash-Vision-Exp 让模型开始看见，rc.8 与 rc.1 则让 DeepSeek Harness 开始具备接住视觉输入、把它交给模型、编排执行并控制风险的基础设施。

这比“增加一个图片上传按钮”重要得多。

下一阶段的 Coding Agent，不只是读代码和日志，还会读截图、设计稿、图表和运行结果。但模型看见什么，只是任务的起点；Harness 能否保存状态、限制权限、调用验证工具并留下可审计证据，仍然决定它能不能走到可靠交付的终点。

---

资料来源：

- [DeepSeek Harness v0.1.0-rc.8 Release](https://github.com/deepseek-ai/deepseek-harness/releases/tag/dsh-v0.1.0-rc.8)
- [DeepSeek Harness v0.1.1-rc.1 Release](https://github.com/deepseek-ai/deepseek-harness/releases/tag/dsh-v0.1.1-rc.1)
- [DeepSeek API 图像理解指南](https://api-docs.deepseek.com/zh-cn/guides/vision)
- [DeepSeek Harness 更新：官方多模态支持](https://mp.weixin.qq.com/s/xw-hxtHMcxxSGqbSbp98RQ)
- [V4-Flash-Vision-Exp 上线，开启多模态 API 服务](https://mp.weixin.qq.com/s/UGMfvPMwBIB4oFYZZejekA)

资料核对时间：2026-08-21。模型与 Harness 均在快速迭代，实际使用时请以最新官方文档、Release 和价格页面为准。
