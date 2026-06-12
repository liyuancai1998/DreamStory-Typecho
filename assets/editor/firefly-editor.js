(function (window) {
    function createModal(html) {
        var overlay = document.createElement("div");
        overlay.className = "firefly-editor-modal-overlay";
        overlay.innerHTML = '<div class="firefly-editor-modal">' + html + "</div>";
        document.body.appendChild(overlay);

        function close() {
            overlay.remove();
            document.removeEventListener("keydown", onKeyDown);
        }

        function onKeyDown(event) {
            if (event.key === "Escape") close();
        }

        overlay.querySelector(".firefly-editor-modal__close").addEventListener("click", close);
        overlay.querySelector(".firefly-editor-modal__cancel").addEventListener("click", close);
        overlay.addEventListener("click", function (event) {
            if (event.target === overlay) close();
        });
        document.addEventListener("keydown", onKeyDown);

        overlay.close = close;
        return overlay;
    }

    function escapeAttribute(value) {
        return String(value).replace(/"/g, "&quot;");
    }

    function createCmAdapter(cm) {
        return {
            getSelection: function () {
                return cm.getSelection();
            },
            insertValue: function (value) {
                cm.replaceSelection(value);
            }
        };
    }

    function insertIndent(editor) {
        editor.insertValue("　　");
    }

    function insertColor(editor) {
        var selected = editor.getSelection() || "";
        var modal = createModal(
            '<div class="firefly-editor-modal__header"><span>彩色文字</span><span class="firefly-editor-modal__close">&times;</span></div>' +
            '<div class="firefly-editor-modal__body">' +
            '<div class="firefly-editor-modal__field"><label>文字颜色 <em>*</em></label><input name="color-value" placeholder="例如 #ff5da2、red、rgb(255,0,0)"></div>' +
            '<div class="firefly-editor-modal__field"><label>文字内容</label><input name="color-text" placeholder="留空则使用选中文字"></div>' +
            '</div><div class="firefly-editor-modal__footer"><button class="firefly-editor-modal__cancel" type="button">取消</button><button class="firefly-editor-modal__confirm" type="button">确定</button></div>'
        );
        modal.querySelector('input[name="color-text"]').value = selected;
        modal.querySelector(".firefly-editor-modal__confirm").addEventListener("click", function () {
            var color = modal.querySelector('input[name="color-value"]').value.trim();
            if (!color) {
                modal.querySelector('input[name="color-value"]').focus();
                return;
            }
            var text = modal.querySelector('input[name="color-text"]').value || selected || "彩色文字";
            editor.insertValue('[font color="' + escapeAttribute(color) + '"]' + text + "[/font]");
            modal.close();
        });
        setTimeout(function () {
            modal.querySelector('input[name="color-value"]').focus();
        }, 50);
    }

    function insertMusic(editor) {
        var modal = createModal(
            '<div class="firefly-editor-modal__header"><span>插入音乐</span><span class="firefly-editor-modal__close">&times;</span></div>' +
            '<div class="firefly-editor-modal__body">' +
            '<div class="firefly-editor-modal__field"><label>音频地址 <em>*</em></label><input name="music-url" placeholder="请输入音频文件链接"></div>' +
            '<div class="firefly-editor-modal__field"><label>歌曲名称</label><input name="music-name" placeholder="选填"></div>' +
            '<div class="firefly-editor-modal__field"><label>歌手</label><input name="music-artist" placeholder="选填"></div>' +
            '</div><div class="firefly-editor-modal__footer"><button class="firefly-editor-modal__cancel" type="button">取消</button><button class="firefly-editor-modal__confirm" type="button">确定</button></div>'
        );
        modal.querySelector(".firefly-editor-modal__confirm").addEventListener("click", function () {
            var url = modal.querySelector('input[name="music-url"]').value.trim();
            if (!url) {
                modal.querySelector('input[name="music-url"]').focus();
                return;
            }
            var name = modal.querySelector('input[name="music-name"]').value.trim();
            var artist = modal.querySelector('input[name="music-artist"]').value.trim();
            var shortcode = '{mp3 url="' + escapeAttribute(url) + '"';
            if (name) shortcode += ' name="' + escapeAttribute(name) + '"';
            if (artist) shortcode += ' artist="' + escapeAttribute(artist) + '"';
            shortcode += "/}";
            editor.insertValue("\n" + shortcode + "\n");
            modal.close();
        });
        setTimeout(function () {
            modal.querySelector('input[name="music-url"]').focus();
        }, 50);
    }

    function insertVideo(editor) {
        var modal = createModal(
            '<div class="firefly-editor-modal__header"><span>插入视频</span><span class="firefly-editor-modal__close">&times;</span></div>' +
            '<div class="firefly-editor-modal__body"><div class="firefly-editor-modal__field"><label>视频地址 <em>*</em></label><input name="video-url" placeholder="请输入视频文件链接，例如 .mp4"></div></div>' +
            '<div class="firefly-editor-modal__footer"><button class="firefly-editor-modal__cancel" type="button">取消</button><button class="firefly-editor-modal__confirm" type="button">确定</button></div>'
        );
        modal.querySelector(".firefly-editor-modal__confirm").addEventListener("click", function () {
            var url = modal.querySelector('input[name="video-url"]').value.trim();
            if (!url) {
                modal.querySelector('input[name="video-url"]').focus();
                return;
            }
            editor.insertValue('\n<video controls src="' + escapeAttribute(url) + '"></video>\n');
            modal.close();
        });
        setTimeout(function () {
            modal.querySelector('input[name="video-url"]').focus();
        }, 50);
    }

    window.FireflyEditor = {
        createCmAdapter: createCmAdapter,
        insertIndent: insertIndent,
        insertColor: insertColor,
        insertMusic: insertMusic,
        insertVideo: insertVideo
    };
})(window);

