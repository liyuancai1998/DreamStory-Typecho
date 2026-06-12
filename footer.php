<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
</main>

<?php if (fireflyIsChecked('features', 'backTop')): ?>
    <button class="floating-btn back-top" id="back-top" aria-label="返回顶部">&uarr;</button>
<?php endif; ?>

<div class="sakura-layer" id="sakura-layer" aria-hidden="true"></div>

<footer class="site-footer">
    <div>
        &copy; <?php echo date('Y'); ?> <a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a>
        <span>Powered by Typecho &middot; DreamStory 追梦主题</span>
    </div>
</footer>

<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
<script src="<?php $this->options->themeUrl('assets/editor/editor.md/lib/prettify.min.js'); ?>"></script>
<?php $fireflyJsVersion = @filemtime(__DIR__ . '/assets/js/firefly.js') ?: time(); ?>
<script src="<?php $this->options->themeUrl('assets/js/firefly.js'); ?>?v=<?php echo $fireflyJsVersion; ?>"></script>
<script>
if (window.lucide) {
    window.lucide.createIcons();
}
</script>
<?php $this->footer(); ?>
</body>
</html>
