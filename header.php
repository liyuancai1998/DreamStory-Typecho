<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$heroLines = preg_split('/\r\n|\r|\n/', trim(fireflyThemeOption('heroImages', '')));
$heroImages = array_values(array_filter(array_map('trim', $heroLines)));
ob_start();
$this->options->themeUrl('default.jpg');
$defaultHeroImage = trim(ob_get_clean());
$heroImage = count($heroImages) > 0 ? $heroImages[array_rand($heroImages)] : $defaultHeroImage;
$isHome = $this->is('index');
$archiveTitle = fireflyArchiveTitle($this);
$pageTitle = $archiveTitle ? $archiveTitle . ' - ' . $this->options->title : $this->options->title;
$logoText = fireflyThemeOption('logoText', $this->options->title);
$heroTitle = fireflyThemeOption('heroTitle', $this->options->title);
$heroSubtitle = fireflyThemeOption('heroSubtitle', $this->options->description);
$heroTypingLines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $heroSubtitle))));
if (empty($heroTypingLines) && $heroSubtitle !== '') {
    $heroTypingLines[] = $heroSubtitle;
}
$sidebarPosition = fireflyThemeOption('sidebarPosition', 'left');
$hasSidebar = $sidebarPosition !== 'none';
$navbarStyle = fireflyThemeOption('navbarStyle', 'glass');
$navbarWidth = fireflyThemeOption('navbarWidth', 'boxed');
$homeHeroHeight = max(36, min(90, intval(fireflyThemeOption('homeHeroHeight', '58'))));
$innerHeroHeight = max(22, min(70, intval(fireflyThemeOption('innerHeroHeight', '36'))));
$heroOverlayOpacity = max(0, min(1, floatval(fireflyThemeOption('heroOverlayOpacity', '0.20'))));
$heroStyle = '--home-hero-height: ' . $homeHeroHeight . 'vh; --inner-hero-height: ' . $innerHeroHeight . 'vh; --hero-overlay-opacity: ' . $heroOverlayOpacity . ';';
if ($heroImage) {
    $heroStyle .= ' background-image: url(' . htmlspecialchars($heroImage) . ');';
}
$musicServer = fireflyThemeOption('musicServer', 'netease');
$musicType = fireflyThemeOption('musicType', 'playlist');
$musicIdRaw = fireflyThemeOption('musicId', '10046455237');
$musicId = preg_match('/(?:id=|playlist\/|song\/|album\/|artist\/)(\d+)/', $musicIdRaw, $musicMatch)
    ? $musicMatch[1]
    : $musicIdRaw;
$musicApi = fireflyThemeOption('musicApi', 'https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r');
$musicFallbackApis = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', fireflyThemeOption('musicFallbackApis', '')))));
?>
<!doctype html>
<html lang="zh-CN" data-default-theme="<?php echo htmlspecialchars(fireflyThemeOption('defaultThemeMode', 'system')); ?>" style="--hue: <?php echo intval(fireflyThemeOption('themeHue', '210')); ?>">
<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="renderer" content="webkit">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <?php $fireflyCssVersion = @filemtime(__DIR__ . '/assets/css/firefly.css') ?: time(); ?>
    <link rel="stylesheet" href="<?php $this->options->themeUrl('assets/css/firefly.css'); ?>?v=<?php echo $fireflyCssVersion; ?>">
    <?php if (fireflyThemeOption('customCss')): ?>
        <style><?php echo fireflyThemeOption('customCss'); ?></style>
    <?php endif; ?>
    <?php $this->header(); ?>
</head>
<body class="<?php echo $isHome ? 'is-home' : 'is-inner'; ?> sidebar-<?php echo htmlspecialchars($sidebarPosition); ?> nav-<?php echo htmlspecialchars($navbarStyle); ?> nav-<?php echo htmlspecialchars($navbarWidth); ?>">
<script>
(function () {
    var root = document.documentElement;
    var mode = localStorage.getItem('firefly-theme') || root.dataset.defaultTheme || 'system';
    var hue = localStorage.getItem('firefly-hue');
    var dark = mode === 'dark' || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    root.classList.toggle('dark', dark);
    if (hue) root.style.setProperty('--hue', hue);
})();
</script>

<header class="site-navbar" id="site-navbar">
    <div class="navbar-inner">
        <a class="brand" href="<?php $this->options->siteUrl(); ?>">
            <?php if (fireflyThemeOption('logoUrl')): ?>
                <img src="<?php echo htmlspecialchars(fireflyThemeOption('logoUrl')); ?>" alt="<?php $this->options->title(); ?>">
            <?php else: ?>
                <span class="brand-icon">&#10022;</span>
            <?php endif; ?>
            <span><?php echo htmlspecialchars($logoText); ?></span>
        </a>
        <nav class="nav-links" aria-label="主导航">
            <a<?php if ($this->is('index')): ?> class="current"<?php endif; ?> href="<?php $this->options->siteUrl(); ?>">首页</a>
            <?php fireflyRenderCategoryNav($this, false); ?>
            <?php fireflyRenderCustomNav(false); ?>
            <?php if (fireflyIsChecked('navbarItems', 'pages')): ?>
                <?php \Widget\Contents\Page\Rows::alloc()->to($pages); ?>
                <?php while ($pages->next()): ?>
                    <?php if (empty($pages->template)): ?>
                        <a<?php if ($this->is('page', $pages->slug)): ?> class="current"<?php endif; ?> href="<?php $pages->permalink(); ?>"><?php $pages->title(); ?></a>
                    <?php endif; ?>
                <?php endwhile; ?>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <form class="nav-search" method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
                <span>&#8981;</span>
                <input type="search" name="s" placeholder="搜索" autocomplete="off">
            </form>
            <button class="icon-btn search-mini" id="search-toggle" aria-label="搜索"><i data-lucide="search"></i></button>
            <button class="icon-btn" id="music-toggle" aria-label="音乐"><i data-lucide="music-2"></i></button>
            <button class="icon-btn" id="display-settings-toggle" aria-label="显示设置"><i data-lucide="settings"></i></button>
            <?php if ($this->user->hasLogin()): ?>
                <a class="icon-btn nav-admin-link" href="<?php $this->options->adminUrl(); ?>" aria-label="进入后台"><i data-lucide="house"></i></a>
            <?php else: ?>
                <a class="icon-btn nav-admin-link" href="<?php $this->options->adminUrl('login.php'); ?>" aria-label="登录后台"><i data-lucide="log-in"></i></a>
            <?php endif; ?>
            <?php if (fireflyIsChecked('features', 'darkSwitch')): ?>
                <button class="icon-btn" id="theme-toggle" aria-label="切换主题"><i data-lucide="circle-dot-dashed"></i></button>
            <?php endif; ?>
            <button class="icon-btn mobile-only" id="menu-toggle" aria-label="菜单"><i data-lucide="menu"></i></button>
        </div>
    </div>
    <div class="mobile-menu" id="mobile-menu">
        <a href="<?php $this->options->siteUrl(); ?>">首页</a>
        <?php fireflyRenderCategoryNav($this, true); ?>
        <?php fireflyRenderCustomNav(true); ?>
        <?php if (fireflyIsChecked('navbarItems', 'pages')): ?>
            <?php \Widget\Contents\Page\Rows::alloc()->to($mobilePages); ?>
            <?php while ($mobilePages->next()): ?>
                <?php if (empty($mobilePages->template)): ?>
                    <a<?php if ($this->is('page', $mobilePages->slug)): ?> class="current"<?php endif; ?> href="<?php $mobilePages->permalink(); ?>"><?php $mobilePages->title(); ?></a>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php endif; ?>
        <?php if ($this->user->hasLogin()): ?>
            <a href="<?php $this->options->adminUrl(); ?>">进入后台</a>
        <?php else: ?>
            <a href="<?php $this->options->adminUrl('login.php'); ?>">登录后台</a>
        <?php endif; ?>
    </div>
</header>

<div class="search-panel" id="search-panel">
    <form method="post" action="<?php $this->options->siteUrl(); ?>" role="search">
        <input type="search" name="s" placeholder="输入关键字搜索" autocomplete="off">
        <button type="submit">搜索</button>
    </form>
</div>

<section
    class="nav-music-panel music-widget card-base"
    id="nav-music-panel"
    data-meting-player
    data-server="<?php echo htmlspecialchars($musicServer); ?>"
    data-type="<?php echo htmlspecialchars($musicType); ?>"
    data-id="<?php echo htmlspecialchars($musicId); ?>"
    data-api="<?php echo htmlspecialchars($musicApi); ?>"
    data-fallback-apis="<?php echo htmlspecialchars(json_encode($musicFallbackApis)); ?>"
    aria-label="音乐播放器"
>
    <div class="music-row">
        <div class="music-cover">
            <img class="music-cover-img" src="" alt="">
            <span class="music-note">♪</span>
        </div>
        <div class="music-info">
            <strong class="music-title">正在加载歌单...</strong>
            <span class="music-artist">Meting API</span>
        </div>
        <button type="button" class="music-lyrics-toggle nav-list-toggle" aria-label="歌词"><i data-lucide="list-music"></i></button>
    </div>
    <div class="music-progress" role="progressbar" aria-label="音乐播放进度">
        <span class="music-progress-bar"></span>
    </div>
    <div class="music-controls nav-music-controls">
        <button type="button" class="music-mode" aria-label="播放模式"><i data-lucide="repeat"></i></button>
        <button type="button" class="music-prev" aria-label="上一首"><i data-lucide="skip-back"></i></button>
        <button type="button" class="music-play" aria-label="播放"><i data-lucide="play"></i></button>
        <button type="button" class="music-next" aria-label="下一首"><i data-lucide="skip-forward"></i></button>
        <button type="button" class="music-list-toggle" aria-label="歌单"><i data-lucide="list"></i></button>
    </div>
    <div class="music-playlist"></div>
    <div class="music-lyrics"></div>
</section>

<section class="display-settings-panel" id="display-settings-panel" aria-label="显示设置">
    <div class="settings-block">
        <h3>主题色相 <output id="hue-value"><?php echo intval(fireflyThemeOption('themeHue', '210')); ?></output></h3>
        <input class="hue-range" id="hue-range" type="range" min="0" max="360" value="<?php echo intval(fireflyThemeOption('themeHue', '210')); ?>">
    </div>
    <div class="settings-block">
        <h3>壁纸模式</h3>
        <div class="settings-grid">
            <button data-wallpaper-mode="banner" class="settings-choice">横幅壁纸</button>
            <button data-wallpaper-mode="fullscreen" class="settings-choice">全屏壁纸</button>
            <button data-wallpaper-mode="overlay" class="settings-choice">全屏透明</button>
            <button data-wallpaper-mode="solid" class="settings-choice">纯色背景</button>
        </div>
    </div>
    <div class="settings-block">
        <h3>壁纸设置</h3>
        <label class="settings-toggle"><span>首页壁纸标题</span><input id="setting-hero-title" type="checkbox" checked></label>
        <label class="settings-toggle"><span>水波纹动画</span><input id="setting-waves" type="checkbox" checked></label>
        <label class="settings-toggle"><span>渐变过渡</span><input id="setting-fade" type="checkbox" checked></label>
    </div>
    <div class="settings-block">
        <h3>特效设置</h3>
        <label class="settings-toggle"><span>樱花特效</span><input id="setting-sakura" type="checkbox"></label>
    </div>
    <div class="settings-block">
        <h3>文章布局</h3>
        <div class="settings-grid">
            <button data-panel-layout="list" class="settings-choice">列表</button>
            <button data-panel-layout="grid" class="settings-choice">网格</button>
        </div>
    </div>
</section>

<section class="hero" style="<?php echo $heroStyle; ?>">
    <div class="hero-dim"></div>
    <?php if ($isHome): ?>
        <div class="hero-copy">
            <h1><?php echo htmlspecialchars($heroTitle); ?></h1>
            <?php if (!empty($heroTypingLines)): ?>
                <p class="hero-typing" data-typing-lines="<?php echo htmlspecialchars(json_encode($heroTypingLines, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($heroTypingLines[0]); ?></p>
            <?php endif; ?>
        </div>
    <?php elseif ($this->is('post')): ?>
        <div class="hero-copy hero-copy-small">
            <h1><?php $this->title(); ?></h1>
            <p><?php $this->date('Y-m-d'); ?> &middot; <?php $this->category(','); ?></p>
        </div>
    <?php elseif ($archiveTitle): ?>
        <div class="hero-copy hero-copy-small">
            <h1><?php echo htmlspecialchars($archiveTitle); ?></h1>
        </div>
    <?php endif; ?>
    <div class="waves" id="header-waves" aria-hidden="true">
        <svg viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="geometricPrecision">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s58 18 88 18 58-18 88-18 58 18 88 18v48h-352z"></path>
            </defs>
            <g class="parallax">
                <use href="#gentle-wave" x="48" y="0"></use>
                <use href="#gentle-wave" x="48" y="3"></use>
                <use href="#gentle-wave" x="48" y="5"></use>
                <use href="#gentle-wave" x="48" y="7"></use>
            </g>
        </svg>
    </div>
</section>

<main class="page-shell <?php echo $hasSidebar ? 'with-sidebar' : 'no-sidebar'; ?> layout-<?php echo htmlspecialchars($sidebarPosition); ?>">
