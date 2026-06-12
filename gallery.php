<?php
/**
 * 相册
 *
 * @package custom
 * @author 冥冥冥冥帝酱
 * @version 1.0.0
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

$galleryTitle = fireflyThemeOption('galleryTitle', '相册');
$galleryDescription = fireflyThemeOption('galleryDescription', '记录生活中的美好瞬间');
$albums = fireflyGalleryAlbums();
$albumId = isset($_GET['album']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['album']) : '';
$currentAlbum = $albumId !== '' ? fireflyGalleryFindAlbum($albumId) : null;
ob_start();
$this->permalink();
$pageUrl = trim(ob_get_clean());
?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section class="content-area gallery-page">
    <?php if ($albumId !== '' && $currentAlbum): ?>
        <?php
        $photos = fireflyGalleryScanPhotos($currentAlbum['id']);
        $cover = fireflyGalleryAlbumCover($currentAlbum, $photos);
        $columnWidth = max(160, min(420, intval(fireflyThemeOption('galleryColumnWidth', '240'))));
        ?>
        <article
            class="card-base gallery-album-detail"
            data-gallery-detail
            data-album-id="<?php echo htmlspecialchars($currentAlbum['id']); ?>"
            data-password="<?php echo htmlspecialchars($currentAlbum['password']); ?>"
        >
            <div class="gallery-detail-hero">
                <?php if ($cover): ?><img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php echo htmlspecialchars($currentAlbum['name']); ?>"><?php endif; ?>
                <div class="gallery-detail-shade"></div>
                <div class="gallery-detail-copy">
                    <a class="gallery-back-link" href="<?php echo htmlspecialchars($pageUrl); ?>">返回相册列表</a>
                    <h1><?php echo htmlspecialchars($currentAlbum['name']); ?></h1>
                    <?php if ($currentAlbum['description']): ?><p><?php echo htmlspecialchars($currentAlbum['description']); ?></p><?php endif; ?>
                    <div class="gallery-meta-row">
                        <?php if ($currentAlbum['date']): ?><span><?php echo htmlspecialchars($currentAlbum['date']); ?></span><?php endif; ?>
                        <?php if ($currentAlbum['location']): ?><span>📍 <?php echo htmlspecialchars($currentAlbum['location']); ?></span><?php endif; ?>
                        <span><?php echo count($photos); ?> 张照片</span>
                    </div>
                    <?php if (!empty($currentAlbum['tags'])): ?>
                        <div class="gallery-tags">
                            <?php foreach ($currentAlbum['tags'] as $tag): ?><span><?php echo htmlspecialchars($tag); ?></span><?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($currentAlbum['password'])): ?>
                <div class="gallery-lock" data-gallery-lock>
                    <strong>此相册需要密码</strong>
                    <?php if ($currentAlbum['passwordHint']): ?><p><?php echo htmlspecialchars($currentAlbum['passwordHint']); ?></p><?php endif; ?>
                    <div class="gallery-password-row">
                        <input type="password" data-gallery-password-input placeholder="输入访问密码">
                        <button type="button" data-gallery-unlock>解锁</button>
                    </div>
                    <span data-gallery-lock-message></span>
                </div>
            <?php endif; ?>

            <div
                class="gallery-masonry<?php echo !empty($currentAlbum['password']) ? ' is-locked' : ''; ?>"
                data-gallery-photos
                style="--gallery-column-width: <?php echo $columnWidth; ?>px;"
            >
                <?php if (!empty($photos)): ?>
                    <?php foreach ($photos as $index => $photo): ?>
                        <a class="gallery-photo-card" href="<?php echo htmlspecialchars($photo['url']); ?>" data-gallery-photo data-index="<?php echo $index; ?>">
                            <img src="<?php echo htmlspecialchars($photo['url']); ?>" alt="<?php echo htmlspecialchars($currentAlbum['name'] . ' ' . ($index + 1)); ?>" loading="lazy">
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="gallery-empty">这个相册还没有图片。请把图片放到 <code>usr/themes/firefly-typecho/gallery/<?php echo htmlspecialchars($currentAlbum['id']); ?>/</code></div>
                <?php endif; ?>
            </div>
        </article>
    <?php elseif ($albumId !== ''): ?>
        <article class="card-base gallery-shell">
            <div class="gallery-empty">没有找到这个相册。</div>
            <a class="small-pill" href="<?php echo htmlspecialchars($pageUrl); ?>">返回相册列表</a>
        </article>
    <?php else: ?>
        <?php
        $albumCards = [];
        $allTags = [];
        foreach ($albums as $album) {
            $photos = fireflyGalleryScanPhotos($album['id']);
            $album['photos'] = $photos;
            $album['photoCount'] = count($photos);
            $album['coverResolved'] = fireflyGalleryAlbumCover($album, $photos);
            $albumCards[] = $album;
            foreach ($album['tags'] as $tag) $allTags[$tag] = true;
        }
        $allTags = array_keys($allTags);
        sort($allTags);
        ?>
        <article class="card-base gallery-shell" data-gallery-list>
            <header class="gallery-head">
                <div class="gallery-head-main">
                    <div class="gallery-title-row">
                        <span class="gallery-title-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24">
                                <rect x="3" y="3" width="18" height="18" rx="2"></rect>
                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                <path d="M21 15l-5-5L5 21"></path>
                                <path d="M14 14l-2.5-2.5L3 20"></path>
                            </svg>
                        </span>
                        <h1><?php echo htmlspecialchars($galleryTitle); ?></h1>
                    </div>
                    <p><?php echo htmlspecialchars($galleryDescription); ?></p>
                </div>
                <span class="gallery-count"><?php echo count($albumCards); ?> 本相册</span>
            </header>

            <?php if (!empty($albumCards)): ?>
                <div class="gallery-search">
                    <span>⌕</span>
                    <input type="search" data-gallery-search placeholder="搜索相册...">
                </div>
                <div class="gallery-filters">
                    <button type="button" class="active" data-gallery-tag="all">全部</button>
                    <?php foreach ($allTags as $tag): ?>
                        <button type="button" data-gallery-tag="<?php echo htmlspecialchars($tag); ?>"><?php echo htmlspecialchars($tag); ?></button>
                    <?php endforeach; ?>
                </div>
                <div class="gallery-grid">
                    <?php foreach ($albumCards as $album): ?>
                        <?php
                        $detailUrl = $pageUrl . (strpos($pageUrl, '?') === false ? '?' : '&') . 'album=' . rawurlencode($album['id']);
                        ?>
                        <a
                            class="gallery-album-card"
                            href="<?php echo htmlspecialchars($detailUrl); ?>"
                            data-tags="<?php echo htmlspecialchars(implode(',', $album['tags'])); ?>"
                            data-search-text="<?php echo htmlspecialchars(strtolower($album['name'] . ' ' . $album['description'] . ' ' . $album['location'] . ' ' . implode(' ', $album['tags']))); ?>"
                        >
                            <div class="gallery-album-cover">
                                <?php if ($album['coverResolved']): ?>
                                    <img src="<?php echo htmlspecialchars($album['coverResolved']); ?>" alt="<?php echo htmlspecialchars($album['name']); ?>" loading="lazy">
                                <?php endif; ?>
                                <span class="gallery-photo-count"><?php echo intval($album['photoCount']); ?> 张照片</span>
                                <?php if ($album['password']): ?><span class="gallery-lock-badge">锁</span><?php endif; ?>
                                <div class="gallery-card-copy">
                                    <h2><?php echo htmlspecialchars($album['name']); ?></h2>
                                    <?php if ($album['description']): ?><p><?php echo htmlspecialchars($album['description']); ?></p><?php endif; ?>
                                    <div class="gallery-meta-row">
                                        <?php if ($album['date']): ?><span><?php echo htmlspecialchars($album['date']); ?></span><?php endif; ?>
                                        <?php if ($album['location']): ?><span>📍 <?php echo htmlspecialchars($album['location']); ?></span><?php endif; ?>
                                    </div>
                                    <?php if (!empty($album['tags'])): ?>
                                        <div class="gallery-tags">
                                            <?php foreach (array_slice($album['tags'], 0, 4) as $tag): ?><span><?php echo htmlspecialchars($tag); ?></span><?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="gallery-empty gallery-no-results" hidden>没有匹配的相册。</div>
            <?php else: ?>
                <div class="gallery-empty">暂无相册。请先在主题设置里添加相册，并把图片放进主题目录的 <code>gallery/相册ID/</code>。</div>
            <?php endif; ?>
        </article>
    <?php endif; ?>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
