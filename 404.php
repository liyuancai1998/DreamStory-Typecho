<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('header.php'); ?>

<section class="content-area solo">
    <article class="card-base empty-card">
        <h1>404</h1>
        <p>页面没有找到，可能已经移动或删除。</p>
        <a class="primary-link" href="<?php $this->options->siteUrl(); ?>">返回首页</a>
    </article>
</section>

<?php $this->need('footer.php'); ?>
