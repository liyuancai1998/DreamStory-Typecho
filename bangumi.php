<?php
/**
 * 番组计划
 *
 * @package custom
 * @author 冥冥冥冥帝酱
 * @version 1.0.0
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

$username = trim(fireflyThemeOption('bangumiUserId', ''));
$title = fireflyThemeOption('bangumiTitle', '番组计划');
$subtitle = fireflyThemeOption('bangumiSubtitle', '记录我的二次元之旅');
$itemsPerPage = max(4, min(60, intval(fireflyThemeOption('bangumiItemsPerPage', '12'))));
$bangumiApi = preg_replace('#/v0/?$#', '', rtrim(fireflyThemeOption('bangumiApi', 'https://api.bgm.tv'), '/'));
$categoryMap = fireflyBangumiCategories();
$enabledCategories = fireflyThemeOption('bangumiCategories', ['anime', 'book', 'music', 'game']);
if (!is_array($enabledCategories)) {
    $enabledCategories = array_filter(array_map('trim', explode(',', $enabledCategories)));
}
$order = array_filter(array_map('trim', explode(',', fireflyThemeOption('bangumiCategoryOrder', 'anime,book,music,game'))));
$categoryKeys = array_values(array_unique(array_merge($order, $enabledCategories)));
$categoryKeys = array_values(array_filter($categoryKeys, function ($key) use ($enabledCategories, $categoryMap) {
    return in_array($key, $enabledCategories) && isset($categoryMap[$key]);
}));

$bangumiData = [];
$fetchError = '';
$serverRender = fireflyThemeOption('bangumiServerRender', '0') === '1';
if ($username !== '' && $serverRender) {
    foreach ($categoryKeys as $key) {
        $result = fireflyBangumiFetchCategory($username, $key);
        $bangumiData[$key] = $result;
        if (!empty($result['error'])) {
            $fetchError = $result['error'];
            break;
        }
    }
}

$firstActive = '';
foreach ($categoryKeys as $key) {
    if (!empty($bangumiData[$key]['items'])) {
        $firstActive = $key;
        break;
    }
}
if ($firstActive === '' && !empty($categoryKeys)) $firstActive = $categoryKeys[0];

function fireflyBangumiSubjectName($subject)
{
    return $subject['name_cn'] ?? $subject['name'] ?? '未知条目';
}

function fireflyBangumiCover($subject)
{
    return $subject['images']['medium'] ?? $subject['images']['large'] ?? $subject['images']['common'] ?? '';
}

function fireflyBangumiTags($item)
{
    if (!empty($item['tags']) && is_array($item['tags'])) return array_slice($item['tags'], 0, 6);
    $subjectTags = $item['subject']['tags'] ?? [];
    $tags = [];
    foreach ($subjectTags as $tag) {
        if (isset($tag['name'])) $tags[] = $tag['name'];
    }
    return array_slice($tags, 0, 6);
}

function fireflyBangumiFilterLabel($status, $subjectType)
{
    if ($status === 'collect') return fireflyBangumiStatusLabel(2, $subjectType);
    if ($status === 'doing') return fireflyBangumiStatusLabel(3, $subjectType);
    if ($status === 'wish') return fireflyBangumiStatusLabel(1, $subjectType);
    if ($status === 'on_hold') return '搁置';
    if ($status === 'dropped') return '抛弃';
    return '全部';
}
?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section
    class="content-area bangumi-page"
    data-bangumi-page
    data-items-per-page="<?php echo $itemsPerPage; ?>"
    data-bangumi-user="<?php echo htmlspecialchars($username); ?>"
    data-bangumi-api="<?php echo htmlspecialchars($bangumiApi); ?>"
    data-bangumi-proxy="<?php $this->options->siteUrl(); ?>?dreamstory_api=bangumi"
    data-bangumi-categories="<?php echo htmlspecialchars(json_encode(array_map(function ($key) use ($categoryMap) {
        return [
            'id' => $key,
            'name' => $categoryMap[$key]['name'],
            'subjectType' => $categoryMap[$key]['subject_type'],
        ];
    }, $categoryKeys), JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>"
>
    <article class="card-base bangumi-panel">
        <header class="bangumi-head">
            <div class="bangumi-title-mark" aria-hidden="true"><i data-lucide="clapperboard"></i></div>
            <div>
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <p><?php echo htmlspecialchars($subtitle); ?></p>
                <span>数据更新于 <?php echo date('Y-m-d H:i:s'); ?></span>
            </div>
        </header>

        <?php if ($username === ''): ?>
            <div class="bangumi-empty">
                <strong>未配置 Bangumi 用户名</strong>
                <p>请在主题设置的“番组计划”里填写 Bangumi 用户名或用户 ID。</p>
            </div>
        <?php elseif ($fetchError): ?>
            <div class="bangumi-empty">
                <strong>Bangumi 数据拉取失败</strong>
                <p><?php echo htmlspecialchars($fetchError); ?></p>
                <small>正在尝试使用浏览器直接拉取数据...</small>
            </div>
        <?php elseif (empty($categoryKeys)): ?>
            <div class="bangumi-empty">
                <strong>暂无分类</strong>
                <p>请在后台至少启用一个番组分类。</p>
            </div>
        <?php elseif ($username !== '' && empty($bangumiData)): ?>
            <div class="bangumi-empty">
                <strong>正在加载番组数据</strong>
                <p>页面已先行打开，番组数据会在浏览器端异步加载。</p>
            </div>
        <?php else: ?>
            <nav class="bangumi-tabs" aria-label="番组分类">
                <?php foreach ($categoryKeys as $key): ?>
                    <?php
                    $items = $bangumiData[$key]['items'] ?? [];
                    if (empty($items)) continue;
                    ?>
                    <button type="button" data-bangumi-tab="<?php echo htmlspecialchars($key); ?>" class="<?php echo $key === $firstActive ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($categoryMap[$key]['name']); ?>
                        <span><?php echo count($items); ?></span>
                    </button>
                <?php endforeach; ?>
            </nav>

            <?php foreach ($categoryKeys as $key): ?>
                <?php
                $items = $bangumiData[$key]['items'] ?? [];
                if (empty($items)) continue;
                $subjectType = $categoryMap[$key]['subject_type'];
                $statusCounts = [];
                foreach ($items as $item) {
                    $status = fireflyBangumiStatusKey($item['type'] ?? 0);
                    $statusCounts[$status] = ($statusCounts[$status] ?? 0) + 1;
                }
                $filters = ['all' => count($items), 'collect' => $statusCounts['collect'] ?? 0, 'doing' => $statusCounts['doing'] ?? 0, 'wish' => $statusCounts['wish'] ?? 0, 'on_hold' => $statusCounts['on_hold'] ?? 0, 'dropped' => $statusCounts['dropped'] ?? 0];
                ?>
                <section class="bangumi-section <?php echo $key === $firstActive ? 'active' : ''; ?>" data-bangumi-section="<?php echo htmlspecialchars($key); ?>">
                    <div class="bangumi-filters">
                        <?php foreach ($filters as $status => $count): ?>
                            <?php if ($status !== 'all' && $count <= 0) continue; ?>
                            <button type="button" data-bangumi-filter="<?php echo htmlspecialchars($status); ?>" class="<?php echo $status === 'all' ? 'active' : ''; ?>">
                                <?php echo htmlspecialchars(fireflyBangumiFilterLabel($status, $subjectType)); ?>
                                <span><?php echo $count; ?></span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="bangumi-grid">
                        <?php foreach ($items as $index => $item): ?>
                            <?php
                            $subject = $item['subject'] ?? [];
                            $cover = fireflyBangumiCover($subject);
                            $name = fireflyBangumiSubjectName($subject);
                            $status = fireflyBangumiStatusKey($item['type'] ?? 0);
                            $score = $subject['score'] ?? '';
                            $date = $subject['date'] ?? '';
                            $year = $date ? substr($date, 0, 4) : '';
                            $tags = fireflyBangumiTags($item);
                            $visibleTags = array_slice($tags, 0, 3);
                            $hiddenCount = max(0, count($tags) - count($visibleTags));
                            $subjectId = $subject['id'] ?? ($item['subject_id'] ?? '');
                            $url = $subjectId ? 'https://bgm.tv/subject/' . rawurlencode($subjectId) : '#';
                            ?>
                            <a class="bangumi-card <?php echo $index >= $itemsPerPage ? 'paged-hidden' : ''; ?>" href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener noreferrer nofollow" data-bangumi-item data-status="<?php echo htmlspecialchars($status); ?>">
                                <div class="bangumi-cover">
                                    <?php if ($cover): ?>
                                        <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($name); ?>" loading="lazy">
                                    <?php else: ?>
                                        <span class="bangumi-no-cover">BOOK</span>
                                    <?php endif; ?>
                                    <span class="bangumi-status status-<?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars(fireflyBangumiStatusLabel($item['type'] ?? 0, $subjectType)); ?></span>
                                    <?php if ($score): ?><span class="bangumi-score">★ <?php echo htmlspecialchars($score); ?></span><?php endif; ?>
                                    <span class="bangumi-card-mask"></span>
                                    <span class="bangumi-info">
                                        <strong><?php echo htmlspecialchars($name); ?></strong>
                                        <?php if ($year): ?><em><?php echo htmlspecialchars($year); ?></em><?php endif; ?>
                                        <?php if (!empty($item['comment'])): ?><small title="<?php echo htmlspecialchars($item['comment']); ?>"><?php echo htmlspecialchars($item['comment']); ?></small><?php endif; ?>
                                        <?php if (!empty($visibleTags)): ?>
                                            <span class="bangumi-tag-row">
                                                <?php foreach ($visibleTags as $tag): ?><b><?php echo htmlspecialchars($tag); ?></b><?php endforeach; ?>
                                                <?php if ($hiddenCount > 0): ?><b>+<?php echo $hiddenCount; ?></b><?php endif; ?>
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <div class="bangumi-pagination" data-bangumi-pagination>
                        <button type="button" data-page-prev>‹</button>
                        <span data-page-label>1 / 1</span>
                        <button type="button" data-page-next>›</button>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </article>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
