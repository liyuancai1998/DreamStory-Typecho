<?php
/**
 * 赞助页面
 *
 * @package custom
 * @author 冥冥冥冥帝酱
 * @version 1.0.0
 */
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$this->need('header.php');

$sponsors = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', fireflyThemeOption('sponsorList', ''))));
$alipayQr = fireflyThemeOption('sponsorAlipayQr', '');
$wechatQr = fireflyThemeOption('sponsorWechatQr', '');
$kofiUrl = fireflyThemeOption('sponsorKofiUrl', '');
$afdianUrl = fireflyThemeOption('sponsorAfdianUrl', '');
$sponsorTitle = fireflyThemeOption('sponsorPageTitle', '赞助支持');
$sponsorDescription = fireflyThemeOption('sponsorPageDescription', '如果我的内容对你有帮助，欢迎通过以下方式赞助我，你的支持是我持续创作的动力！');
$sponsorNotice = fireflyThemeOption('sponsorPageNotice', '您的赞助将用于服务器维护、内容创作和功能开发，帮助我持续提供优质内容。');
$kofiTitle = fireflyThemeOption('sponsorKofiTitle', 'Ko-fi');
$kofiDescription = fireflyThemeOption('sponsorKofiDescription', '');
$afdianTitle = fireflyThemeOption('sponsorAfdianTitle', '爱发电');
$afdianDescription = fireflyThemeOption('sponsorAfdianDescription', '通过 爱发电 进行赞助');
if ($kofiDescription === '') {
    $kofiDescription = 'Buy a Coffee for ' . fireflyThemeOption('profileName', $this->options->title);
}
?>

<?php if (fireflySidebarEnabled('left')) $this->need('sidebar-left.php'); ?>

<section class="content-area sponsor-page">
    <article class="card-base sponsor-hero-card">
        <div class="sponsor-title">
            <span class="sponsor-icon">♥</span>
            <div>
                <h1><?php echo htmlspecialchars($sponsorTitle); ?></h1>
                <p><?php echo nl2br(htmlspecialchars($sponsorDescription)); ?></p>
            </div>
        </div>
        <div class="sponsor-note">
            <span>ⓘ</span>
            <p><?php echo nl2br(htmlspecialchars($sponsorNotice)); ?></p>
        </div>

        <div class="sponsor-methods">
            <section class="sponsor-method">
                <h2>支付宝</h2>
                <p>使用支付宝扫码赞助</p>
                <div class="qr-box">
                    <?php if ($alipayQr): ?>
                        <img src="<?php echo htmlspecialchars($alipayQr); ?>" alt="支付宝赞助二维码">
                    <?php else: ?>
                        <span>请在主题设置填写二维码</span>
                    <?php endif; ?>
                </div>
            </section>

            <section class="sponsor-method">
                <h2>微信</h2>
                <p>使用微信扫码赞助</p>
                <div class="qr-box">
                    <?php if ($wechatQr): ?>
                        <img src="<?php echo htmlspecialchars($wechatQr); ?>" alt="微信赞助二维码">
                    <?php else: ?>
                        <span>请在主题设置填写二维码</span>
                    <?php endif; ?>
                </div>
            </section>

            <?php if ($kofiUrl): ?>
                <section class="sponsor-method sponsor-link-card">
                    <h2><?php echo htmlspecialchars($kofiTitle); ?></h2>
                    <?php if ($kofiDescription): ?><p><?php echo htmlspecialchars($kofiDescription); ?></p><?php endif; ?>
                    <a class="sponsor-button" href="<?php echo htmlspecialchars($kofiUrl); ?>" target="_blank" rel="noopener noreferrer">前往赞助 ↗</a>
                </section>
            <?php endif; ?>

            <?php if ($afdianUrl): ?>
                <section class="sponsor-method sponsor-link-card">
                    <h2><?php echo htmlspecialchars($afdianTitle); ?></h2>
                    <?php if ($afdianDescription): ?><p><?php echo htmlspecialchars($afdianDescription); ?></p><?php endif; ?>
                    <a class="sponsor-button" href="<?php echo htmlspecialchars($afdianUrl); ?>" target="_blank" rel="noopener noreferrer">前往赞助 ↗</a>
                </section>
            <?php endif; ?>
        </div>
    </article>

    <section class="card-base sponsor-list-card">
        <div class="sponsor-list-head">
            <h2><span>♜</span> 赞助列表</h2>
            <strong><?php echo count($sponsors); ?></strong>
        </div>
        <?php if (!empty($sponsors)): ?>
            <div class="sponsor-list">
                <?php foreach ($sponsors as $line): ?>
                    <?php
                    $parts = array_map('trim', explode('|', $line));
                    $name = $parts[0] ?? '匿名用户';
                    $amount = $parts[1] ?? '';
                    $date = $parts[2] ?? '';
                    $avatar = $parts[3] ?? '';
                    $initial = function_exists('mb_substr') ? mb_substr($name, 0, 1, 'UTF-8') : substr($name, 0, 1);
                    ?>
                    <div class="sponsor-person">
                        <div class="sponsor-avatar">
                            <?php if ($avatar): ?>
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="<?php echo htmlspecialchars($name); ?>">
                            <?php else: ?>
                                <span><?php echo htmlspecialchars($initial); ?></span>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($name); ?> <?php if ($amount): ?><em><?php echo htmlspecialchars($amount); ?></em><?php endif; ?></strong>
                            <?php if ($date): ?><span><?php echo htmlspecialchars($date); ?></span><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="sponsor-empty">还没有赞助记录。</p>
        <?php endif; ?>
    </section>
</section>

<?php if (fireflySidebarEnabled('right')) $this->need('sidebar-right.php'); ?>
<?php $this->need('footer.php'); ?>
