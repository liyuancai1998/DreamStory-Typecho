<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
/**
 * 动态时间线
 *
 * @package custom
 */

function fireflyTimelineThemeUrl($file)
{
    ob_start();
    \Typecho\Widget::widget('\Widget\Options')->themeUrl($file);
    return trim(ob_get_clean());
}

function fireflyTimelineLines($text)
{
    return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) $text))));
}

function fireflyTimelineExtractImage($content)
{
    $images = [];
    $content = preg_replace_callback('/^\[\[image:(.+?)\]\]\s*$/mi', function ($match) use (&$images) {
        $images = array_merge($images, fireflyTimelineParseImages($match[1]));
        return '';
    }, $content);

    if (preg_match_all('/!\[[^\]]*\]\((https?:\/\/[^)\s]+)\)/i', $content, $matches)) {
        foreach ($matches[1] as $url) {
            $images[] = trim($url);
        }
        $content = str_replace($matches[0], '', $content);
    }

    return [trim($content), array_values(array_unique(array_filter($images)))];
}

function fireflyTimelineParseImages($text)
{
    return array_values(array_filter(array_map('trim', preg_split('/[\r\n,，]+/u', (string) $text))));
}

function fireflyTimelineUploadImages()
{
    if (empty($_FILES['image_file']) || empty($_FILES['image_file']['tmp_name'])) return [];

    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    $dir = __DIR__ . DIRECTORY_SEPARATOR . 'timeline-images';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    if (!is_dir($dir) || !is_writable($dir)) return [];

    $names = is_array($_FILES['image_file']['name']) ? $_FILES['image_file']['name'] : [$_FILES['image_file']['name']];
    $tmpNames = is_array($_FILES['image_file']['tmp_name']) ? $_FILES['image_file']['tmp_name'] : [$_FILES['image_file']['tmp_name']];
    $urls = [];
    foreach ($tmpNames as $index => $tmpName) {
        if ($tmpName === '' || !is_uploaded_file($tmpName)) continue;
        $ext = strtolower(pathinfo($names[$index] ?? '', PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) continue;
        $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $target = $dir . DIRECTORY_SEPARATOR . $filename;
        if (move_uploaded_file($tmpName, $target)) {
            $urls[] = fireflyTimelineThemeUrl('timeline-images/' . $filename);
        }
    }

    return $urls;
}

function fireflyTimelineItems()
{
    $lines = fireflyTimelineLines(fireflyThemeOption('timelineItems', ''));
    $items = [];
    foreach ($lines as $line) {
        $parts = array_pad(array_map('trim', explode('|', $line, 8)), 8, '');
        if ($parts[2] === '') continue;
        $tags = array_values(array_filter(array_map('trim', preg_split('/[,，]/u', $parts[3]))));
        $images = fireflyTimelineParseImages($parts[7]);
        $items[] = [
            'time' => $parts[0],
            'timestamp' => strtotime($parts[0]) ?: 0,
            'author' => $parts[1] !== '' ? $parts[1] : fireflyThemeOption('profileName', \Typecho\Widget::widget('\Widget\Options')->title),
            'content' => $parts[2],
            'tags' => $tags,
            'device' => $parts[4],
            'likes' => $parts[5] !== '' ? intval($parts[5]) : 0,
            'comments' => $parts[6] !== '' ? intval($parts[6]) : 0,
            'image' => $images[0] ?? '',
            'images' => $images,
            'source' => 'option',
        ];
    }
    return $items;
}

function fireflyTimelineCommentItems($archive)
{
    $archive->comments()->to($comments);
    $items = [];
    while ($comments->next()) {
        ob_start();
        $comments->content();
        $contentRaw = trim(strip_tags(ob_get_clean()));
        [$content, $images] = fireflyTimelineExtractImage($contentRaw);
        if ($content === '' && empty($images)) continue;

        ob_start();
        $comments->author();
        $author = trim(strip_tags(ob_get_clean()));

        $items[] = [
            'time' => date('Y-m-d H:i:s', intval($comments->created)),
            'timestamp' => intval($comments->created),
            'author' => $author !== '' ? $author : fireflyThemeOption('profileName', \Typecho\Widget::widget('\Widget\Options')->title),
            'content' => $content,
            'tags' => [],
            'device' => '',
            'likes' => 0,
            'comments' => 0,
            'image' => $images[0] ?? '',
            'images' => $images,
            'source' => 'comment',
        ];
    }
    return $items;
}

function fireflyTimelineRelative($timeText)
{
    if ($timeText === '') return '';
    $timestamp = strtotime($timeText);
    if (!$timestamp) return $timeText;
    $diff = time() - $timestamp;
    if ($diff < 60) return '刚刚';
    if ($diff < 3600) return floor($diff / 60) . ' 分钟前';
    if ($diff < 86400) return floor($diff / 3600) . ' 小时前';
    if ($diff < 2592000) return floor($diff / 86400) . ' 天前';
    return date('Y-m-d', $timestamp);
}

function fireflyTimelineHandlePost($archive)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['firefly_timeline_action'])) return;
    $user = \Typecho\Widget::widget('\Widget\User');
    if (!$user->hasLogin() || !isset($user->group) || $user->group !== 'administrator') return;

    $text = trim($_POST['text'] ?? '');
    $images = fireflyTimelineParseImages($_POST['image_url'] ?? '');
    $uploadedImages = fireflyTimelineUploadImages();
    if (!empty($uploadedImages)) $images = array_merge($images, $uploadedImages);
    $images = array_values(array_unique(array_filter($images)));
    if ($text === '' && empty($images)) return;
    if (!empty($images)) $text .= "\n[[image:" . implode(',', $images) . "]]";

    $options = \Typecho\Widget::widget('\Widget\Options');
    $db = \Typecho\Db::get();
    $created = time();
    $author = $user->screenName ?: $user->name;
    $mail = $user->mail ?: $options->adminEmail;
    $url = $user->url ?: $options->siteUrl;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? 'DreamStory Timeline';

    $db->query($db->insert('table.comments')->rows([
        'cid' => $archive->cid,
        'created' => $created,
        'author' => $author,
        'authorId' => $user->uid,
        'ownerId' => $archive->authorId,
        'mail' => $mail,
        'url' => $url,
        'ip' => $ip,
        'agent' => $agent,
        'text' => $text,
        'type' => 'comment',
        'status' => 'approved',
        'parent' => 0,
    ]));

    $db->query($db->update('table.contents')->expression('commentsNum', 'commentsNum + 1')->where('cid = ?', $archive->cid));
    $archive->response->redirect($archive->permalink);
}

fireflyTimelineHandlePost($this);
$this->need('header.php');
$timelineTitle = fireflyThemeOption('timelineTitle', '我的动态');
$timelineSubtitle = fireflyThemeOption('timelineSubtitle', '记录生活里的碎片、灵感和正在发生的小事。');
$timelineCover = fireflyThemeOption('timelineCover', '');
if ($timelineCover === '') {
    $timelineCover = fireflyTimelineThemeUrl('default.jpg');
}
$socialLinks = fireflyTimelineLines(fireflyThemeOption('timelineSocialLinks', ''));
$items = array_merge(fireflyTimelineCommentItems($this), fireflyTimelineItems());
usort($items, function ($a, $b) {
    return intval($b['timestamp']) <=> intval($a['timestamp']);
});
$tagMap = [];
foreach ($items as $item) {
    foreach ($item['tags'] as $tag) {
        $tagMap[$tag] = true;
    }
}
$avatar = fireflyThemeOption('profileAvatar', '');
$profileName = fireflyThemeOption('profileName', $this->options->title);
$timelineCanPost = $this->user->hasLogin() && isset($this->user->group) && $this->user->group === 'administrator';
?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section class="content-area timeline-page">
    <article class="timeline-cover card-base" style="background-image: url('<?php echo htmlspecialchars($timelineCover); ?>');">
        <div class="timeline-cover-mask"></div>
        <div class="timeline-cover-main">
            <div>
                <h1><?php echo htmlspecialchars($timelineTitle); ?></h1>
                <?php if ($timelineSubtitle !== ''): ?>
                    <p><?php echo htmlspecialchars($timelineSubtitle); ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($socialLinks)): ?>
                <div class="timeline-socials">
                    <?php foreach ($socialLinks as $line): ?>
                        <?php $parts = array_pad(array_map('trim', explode('|', $line, 2)), 2, ''); ?>
                        <?php if ($parts[0] !== '' && $parts[1] !== ''): ?>
                            <a href="<?php echo htmlspecialchars($parts[1]); ?>" target="_blank" rel="noopener noreferrer nofollow"><?php echo htmlspecialchars($parts[0]); ?></a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="timeline-stats">
            <span><strong><?php echo count($items); ?></strong><em>动态</em></span>
            <span><strong><?php echo count($tagMap); ?></strong><em>标签</em></span>
            <span><strong><?php echo count($socialLinks); ?></strong><em>链接</em></span>
        </div>
    </article>

    <?php if ($timelineCanPost && $this->allow('comment')): ?>
        <section class="timeline-compose card-base">
            <div class="timeline-compose-head">
                <strong>写动态</strong>
                <span>记录一点现在的想法</span>
            </div>
            <form method="post" action="<?php $this->permalink(); ?>" class="timeline-compose-form" enctype="multipart/form-data">
                <input type="hidden" name="firefly_timeline_action" value="post">
                <textarea name="text" rows="4" maxlength="65525" placeholder="今天发生了什么？"></textarea>
                <div class="timeline-compose-image">
                    <textarea name="image_url" rows="2" placeholder="图片 URL，可多行或用逗号分隔"></textarea>
                    <label>
                        <span>选择图片</span>
                        <input type="file" name="image_file[]" accept="image/jpeg,image/png,image/webp,image/gif" data-timeline-image-file multiple>
                    </label>
                    <button type="button" class="timeline-image-clear" data-timeline-image-clear hidden>清除</button>
                    <span class="timeline-image-status" data-timeline-image-status>未选择图片</span>
                </div>
                <div class="timeline-compose-actions">
                    <span>会发布到当前动态页</span>
                    <button type="submit" class="submit">发布动态</button>
                </div>
            </form>
        </section>
    <?php endif; ?>

    <div class="timeline-stream" id="timeline-moments">
        <?php if (!empty($items)): ?>
            <?php foreach ($items as $item): ?>
                <article class="timeline-entry">
                    <div class="timeline-node">
                        <?php if ($avatar): ?>
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($profileName); ?>">
                        <?php else: ?>
                            <span><?php echo htmlspecialchars(function_exists('mb_substr') ? mb_substr($profileName, 0, 1, 'UTF-8') : substr($profileName, 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="timeline-card card-base">
                        <header>
                            <strong><?php echo htmlspecialchars($item['author']); ?></strong>
                            <?php if ($item['time'] !== ''): ?>
                                <time datetime="<?php echo htmlspecialchars(date('c', strtotime($item['time']) ?: time())); ?>"><?php echo htmlspecialchars(fireflyTimelineRelative($item['time'])); ?></time>
                            <?php endif; ?>
                        </header>
                        <div class="timeline-text"><?php echo nl2br(htmlspecialchars($item['content'])); ?></div>
                        <?php $timelineImages = !empty($item['images']) ? $item['images'] : (!empty($item['image']) ? [$item['image']] : []); ?>
                        <?php if (!empty($timelineImages)): ?>
                            <div class="timeline-image-grid image-count-<?php echo min(count($timelineImages), 4); ?>">
                                <?php foreach ($timelineImages as $imageIndex => $image): ?>
                                    <a class="timeline-image" href="<?php echo htmlspecialchars($image); ?>" target="_blank" rel="noopener noreferrer nofollow">
                                        <img src="<?php echo htmlspecialchars($image); ?>" alt="" loading="lazy">
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($item['tags'])): ?>
                            <div class="timeline-tags">
                                <?php foreach ($item['tags'] as $tag): ?><span># <?php echo htmlspecialchars($tag); ?></span><?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <footer>
                            <span>评论 <?php echo intval($item['comments']); ?></span>
                            <span>赞 <?php echo intval($item['likes']); ?></span>
                            <?php if ($item['device'] !== ''): ?><span>发自 <?php echo htmlspecialchars($item['device']); ?></span><?php endif; ?>
                        </footer>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="timeline-empty card-base">
                <strong>还没有动态</strong>
                <p>到主题后台的“动态时间线”里添加内容后，这里就会显示出来。</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
