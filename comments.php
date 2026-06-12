<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<section id="comments" class="comments card-base">
    <?php $this->comments()->to($comments); ?>
    <?php if ($comments->have()): ?>
        <h2><?php $this->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></h2>
        <ol class="comment-list">
            <?php $comments->listComments(); ?>
        </ol>
        <nav class="comment-page-nav"><?php $comments->pageNav(); ?></nav>
    <?php endif; ?>

    <?php if ($this->allow('comment')): ?>
        <div id="<?php $this->respondId(); ?>" class="respond">
            <div class="cancel-comment-reply"><?php $comments->cancelReply(); ?></div>
            <h2 id="response">添加评论</h2>
            <form method="post" action="<?php $this->commentUrl() ?>" id="comment-form">
                <?php if ($this->user->hasLogin()): ?>
                    <p class="logged-in">登录身份：<a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a> · <a href="<?php $this->options->logoutUrl(); ?>">退出</a></p>
                <?php else: ?>
                    <div class="form-grid">
                        <label>称呼<input type="text" name="author" value="<?php $this->remember('author'); ?>" required></label>
                        <label>Email<input type="email" name="mail" value="<?php $this->remember('mail'); ?>"<?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?>></label>
                        <label>网站<input type="url" name="url" placeholder="https://" value="<?php $this->remember('url'); ?>"<?php if ($this->options->commentsRequireUrl): ?> required<?php endif; ?>></label>
                    </div>
                <?php endif; ?>
                <label>内容<textarea rows="6" name="text" required><?php $this->remember('text'); ?></textarea></label>
                <button type="submit" class="submit">提交评论</button>
            </form>
        </div>
    <?php else: ?>
        <h2>评论已关闭</h2>
    <?php endif; ?>
</section>
