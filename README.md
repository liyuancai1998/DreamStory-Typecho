# DreamStory 主题使用说明

DreamStory 是一款 Typecho 博客主题，内置首页横幅、左右侧栏、资料卡、音乐播放器、赞助页、Bangumi 番组页、相册页、动态时间线、Markdown 编辑器和主题设置备份功能。

当前版本已作为免费标准版使用，不需要填写授权码，也不需要连接授权服务器。

## 适用环境

- 程序：Typecho
- PHP：建议 PHP 7.4 及以上，推荐 PHP 8.0+
- 浏览器：现代浏览器即可
- 目录位置：`usr/themes/DreamStory`

如果你的服务器关闭了 `curl`，Bangumi 功能仍可尝试用浏览器端异步加载，但服务端预加载会受影响。

## 安装与启用

1. 将主题目录放到 Typecho 的 `usr/themes/DreamStory`。
2. 登录 Typecho 后台。
3. 进入 `控制台 -> 外观`。
4. 找到 `DreamStory` 并启用。
5. 进入 `设置外观`，按左侧分组完成主题配置。

建议首次启用后先保存一次主题设置，让 Typecho 写入默认配置。

## 首次推荐配置

最少配置以下项目即可正常使用：

- `基础设置`：站点标题、默认封面、默认主题模式。
- `导航顶部`：是否显示独立页面，自定义导航菜单。
- `壁纸横幅`：首页横幅图片、首页标题、副标题。
- `侧栏组件`：选择左侧栏、右侧栏显示哪些模块。
- `资料公告`：头像、昵称、简介、公告。
- `前端功能`：暗色切换、列表布局切换、返回顶部按钮等。

## 后台设置分组说明

### 基础设置

- `logoText`：导航栏文字 Logo，留空时使用站点标题。
- `logoUrl`：Logo 图片地址，填写图片 URL 后优先显示图片。
- `archivePageSize`：首页、分类、标签、搜索等列表每页文章数。填 `0` 使用 Typecho 全局阅读设置。
- `enableMarkdownEditor`：是否启用后台 Markdown 编辑器。
- `markdownEditorHeight`：Markdown 编辑器高度，单位 px，最低 360。
- `markdownEditorCodeTheme`：Markdown 代码高亮主题，可选 `default`、`monokai`、`ambiance`、`twilight`、`pastel-on-dark`。
- `defaultCover`：文章没有封面且正文没有图片时使用的默认封面。
- `defaultThemeMode`：默认主题模式，可选跟随系统、浅色、深色。
- `themeHue`：主题色 Hue，填写 `0-360` 的数字。
- `customCss`：自定义 CSS，会输出到页面 `<head>` 中。

### 导航顶部

- `navbarStyle`：顶部导航样式，可选毛玻璃、实色卡片、透明。
- `navbarWidth`：顶部导航宽度，可选居中宽度、通栏宽度。
- `navbarItems`：是否在导航中显示独立页面。
- `customNavItems`：自定义导航菜单，每行一个。

自定义导航格式：

```text
名称|链接|父级
```

示例：

```text
我的|| 
相册|/gallery.html|我的
动态|/timeline.html|我的
GitHub|https://github.com|链接
邮箱|mailto:name@example.com|联系
电话|tel:13800000000|联系
```

说明：

- 链接留空或填写 `#` 时只作为父级菜单，不跳转。
- 链接支持站内路径、完整外链、简写域名、`mailto:`、`tel:`。
- 第三列填写父级名称时，会作为该父级下的二级菜单。

### 壁纸横幅

- `homeHeroHeight`：首页顶部横幅高度，填写 vh 数字，例如 `58`。
- `innerHeroHeight`：内页顶部横幅高度，填写 vh 数字，例如 `36`。
- `heroOverlayOpacity`：横幅遮罩强度，范围 `0-1`，数字越大图片越暗。
- `heroImages`：横幅背景图，每行一个图片 URL。
- `heroTitle`：首页大标题，留空时使用站点标题。
- `heroSubtitle`：首页副标题，留空时使用站点描述。

示例：

```text
https://example.com/images/banner-1.jpg
https://example.com/images/banner-2.jpg
```

### 侧栏组件

- `sidebarPosition`：侧栏布局，可选只显示左侧栏、只显示右侧栏、左右都显示、不显示。
- `leftSidebarBlock`：左侧栏模块，包括资料卡、公告、音乐卡片、分类、标签。
- `rightSidebarBlock`：右侧栏模块，包括站点统计、日历、最新文章、最新评论、归档、其他链接。

### 资料公告

- `profileAvatar`：资料卡头像 URL，留空时使用站点标题首字。
- `profileName`：资料卡名称，留空时使用站点标题。
- `profileBio`：资料卡简介，留空时使用站点描述。
- `profileLinks`：资料卡按钮，每行一个。
- `announcementTitle`：公告标题。
- `announcementText`：公告内容。

资料卡按钮格式：

```text
文字|链接
```

示例：

```text
GitHub|https://github.com/yourname
关于我|/about.html
邮箱|mailto:name@example.com
```

### 音乐播放器

- `musicServer`：音乐平台，支持网易云音乐、QQ 音乐、酷狗音乐、百度音乐。
- `musicType`：音乐类型，支持歌单、单曲、专辑、歌手、搜索关键词。
- `musicId`：歌单、歌曲、专辑等 ID，也可以粘贴对应链接，主题会尝试提取 ID。
- `musicApi`：Meting API 地址，支持 `:server`、`:type`、`:id`、`:r` 占位符。
- `musicFallbackApis`：备用 Meting API，每行一个，主 API 失败时依次尝试。

默认 API 示例：

```text
https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r
```

### 前端功能

- `darkSwitch`：暗色模式切换。
- `layoutSwitch`：文章列表布局切换。
- `backTop`：返回顶部按钮。
- `sakura`：樱花飘落效果。

## 独立页面模板

DreamStory 提供 4 个独立页面模板：赞助页面、番组计划、相册页面、动态时间线。

创建方法：

1. 进入 Typecho 后台 `管理 -> 独立页面 -> 新增`。
2. 输入页面标题和缩略名。
3. 在页面模板中选择对应模板。
4. 发布页面。
5. 如需显示在导航中，可在 `导航顶部` 中勾选独立页面，或用自定义导航添加链接。

推荐缩略名：

| 页面 | 模板文件 | 推荐缩略名 | 示例链接 |
| --- | --- | --- | --- |
| 赞助页面 | `sponsor.php` | `sponsor` | `/sponsor.html` |
| 番组计划 | `bangumi.php` | `bangumi` | `/bangumi.html` |
| 相册页面 | `gallery.php` | `gallery` | `/gallery.html` |
| 动态时间线 | `timeline.php` | `timeline` | `/timeline.html` |

实际链接取决于你的 Typecho 永久链接规则。

## 赞助页面

赞助页面用于展示支付宝、微信、Ko-fi、爱发电以及赞助名单。

相关配置在 `赞助页面` 分组：

- `sponsorPageTitle`：赞助页标题。
- `sponsorPageDescription`：赞助页说明。
- `sponsorPageNotice`：赞助提示文字。
- `sponsorAlipayQr`：支付宝收款码图片 URL。
- `sponsorWechatQr`：微信收款码图片 URL。
- `sponsorKofiUrl`：Ko-fi 赞助链接。
- `sponsorKofiTitle`：Ko-fi 卡片标题。
- `sponsorKofiDescription`：Ko-fi 卡片说明。
- `sponsorAfdianUrl`：爱发电赞助链接。
- `sponsorAfdianTitle`：爱发电卡片标题。
- `sponsorAfdianDescription`：爱发电卡片说明。
- `sponsorList`：赞助名单。

赞助名单格式：

```text
昵称|金额|日期|头像URL
```

示例：

```text
小明|￥20|2026-06-01|https://example.com/avatar/ming.jpg
Alice|$5|2026-06-02|
```

头像 URL 可以留空。

## Bangumi 番组计划

Bangumi 页面会根据你的 Bangumi 用户名或用户 ID 拉取收藏数据，并按分类展示。

相关配置在 `番组计划` 分组：

- `bangumiUserId`：Bangumi 用户名或用户 ID。
- `bangumiTitle`：番组页标题。
- `bangumiSubtitle`：番组页副标题。
- `bangumiCategories`：启用分类，可选动画、书籍、音乐、游戏、三次元。
- `bangumiCategoryOrder`：分类排序，使用英文逗号分隔。
- `bangumiItemsPerPage`：每页显示数量。
- `bangumiApi`：Bangumi API 地址，推荐 `https://api.bgm.tv`。
- `bangumiServerRender`：服务端预加载。服务器能稳定访问 Bangumi API 时再开启。
- `bangumiCacheMinutes`：缓存分钟数，默认 `360`。填 `0` 表示不缓存。
- `bangumiMaxTotal`：单分类最多拉取条数，填 `0` 表示不限制。
- `bangumiPageLimit`：API 每次请求数量，建议不超过 `50`。

分类排序示例：

```text
anime,book,music,game,real
```

分类 ID 对照：

| ID | 分类 |
| --- | --- |
| `anime` | 动画 |
| `book` | 书籍 |
| `music` | 音乐 |
| `game` | 游戏 |
| `real` | 三次元 |

如果页面提示未配置用户，请检查 `bangumiUserId`。如果数据为空，优先检查服务器或浏览器是否能访问 Bangumi API。

## 相册页面

相册页面由后台相册列表和主题目录中的图片文件共同组成。

相关配置在 `相册页面` 分组：

- `galleryTitle`：相册页标题。
- `galleryDescription`：相册页描述。
- `galleryColumnWidth`：相册详情瀑布流列宽，单位 px，默认 `240`。
- `galleryAlbums`：相册列表，每行一个相册。

相册列表格式：

```text
ID|名称|描述|地点|日期|标签逗号分隔|密码|密码提示|封面URL
```

示例：

```text
travel-2026|夏日旅行|海边和城市的照片|厦门|2026-06-01|旅行,海边|||
private-life|私人记录|只给朋友看的相册|家|2026-06-02|生活,私密|123456|密码是 123456|
```

图片存放位置：

```text
usr/themes/DreamStory/gallery/相册ID/
```

例如相册 ID 是 `travel-2026`，则图片放到：

```text
usr/themes/DreamStory/gallery/travel-2026/
```

支持的图片格式：

```text
jpg, jpeg, png, webp, gif
```

封面规则：

1. 如果 `封面URL` 已填写，优先使用该地址。
2. 如果相册目录下有 `cover.jpg`、`cover.png`、`cover.webp` 等封面图，优先使用封面图。
3. 如果没有封面图，则使用相册中的第一张图片。

访问相册详情时，主题会在相册页面链接后追加参数：

```text
/gallery.html?album=travel-2026
```

注意：相册密码是前端访问锁，适合普通展示保护，不适合作为高安全私密存储。

## 动态时间线

动态时间线可以展示后台配置的动态，也可以让管理员在前台发布动态。

相关配置在 `动态时间线` 分组：

- `timelineTitle`：动态页标题。
- `timelineSubtitle`：动态页副标题。
- `timelineCover`：动态页顶部封面，留空时使用主题 `default.jpg`。
- `timelineSocialLinks`：动态页社交按钮。
- `timelineItems`：静态动态内容。

社交按钮格式：

```text
名称|链接
```

示例：

```text
GitHub|https://github.com/yourname
微博|https://weibo.com/yourname
邮箱|mailto:name@example.com
```

动态内容格式：

```text
时间|作者|内容|标签逗号分隔|来源设备|点赞数|评论数|图片URL
```

示例：

```text
2026-06-04 15:30|站长|今天把博客换成了 DreamStory。|博客,日常|Windows|2|0|https://example.com/photo.jpg
2026-06-03 22:10|站长|今晚适合写点碎碎念。|随笔,音乐|Mobile|5|1|
```

多图可以用英文逗号分隔：

```text
2026-06-04 15:30|站长|一次旅行记录。|旅行|iPhone|8|0|https://example.com/1.jpg,https://example.com/2.jpg
```

管理员前台发布：

- 使用管理员账号登录。
- 进入动态时间线页面。
- 页面开启评论时，会显示发布框。
- 上传的图片保存到 `usr/themes/DreamStory/timeline-images/`。

如果无法上传图片，请检查 `timeline-images` 目录是否可写。目录不存在时主题会尝试自动创建。

## 文章与页面写作

### 文章封面

主题为文章和页面增加了一个自定义字段：

```text
cover
```

填写图片 URL 后，该图片会显示在文章卡片和文章页顶部。

封面优先级：

1. 文章或页面自定义字段 `cover`。
2. 正文中的第一张图片。
3. 主题设置里的 `defaultCover`。
4. 主题自带默认图。

### Markdown 编辑器

在 `基础设置` 中开启 `enableMarkdownEditor` 后，后台文章和页面编辑页会使用 Markdown 编辑器。

如果编辑器显示异常，可先关闭该选项并保存，再检查浏览器控制台或插件冲突。

### 彩色文字短代码

正文支持以下短代码：

```text
[font color=red]红色文字[/font]
[font color="#2563eb"]蓝色文字[/font]
```

`color` 可以填写颜色名称、HEX、RGB 等常见 CSS 颜色值。

## 设置备份与恢复

主题后台提供 `设置备份` 分组：

- `导出设置`：将当前表单中的主题设置导出为 JSON 文件。
- `选择备份文件`：选择之前导出的 JSON 文件。
- `导入到表单`：把 JSON 内容填回当前设置表单。

导入后需要检查配置内容，并点击页面底部的保存按钮，才会真正写入数据库。

升级主题或迁移站点前，建议同时备份：

- Typecho 数据库。
- `usr/themes/DreamStory` 主题目录。
- `usr/themes/DreamStory/gallery` 相册图片。
- `usr/themes/DreamStory/timeline-images` 动态图片。
- 主题设置备份 JSON。

## 免费标准版说明

当前 DreamStory 已按免费标准版处理：

- 不需要授权码。
- 不需要激活。
- 不拦截前台访问。
- 不连接授权服务器。
- 后台授权页仅作为状态展示。

如果后台看到免费标准版状态，属于正常现象。

## 常见问题

### 首页或文章没有封面

检查顺序：

1. 文章自定义字段 `cover` 是否填写了可访问图片 URL。
2. 正文中是否包含图片。
3. `基础设置 -> defaultCover` 是否已填写。
4. 图片链接是否支持外部访问。

### 自定义导航没有显示二级菜单

确认第三列父级名称和父级菜单第一列名称完全一致。例如：

```text
我的|#|
相册|/gallery.html|我的
动态|/timeline.html|我的
```

### 相册页面没有图片

确认：

- `galleryAlbums` 中的 ID 与目录名一致。
- 图片放在 `usr/themes/DreamStory/gallery/相册ID/`。
- 图片扩展名是 `jpg`、`jpeg`、`png`、`webp` 或 `gif`。
- 服务器对该目录有读取权限。

### 相册密码输入后仍打不开

确认后台 `galleryAlbums` 中的密码没有多余空格。密码对大小写敏感。

### Bangumi 页面一直加载或为空

检查：

- `bangumiUserId` 是否正确。
- `bangumiCategories` 至少启用了一个分类。
- `bangumiApi` 推荐填写 `https://api.bgm.tv`。
- 如果服务器访问 Bangumi 较慢，关闭 `bangumiServerRender`，让浏览器异步加载。
- 如果开启缓存后数据不更新，可以把 `bangumiCacheMinutes` 暂时设为 `0` 测试。

### 动态时间线无法前台发布

确认：

- 当前登录用户是管理员。
- 动态页面允许评论。
- 如果要上传图片，`usr/themes/DreamStory/timeline-images/` 需要可写。

### 音乐播放器没有声音或列表为空

检查：

- `musicServer`、`musicType`、`musicId` 是否匹配。
- 歌单或歌曲是否公开可访问。
- `musicApi` 是否可访问。
- 可在 `musicFallbackApis` 添加备用 Meting API。

### 后台设置保存后不生效

可以尝试：

1. 清理浏览器缓存。
2. 清理 Typecho 缓存或缓存插件。
3. 确认当前启用的是 `DreamStory` 主题。
4. 确认没有其他插件覆盖前台输出。

## 维护建议

- 修改主题文件前，先备份 `usr/themes/DreamStory`。
- 相册、动态图片建议单独备份。
- 自定义 CSS 尽量写在后台 `customCss`，减少升级主题时的文件冲突。
- 不建议直接删除 `common`、`assets`、`gallery` 等目录。
- 如果需要排查 PHP 语法，可使用 PHP 命令检查文件。

## 目录速览

```text
usr/themes/DreamStory/
├─ assets/              前端静态资源
├─ common/              主题公共功能
├─ gallery/             相册图片目录
├─ bangumi.php          番组计划页面模板
├─ gallery.php          相册页面模板
├─ sponsor.php          赞助页面模板
├─ timeline.php         动态时间线页面模板
├─ functions.php        主题功能与后台设置
├─ header.php           页面头部
├─ footer.php           页面底部
├─ sidebar.php          侧栏内容
├─ post.php             文章页
├─ page.php             独立页面
├─ index.php            列表页
└─ style.css            主题声明
```

