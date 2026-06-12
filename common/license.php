<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function fireflyLicenseOptionName()
{
    try {
        $options = \Typecho\Widget::widget('\Widget\Options');
        $theme = isset($options->theme) && $options->theme !== '' ? (string) $options->theme : 'DreamStory';
        return 'theme:' . $theme;
    } catch (\Throwable $e) {
        return 'theme:DreamStory';
    }
}

function fireflyStandaloneLicenseOptionName()
{
    return 'dreamstory:license';
}

function fireflyReadThemeOptions()
{
    try {
        $db = \Typecho\Db::get();
        $row = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', fireflyLicenseOptionName()));
        if (!$row || !isset($row['value'])) {
            return [];
        }

        $value = (string) $row['value'];
        $decoded = json_decode($value, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        $decoded = @unserialize($value);
        return is_array($decoded) ? $decoded : [];
    } catch (\Throwable $e) {
        return [];
    }
}

function fireflyReadStandaloneLicenseOptions()
{
    try {
        $db = \Typecho\Db::get();
        $row = $db->fetchRow($db->select('value')->from('table.options')->where('name = ?', fireflyStandaloneLicenseOptionName()));
        if (!$row || !isset($row['value'])) {
            return [];
        }

        $decoded = json_decode((string) $row['value'], true);
        return is_array($decoded) ? $decoded : [];
    } catch (\Throwable $e) {
        return [];
    }
}

function fireflyWriteThemeOptions(array $themeOptions)
{
    $db = \Typecho\Db::get();
    $name = fireflyLicenseOptionName();
    $value = serialize($themeOptions);
    $row = $db->fetchRow($db->select('name')->from('table.options')->where('name = ?', $name)->limit(1));

    if ($row) {
        $db->query($db->update('table.options')->rows(['value' => $value])->where('name = ?', $name));
        return;
    }

    $db->query($db->insert('table.options')->rows([
        'name' => $name,
        'user' => 0,
        'value' => $value,
    ]));
}

function fireflyWriteStandaloneLicenseOptions(array $licenseOptions)
{
    $db = \Typecho\Db::get();
    $name = fireflyStandaloneLicenseOptionName();
    $value = json_encode($licenseOptions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $row = $db->fetchRow($db->select('name')->from('table.options')->where('name = ?', $name)->limit(1));

    if ($row) {
        $db->query($db->update('table.options')->rows(['value' => $value])->where('name = ?', $name));
        return;
    }

    $db->query($db->insert('table.options')->rows([
        'name' => $name,
        'user' => 0,
        'value' => $value,
    ]));
}

function fireflyGetStoredLicenseKey()
{
    $themeOptions = fireflyReadThemeOptions();
    $license = isset($themeOptions['dreamstoryLicenseKey']) ? trim((string) $themeOptions['dreamstoryLicenseKey']) : '';
    if ($license !== '') {
        return $license;
    }

    $standaloneOptions = fireflyReadStandaloneLicenseOptions();
    return isset($standaloneOptions['key']) ? trim((string) $standaloneOptions['key']) : '';
}

function fireflyGetAuthConfig()
{
    $license = fireflyGetStoredLicenseKey();

    return [
        'license' => $license,
        'host' => isset($_SERVER['HTTP_HOST']) ? strtolower((string) $_SERVER['HTTP_HOST']) : '',
        'endpoint' => fireflyLicenseServerEndpoint(),
    ];
}

function fireflyLicenseServerEndpoint()
{
    if (defined('DREAMSTORY_LICENSE_ENDPOINT')) {
        return trim((string) DREAMSTORY_LICENSE_ENDPOINT);
    }

    return 'https://blog.dreamstory.cn/license-server/index.php';
}

function fireflyLicenseNormalizeHost($host)
{
    $host = strtolower(trim((string) $host));
    $host = preg_replace('/^https?:\/\//', '', $host);
    $host = preg_replace('/\/.*$/', '', $host);
    return preg_replace('/:\d+$/', '', $host);
}

function fireflyBuildLicensePayload($license)
{
    $host = isset($_SERVER['HTTP_HOST']) ? fireflyLicenseNormalizeHost($_SERVER['HTTP_HOST']) : '';

    return [
        'h' => $host,
        'e' => 0,
        'p' => 'sponsor',
        'f' => false,
        'k' => substr(hash('sha256', $license), 0, 12),
    ];
}

function fireflyRemoteLicenseRequest($action, array $payload = [])
{
    $endpoint = fireflyLicenseServerEndpoint();
    if ($endpoint === '') {
        return null;
    }

    $config = fireflyGetAuthConfig();
    $body = array_merge([
        'action' => $action,
        'product' => 'dreamstory',
        'domain' => fireflyLicenseNormalizeHost($config['host']),
        'version' => defined('DREAMSTORY_VERSION') ? DREAMSTORY_VERSION : '1.0.0',
    ], $payload);

    $postData = http_build_query($body);
    $response = null;

    if (function_exists('curl_init')) {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $postData,
                'timeout' => 10,
            ],
        ]);
        $response = @file_get_contents($endpoint, false, $context);
    }

    if (!is_string($response) || $response === '') {
        return null;
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}

function fireflyBuildStatusFromServer(array $remote, $license)
{
    $data = isset($remote['data']) && is_array($remote['data']) ? $remote['data'] : [];
    $payload = isset($data['payload']) && is_array($data['payload']) ? $data['payload'] : [];
    $expires = isset($payload['e']) ? (int) $payload['e'] : 0;
    $now = time();
    $days = $expires > 0 ? max(0, (int) ceil(($expires - $now) / 86400)) : null;

    if (empty($payload)) {
        $payload = fireflyBuildLicensePayload($license);
    }

    return [
        's' => !empty($remote['success']),
        'p' => $payload,
        'e' => $expires > 0 ? $expires : PHP_INT_MAX,
        'd' => $days,
        'c' => false,
        'msg' => isset($remote['message']) ? (string) $remote['message'] : '',
    ];
}

function fireflyLicenseIcon($isValid)
{
    if ($isValid) {
        return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>';
    }

    return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><circle cx="12" cy="11" r="2"></circle><path d="M12 13v3"></path></svg>';
}

function fireflyAuthGetStatus($force = false)
{
    $license = fireflyGetStoredLicenseKey();
    $payload = fireflyBuildLicensePayload($license !== '' ? $license : 'dreamstory-free-standard');
    $payload['p'] = 'free-standard';
    $payload['f'] = false;

    return [
        's' => true,
        'p' => $payload,
        'e' => PHP_INT_MAX,
        'd' => null,
        'c' => false,
        'msg' => '免费标准版已启用',
    ];
}
function fireflyLicenseIsActive()
{
    $status = fireflyAuthGetStatus();
    return !empty($status['s']);
}

function fireflyLicenseBlockFrontend()
{
    return;
}
function fireflyRenderLicensePanel()
{
    $status = fireflyAuthGetStatus();
    $payload = isset($status['p']) && is_array($status['p']) ? $status['p'] : [];
    $domain = isset($payload['h']) && $payload['h'] !== '' ? $payload['h'] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');

    ob_start();
    ?>
    <div class="firefly-license-container" data-active="1">
      <div class="firefly-license-hero is-valid">
        <div class="firefly-license-hero-main">
          <div class="firefly-license-hero-icon"><?php echo fireflyLicenseIcon(true); ?></div>
          <div class="firefly-license-hero-text">
            <div class="firefly-license-hero-title" id="firefly-license-title">DreamStory 免费标准版已启用</div>
            <div class="firefly-license-hero-desc" id="firefly-license-desc">当前版本不需要授权密钥或远程授权服务器。</div>
            <div class="firefly-license-hero-meta">
              <span class="firefly-license-pill valid" id="firefly-license-pill">免费标准版</span>
              <span class="firefly-license-pill light" id="firefly-license-source">本地启用</span>
            </div>
          </div>
        </div>
      </div>

      <div class="firefly-license-grid">
        <div class="firefly-license-card">
          <div class="label">站点域名</div>
          <div class="value" id="firefly-license-domain"><?php echo htmlspecialchars($domain, ENT_QUOTES, 'UTF-8'); ?></div>
        </div>
        <div class="firefly-license-card">
          <div class="label">版本类型</div>
          <div class="value" id="firefly-license-plan">免费标准版</div>
        </div>
        <div class="firefly-license-card">
          <div class="label">有效时间</div>
          <div class="value" id="firefly-license-expire">长期有效</div>
        </div>
        <div class="firefly-license-card">
          <div class="label">授权方式</div>
          <div class="value" id="firefly-license-days">无需激活</div>
        </div>
      </div>

      <div class="firefly-license-upgrade">
        <strong>想解锁更多能力？</strong>
        <p>免费标准版可长期使用。如需更多专业功能、优先更新以及后续主题维护支持，可升级专业版激活。咨询与开通请联系 QQ：2013458886。</p>
      </div>
    </div>
    <?php
    return ob_get_clean();
}
function fireflyLicenseJsonResponse(array $payload)
{
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

function fireflyVerifyLicenseApiToken()
{
    $token = isset($_REQUEST['_']) ? (string) $_REQUEST['_'] : '';
    if ($token === '') {
        return false;
    }

    try {
        $security = \Widget\Security::alloc();
        return hash_equals($security->getToken('dreamstory_license_api'), $token);
    } catch (\Throwable $e) {
        return false;
    }
}

function fireflyHandleLicenseApi($api)
{
    $user = \Typecho\Widget::widget('\Widget\User');
    if (!$user->hasLogin() || !$user->pass('administrator', true)) {
        return ['code' => -1, 'success' => false, 'message' => '权限不足'];
    }

    if (!fireflyVerifyLicenseApiToken()) {
        return ['code' => -1, 'success' => false, 'message' => '安全校验失败，请刷新后台后重试'];
    }

    if ($api === 'license_activate') {
        $key = isset($_POST['key']) ? trim((string) $_POST['key']) : 'dreamstory-free-standard';
        if ($key === '') {
            $key = 'dreamstory-free-standard';
        }

        $themeOptions = fireflyReadThemeOptions();
        $themeOptions['dreamstoryLicenseKey'] = $key;
        $themeOptions['dreamstoryLicenseActivatedAt'] = time();
        fireflyWriteThemeOptions($themeOptions);
        fireflyWriteStandaloneLicenseOptions([
            'key' => $key,
            'activated_at' => time(),
            'host' => isset($_SERVER['HTTP_HOST']) ? fireflyLicenseNormalizeHost($_SERVER['HTTP_HOST']) : '',
            'plan' => 'free-standard',
        ]);

        return [
            'code' => 0,
            'success' => true,
            'message' => '免费标准版已启用',
            'data' => fireflyAuthGetStatus(true),
        ];
    }

    if ($api === 'license_verify') {
        $status = fireflyAuthGetStatus(true);
        return [
            'code' => 0,
            'success' => true,
            'message' => '免费标准版已启用',
            'data' => $status,
        ];
    }

    if ($api === 'license_check_update') {
        return [
            'code' => 0,
            'success' => true,
            'message' => '免费标准版无需授权服务器',
            'data' => [
                'has_update' => false,
                'version' => defined('DREAMSTORY_VERSION') ? DREAMSTORY_VERSION : '1.0.0',
            ],
        ];
    }

    return ['code' => -1, 'success' => false, 'message' => '未知接口'];
}

