<?php
if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

function fireflyEditorOptions()
{
    try {
        return \Typecho\Widget::widget('\Widget\Options');
    } catch (\Exception $e) {
        return \Widget\Options::alloc();
    }
}

function fireflyEditorThemeUrl()
{
    $options = fireflyEditorOptions();
    return rtrim($options->themeUrl, '/');
}

function fireflyRenderMarkdownEditor($content)
{
    $options = fireflyEditorOptions();
    $themeUrl = fireflyEditorThemeUrl();
    $height = max(360, intval(fireflyThemeOption('markdownEditorHeight', '640')));
    $editorTheme = fireflyThemeOption('markdownEditorCodeTheme', 'default');
    $allowedEditorThemes = ['default', 'monokai', 'ambiance', 'twilight', 'pastel-on-dark'];
    if (!in_array($editorTheme, $allowedEditorThemes, true)) {
        $editorTheme = 'default';
    }
    $uploadUrl = \Widget\Security::alloc()->getTokenUrl(
        \Typecho\Common::url('/action/upload', $options->index)
    );
    ?>
    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/assets/editor/editor.md/css/editormd.min.css">
    <?php if ($editorTheme !== 'default'): ?>
    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/assets/editor/editor.md/lib/codemirror/theme/<?php echo htmlspecialchars($editorTheme, ENT_QUOTES, 'UTF-8'); ?>.css">
    <?php endif; ?>
    <link rel="stylesheet" href="<?php echo $themeUrl; ?>/assets/editor/firefly-editor.css">
    <style>
        .editormd-fullscreen { z-index: 99999; }
        .editormd { margin-bottom: 15px; }
        #text { display: none; }
        .typecho-post-area > .col-mb-9 .typecho-label { display: none; }
    </style>
    <script src="<?php echo $themeUrl; ?>/assets/editor/editor.md/editormd.min.js"></script>
    <script src="<?php echo $themeUrl; ?>/assets/editor/firefly-editor.js"></script>
    <script>
    (function () {
        $(document).ready(function () {
            var originalTextarea = $("#text");
            if (!originalTextarea.length || typeof editormd === "undefined") return;

            var editorContainer = $('<div id="firefly-editormd"></div>');
            originalTextarea.after(editorContainer);
            editorContainer.append(originalTextarea);

            var fireflyEditor = editormd("firefly-editormd", {
                width: "100%",
                height: <?php echo $height; ?>,
                path: "<?php echo $themeUrl; ?>/assets/editor/editor.md/lib/",
                theme: "default",
                previewTheme: "default",
                editorTheme: <?php echo json_encode($editorTheme); ?>,
                markdown: originalTextarea.val(),
                inputStyle: "textarea",
                watch: false,
                lineNumbers: false,
                codeFold: false,
                saveHTMLToTextarea: false,
                searchReplace: false,
                htmlDecode: true,
                taskList: true,
                tocm: false,
                tex: false,
                flowChart: false,
                sequenceDiagram: false,
                autoFocus: false,
                styleActiveLine: true,
                imageUpload: true,
                imageFormats: ["jpg", "jpeg", "gif", "png", "avif", "webp"],
                imageUploadURL: <?php echo json_encode($uploadUrl, JSON_UNESCAPED_SLASHES); ?>,
                syncScrolling: "single",
                toolbarIcons: function () {
                    return [
                        "bold", "del", "italic", "quote", "|",
                        "h1", "h2", "h3", "|",
                        "list-ul", "list-ol", "hr", "|",
                        "link", "image", "code", "code-block", "table", "|",
                        "insertIndent", "insertColor", "insertMusic", "insertVideo", "|",
                        "search", "watch", "preview", "fullscreen", "help"
                    ];
                },
                toolbarIconsClass: {
                    insertIndent: "fa-indent",
                    insertColor: "fa-font",
                    insertMusic: "fa-music",
                    insertVideo: "fa-film"
                },
                lang: {
                    toolbar: {
                        insertIndent: "首行缩进",
                        insertColor: "彩色文字",
                        insertMusic: "插入音乐",
                        insertVideo: "插入视频"
                    }
                },
                toolbarHandlers: {
                    insertIndent: function (cm) {
                        FireflyEditor.insertIndent(FireflyEditor.createCmAdapter(cm));
                    },
                    insertColor: function (cm) {
                        FireflyEditor.insertColor(FireflyEditor.createCmAdapter(cm));
                    },
                    insertMusic: function (cm) {
                        FireflyEditor.insertMusic(FireflyEditor.createCmAdapter(cm));
                    },
                    insertVideo: function (cm) {
                        FireflyEditor.insertVideo(FireflyEditor.createCmAdapter(cm));
                    }
                },
                onload: function () {
                    $('label[for="text"]').hide();
                    updateEmptyState();
                },
                onchange: function () {
                    originalTextarea.val(this.getMarkdown());
                    $('form[name="write_post"], form[name="write_page"]').trigger("write");
                    updateEmptyState();
                }
            });

            function updateEmptyState() {
                $("#firefly-editormd").toggleClass(
                    "firefly-editor-empty",
                    !fireflyEditor.getMarkdown()
                );
            }

            if (fireflyEditor.cm) {
                fireflyEditor.cm.on("cursorActivity", updateEmptyState);
                fireflyEditor.cm.on("focus", updateEmptyState);
                fireflyEditor.cm.on("blur", updateEmptyState);
            }

            $('form[name="write_post"], form[name="write_page"]').on("submit", function () {
                originalTextarea.val(fireflyEditor.getMarkdown());
            });

            if (typeof Typecho !== "undefined") {
                Typecho.insertFileToEditor = function (file, url, isImage) {
                    var value = isImage ? "![" + file + "](" + url + ")" : "[" + file + "](" + url + ")";
                    fireflyEditor.insertValue(value);
                };
                Typecho.uploadComplete = function (attachment) {
                    Typecho.insertFileToEditor(attachment.title, attachment.url, attachment.isImage);
                };
            }
        });
    })();
    </script>
    <?php
}

function fireflyRegisterEditorHooks()
{
    $enabled = fireflyThemeOption('enableMarkdownEditor', '1');
    if ($enabled !== '1') {
        return;
    }

    \Typecho\Plugin::factory('admin/write-post.php')->richEditor = function ($post) {
        fireflyRenderMarkdownEditor($post);
    };
    \Typecho\Plugin::factory('admin/write-page.php')->richEditor = function ($page) {
        fireflyRenderMarkdownEditor($page);
    };
}
