> 不用装第三方插件，也不用等原生客户端。借助 Chrome 自带的“创建快捷方式 / 安装页面为应用”，可以把 DeepSeek Harness 的本地 Web UI 变成独立窗口：有自己的应用图标，可固定在 Dock，也能用 Spotlight 搜索打开。

![DeepSeek Harness Web UI 桌面化封面](https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/00-cover.png)

每天都要打开的工具，如果每次都得先找浏览器、再翻标签页，用久了总会觉得有点绕。

DeepSeek Harness 也是如此。它的 Web UI 默认运行在本机：

```text
http://127.0.0.1:3080
```

这个地址只有自己电脑可以访问，安全又直接，但使用路径通常是：打开浏览器 → 输入地址 → 在一堆标签页里找到它。

其实，Chrome 已经提供了更顺手的入口：把这个本地页面包装成一个独立应用窗口。

先说清楚：这不是新的原生客户端，也不会让 DSH 离线运行。它仍然使用 Chrome 打开本地 Web UI，只是去掉地址栏、标签页和书签栏，获得更接近桌面 App 的使用体验。

## 第一步：先把 DSH 服务跑起来

如果你是通过 npm 使用 DSH，官方 README 给出的启动方式是：

```bash
npx @deepseek-ai/dsh web
```

从源码运行则使用：

```bash
pnpm dsh web
```

服务启动后，在浏览器访问：

```text
http://127.0.0.1:3080
```

![先启动 DeepSeek Harness Web UI 服务](https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/01-start-command.png)

这里有一个最重要的前提：**桌面图标只负责打开网址，不负责启动服务。**

所以请让终端中的 DSH 服务保持运行。服务停了，桌面应用自然也打不开。

## 第二步：用 Chrome 打开 Web UI

在 Chrome 地址栏输入 `http://127.0.0.1:3080`，确认页面可以正常加载。

![Chrome 中打开 DeepSeek Harness Web UI 的真实界面](https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/01-browser-webui-clean.jpg)

这一步不要急着创建快捷方式。先确认页面、会话列表和输入框都正常，避免把一个尚未启动成功的地址做成应用。

## 第三步：把页面创建为桌面应用

点击 Chrome 右上角的 `⋮` 菜单。不同版本的 Chrome 文案可能略有差异，看到下面任意一条路径都可以。

下图是本机已经创建应用后的真实菜单：同一位置会显示“在 DeepSeek Harness 中打开”，下方仍然可以选择“创建快捷方式…”。账号、标签页和书签信息已做脱敏处理。

![Chrome 真实菜单：在 DeepSeek Harness 中打开或创建快捷方式](https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/02-install-path.png)

### 路径 A：安装页面为应用

近期版本通常位于：

```text
⋮ → 投放、保存和分享 → 安装页面为应用…
```

部分版本把这一组菜单写成“转换、保存和分享”。进入后直接确认安装即可。

### 路径 B：创建快捷方式

如果你看到的是“创建快捷方式…”，创建时请勾选：

```text
在窗口中打开
```

这一步决定了它打开后是普通浏览器标签页，还是没有地址栏的独立窗口。

如果同一位置已经显示“在 DeepSeek Harness 中打开”，说明这个应用已经创建成功，无需重复安装，直接点击即可进入独立窗口。

## 完成：它已经是 Dock 里的独立窗口了

在 macOS 上，Chrome 创建的应用通常位于：

```text
~/Applications/Chrome Apps.localized/
```

本机实测可以在这里看到 `DeepSeek Harness.app`。把它拖到 Dock，就能固定在常用位置；按 `⌘ + 空格` 打开 Spotlight，搜索 “DeepSeek Harness” 也可以直接启动。

![DeepSeek Harness 应用在 Chrome Apps.localized 中的真实位置](https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/04-app-location.jpg)

打开后，Web UI 会出现在一个真正独立的窗口里：

![没有地址栏和标签页的 DeepSeek Harness 独立窗口](https://denghy.cn/images/articles/deepseek-harness-webui-desktop-app/03-app-window.jpg)

和普通浏览器标签页相比，它有几个很直观的变化：

- 没有地址栏、书签栏和标签页，界面更干净；
- 有独立的应用图标，可以固定在 Dock；
- 可以通过 Spotlight 搜索唤起；
- 不会混在日常浏览器标签页里；
- 仍然保留 Chrome 的网页运行环境，不需要额外插件。

这已经足以覆盖大多数“我只是想更快打开 DSH”的场景。

## 两个容易踩的坑

### 1. 图标能打开，不代表服务已经启动

如果启动后出现空白页或“无法访问此网站”，先检查终端里的 DSH 服务是否还在运行，再确认 `http://127.0.0.1:3080` 能不能在普通 Chrome 标签页里打开。

排查顺序很简单：

1. 先运行 `npx @deepseek-ai/dsh web`；
2. 再用普通浏览器打开 `127.0.0.1:3080`；
3. 页面正常后，再点击桌面应用图标。

### 2. 安装后直接进入全屏

当前 DSH Web UI 的 manifest 将显示模式声明为 `fullscreen`。如果你通过“安装页面为应用”创建后直接进入全屏，而自己更习惯普通窗口，可以改用：

```text
创建快捷方式… → 在窗口中打开
```

本文实测的独立窗口就是这种效果。

## Safari 和 Windows 也能这样做

如果你不用 Chrome，也有相近方案：

- **Safari（macOS Sonoma 14 及以上）**：打开页面后，选择“文件 → 添加到程序坞”；
- **Microsoft Edge（Windows）**：打开页面后，选择“⋯ → 应用 → 将此站点作为应用安装”。

不同浏览器的菜单名称会随版本调整，但核心原理相同：用浏览器的应用模式打开本地 Web UI。

## 最后再强调一次

这套方法解决的是“快速进入”和“独立窗口体验”，不是把 Web UI 变成真正的原生程序。

它的优点也恰恰在这里：不需要额外插件，不改 DSH 代码，几十秒就能完成；以后 DSH 页面更新，桌面入口仍然打开同一个本地地址。

DeepSeek Harness 目前仍处于 Developer Preview，功能和界面都在快速迭代。本文以当前版本的 macOS + Chrome 实测界面为准；如果菜单文案变化，寻找“安装页面为应用”或“创建快捷方式”即可。

项目地址：[github.com/deepseek-ai/deepseek-harness](https://github.com/deepseek-ai/deepseek-harness)

---

如果这篇文章帮你少翻了几个标签页，欢迎把它分享给也在使用 DeepSeek Harness 的朋友。
