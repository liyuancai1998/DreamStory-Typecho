<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$side = isset($side) && in_array($side, ['left', 'right']) ? $side : 'left';
if (!fireflySidebarEnabled($side)) return;

$profileName = fireflyThemeOption('profileName', $this->options->title);
$profileBio = fireflyThemeOption('profileBio', $this->options->description);
$profileAvatar = fireflyThemeOption('profileAvatar', '');
$profileInitial = function_exists('mb_substr') ? mb_substr($profileName, 0, 1, 'UTF-8') : substr($profileName, 0, 1);
$profileLinks = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', fireflyThemeOption('profileLinks', ''))));
$timelineUrl = '';
try {
    \Widget\Contents\Page\Rows::alloc()->to($timelinePages);
    while ($timelinePages->next()) {
        if ($timelinePages->template === 'timeline.php') {
            ob_start();
            $timelinePages->permalink();
            $timelineUrl = trim(ob_get_clean());
            break;
        }
    }
} catch (\Throwable $e) {
    $timelineUrl = '';
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

<aside class="sidebar sidebar-<?php echo htmlspecialchars($side); ?>">
    <?php if ($side === 'left'): ?>
        <?php if (fireflySidebarHas('left', 'profile')): ?>
            <section class="widget profile-widget card-base">
                <?php if ($timelineUrl): ?><a class="profile-entry-link" href="<?php echo htmlspecialchars($timelineUrl); ?>" aria-label="查看动态"><?php endif; ?>
                    <div class="profile-avatar">
                        <?php if ($profileAvatar): ?>
                            <img src="<?php echo htmlspecialchars($profileAvatar); ?>" alt="<?php echo htmlspecialchars($profileName); ?>">
                        <?php else: ?>
                            <span><?php echo htmlspecialchars($profileInitial); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3><?php echo htmlspecialchars($profileName); ?></h3>
                    <span class="profile-mark"></span>
                <?php if ($timelineUrl): ?></a><?php endif; ?>
                <p><?php echo htmlspecialchars($profileBio); ?></p>
                <?php if (!empty($profileLinks)): ?>
                    <div class="profile-links">
                        <?php foreach ($profileLinks as $line): ?>
                            <?php
                            $parts = array_pad(array_map('trim', explode('|', $line, 3)), 3, '');
                            $label = $parts[0] ?? '';
                            $href = $parts[1] ?? '#';
                            $icon = function_exists('fireflyProfileLinkIcon') ? fireflyProfileLinkIcon($label, $href, $parts[2] ?? '') : '';
                            if ($label === '') continue;
                            ?>
                            <a
                                class="<?php echo $icon ? 'profile-link-icon' : 'profile-link-text'; ?>"
                                href="<?php echo htmlspecialchars($href); ?>"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="<?php echo htmlspecialchars($label); ?>"
                                title="<?php echo htmlspecialchars($label); ?>"
                            >
                                <?php echo $icon ?: htmlspecialchars($label); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('left', 'announcement')): ?>
            <section class="widget card-base">
                <h3><?php echo htmlspecialchars(fireflyThemeOption('announcementTitle', '公告')); ?></h3>
                <p class="announcement-text"><?php echo nl2br(htmlspecialchars(fireflyThemeOption('announcementText', '欢迎来到我的博客！这是一则示例公告。'))); ?></p>
                <a class="small-pill" href="<?php $this->options->siteUrl(); ?>">了解更多</a>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('left', 'music')): ?>
            <section
                class="widget music-widget card-base"
                data-meting-player
                data-server="<?php echo htmlspecialchars($musicServer); ?>"
                data-type="<?php echo htmlspecialchars($musicType); ?>"
                data-id="<?php echo htmlspecialchars($musicId); ?>"
                data-api="<?php echo htmlspecialchars($musicApi); ?>"
                data-fallback-apis="<?php echo htmlspecialchars(json_encode($musicFallbackApis)); ?>"
            >
                <h3>音乐</h3>
                <div class="music-row">
                    <div class="music-cover">
                        <img class="music-cover-img" src="" alt="">
                        <span class="music-note">♪</span>
                    </div>
                    <div class="music-info">
                        <strong class="music-title">正在加载歌单...</strong>
                        <span class="music-artist">Meting API</span>
                    </div>
                </div>
                <div class="music-progress" role="progressbar" aria-label="音乐播放进度">
                    <span class="music-progress-bar"></span>
                </div>
                <div class="music-controls">
                    <button type="button" class="music-prev" aria-label="上一首">‹</button>
                    <button type="button" class="music-play" aria-label="播放">▶</button>
                    <button type="button" class="music-next" aria-label="下一首">›</button>
                    <button type="button" class="music-list-toggle" aria-label="歌单">☰</button>
                </div>
                <div class="music-playlist"></div>
                <div class="music-lyrics"></div>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('left', 'category')): ?>
            <section class="widget card-base">
                <h3>分类</h3>
                <ul class="count-list">
                    <?php \Widget\Metas\Category\Rows::alloc()->to($categories); ?>
                    <?php while ($categories->next()): ?>
                        <li>
                            <a href="<?php $categories->permalink(); ?>"><?php $categories->name(); ?></a>
                            <span><?php echo intval($categories->count); ?></span>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('left', 'tags')): ?>
            <section class="widget card-base">
                <h3>标签</h3>
                <div class="tag-cloud">
                    <?php \Widget\Metas\Tag\Cloud::alloc('ignoreZeroCount=1&limit=18')->to($tags); ?>
                    <?php while ($tags->next()): ?>
                        <a href="<?php $tags->permalink(); ?>"><?php $tags->name(); ?></a>
                    <?php endwhile; ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($side === 'right'): ?>
        <?php if (fireflySidebarHas('right', 'stats')): ?>
            <section class="widget stats-widget card-base">
                <h3>站点统计</h3>
                <ul>
                    <li><span>文章</span><strong><?php echo number_format(fireflyPostCount()); ?></strong></li>
                    <li><span>分类</span><strong><?php echo number_format(fireflyMetaCount('category')); ?></strong></li>
                    <li><span>标签</span><strong><?php echo number_format(fireflyMetaCount('tag')); ?></strong></li>
                    <li><span>总字数</span><strong><?php echo number_format(fireflyWordCount()); ?></strong></li>
                    <li><span>最后活动</span><strong><?php echo date('Y-m-d'); ?></strong></li>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('right', 'calendar')): ?>
            <section class="widget calendar-widget card-base" data-calendar-widget>
                <div class="calendar-head">
                    <button type="button" data-calendar-prev aria-label="上个月">‹</button>
                    <strong data-calendar-title><?php echo date('Y年n月'); ?></strong>
                    <button type="button" data-calendar-next aria-label="下个月">›</button>
                </div>
                <div class="calendar-grid" data-calendar-grid>
                    <?php foreach (['日', '一', '二', '三', '四', '五', '六'] as $day): ?>
                        <span class="muted"><?php echo $day; ?></span>
                    <?php endforeach; ?>
                    <?php
                    $firstWeekday = intval(date('w', strtotime(date('Y-m-01'))));
                    $days = intval(date('t'));
                    for ($i = 0; $i < $firstWeekday; $i++) echo '<span></span>';
                    for ($day = 1; $day <= $days; $day++):
                    ?>
                        <span class="<?php echo $day === intval(date('j')) ? 'today' : ''; ?>"><?php echo $day; ?></span>
                    <?php endfor; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('right', 'recentPosts')): ?>
            <section class="widget card-base">
                <h3>最新文章</h3>
                <ul class="widget-list">
                    <?php \Widget\Contents\Post\Recent::alloc('pageSize=6')->parse('<li><a href="{permalink}">{title}</a></li>'); ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('right', 'recentComments')): ?>
            <section class="widget card-base">
                <h3>最新评论</h3>
                <ul class="widget-list">
                    <?php \Widget\Comments\Recent::alloc('pageSize=5')->to($comments); ?>
                    <?php while ($comments->next()): ?>
                        <li><a href="<?php $comments->permalink(); ?>"><?php $comments->author(false); ?></a>: <?php $comments->excerpt(28, '...'); ?></li>
                    <?php endwhile; ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('right', 'archive')): ?>
            <section class="widget card-base">
                <h3>归档</h3>
                <ul class="widget-list">
                    <?php \Widget\Contents\Post\Date::alloc('type=month&format=Y 年 m 月')->parse('<li><a href="{permalink}">{date}</a></li>'); ?>
                </ul>
            </section>
        <?php endif; ?>

        <?php if (fireflySidebarHas('right', 'other')): ?>
            <section class="widget card-base">
                <h3>其它</h3>
                <ul class="widget-list">
                    <?php if ($this->user->hasLogin()): ?>
                        <li><a href="<?php $this->options->adminUrl(); ?>">进入后台</a></li>
                        <li><a href="<?php $this->options->logoutUrl(); ?>">退出登录</a></li>
                    <?php else: ?>
                        <li><a href="<?php $this->options->adminUrl('login.php'); ?>">登录</a></li>
                    <?php endif; ?>
                    <li><a href="<?php $this->options->feedUrl(); ?>">文章 RSS</a></li>
                    <li><a href="<?php $this->options->commentsFeedUrl(); ?>">评论 RSS</a></li>
                </ul>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</aside>
