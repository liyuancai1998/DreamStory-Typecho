<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section class="content-area">
    <article class="article-page card-base" itemscope itemtype="http://schema.org/BlogPosting">
        <?php $cover = fireflyPostCover($this); ?>
        <?php if ($cover): ?>
            <div class="article-cover"><img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php $this->title(); ?>"></div>
        <?php endif; ?>
        <header class="article-header">
            <h1 itemprop="name headline"><?php $this->title(); ?></h1>
            <div class="post-meta">
                <span><?php $this->date('Y-m-d'); ?></span>
                <span><?php $this->category(','); ?></span>
                <span><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></span>
            </div>
        </header>
        <div class="article-content" itemprop="articleBody">
            <?php ob_start(); $this->content(); echo fireflyRenderColorShortcodes(ob_get_clean()); ?>
        </div>
        <footer class="article-footer">
            <div class="tags"><?php $this->tags(' ', true, ''); ?></div>
        </footer>
    </article>

    <nav class="post-near card-base">
        <div>上一篇：<?php $this->thePrev('%s', '没有了'); ?></div>
        <div>下一篇：<?php $this->theNext('%s', '没有了'); ?></div>
    </nav>

    <?php $this->need('comments.php'); ?>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
