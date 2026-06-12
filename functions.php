<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function fireflyThemeOption($name, $default = '')
{
    $options = \Typecho\Widget::widget('\Widget\Options');
    return isset($options->{$name}) && $options->{$name} !== '' ? $options->{$name} : $default;
}

require_once __DIR__ . '/common/editor.php';

function fireflyIntegrityHardStop($reason = '')
{
    if (!headers_sent()) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
    }

    $reason = trim((string) $reason);
    ?>
<!doctype html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>&#20027;&#39064;&#25991;&#20214;&#34987;&#31613;&#25913;</title>
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f6f7fb; color: #172033; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .panel { width: min(560px, calc(100vw - 32px)); padding: 28px; border: 1px solid #fecaca; border-radius: 12px; background: #fff; box-shadow: 0 16px 40px rgba(15, 23, 42, .08); }
    .badge { display: inline-flex; align-items: center; min-height: 26px; padding: 3px 10px; border-radius: 999px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 800; }
    h1 { margin: 16px 0 8px; font-size: 24px; line-height: 1.25; }
    p { margin: 0; color: #64748b; line-height: 1.75; }
    code { display: inline-block; margin-top: 14px; padding: 6px 8px; border-radius: 8px; background: #f1f5f9; color: #0f172a; overflow-wrap: anywhere; }
  </style>
</head>
<body>
  <main class="panel">
    <span class="badge">&#23433;&#20840;&#26657;&#39564;&#22833;&#36133;</span>
    <h1>&#20027;&#39064;&#34987;&#31613;&#25913;&#65292;&#24050;&#20572;&#27490;&#36816;&#34892;</h1>
    <p>&#26816;&#27979;&#21040; DreamStory &#20027;&#39064;&#25991;&#20214;&#34987;&#20462;&#25913;&#25110;&#32570;&#22833;&#65292;&#35831;&#37325;&#26032;&#19978;&#20256;&#23436;&#25972;&#21457;&#24067;&#21253;&#21518;&#20877;&#20351;&#29992;&#12290;</p>
    <?php if ($reason !== ''): ?><code><?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'); ?></code><?php endif; ?>
  </main>
</body>
</html>
    <?php
    exit;
}

$fireflyIntegrityFile = __DIR__ . '/common/integrity.php';
if (is_file($fireflyIntegrityFile)) {
    require_once $fireflyIntegrityFile;
}
require_once __DIR__ . '/common/license.php';
fireflyRegisterEditorHooks();

function themeInit($archive)
{
    fireflyRegisterContentFilters();

    $api = isset($_REQUEST['dreamstory_api']) ? trim((string) $_REQUEST['dreamstory_api']) : '';
    if ($api !== '') {
        if ($api === 'bangumi') {
            fireflyBangumiJsonResponse(fireflyHandleBangumiApi());
        }
        if (function_exists('fireflyHandleLicenseApi')) {
            fireflyLicenseJsonResponse(fireflyHandleLicenseApi($api));
        }
        fireflyLicenseJsonResponse(['code' => -1, 'success' => false, 'message' => '授权模块不可用']);
    }

    $pageSize = intval(fireflyThemeOption('archivePageSize', '10'));
    if ($pageSize <= 0) return;

    $type = isset($archive->parameter->type) ? $archive->parameter->type : '';
    if (in_array($type, ['single', 'page', '404'], true)) return;

    $archive->parameter->pageSize = max(1, min(50, $pageSize));
}

function fireflyRegisterContentFilters()
{
    foreach (['Widget_Abstract_Contents', '\Widget\Base\Contents'] as $handle) {
        $contentsFactory = \Typecho\Plugin::factory($handle);
        $prevContentEx = isset($contentsFactory->contentEx) ? $contentsFactory->contentEx : null;

        $contentsFactory->contentEx = function ($content, $widget) use ($prevContentEx) {
            if ($prevContentEx) {
                $content = call_user_func($prevContentEx, $content, $widget);
            }

            return fireflyRenderColorShortcodes($content);
        };
    }
}

function fireflyIsSafeCssColor($color)
{
    $color = trim(html_entity_decode((string) $color, ENT_QUOTES, 'UTF-8'));
    if ($color === '' || strlen($color) > 64 || preg_match('/[;<>{}]/', $color)) {
        return false;
    }

    if (preg_match('/^#[0-9a-f]{3}(?:[0-9a-f]{1})?(?:[0-9a-f]{2}(?:[0-9a-f]{2})?)?$/i', $color)) {
        return true;
    }

    if (preg_match('/^(?:rgb|rgba|hsl|hsla)\(\s*[-+0-9.,%\s]+\)$/i', $color)) {
        return true;
    }

    return preg_match('/^[a-z][a-z0-9-]*$/i', $color) === 1;
}

function fireflyRenderColorShortcodes($content)
{
    return preg_replace_callback(
        '/\[font\s+color\s*=\s*(?:"|\'|&quot;|&#34;)?([^"\'\]\r\n]+)(?:"|\'|&quot;|&#34;)?\](.*?)\[\/font\]/is',
        function ($matches) {
            $color = trim(html_entity_decode($matches[1], ENT_QUOTES, 'UTF-8'));
            if (!fireflyIsSafeCssColor($color)) {
                return $matches[0];
            }

            return '<span class="firefly-color-text" style="color: ' .
                htmlspecialchars($color, ENT_QUOTES, 'UTF-8') . ';">' .
                $matches[2] . '</span>';
        },
        (string) $content
    );
}

function fireflyProfileLinkIcon($label, $href = '', $icon = '')
{
    $key = strtolower(trim($icon ?: ($label . ' ' . $href)));
    $icons = [
        'github' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.48 2 2 6.58 2 12.26c0 4.52 2.87 8.35 6.84 9.71.5.09.68-.22.68-.49 0-.24-.01-.88-.01-1.73-2.78.62-3.37-1.37-3.37-1.37-.45-1.18-1.11-1.49-1.11-1.49-.91-.64.07-.63.07-.63 1 .07 1.53 1.06 1.53 1.06.89 1.56 2.34 1.11 2.91.85.09-.66.35-1.11.63-1.37-2.22-.26-4.56-1.14-4.56-5.07 0-1.12.39-2.03 1.03-2.75-.1-.26-.45-1.3.1-2.71 0 0 .84-.27 2.75 1.05A9.37 9.37 0 0 1 12 6.98c.85 0 1.7.12 2.5.35 1.9-1.32 2.74-1.05 2.74-1.05.55 1.41.2 2.45.1 2.71.64.72 1.03 1.63 1.03 2.75 0 3.94-2.34 4.81-4.57 5.06.36.32.68.94.68 1.9 0 1.37-.01 2.48-.01 2.82 0 .27.18.59.69.49A10.04 10.04 0 0 0 22 12.26C22 6.58 17.52 2 12 2Z"/></svg>',
        'weibo' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.39 19.74c-3.8.38-7.1-1.27-7.37-3.69-.27-2.43 2.6-4.7 6.4-5.08 3.81-.38 7.11 1.27 7.38 3.7.27 2.42-2.6 4.69-6.41 5.07Zm8.96-7.19c-.33.41-.93.47-1.33.14a.96.96 0 0 1-.14-1.35c.58-.72.78-1.57.54-2.33-.37-1.18-1.7-1.87-3.18-1.65a.95.95 0 0 1-.28-1.89c2.4-.36 4.57.9 5.29 3.07.43 1.35.1 2.81-.9 4.01Zm2.18 1.91a.75.75 0 0 1-1.06-.05.78.78 0 0 1 .05-1.08c1.07-.99 1.51-2.34 1.18-3.62-.51-1.94-2.58-3.09-4.92-2.73a.76.76 0 0 1-.23-1.51c3.12-.48 5.93 1.2 6.64 3.84.47 1.77-.15 3.7-1.66 5.15ZM8.41 13.47c-1.3.14-2.28.89-2.18 1.69.1.8 1.24 1.34 2.54 1.2 1.31-.13 2.29-.89 2.19-1.69-.1-.8-1.24-1.34-2.55-1.2Zm3.68.96c-.46.04-.81.31-.78.6.04.28.44.47.9.43.47-.05.82-.32.78-.6-.03-.29-.43-.48-.9-.43Z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Zm8 8.35L4.8 7H4v.58l8 7.05 8-7.05V7h-.8L12 13.35Z"/></svg>',
        'rss' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.18 17.82a2.18 2.18 0 1 1 0 4.36 2.18 2.18 0 0 1 0-4.36ZM4 10.5c5.25 0 9.5 4.25 9.5 9.5h-3A6.5 6.5 0 0 0 4 13.5v-3Zm0-6.5c8.84 0 16 7.16 16 16h-3A13 13 0 0 0 4 7V4Z"/></svg>',
        'bilibili' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8.2 3.4 11 6.1h2l2.8-2.7 1.3 1.3-1.5 1.4H18a3 3 0 0 1 3 3V17a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V9.1a3 3 0 0 1 3-3h2.4L6.9 4.7 8.2 3.4ZM6 8.1a1 1 0 0 0-1 1V17a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V9.1a1 1 0 0 0-1-1H6Zm2 3.4h2v3H8v-3Zm6 0h2v3h-2v-3Z"/></svg>',
        'qq' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2c3.14 0 5.42 2.7 5.42 6.5 0 1.34.35 2.47.86 3.58.49 1.08 1.12 2.21 1.55 3.73.2.72-.22 1.47-.93 1.66-.5.14-1.04-.03-1.36-.42-.28.86-.79 1.6-1.5 2.16.54.28.89.65.89 1.04 0 .69-2.2 1.25-4.93 1.25s-4.93-.56-4.93-1.25c0-.39.35-.76.89-1.04a5.17 5.17 0 0 1-1.5-2.16c-.32.39-.86.56-1.36.42a1.34 1.34 0 0 1-.93-1.66c.43-1.52 1.06-2.65 1.55-3.73.51-1.11.86-2.24.86-3.58C6.58 4.7 8.86 2 12 2Z"/></svg>',
        'telegram' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.9 4.12 18.7 19.2c-.24 1.06-.86 1.32-1.74.82l-4.8-3.54-2.32 2.23c-.26.26-.47.47-.96.47l.34-4.87 8.86-8c.39-.34-.08-.53-.59-.19L6.54 13.01 1.82 11.53C.79 11.21.77 10.5 2.04 10L20.47 2.9c.86-.31 1.61.2 1.43 1.22Z"/></svg>',
        'x' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14.28 10.16 22.22 1h-1.88l-6.9 7.95L7.93 1H1.58l8.33 12.02L1.58 22.63h1.88l7.28-8.39 5.82 8.39h6.35l-8.63-12.47Zm-2.58 2.97-.84-1.2L4.14 2.4h2.89l5.42 7.69.84 1.2 7.05 10h-2.89l-5.75-8.16Z"/></svg>',
    ];

    foreach ($icons as $name => $svg) {
        if (strpos($key, $name) !== false) {
            return $svg;
        }
    }

    if (strpos($key, '@') !== false || strpos($key, 'mailto:') !== false || strpos($key, 'email') !== false) {
        return $icons['mail'];
    }

    if (strpos($key, 'twitter') !== false) {
        return $icons['x'];
    }

    return '';
}

function fireflyIsChecked($name, $value)
{
    $options = \Typecho\Widget::widget('\Widget\Options');
    return !empty($options->{$name}) && in_array($value, $options->{$name});
}

function fireflySidebarEnabled($side)
{
    $position = fireflyThemeOption('sidebarPosition', 'both');
    return $position === 'both' || $position === $side;
}

function fireflySidebarHas($side, $value)
{
    $optionName = $side === 'right' ? 'rightSidebarBlock' : 'leftSidebarBlock';
    $options = \Typecho\Widget::widget('\Widget\Options');
    if (!empty($options->{$optionName})) {
        return in_array($value, $options->{$optionName});
    }

    $defaults = $side === 'right'
        ? ['stats', 'calendar']
        : ['profile', 'announcement', 'music', 'category', 'tags'];

    return in_array($value, $defaults);
}

function fireflyCountRows($table)
{
    try {
        $db = \Typecho\Db::get();
        $row = $db->fetchRow($db->select(['COUNT(*)' => 'num'])->from($table));
        return isset($row['num']) ? intval($row['num']) : 0;
    } catch (\Throwable $e) {
        return 0;
    }
}

function fireflyPostCount()
{
    try {
        $db = \Typecho\Db::get();
        $row = $db->fetchRow(
            $db->select(['COUNT(*)' => 'num'])
                ->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
        );
        return isset($row['num']) ? intval($row['num']) : 0;
    } catch (\Throwable $e) {
        return 0;
    }
}

function fireflyMetaCount($type)
{
    try {
        $db = \Typecho\Db::get();
        $row = $db->fetchRow(
            $db->select(['COUNT(*)' => 'num'])
                ->from('table.metas')
                ->where('type = ?', $type)
        );
        return isset($row['num']) ? intval($row['num']) : 0;
    } catch (\Throwable $e) {
        return 0;
    }
}

function fireflyWordCount()
{
    try {
        $db = \Typecho\Db::get();
        $rows = $db->fetchAll(
            $db->select('text')
                ->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
        );
        $count = 0;
        foreach ($rows as $row) {
            $text = strip_tags($row['text']);
            $count += function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        }
        return $count;
    } catch (\Throwable $e) {
        return 0;
    }
}

function fireflyCustomNavItems()
{
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', fireflyThemeOption('customNavItems', '')))));
    $items = [];
    foreach ($lines as $line) {
        $parts = array_pad(array_map('trim', explode('|', $line, 3)), 3, '');
        if ($parts[0] === '') continue;
        $items[] = [
            'label' => $parts[0],
            'url' => $parts[1],
            'parent' => $parts[2],
        ];
    }
    return $items;
}

function fireflyNormalizeNavUrl($url)
{
    $url = trim((string) $url);
    if ($url === '' || $url === '#') {
        return 'javascript:void(0)';
    }

    if (preg_match('/^(?:https?:)?\/\//i', $url) || preg_match('/^(?:mailto|tel):/i', $url)) {
        return $url;
    }

    if (preg_match('/^[a-z][a-z0-9.-]*\.[a-z]{2,}(?:[\/?#].*)?$/i', $url)) {
        return 'https://' . $url;
    }

    return $url;
}

function fireflyNavLinkAttrs($url)
{
    $attrs = 'href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
    if (preg_match('/^(?:https?:)?\/\//i', $url)) {
        $attrs .= ' target="_blank" rel="noopener noreferrer"';
    }

    return $attrs;
}

function fireflyRenderCustomNav($mobile = false)
{
    $items = fireflyCustomNavItems();
    if (empty($items)) return;

    $children = [];
    foreach ($items as $item) {
        if ($item['parent'] !== '') {
            if (!isset($children[$item['parent']])) $children[$item['parent']] = [];
            $children[$item['parent']][] = $item;
        }
    }

    foreach ($items as $item) {
        if ($item['parent'] !== '') continue;
        $hasChildren = !empty($children[$item['label']]);
        $label = htmlspecialchars($item['label']);
        $url = fireflyNormalizeNavUrl($item['url']);
        if ($mobile) {
            if ($hasChildren) {
                echo '<span class="mobile-menu-label">' . $label . '</span>';
                foreach ($children[$item['label']] as $child) {
                    $childUrl = fireflyNormalizeNavUrl($child['url']);
                    echo '<a class="mobile-sub-link" ' . fireflyNavLinkAttrs($childUrl) . '>' . htmlspecialchars($child['label']) . '</a>';
                }
            } else {
                echo '<a ' . fireflyNavLinkAttrs($url) . '>' . $label . '</a>';
            }
            continue;
        }

        if ($hasChildren) {
            echo '<span class="nav-dropdown">';
            if ($url !== 'javascript:void(0)') {
                echo '<a class="nav-dropdown-trigger" ' . fireflyNavLinkAttrs($url) . '>' . $label . '<b>⌄</b></a>';
            } else {
                echo '<button type="button" class="nav-dropdown-trigger">' . $label . '<b>⌄</b></button>';
            }
            echo '<span class="nav-dropdown-menu">';
            foreach ($children[$item['label']] as $child) {
                $childUrl = fireflyNormalizeNavUrl($child['url']);
                echo '<a ' . fireflyNavLinkAttrs($childUrl) . '>' . htmlspecialchars($child['label']) . '</a>';
            }
            echo '</span></span>';
        } else {
            echo '<a ' . fireflyNavLinkAttrs($url) . '>' . $label . '</a>';
        }
    }
}

function fireflyCollectCategories()
{
    $items = [];
    \Widget\Metas\Category\Rows::alloc()->to($categories);
    while ($categories->next()) {
        ob_start();
        $categories->permalink();
        $url = trim(ob_get_clean());
        $mid = isset($categories->mid) ? intval($categories->mid) : 0;
        $parent = isset($categories->parent) ? intval($categories->parent) : 0;
        $items[] = [
            'mid' => $mid,
            'parent' => $parent,
            'name' => $categories->name,
            'slug' => $categories->slug,
            'url' => $url,
        ];
    }
    return $items;
}

function fireflyCategoryIsCurrent($archive, $category, $children)
{
    if ($archive->is('category', $category['slug'])) return true;
    if (empty($children[$category['mid']])) return false;
    foreach ($children[$category['mid']] as $child) {
        if (fireflyCategoryIsCurrent($archive, $child, $children)) return true;
    }
    return false;
}

function fireflyRenderCategoryNav($archive, $mobile = false)
{
    $categories = fireflyCollectCategories();
    if (empty($categories)) return;

    $byMid = [];
    foreach ($categories as $category) {
        $byMid[$category['mid']] = $category;
    }

    $roots = [];
    $children = [];
    foreach ($categories as $category) {
        if ($category['parent'] > 0 && isset($byMid[$category['parent']])) {
            if (!isset($children[$category['parent']])) $children[$category['parent']] = [];
            $children[$category['parent']][] = $category;
        } else {
            $roots[] = $category;
        }
    }

    foreach ($roots as $category) {
        $hasChildren = !empty($children[$category['mid']]);
        $current = fireflyCategoryIsCurrent($archive, $category, $children);
        $name = htmlspecialchars($category['name']);
        $url = htmlspecialchars($category['url']);

        if ($mobile) {
            echo '<a class="' . ($current ? 'current ' : '') . ($hasChildren ? 'mobile-menu-label' : '') . '" href="' . $url . '">' . $name . '</a>';
            if ($hasChildren) {
                foreach ($children[$category['mid']] as $child) {
                    $childCurrent = fireflyCategoryIsCurrent($archive, $child, $children);
                    echo '<a class="' . ($childCurrent ? 'current ' : '') . 'mobile-sub-link" href="' . htmlspecialchars($child['url']) . '">' . htmlspecialchars($child['name']) . '</a>';
                }
            }
            continue;
        }

        if ($hasChildren) {
            echo '<span class="nav-dropdown">';
            echo '<a class="nav-dropdown-trigger' . ($current ? ' current' : '') . '" href="' . $url . '">' . $name . '<b>⌄</b></a>';
            echo '<span class="nav-dropdown-menu">';
            foreach ($children[$category['mid']] as $child) {
                $childCurrent = fireflyCategoryIsCurrent($archive, $child, $children);
                echo '<a' . ($childCurrent ? ' class="current"' : '') . ' href="' . htmlspecialchars($child['url']) . '">' . htmlspecialchars($child['name']) . '</a>';
            }
            echo '</span></span>';
        } else {
            echo '<a' . ($current ? ' class="current"' : '') . ' href="' . $url . '">' . $name . '</a>';
        }
    }
}

function fireflyExcerpt($archive, $length = 120)
{
    ob_start();
    $archive->excerpt($length * 2, '');
    $text = strip_tags(ob_get_clean());
    $text = preg_replace('/\s+/u', ' ', $text);
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $length) {
        return mb_substr($text, 0, $length, 'UTF-8') . '...';
    }
    if (strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

function fireflyPostCover($archive)
{
    if (isset($archive->fields->cover) && $archive->fields->cover) {
        return $archive->fields->cover;
    }

    $content = $archive->content;
    if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
        return $matches[1];
    }

    return fireflyThemeOption('defaultCover', '');
}

function fireflyPostTagLinks($archive)
{
    ob_start();
    $archive->tags('</span><span>', true, '');
    $tags = trim(ob_get_clean());
    if ($tags === '') return '';
    return '<span>' . $tags . '</span>';
}

function fireflyArchiveTitle($archive)
{
    ob_start();
    $archive->archiveTitle([
        'category' => '分类 %s 下的文章',
        'search' => '包含关键字 %s 的文章',
        'tag' => '标签 %s 下的文章',
        'author' => '%s 发布的文章',
        'date' => '%s 发布的文章',
    ], '', '');
    return trim(ob_get_clean());
}

function fireflyRenderPostCard($archive, $delay = 0)
{
    $cover = fireflyPostCover($archive);
    ?>
    <article class="post-card card-base onload" style="--delay: <?php echo intval($delay); ?>ms">
        <?php if ($cover): ?>
            <a class="post-card-cover" href="<?php $archive->permalink(); ?>" aria-label="<?php $archive->title(); ?>">
                <img src="<?php echo htmlspecialchars($cover); ?>" alt="<?php $archive->title(); ?>" loading="lazy">
                <span class="cover-mask"></span>
            </a>
        <?php endif; ?>
        <div class="post-card-body <?php echo $cover ? '' : 'no-cover'; ?>">
            <a class="post-card-title" href="<?php $archive->permalink(); ?>"><?php $archive->title(); ?></a>
            <div class="post-meta">
                <span class="post-meta-item post-date"><i data-lucide="calendar-days"></i><?php $archive->date('Y-m-d'); ?></span>
                <span class="post-meta-item post-category"><i data-lucide="folder"></i><?php $archive->category(','); ?></span>
                <a class="post-meta-item post-comments" href="<?php $archive->permalink(); ?>#comments"><i data-lucide="message-circle"></i><?php $archive->commentsNum('暂无评论', '1 条评论', '%d 条评论'); ?></a>
            </div>
            <p class="post-excerpt"><?php echo htmlspecialchars(fireflyExcerpt($archive, 110)); ?></p>
            <div class="post-card-footer">
                <span class="post-tags"><?php echo fireflyPostTagLinks($archive); ?></span>
                <a class="enter-link" href="<?php $archive->permalink(); ?>" aria-label="阅读全文">›</a>
            </div>
        </div>
    </article>
    <?php
}

function fireflyBangumiCategories()
{
    return [
        'anime' => ['name' => '动画', 'subject_type' => 2],
        'book' => ['name' => '书籍', 'subject_type' => 1],
        'music' => ['name' => '音乐', 'subject_type' => 3],
        'game' => ['name' => '游戏', 'subject_type' => 4],
        'real' => ['name' => '三次元', 'subject_type' => 6],
    ];
}

function fireflyBangumiStatusKey($type)
{
    $map = [1 => 'wish', 2 => 'collect', 3 => 'doing', 4 => 'on_hold', 5 => 'dropped'];
    return $map[intval($type)] ?? 'unknown';
}

function fireflyBangumiStatusLabel($collectionType, $subjectType)
{
    $collectionType = intval($collectionType);
    $subjectType = intval($subjectType);
    if ($collectionType === 1) {
        if ($subjectType === 1) return '想读';
        if ($subjectType === 3) return '想听';
        if ($subjectType === 4) return '想玩';
        return '想看';
    }
    if ($collectionType === 2) {
        if ($subjectType === 1) return '读过';
        if ($subjectType === 3) return '听过';
        if ($subjectType === 4) return '玩过';
        return '看过';
    }
    if ($collectionType === 3) {
        if ($subjectType === 1) return '在读';
        if ($subjectType === 3) return '在听';
        if ($subjectType === 4) return '在玩';
        return '在看';
    }
    if ($collectionType === 4) return '搁置';
    if ($collectionType === 5) return '抛弃';
    return '未知';
}

function fireflyBangumiFetchJson($url)
{
    $headers = [
        'User-Agent: DreamStory Theme',
        'Accept: application/json',
    ];

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 8);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $body = curl_exec($ch);
        $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        curl_close($ch);
        if ($body !== false && $status >= 200 && $status < 300) {
            return json_decode($body, true);
        }
        return null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 15,
            'header' => implode("\r\n", $headers),
        ],
    ]);
    $body = @file_get_contents($url, false, $context);
    return $body ? json_decode($body, true) : null;
}

function fireflyBangumiCachePath($key)
{
    return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'dreamstory-' . md5($key) . '.json';
}

function fireflyBangumiFetchCategory($username, $categoryKey)
{
    $categories = fireflyBangumiCategories();
    if (empty($categories[$categoryKey])) return ['items' => [], 'error' => '未知分类'];

    $api = preg_replace('#/v0/?$#', '', rtrim(fireflyThemeOption('bangumiApi', 'https://api.bgm.tv'), '/'));
    $cacheMinutes = max(0, intval(fireflyThemeOption('bangumiCacheMinutes', '360')));
    $limit = max(10, min(50, intval(fireflyThemeOption('bangumiPageLimit', '50'))));
    $maxTotal = max(0, intval(fireflyThemeOption('bangumiMaxTotal', '300')));
    $subjectType = $categories[$categoryKey]['subject_type'];
    $cacheKey = $api . '|' . $username . '|' . $subjectType . '|' . $limit . '|' . $maxTotal;
    $cachePath = fireflyBangumiCachePath($cacheKey);

    if ($cacheMinutes > 0 && is_file($cachePath) && (time() - filemtime($cachePath)) < $cacheMinutes * 60) {
        $cached = json_decode(@file_get_contents($cachePath), true);
        if (is_array($cached)) return $cached;
    }

    $items = [];
    $offset = 0;
    do {
        if ($maxTotal > 0 && count($items) >= $maxTotal) break;
        $url = $api . '/v0/users/' . rawurlencode($username) . '/collections?subject_type=' . $subjectType . '&limit=' . $limit . '&offset=' . $offset;
        $json = fireflyBangumiFetchJson($url);
        if (!is_array($json)) return ['items' => $items, 'error' => 'Bangumi API 请求失败'];
        $batch = isset($json['data']) && is_array($json['data']) ? $json['data'] : [];
        $items = array_merge($items, $batch);
        $offset += $limit;
        $hasMore = count($batch) >= $limit;
        if ($hasMore) usleep(50000);
    } while ($hasMore);

    if ($maxTotal > 0 && count($items) > $maxTotal) {
        $items = array_slice($items, 0, $maxTotal);
    }

    $result = ['items' => $items, 'error' => '', 'updated' => date('Y-m-d H:i:s')];
    if ($cacheMinutes > 0) @file_put_contents($cachePath, json_encode($result, JSON_UNESCAPED_UNICODE));
    return $result;
}

function fireflyBangumiReadCategoryCache($username, $categoryKey)
{
    $categories = fireflyBangumiCategories();
    if (empty($categories[$categoryKey])) return null;

    $api = preg_replace('#/v0/?$#', '', rtrim(fireflyThemeOption('bangumiApi', 'https://api.bgm.tv'), '/'));
    $limit = max(10, min(50, intval(fireflyThemeOption('bangumiPageLimit', '50'))));
    $maxTotal = max(0, intval(fireflyThemeOption('bangumiMaxTotal', '300')));
    $subjectType = $categories[$categoryKey]['subject_type'];
    $cacheKey = $api . '|' . $username . '|' . $subjectType . '|' . $limit . '|' . $maxTotal;
    $cachePath = fireflyBangumiCachePath($cacheKey);

    if (!is_file($cachePath)) return null;
    $cached = json_decode(@file_get_contents($cachePath), true);
    return is_array($cached) ? $cached : null;
}

function fireflyBangumiJsonResponse(array $payload)
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function fireflyHandleBangumiApi()
{
    $configuredUser = trim((string) fireflyThemeOption('bangumiUserId', ''));
    $username = isset($_GET['user']) ? trim((string) $_GET['user']) : $configuredUser;
    $category = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
    $categories = fireflyBangumiCategories();

    if ($configuredUser === '' || $username === '' || $username !== $configuredUser) {
        return ['success' => false, 'message' => 'Bangumi 用户未配置或不匹配'];
    }
    if ($category === '' || empty($categories[$category])) {
        return ['success' => false, 'message' => 'Bangumi 分类无效'];
    }

    $result = fireflyBangumiReadCategoryCache($username, $category);
    if (!is_array($result)) {
        return ['success' => false, 'message' => '暂无服务器缓存'];
    }

    if (!empty($result['error'])) {
        return ['success' => false, 'message' => $result['error'], 'items' => $result['items'] ?? []];
    }

    return [
        'success' => true,
        'category' => $category,
        'items' => $result['items'] ?? [],
        'updated' => $result['updated'] ?? date('Y-m-d H:i:s'),
    ];
}

function fireflyGalleryDefaultAlbums()
{
    return "firefly-demo|可爱流萤|飞萤之火自无梦的长夜亮起，绽放在终竟的明天。|崩坏：星穹铁道|2026-01-01|崩坏星穹铁道,流萤|||\n"
        . "encrypted-demo|加密相册示例|这是一个加密相册的示例，设置了访问密码，只有输入正确的密码才能查看相册内容。|崩坏：星穹铁道|2026-02-01|加密相册,示例|123456|示例密码123456|";
}

function fireflyGalleryAlbums()
{
    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', fireflyThemeOption('galleryAlbums', fireflyGalleryDefaultAlbums())))));
    $albums = [];
    foreach ($lines as $line) {
        $parts = array_pad(array_map('trim', explode('|', $line, 9)), 9, '');
        if ($parts[0] === '' || $parts[1] === '') continue;
        $id = preg_replace('/[^a-zA-Z0-9_-]/', '-', $parts[0]);
        $albums[] = [
            'id' => $id,
            'name' => $parts[1],
            'description' => $parts[2],
            'location' => $parts[3],
            'date' => $parts[4],
            'tags' => array_values(array_filter(array_map('trim', explode(',', $parts[5])))),
            'password' => $parts[6],
            'passwordHint' => $parts[7],
            'cover' => $parts[8],
        ];
    }

    usort($albums, function ($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    return $albums;
}

function fireflyGalleryImageExtensions()
{
    return ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif'];
}

function fireflyGalleryBasePath()
{
    return __DIR__ . DIRECTORY_SEPARATOR . 'gallery';
}

function fireflyGalleryAssetUrl($path)
{
    ob_start();
    \Typecho\Widget::widget('\Widget\Options')->themeUrl($path);
    return trim(ob_get_clean());
}

function fireflyGalleryScanPhotos($albumId)
{
    $dir = fireflyGalleryBasePath() . DIRECTORY_SEPARATOR . $albumId;
    if (!is_dir($dir)) return [];
    $photos = [];
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        if (!is_file($path)) continue;
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (!in_array($ext, fireflyGalleryImageExtensions())) continue;
        $photos[] = [
            'file' => $file,
            'url' => fireflyGalleryAssetUrl('gallery/' . rawurlencode($albumId) . '/' . rawurlencode($file)),
            'isCover' => preg_match('/^cover\./i', $file) === 1,
        ];
    }
    usort($photos, function ($a, $b) {
        if ($a['isCover'] !== $b['isCover']) return $a['isCover'] ? -1 : 1;
        return strnatcasecmp($a['file'], $b['file']);
    });
    return $photos;
}

function fireflyGalleryAlbumCover($album, $photos)
{
    if (!empty($album['cover'])) return $album['cover'];
    if (empty($photos)) return '';
    foreach ($photos as $photo) {
        if (!empty($photo['isCover'])) return $photo['url'];
    }
    return $photos[0]['url'];
}

function fireflyGalleryFindAlbum($albumId)
{
    foreach (fireflyGalleryAlbums() as $album) {
        if ($album['id'] === $albumId) return $album;
    }
    return null;
}

function themeConfig($form)
{
    fireflyThemeConfigLayout($form);

    $licenseKey = function_exists('fireflyGetStoredLicenseKey') ? fireflyGetStoredLicenseKey() : '';
    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Hidden(
        'dreamstoryLicenseKey',
        null,
        $licenseKey
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'logoText',
        null,
        null,
        '导航标题',
        '留空时使用站点标题。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'logoUrl',
        null,
        null,
        'Logo 图片地址',
        '可填写图片 URL，留空时显示图标和文字。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'archivePageSize',
        null,
        '10',
        '每页文章数',
        '首页、分类、标签、搜索等文章列表每页显示的文章数量。填 0 则使用 Typecho 全局阅读设置。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Radio(
        'enableMarkdownEditor',
        ['1' => '启用', '0' => '关闭'],
        '1',
        'Markdown 编辑器',
        '启用后后台文章/页面编辑页会使用 editor.md 编辑器。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'markdownEditorHeight',
        null,
        '640',
        'Markdown 编辑器高度',
        '单位 px，最低 360。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Radio(
        'markdownEditorCodeTheme',
        ['default' => '默认', 'monokai' => 'Monokai', 'ambiance' => 'Ambiance', 'twilight' => 'Twilight', 'pastel-on-dark' => 'Pastel Dark'],
        'default',
        'Markdown 代码主题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Radio(
        'navbarStyle',
        ['glass' => '毛玻璃', 'solid' => '实色卡片', 'clear' => '透明'],
        'glass',
        '顶部导航样式'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Radio(
        'navbarWidth',
        ['boxed' => '居中宽度', 'full' => '通栏宽度'],
        'boxed',
        '顶部导航宽度'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'navbarItems',
        [
            'pages' => '同时显示独立页面',
        ],
        [],
        '顶部导航内容',
        '默认显示首页和分类。勾选后会在分类后追加独立页面。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'customNavItems',
        null,
        '',
        '自定义导航菜单',
        '每行一个菜单项，格式：名称|链接|父级。链接留空或填写 # 表示只作为父级菜单不跳转；链接支持站内地址 /gallery.html、完整外链 https://example.com、简写域名 example.com、邮箱 mailto:name@example.com、电话 tel:13800000000。第三列填写父级菜单名称时会作为二级菜单。示例：我的|| 换行 相册|/gallery.html|我的 换行 DreamStory|https://dreamstory.cn|链接'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'homeHeroHeight',
        null,
        '58',
        '首页顶部高度',
        '填写 vh 数值，例如 58。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'innerHeroHeight',
        null,
        '36',
        '内页顶部高度',
        '填写 vh 数值，例如 36。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'heroOverlayOpacity',
        null,
        '0.20',
        '顶部遮罩强度',
        '0 到 1，数字越大图片/渐变越暗。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'heroImages',
        null,
        '',
        '首页/横幅背景图',
        '每行一个图片 URL。为空时使用主题自带渐变背景。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'heroTitle',
        null,
        '',
        '首页大标题',
        '显示在首页背景图上，留空时使用站点标题。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'heroSubtitle',
        null,
        '',
        '首页副标题',
        '显示在首页背景图上，留空时使用站点描述。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'defaultCover',
        null,
        '',
        '默认文章封面',
        '文章没有自定义字段 cover 且正文没有图片时使用。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Radio(
        'sidebarPosition',
        ['left' => '只显示左侧栏', 'right' => '只显示右侧栏', 'both' => '左右都显示', 'none' => '不显示'],
        'both',
        '侧边栏布局'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'leftSidebarBlock',
        [
            'profile' => '个人资料卡',
            'announcement' => '公告',
            'music' => '音乐卡片',
            'category' => '分类',
            'tags' => '标签',
        ],
        ['profile', 'announcement', 'music', 'category', 'tags'],
        '左侧栏模块'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'rightSidebarBlock',
        [
            'stats' => '站点统计',
            'calendar' => '日历',
            'recentPosts' => '最新文章',
            'recentComments' => '最新评论',
            'archive' => '归档',
            'other' => '其它链接',
        ],
        ['stats', 'calendar'],
        '右侧栏模块'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'profileAvatar',
        null,
        '',
        '资料卡头像',
        '填写图片 URL，留空时使用站点标题首字。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'profileName',
        null,
        '',
        '资料卡名称',
        '留空时使用站点标题。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'profileBio',
        null,
        '',
        '资料卡简介',
        '留空时使用站点描述。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'profileLinks',
        null,
        '',
        '资料卡按钮',
        '每行一个，格式：文字|链接。例如：GitHub|https://github.com'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'announcementTitle',
        null,
        '公告',
        '公告标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'announcementText',
        null,
        '欢迎来到我的博客！这是一则示例公告。',
        '公告内容'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Select(
        'musicServer',
        [
            'netease' => '网易云音乐',
            'tencent' => 'QQ 音乐',
            'kugou' => '酷狗音乐',
            'baidu' => '百度音乐',
        ],
        'netease',
        '音乐平台'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Select(
        'musicType',
        [
            'playlist' => '歌单',
            'song' => '单曲',
            'album' => '专辑',
            'artist' => '歌手',
            'search' => '搜索关键词',
        ],
        'playlist',
        '音乐类型'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'musicId',
        null,
        '10046455237',
        '歌单/歌曲 ID 或链接',
        '可填网易云歌单 ID，也可直接粘贴歌单链接，主题会自动提取 id。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'musicApi',
        null,
        'https://api.i-meto.com/meting/api?server=:server&type=:type&id=:id&r=:r',
        'Meting API 地址',
        '支持 :server、:type、:id、:r 占位符。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'musicFallbackApis',
        null,
        "https://api.injahow.cn/meting/?server=:server&type=:type&id=:id\nhttps://api.moeyao.cn/meting/?server=:server&type=:type&id=:id",
        '备用 Meting API',
        '每行一个 API，主 API 失败时依次尝试。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorPageTitle',
        null,
        '赞助支持',
        '赞助页标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'sponsorPageDescription',
        null,
        '如果我的内容对你有帮助，欢迎通过以下方式赞助我，你的支持是我持续创作的动力！',
        '赞助页说明'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'sponsorPageNotice',
        null,
        '您的赞助将用于服务器维护、内容创作和功能开发，帮助我持续提供优质内容。',
        '赞助页提示文字'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorAlipayQr',
        null,
        '',
        '支付宝赞助二维码',
        '填写支付宝收款码图片 URL。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorWechatQr',
        null,
        '',
        '微信赞助二维码',
        '填写微信收款码图片 URL。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorKofiUrl',
        null,
        '',
        'Ko-fi 赞助链接'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorKofiTitle',
        null,
        'Ko-fi',
        'Ko-fi 卡片标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorKofiDescription',
        null,
        '',
        'Ko-fi 卡片说明',
        '留空时使用 Buy a Coffee for 站点名。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorAfdianUrl',
        null,
        '',
        '爱发电赞助链接'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorAfdianTitle',
        null,
        '爱发电',
        '爱发电卡片标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'sponsorAfdianDescription',
        null,
        '通过 爱发电 进行赞助',
        '爱发电卡片说明'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'sponsorList',
        null,
        '',
        '赞助名单',
        '每行一位，格式：昵称|金额|日期|头像URL。头像可留空。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiUserId',
        null,
        '',
        'Bangumi 用户名',
        '填写 Bangumi 用户名或用户 ID。为空时番组页面会显示配置提示。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiTitle',
        null,
        '番组计划',
        '番组页面标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiSubtitle',
        null,
        '记录我的二次元之旅',
        '番组页面副标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'bangumiCategories',
        [
            'anime' => '动画',
            'book' => '书籍',
            'music' => '音乐',
            'game' => '游戏',
            'real' => '三次元',
        ],
        ['anime', 'book', 'music', 'game'],
        '番组分类'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiCategoryOrder',
        null,
        'anime,book,music,game',
        '番组分类排序',
        '用英文逗号分隔，例如 anime,book,music,game,real。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiItemsPerPage',
        null,
        '12',
        '番组每页数量'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiApi',
        null,
        'https://api.bgm.tv',
        'Bangumi API 地址',
        '推荐填写 https://api.bgm.tv。填 https://api.bgm.tv/v0 也会自动兼容。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Radio(
        'bangumiServerRender',
        ['1' => '开启', '0' => '关闭'],
        '0',
        '服务端预加载番组数据',
        '服务器能稳定访问 Bangumi API 时再开启。关闭时页面先打开，再由浏览器异步加载，避免拖慢整站。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiCacheMinutes',
        null,
        '360',
        '番组缓存分钟数',
        '默认 360 分钟。填 0 表示不缓存。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiMaxTotal',
        null,
        '300',
        '单分类最多拉取条数',
        '防止页面请求太慢，0 表示不限制。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'bangumiPageLimit',
        null,
        '50',
        'API 每次请求数量',
        'Bangumi API 建议不超过 50。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'galleryTitle',
        null,
        '相册',
        '相册页面标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'galleryDescription',
        null,
        '记录生活中的美好瞬间',
        '相册页面描述'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'galleryColumnWidth',
        null,
        '240',
        '相册详情瀑布流列宽',
        '填写像素数字，越小列数越多。默认 240。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'galleryAlbums',
        null,
        fireflyGalleryDefaultAlbums(),
        '相册列表',
        '每行一个相册，格式：ID|名称|描述|地点|日期|标签逗号分隔|密码|密码提示|封面URL。图片放到主题目录 gallery/ID/ 下。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'timelineTitle',
        null,
        '我的动态',
        '动态页标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'timelineSubtitle',
        null,
        '记录生活里的碎片、灵感和正在发生的小事。',
        '动态页副标题'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'timelineCover',
        null,
        '',
        '动态页顶部封面',
        '填写图片 URL；留空时使用主题根目录 default.jpg。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'timelineSocialLinks',
        null,
        "GitHub|https://github.com\n微博|https://weibo.com",
        '动态页社交按钮',
        '每行一个，格式：名称|链接。留空则不显示社交按钮。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'timelineItems',
        null,
        "2026-06-04 15:30|友人C|把博客调成了新的动态时间线，像把日常碎片收进一个玻璃小盒子。|博客,日常|Windows|2|0|\n2026-06-03 22:10|友人C|今晚适合听歌、写字、顺便整理一点还没发布的想法。|随笔,音乐|Mobile|5|1|",
        '动态内容',
        '每行一条动态，格式：时间|作者|内容|标签逗号分隔|来源设备|点赞数|评论数|图片URL。图片 URL 可留空。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Checkbox(
        'features',
        [
            'darkSwitch' => '暗色模式切换',
            'layoutSwitch' => '文章列表布局切换',
            'backTop' => '返回顶部按钮',
            'sakura' => '樱花飘落效果',
        ],
        ['darkSwitch', 'layoutSwitch', 'backTop'],
        '前端功能'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Select(
        'defaultThemeMode',
        ['system' => '跟随系统', 'light' => '浅色', 'dark' => '深色'],
        'system',
        '默认主题模式'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Text(
        'themeHue',
        null,
        '210',
        '主题色 Hue',
        '填写 0-360 的数字，例如 210。'
    ));

    $form->addInput(new \Typecho\Widget\Helper\Form\Element\Textarea(
        'customCss',
        null,
        '',
        '自定义 CSS',
        '会直接输出到页面 head。'
    ));
}

function fireflyThemeConfigLayout($form)
{
    $licensePanel = function_exists('fireflyRenderLicensePanel') ? fireflyRenderLicensePanel() : '';
    $html = <<<'HTML'
<div class="firefly-config-shell" id="firefly-config-shell">
  <aside class="firefly-config-nav">
    <div class="firefly-config-logo"><strong>DreamStory</strong><span>追梦主题</span></div>
    <button type="button" data-group="basic" class="active">基础设置</button>
    <button type="button" data-group="navbar">导航顶部</button>
    <button type="button" data-group="hero">壁纸横幅</button>
    <button type="button" data-group="sidebar">侧栏组件</button>
    <button type="button" data-group="profile">资料公告</button>
    <button type="button" data-group="music">音乐播放器</button>
    <button type="button" data-group="sponsor">赞助页面</button>
    <button type="button" data-group="bangumi">番组计划</button>
    <button type="button" data-group="gallery">相册页面</button>
    <button type="button" data-group="timeline">动态时间线</button>
    <button type="button" data-group="features">前端功能</button>
    <button type="button" data-group="license">授权激活</button>
    <button type="button" data-group="backup">设置备份</button>
  </aside>
  <section class="firefly-config-content">
    <div class="firefly-config-intro">
      <strong>DreamStory 追梦主题设置</strong>
      <span>按左侧分类配置主题，保存按钮在页面底部。</span>
    </div>
  </section>
  <template id="firefly-license-template">__FIREFLY_LICENSE_PANEL__</template>
</div>
<style>
  .typecho-page-main [role="form"] { width: 100%; max-width: 1180px; margin: 0 auto; }
  .firefly-config-shell { display: grid; grid-template-columns: 240px minmax(0, 1fr); gap: 18px; align-items: start; }
  .firefly-config-nav { position: sticky; top: 16px; display: grid; gap: 6px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
  .firefly-config-logo { display: grid; gap: 3px; padding: 12px 10px; border-bottom: 1px solid #eee; margin-bottom: 8px; }
  .firefly-config-logo strong { font-size: 18px; line-height: 1.15; color: #111827; }
  .firefly-config-logo span { font-size: 13px; line-height: 1.2; color: #6b7280; letter-spacing: 0.04em; }
  .firefly-config-nav button { border: 0; border-radius: 8px; padding: 10px 12px; background: transparent; text-align: left; cursor: pointer; color: #374151; }
  .firefly-config-nav button.active { background: #eef6ff; color: #2563eb; font-weight: 700; }
  .firefly-config-content { min-height: 520px; padding: 20px; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; }
  .firefly-config-intro { display: flex; justify-content: space-between; gap: 12px; padding-bottom: 14px; margin-bottom: 16px; border-bottom: 1px dashed #ddd; }
  .firefly-config-intro span { color: #6b7280; }
  .firefly-config-group { display: none; }
  .firefly-config-group.active { display: block; }
  .firefly-config-group h3 { margin: 0 0 16px; font-size: 18px; }
  .firefly-config-group .typecho-option { margin-bottom: 18px; padding-bottom: 14px; border-bottom: 1px dashed #eee; }
  .firefly-config-group textarea { min-height: 96px; }
  .firefly-backup-tools { display: grid; gap: 14px; max-width: 760px; }
  .firefly-backup-actions { display: flex; flex-wrap: wrap; gap: 10px; }
  .firefly-backup-actions button, .firefly-backup-file-label { display: inline-flex; align-items: center; justify-content: center; min-height: 36px; padding: 8px 14px; border: 0; border-radius: 8px; background: #2563eb; color: #fff; font-weight: 700; cursor: pointer; }
  .firefly-backup-actions button.secondary, .firefly-backup-file-label { background: #eef6ff; color: #2563eb; }
  .firefly-backup-file-label input { display: none; }
  .firefly-backup-tools textarea { width: 100%; min-height: 180px; box-sizing: border-box; font-family: Consolas, Monaco, monospace; }
  .firefly-backup-note { margin: 0; color: #6b7280; line-height: 1.7; }
  .firefly-backup-status { min-height: 20px; color: #2563eb; font-weight: 700; }
  .firefly-license-container { display: grid; gap: 16px; max-width: 900px; }
  .firefly-license-hero { display: flex; justify-content: space-between; gap: 16px; align-items: center; padding: 20px; border: 1px solid #e5e7eb; border-radius: 10px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); }
  .firefly-license-hero.is-valid { border-color: #bbf7d0; background: linear-gradient(135deg, #f0fdf4 0%, #f8fafc 100%); }
  .firefly-license-hero-main { display: flex; gap: 14px; align-items: center; min-width: 0; }
  .firefly-license-hero-icon { width: 48px; height: 48px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; background: #2563eb; color: #fff; font-size: 24px; font-weight: 800; flex: 0 0 auto; }
  .firefly-license-hero-icon svg { width: 25px; height: 25px; display: block; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .firefly-license-hero.is-valid .firefly-license-hero-icon { background: #16a34a; }
  .firefly-license-hero-title { font-size: 20px; line-height: 1.25; font-weight: 800; color: #111827; }
  .firefly-license-hero-desc { margin-top: 5px; color: #64748b; line-height: 1.55; }
  .firefly-license-hero-meta, .firefly-license-hero-actions, .firefly-license-input-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
  .firefly-license-hero-meta { margin-top: 10px; }
  .firefly-license-pill { display: inline-flex; align-items: center; min-height: 24px; padding: 2px 9px; border-radius: 999px; font-size: 12px; font-weight: 700; background: #fee2e2; color: #b91c1c; }
  .firefly-license-pill.valid { background: #dcfce7; color: #15803d; }
  .firefly-license-pill.light { background: #e0f2fe; color: #0369a1; }
  .firefly-license-btn { border: 0; border-radius: 8px; min-height: 36px; padding: 8px 14px; cursor: pointer; font-weight: 800; }
  .firefly-license-btn.primary { background: #2563eb; color: #fff; }
  .firefly-license-btn.ghost { background: #eef6ff; color: #2563eb; }
  .firefly-license-btn:disabled { opacity: .65; cursor: wait; }
  .firefly-license-message { padding: 10px 12px; border-radius: 8px; background: #eff6ff; color: #1d4ed8; font-weight: 700; }
  .firefly-license-message.error { background: #fef2f2; color: #dc2626; }
  .firefly-license-activate-box { display: grid; gap: 8px; padding: 18px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; }
  .firefly-license-activate-box label { font-weight: 800; color: #111827; }
  .firefly-license-activate-box p { margin: 0; color: #64748b; line-height: 1.6; }
  .firefly-license-input { flex: 1 1 280px; min-height: 38px; box-sizing: border-box; border: 1px solid #d1d5db; border-radius: 8px; padding: 8px 11px; }
  .firefly-license-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
  .firefly-license-card { min-height: 74px; padding: 14px; border: 1px solid #e5e7eb; border-radius: 10px; background: #fff; box-sizing: border-box; }
  .firefly-license-card .label { font-size: 12px; color: #64748b; margin-bottom: 8px; }
  .firefly-license-card .value { font-weight: 800; color: #111827; overflow-wrap: anywhere; }
  .firefly-license-upgrade { padding: 16px 18px; border: 1px solid #bfdbfe; border-radius: 10px; background: #eff6ff; color: #1e3a8a; }
  .firefly-license-upgrade strong { display: block; margin-bottom: 6px; color: #1d4ed8; font-size: 15px; }
  .firefly-license-upgrade p { margin: 0; line-height: 1.7; color: #1e40af; }
  .firefly-config-shell + .typecho-option, .firefly-config-shell ~ .typecho-option { display: none; }
  .firefly-config-content .typecho-option { display: block !important; }
  @media (max-width: 768px) {
    .firefly-config-shell { grid-template-columns: 1fr; }
    .firefly-config-nav { position: static; }
    .firefly-license-hero { align-items: stretch; flex-direction: column; }
    .firefly-license-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  }
  @media (max-width: 520px) {
    .firefly-license-grid { grid-template-columns: 1fr; }
  }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var shell = document.getElementById('firefly-config-shell');
  if (!shell) return;
  var content = shell.querySelector('.firefly-config-content');
  var form = shell.closest('form');
  if (!form) return;

  var groups = {
    basic: { title: '基础设置', fields: ['logoText','logoUrl','archivePageSize','enableMarkdownEditor','markdownEditorHeight','markdownEditorCodeTheme','defaultCover','defaultThemeMode','themeHue','customCss'] },
    navbar: { title: '导航顶部', fields: ['navbarStyle','navbarWidth','navbarItems','customNavItems'] },
    hero: { title: '壁纸横幅', fields: ['homeHeroHeight','innerHeroHeight','heroOverlayOpacity','heroImages','heroTitle','heroSubtitle'] },
    sidebar: { title: '侧栏组件', fields: ['sidebarPosition','leftSidebarBlock','rightSidebarBlock'] },
    profile: { title: '资料公告', fields: ['profileAvatar','profileName','profileBio','profileLinks','announcementTitle','announcementText'] },
    music: { title: '音乐播放器', fields: ['musicServer','musicType','musicId','musicApi','musicFallbackApis'] },
    sponsor: { title: '赞助页面', fields: ['sponsorPageTitle','sponsorPageDescription','sponsorPageNotice','sponsorAlipayQr','sponsorWechatQr','sponsorKofiUrl','sponsorKofiTitle','sponsorKofiDescription','sponsorAfdianUrl','sponsorAfdianTitle','sponsorAfdianDescription','sponsorList'] },
    bangumi: { title: '番组计划', fields: ['bangumiUserId','bangumiTitle','bangumiSubtitle','bangumiCategories','bangumiCategoryOrder','bangumiItemsPerPage','bangumiApi','bangumiServerRender','bangumiCacheMinutes','bangumiMaxTotal','bangumiPageLimit'] },
    gallery: { title: '相册页面', fields: ['galleryTitle','galleryDescription','galleryColumnWidth','galleryAlbums'] },
    timeline: { title: '动态时间线', fields: ['timelineTitle','timelineSubtitle','timelineCover','timelineSocialLinks','timelineItems'] },
    features: { title: '前端功能', fields: ['features'] },
    license: { title: '授权激活', fields: [], license: true }
  };

  groups.backup = { title: '设置备份', fields: [], backup: true };

  Object.keys(groups).forEach(function (key) {
    var panel = document.createElement('div');
    panel.className = 'firefly-config-group';
    panel.dataset.group = key;
    panel.innerHTML = '<h3>' + groups[key].title + '</h3>';
    if (groups[key].license) {
      var licenseTemplate = document.getElementById('firefly-license-template');
      if (licenseTemplate && licenseTemplate.content) {
        panel.appendChild(licenseTemplate.content.cloneNode(true));
      }
    } else if (groups[key].backup) {
      panel.innerHTML += '' +
        '<div class="firefly-backup-tools">' +
          '<p class="firefly-backup-note">导出会把当前表单里的主题设置保存为 JSON 文件。导入后请检查设置，并点击页面底部的保存按钮才会正式写入数据库。</p>' +
          '<div class="firefly-backup-actions">' +
            '<button type="button" id="firefly-export-settings">导出设置</button>' +
            '<label class="firefly-backup-file-label">选择备份文件<input type="file" id="firefly-import-file" accept="application/json,.json"></label>' +
            '<button type="button" class="secondary" id="firefly-import-settings">导入到表单</button>' +
          '</div>' +
          '<textarea id="firefly-backup-json" placeholder="也可以把备份 JSON 粘贴到这里，然后点击“导入到表单”。"></textarea>' +
          '<div class="firefly-backup-status" id="firefly-backup-status"></div>' +
        '</div>';
    } else {
      groups[key].fields.forEach(function (name) {
        var item = form.querySelector('[id^="typecho-option-item-' + name + '-"]');
        if (item) panel.appendChild(item);
      });
    }
    content.appendChild(panel);
  });

  var settingFields = [];
  Object.keys(groups).forEach(function (key) {
    groups[key].fields.forEach(function (name) {
      if (settingFields.indexOf(name) === -1) settingFields.push(name);
    });
  });

  function fieldNodes(name) {
    return Array.prototype.slice.call(form.querySelectorAll('[name="' + name + '"], [name="' + name + '[]"]'));
  }

  function checkboxNodes(name) {
    return fieldNodes(name).filter(function (node) { return node.type === 'checkbox'; });
  }

  function dispatchFieldEvents(nodes) {
    nodes.forEach(function (node) {
      node.dispatchEvent(new Event('input', { bubbles: true }));
      node.dispatchEvent(new Event('change', { bubbles: true }));
    });
  }

  function readField(name) {
    var nodes = fieldNodes(name);
    if (!nodes.length) return null;
    var checkboxes = checkboxNodes(name);
    if (checkboxes.length) {
      return checkboxes.filter(function (node) { return node.checked; }).map(function (node) { return node.value; });
    }
    var radios = nodes.filter(function (node) { return node.type === 'radio'; });
    if (radios.length) {
      var checked = radios.filter(function (node) { return node.checked; })[0];
      return checked ? checked.value : '';
    }
    var first = nodes.filter(function (node) { return node.type !== 'hidden'; })[0] || nodes[0];
    return first.value;
  }

  function writeField(name, value) {
    var nodes = fieldNodes(name);
    if (!nodes.length) return;
    var checkboxes = checkboxNodes(name);
    if (checkboxes.length) {
      var values = Array.isArray(value) ? value.map(String) : [String(value)];
      checkboxes.forEach(function (node) { node.checked = values.indexOf(String(node.value)) !== -1; });
      dispatchFieldEvents(checkboxes);
      return;
    }
    var radios = nodes.filter(function (node) { return node.type === 'radio'; });
    if (radios.length) {
      radios.forEach(function (node) { node.checked = String(node.value) === String(value); });
      dispatchFieldEvents(radios);
      return;
    }
    var first = nodes.filter(function (node) { return node.type !== 'hidden'; })[0] || nodes[0];
    first.value = value == null ? '' : String(value);
    dispatchFieldEvents([first]);
  }

  settingFields.forEach(function (name) {
    var checkboxes = checkboxNodes(name);
    if (!checkboxes.length || form.querySelector('input[type="hidden"][data-firefly-empty-field="' + name + '"]')) return;
    var emptyInput = document.createElement('input');
    emptyInput.type = 'hidden';
    emptyInput.name = name;
    emptyInput.value = '';
    emptyInput.dataset.fireflyEmptyField = name;
    if (checkboxes[0].parentNode) {
      checkboxes[0].parentNode.insertBefore(emptyInput, checkboxes[0]);
    } else {
      form.appendChild(emptyInput);
    }
  });

  function setBackupStatus(message, isError) {
    var status = document.getElementById('firefly-backup-status');
    if (!status) return;
    status.textContent = message;
    status.style.color = isError ? '#dc2626' : '#2563eb';
  }

  function exportSettings() {
    var settings = {};
    settingFields.forEach(function (name) {
      var value = readField(name);
      if (value !== null) settings[name] = value;
    });
    var payload = {
      theme: 'firefly-typecho',
      type: 'theme-settings-backup',
      exportedAt: new Date().toISOString(),
      settings: settings
    };
    var json = JSON.stringify(payload, null, 2);
    var textarea = document.getElementById('firefly-backup-json');
    if (textarea) textarea.value = json;
    var blob = new Blob([json], { type: 'application/json;charset=utf-8' });
    var link = document.createElement('a');
    var date = new Date().toISOString().slice(0, 10);
    link.href = URL.createObjectURL(blob);
    link.download = 'firefly-typecho-settings-' + date + '.json';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(link.href);
    setBackupStatus('已生成并下载备份文件。', false);
  }

  function importSettings() {
    var textarea = document.getElementById('firefly-backup-json');
    var text = textarea ? textarea.value.trim() : '';
    if (!text) {
      setBackupStatus('请先选择备份文件，或粘贴备份 JSON。', true);
      return;
    }
    try {
      var payload = JSON.parse(text);
      var settings = payload && payload.settings ? payload.settings : payload;
      if (!settings || typeof settings !== 'object') throw new Error('Invalid backup');
      settingFields.forEach(function (name) {
        if (Object.prototype.hasOwnProperty.call(settings, name)) writeField(name, settings[name]);
      });
      setBackupStatus('已导入到表单。确认无误后，请点击页面底部保存按钮。', false);
    } catch (error) {
      setBackupStatus('备份 JSON 解析失败，请确认文件内容是否完整。', true);
    }
  }

  var exportButton = document.getElementById('firefly-export-settings');
  var importButton = document.getElementById('firefly-import-settings');
  var importFile = document.getElementById('firefly-import-file');
  if (exportButton) exportButton.addEventListener('click', exportSettings);
  if (importButton) importButton.addEventListener('click', importSettings);
  if (importFile) {
    importFile.addEventListener('change', function () {
      var file = importFile.files && importFile.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function () {
        var textarea = document.getElementById('firefly-backup-json');
        if (textarea) textarea.value = String(reader.result || '');
        setBackupStatus('备份文件已读取，可以点击“导入到表单”。', false);
      };
      reader.onerror = function () {
        setBackupStatus('备份文件读取失败。', true);
      };
      reader.readAsText(file);
    });
  }

  function activate(key) {
    shell.querySelectorAll('.firefly-config-nav button').forEach(function (btn) {
      btn.classList.toggle('active', btn.dataset.group === key);
    });
    shell.querySelectorAll('.firefly-config-group').forEach(function (panel) {
      panel.classList.toggle('active', panel.dataset.group === key);
    });
  }

  shell.querySelectorAll('.firefly-config-nav button').forEach(function (btn) {
    btn.addEventListener('click', function () { activate(btn.dataset.group); });
  });

  function initLicensePanel() {
    var panel = shell.querySelector('.firefly-license-container');
    if (!panel) return;
    var apiBase = panel.dataset.apiBase || '?dreamstory_api=';
    var token = panel.dataset.token || '';
    var input = document.getElementById('dreamstoryLicenseKey');
    var storedInput = form.querySelector('[name="dreamstoryLicenseKey"]');
    var message = document.getElementById('firefly-license-message');
    var activateButton = document.getElementById('firefly-license-activate-btn');
    var verifyButton = document.getElementById('firefly-license-verify-btn');
    var updateButton = document.getElementById('firefly-license-check-update-btn');

    function showMessage(text, isError) {
      if (!message) return;
      message.textContent = text || '';
      message.style.display = text ? 'block' : 'none';
      message.classList.toggle('error', !!isError);
    }

    function postApi(api, data) {
      var body = new FormData();
      body.append('_', token);
      Object.keys(data || {}).forEach(function (key) { body.append(key, data[key]); });
      return fetch(apiBase + encodeURIComponent(api), {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      }).then(function (res) { return res.json(); });
    }

    function renderStatus(status) {
      if (!status) return;
      var valid = !!status.s;
      var payload = status.p || {};
      var title = document.getElementById('firefly-license-title');
      var desc = document.getElementById('firefly-license-desc');
      var pill = document.getElementById('firefly-license-pill');
      var source = document.getElementById('firefly-license-source');
      var domain = document.getElementById('firefly-license-domain');
      var plan = document.getElementById('firefly-license-plan');
      var expire = document.getElementById('firefly-license-expire');
      var days = document.getElementById('firefly-license-days');
      var hero = shell.querySelector('.firefly-license-hero');
      panel.dataset.active = valid ? '1' : '0';

      if (hero) {
        hero.classList.toggle('is-valid', valid);
        hero.classList.toggle('is-idle', !valid);
      }
      if (title) title.textContent = valid ? 'DreamStory 主题已激活' : '激活 DreamStory 主题';
      if (desc) desc.textContent = valid ? '感谢你对 DreamStory 主题的支持' : '输入授权密钥后即可启用授权状态面板';
      if (pill) {
        pill.textContent = valid ? '赞助版' : '未授权';
        pill.classList.toggle('valid', valid);
        pill.classList.toggle('invalid', !valid);
      }
      if (source) source.textContent = valid ? '本地授权' : '等待激活';
      if (domain) domain.textContent = payload.h || location.host;
      if (plan) plan.textContent = valid ? '赞助版' : '未激活';
      if (expire) expire.textContent = valid ? '长期有效' : '未知';
      if (days) days.textContent = '长期有效';
      if (verifyButton) verifyButton.textContent = valid ? '刷新状态' : '立即激活';
    }

    function withBusy(button, text, task) {
      if (!button) return task();
      var oldText = button.textContent;
      button.disabled = true;
      button.textContent = text;
      return task().finally(function () {
        button.disabled = false;
        button.textContent = oldText;
      });
    }

    function activateLicense() {
      var key = input ? input.value.trim() : '';
      if (storedInput) storedInput.value = key;
      showMessage('', false);
      if (!key) {
        showMessage('请输入授权密钥。', true);
        return;
      }
      withBusy(activateButton, '激活中...', function () {
        return postApi('license_activate', { key: key }).then(function (res) {
          showMessage(res.message || (res.success ? '激活成功。' : '激活失败。'), !res.success);
          if (res.success) {
            if (storedInput) storedInput.value = key;
            renderStatus(res.data);
          }
        }).catch(function () {
          showMessage('请求失败，请检查后台登录状态后重试。', true);
        });
      });
    }

    function verifyLicense() {
      if (panel.dataset.active !== '1' && input && input.value.trim()) {
        activateLicense();
        return;
      }
      withBusy(verifyButton, '刷新中...', function () {
        return postApi('license_verify', {}).then(function (res) {
          showMessage(res.message || '状态已刷新。', !res.success);
          if (res.data) renderStatus(res.data);
        }).catch(function () {
          showMessage('请求失败，请刷新后台后重试。', true);
        });
      });
    }

    if (activateButton) activateButton.addEventListener('click', activateLicense);
    if (verifyButton) verifyButton.addEventListener('click', verifyLicense);
    if (updateButton) {
      updateButton.addEventListener('click', function () {
        withBusy(updateButton, '检查中...', function () {
          return postApi('license_check_update', {}).then(function (res) {
            showMessage(res.message || '当前已是最新版本。', !res.success);
          }).catch(function () {
            showMessage('检查更新失败，请稍后重试。', true);
          });
        });
      });
    }
  }

  initLicensePanel();
  activate('basic');
});
</script>
HTML;

    $html = str_replace('__FIREFLY_LICENSE_PANEL__', $licensePanel, $html);

    $layout = new \Typecho\Widget\Helper\Layout('div', ['class' => 'firefly-config-inject']);
    $layout->html($html);
    $form->addItem($layout);
}

function themeFields($layout)
{
    $layout->addItem(new \Typecho\Widget\Helper\Form\Element\Text(
        'cover',
        null,
        null,
        '文章封面',
        '填写图片 URL，会显示在文章卡片和文章页顶部。'
    ));
}
