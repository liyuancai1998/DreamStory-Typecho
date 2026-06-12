<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section class="content-area">
    <article class="article-page card-base" itemscope itemtype="http://schema.org/BlogPosting">
        <header class="article-header">
            <h1 itemprop="name headline"><?php $this->title(); ?></h1>
        </header>
        <div class="article-content" itemprop="articleBody">
            <?php ob_start(); $this->content(); echo fireflyRenderColorShortcodes(ob_get_clean()); ?>
        </div>
    </article>
    <?php $this->need('comments.php'); ?>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
