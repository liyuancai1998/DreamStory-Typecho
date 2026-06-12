<?php
/**
 * DreamStory 追梦主题 for Typecho.
 *
 * @package DreamStory
 * @author 冥冥冥冥帝酱
 * @version 1.0.0
 * @link https://typecho.org
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');
?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section class="content-area">
    <?php if (!($this->is('index')) && !($this->is('post'))): ?>
        <div class="archive-title card-base"><?php echo htmlspecialchars(fireflyArchiveTitle($this)); ?></div>
    <?php endif; ?>

    <?php if (fireflyIsChecked('features', 'layoutSwitch')): ?>
        <div class="list-toolbar card-base">
            <div class="list-toolbar-title">
                <strong>文章</strong>
                <span>切换浏览方式</span>
            </div>
            <div class="layout-switcher" role="group" aria-label="文章布局">
                <button class="toolbar-btn active" data-layout="list">列表</button>
                <button class="toolbar-btn" data-layout="grid">网格</button>
            </div>
        </div>
    <?php endif; ?>

    <div class="post-list" id="post-list">
        <?php if ($this->have()): ?>
            <?php $i = 0; while ($this->next()): ?>
                <?php fireflyRenderPostCard($this, $i * 70); ?>
            <?php $i++; endwhile; ?>
        <?php else: ?>
            <article class="card-base empty-card">
                <h2>没有找到内容</h2>
                <p>换个关键词再试试吧。</p>
            </article>
        <?php endif; ?>
    </div>

    <nav class="page-nav">
        <?php $this->pageNav('上一页', '下一页'); ?>
    </nav>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
