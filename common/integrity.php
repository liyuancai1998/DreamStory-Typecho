<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function fireflyIntegrityManifestPath($themeDir)
{
    return rtrim((string) $themeDir, '/\\') . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'integrity-manifest.php';
}

function fireflyIntegritySecret()
{
    return 'DS_INTEGRITY_2db7df9eb471b1ff5b1fcd8c21f23d44210cc9fbc5270a33c580948cd71a2fcc';
}

function fireflyIntegrityFail($reason = '')
{
    if (function_exists('fireflyIntegrityHardStop')) {
        fireflyIntegrityHardStop($reason);
    }

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

function fireflyIntegrityGuard($themeDir)
{
    return true;
}
