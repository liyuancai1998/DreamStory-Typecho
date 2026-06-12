(function () {
    var root = document.documentElement;
    var searchToggle = document.getElementById('search-toggle');
    var searchPanel = document.getElementById('search-panel');
    var menuToggle = document.getElementById('menu-toggle');
    var mobileMenu = document.getElementById('mobile-menu');
    var themeToggle = document.getElementById('theme-toggle');
    var musicToggle = document.getElementById('music-toggle');
    var navMusicPanel = document.getElementById('nav-music-panel');
    var settingsToggle = document.getElementById('display-settings-toggle');
    var settingsPanel = document.getElementById('display-settings-panel');
    var hueRange = document.getElementById('hue-range');
    var hueValue = document.getElementById('hue-value');
    var heroTitleToggle = document.getElementById('setting-hero-title');
    var wavesToggle = document.getElementById('setting-waves');
    var fadeToggle = document.getElementById('setting-fade');
    var sakuraToggle = document.getElementById('setting-sakura');
    var backTop = document.getElementById('back-top');
    var postList = document.getElementById('post-list');
    var sakuraTimer = null;

    document.querySelectorAll('.article-content pre').forEach(function (pre) {
        var code = pre.querySelector('code');
        var className = code ? code.className : pre.className;
        var language = '';
        var match = className && className.match(/(?:language|lang)-([a-z0-9_+-]+)/i);

        if (match) {
            language = match[1].replace(/^c\+\+$/i, 'cpp');
            pre.classList.add('lang-' + language);
        }

        if (!pre.dataset.lang) {
            pre.dataset.lang = language || 'code';
        }

        pre.classList.add('prettyprint');
    });

    if (typeof window.prettyPrint === 'function') {
        window.prettyPrint();
    }

    function closePanels(event) {
        var target = event.target;
        if (searchPanel && searchPanel.classList.contains('open')) {
            if (!searchPanel.contains(target) && searchToggle && !searchToggle.contains(target)) {
                searchPanel.classList.remove('open');
            }
        }
        if (mobileMenu && mobileMenu.classList.contains('open')) {
            if (!mobileMenu.contains(target) && menuToggle && !menuToggle.contains(target)) {
                mobileMenu.classList.remove('open');
            }
        }
        if (settingsPanel && settingsPanel.classList.contains('open')) {
            if (!settingsPanel.contains(target) && settingsToggle && !settingsToggle.contains(target)) {
                settingsPanel.classList.remove('open');
            }
        }
        if (navMusicPanel && navMusicPanel.classList.contains('open')) {
            if (!navMusicPanel.contains(target) && musicToggle && !musicToggle.contains(target)) {
                navMusicPanel.classList.remove('open');
            }
        }
    }

    if (searchToggle && searchPanel) {
        searchToggle.addEventListener('click', function () {
            searchPanel.classList.toggle('open');
            var input = searchPanel.querySelector('input');
            if (searchPanel.classList.contains('open') && input) {
                setTimeout(function () {
                    input.focus();
                }, 60);
            }
        });
    }

    if (menuToggle && mobileMenu) {
        menuToggle.addEventListener('click', function () {
            mobileMenu.classList.toggle('open');
        });
    }

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            var isDark = root.classList.toggle('dark');
            localStorage.setItem('firefly-theme', isDark ? 'dark' : 'light');
        });
    }

    if (settingsToggle && settingsPanel) {
        settingsToggle.addEventListener('click', function () {
            settingsPanel.classList.toggle('open');
            if (navMusicPanel) navMusicPanel.classList.remove('open');
        });
    }

    if (musicToggle && navMusicPanel) {
        musicToggle.addEventListener('click', function () {
            navMusicPanel.classList.toggle('open');
            if (settingsPanel) settingsPanel.classList.remove('open');
        });
        navMusicPanel.addEventListener('click', function (event) {
            event.stopPropagation();
        });
    }

    document.addEventListener('click', closePanels);

    function applyStoredSetting(key, className, defaultValue) {
        var stored = localStorage.getItem(key);
        var enabled = stored === null ? defaultValue : stored === 'true';
        root.ownerDocument.body.classList.toggle(className, !enabled);
        return enabled;
    }

    function bindSwitch(input, key, className, defaultValue) {
        if (!input) return;
        input.checked = applyStoredSetting(key, className, defaultValue);
        input.addEventListener('change', function () {
            localStorage.setItem(key, input.checked ? 'true' : 'false');
            root.ownerDocument.body.classList.toggle(className, !input.checked);
        });
    }

    if (hueRange) {
        var storedHue = localStorage.getItem('firefly-hue');
        if (storedHue) hueRange.value = storedHue;
        root.style.setProperty('--hue', hueRange.value);
        if (hueValue) hueValue.textContent = hueRange.value;
        hueRange.addEventListener('input', function () {
            root.style.setProperty('--hue', hueRange.value);
            localStorage.setItem('firefly-hue', hueRange.value);
            if (hueValue) hueValue.textContent = hueRange.value;
        });
    }

    function setWallpaperMode(mode) {
        ['banner', 'fullscreen', 'overlay', 'solid'].forEach(function (name) {
            document.body.classList.toggle('wallpaper-' + name, name === mode);
        });
        localStorage.setItem('firefly-wallpaper-mode', mode);
        document.querySelectorAll('[data-wallpaper-mode]').forEach(function (button) {
            button.classList.toggle('active', button.dataset.wallpaperMode === mode);
        });
    }

    document.querySelectorAll('[data-wallpaper-mode]').forEach(function (button) {
        button.addEventListener('click', function () {
            setWallpaperMode(button.dataset.wallpaperMode || 'banner');
        });
    });
    setWallpaperMode(localStorage.getItem('firefly-wallpaper-mode') || 'banner');

    bindSwitch(heroTitleToggle, 'firefly-show-hero-title', 'hide-hero-title', true);
    bindSwitch(wavesToggle, 'firefly-show-waves', 'hide-waves', true);
    bindSwitch(fadeToggle, 'firefly-show-fade', 'hide-fade', true);

    document.querySelectorAll('[data-typing-lines]').forEach(function (typingEl) {
        var lines;
        try {
            lines = JSON.parse(typingEl.dataset.typingLines || '[]');
        } catch (error) {
            lines = [];
        }

        lines = lines.filter(function (line) {
            return typeof line === 'string' && line.trim() !== '';
        });
        if (!lines.length) return;

        var lineIndex = 0;
        var charIndex = 0;
        var deleting = false;
        var typeSpeed = 115;
        var deleteSpeed = 58;
        var stayDelay = lines.length > 1 ? 1900 : 0;
        typingEl.textContent = '';

        function tick() {
            var current = Array.from(lines[lineIndex]);
            if (!deleting) {
                charIndex += 1;
                typingEl.textContent = current.slice(0, charIndex).join('');
                if (charIndex >= current.length) {
                    if (lines.length === 1) return;
                    deleting = true;
                    setTimeout(tick, stayDelay);
                    return;
                }
                setTimeout(tick, typeSpeed);
                return;
            }

            charIndex -= 1;
            typingEl.textContent = current.slice(0, Math.max(charIndex, 0)).join('');
            if (charIndex <= 0) {
                deleting = false;
                lineIndex = (lineIndex + 1) % lines.length;
                setTimeout(tick, 360);
                return;
            }
            setTimeout(tick, deleteSpeed);
        }

        setTimeout(tick, 320);
    });

    if (postList) {
        var storedLayout = localStorage.getItem('firefly-post-layout') || 'list';
        var buttons = document.querySelectorAll('[data-layout]');

        function setLayout(layout) {
            postList.classList.toggle('grid', layout === 'grid');
            localStorage.setItem('firefly-post-layout', layout);
            document.querySelectorAll('[data-layout], [data-panel-layout]').forEach(function (button) {
                var value = button.dataset.layout || button.dataset.panelLayout;
                button.classList.toggle('active', value === layout);
            });
        }

        document.querySelectorAll('[data-layout], [data-panel-layout]').forEach(function (button) {
            button.addEventListener('click', function () {
                setLayout(button.dataset.layout || button.dataset.panelLayout || 'list');
            });
        });

        setLayout(storedLayout);
    }

    if (backTop) {
        function updateBackTop() {
            backTop.classList.toggle('show', window.scrollY > 500);
        }

        backTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
        window.addEventListener('scroll', updateBackTop, { passive: true });
        updateBackTop();
    }

    document.querySelectorAll('[data-calendar-widget]').forEach(function (calendar) {
        var title = calendar.querySelector('[data-calendar-title]');
        var grid = calendar.querySelector('[data-calendar-grid]');
        var prev = calendar.querySelector('[data-calendar-prev]');
        var next = calendar.querySelector('[data-calendar-next]');
        if (!title || !grid || !prev || !next) return;

        var today = new Date();
        var viewDate = new Date(today.getFullYear(), today.getMonth(), 1);
        var weekdays = ['日', '一', '二', '三', '四', '五', '六'];

        function renderCalendar() {
            var year = viewDate.getFullYear();
            var month = viewDate.getMonth();
            var firstWeekday = new Date(year, month, 1).getDay();
            var days = new Date(year, month + 1, 0).getDate();
            title.textContent = year + '年' + (month + 1) + '月';
            grid.innerHTML = '';

            weekdays.forEach(function (day) {
                var node = document.createElement('span');
                node.className = 'muted';
                node.textContent = day;
                grid.appendChild(node);
            });

            for (var i = 0; i < firstWeekday; i += 1) {
                grid.appendChild(document.createElement('span'));
            }

            for (var dayNum = 1; dayNum <= days; dayNum += 1) {
                var dayNode = document.createElement('span');
                if (
                    year === today.getFullYear() &&
                    month === today.getMonth() &&
                    dayNum === today.getDate()
                ) {
                    dayNode.className = 'today';
                }
                dayNode.textContent = dayNum;
                grid.appendChild(dayNode);
            }
        }

        prev.addEventListener('click', function () {
            viewDate.setMonth(viewDate.getMonth() - 1);
            renderCalendar();
        });

        next.addEventListener('click', function () {
            viewDate.setMonth(viewDate.getMonth() + 1);
            renderCalendar();
        });

        renderCalendar();
    });

    var sakuraLayer = document.getElementById('sakura-layer');
    function startSakura() {
        if (!sakuraLayer || sakuraTimer) return;
        sakuraTimer = setInterval(function () {
            var petal = document.createElement('span');
            var left = Math.random() * 100;
            var duration = 6 + Math.random() * 6;
            var drift = (Math.random() * 160 - 80) + 'px';
            petal.className = 'sakura';
            petal.style.left = left + 'vw';
            petal.style.animationDuration = duration + 's';
            petal.style.setProperty('--drift', drift);
            sakuraLayer.appendChild(petal);
            setTimeout(function () {
                petal.remove();
            }, duration * 1000 + 200);
        }, 900);
    }

    function stopSakura() {
        if (sakuraTimer) clearInterval(sakuraTimer);
        sakuraTimer = null;
        if (sakuraLayer) sakuraLayer.innerHTML = '';
    }

    if (sakuraToggle) {
        var sakuraEnabled = localStorage.getItem('firefly-sakura-enabled') === 'true';
        sakuraToggle.checked = sakuraEnabled;
        sakuraEnabled ? startSakura() : stopSakura();
        sakuraToggle.addEventListener('change', function () {
            localStorage.setItem('firefly-sakura-enabled', sakuraToggle.checked ? 'true' : 'false');
            sakuraToggle.checked ? startSakura() : stopSakura();
        });
    } else if (sakuraLayer) {
        startSakura();
    }

    function initMetingPlayers() {
        var players = document.querySelectorAll('[data-meting-player]');
        if (!players.length) return;
        var allStates = [];
        var sharedMusic = window.__fireflyMetingShared || {
            playlist: [],
            index: 0,
            mode: localStorage.getItem('firefly-music-mode') || 'list'
        };
        window.__fireflyMetingShared = sharedMusic;

        var audio = window.__fireflyMetingAudio;
        if (!audio) {
            audio = document.createElement('audio');
            audio.preload = 'none';
            audio.crossOrigin = 'anonymous';
            audio.style.display = 'none';
            document.body.appendChild(audio);
            window.__fireflyMetingAudio = audio;
        }

        players.forEach(function (player) {
            if (player.dataset.ready === 'true') return;
            player.dataset.ready = 'true';

            var state = {
                playlist: [],
                index: 0,
                playing: false,
                owner: false,
                mode: sharedMusic.mode,
                renderSong: null,
                renderPlaylist: null,
                updateModeButton: null,
                setPlaying: null
            };
            allStates.push(state);

            var coverBox = player.querySelector('.music-cover');
            var cover = player.querySelector('.music-cover-img');
            var title = player.querySelector('.music-title');
            var artist = player.querySelector('.music-artist');
            var progress = player.querySelector('.music-progress-bar');
            var modeButton = player.querySelector('.music-mode');
            var prev = player.querySelector('.music-prev');
            var play = player.querySelector('.music-play');
            var next = player.querySelector('.music-next');
            var listToggle = player.querySelector('.music-controls .music-list-toggle');
            var lyricsToggle = player.querySelector('.music-lyrics-toggle');
            var list = player.querySelector('.music-playlist');
            var lyrics = player.querySelector('.music-lyrics');

            function apiUrl(template) {
                return template
                    .replace(':server', encodeURIComponent(player.dataset.server || 'netease'))
                    .replace(':type', encodeURIComponent(player.dataset.type || 'playlist'))
                    .replace(':id', encodeURIComponent(player.dataset.id || ''))
                    .replace(':r', Math.random().toString());
            }

            function normalizeSong(item) {
                return {
                    name: item.title || item.name || 'Unknown',
                    artist: item.author || item.artist || 'Unknown',
                    url: item.url || '',
                    pic: item.pic || item.cover || '',
                    lrc: item.lrc || item.lyric || item.lyrics || item.lrc_url || ''
                };
            }

            function parseLrc(raw) {
                if (!raw) return [];
                var rows = [];
                var reg = /\[(\d{2}):(\d{2})(?:\.(\d{2,3}))?\](.*)/;
                raw.split(/\r?\n/).forEach(function (line) {
                    var match = line.match(reg);
                    if (!match) return;
                    var min = parseInt(match[1], 10);
                    var sec = parseInt(match[2], 10);
                    var ms = match[3] ? parseInt(match[3].padEnd(3, '0'), 10) : 0;
                    var text = (match[4] || '').trim();
                    if (!text) return;
                    rows.push({ time: min * 60 + sec + ms / 1000, text: text });
                });
                return rows.sort(function (a, b) {
                    return a.time - b.time;
                });
            }

            function extractLrcText(raw) {
                if (!raw) return '';
                if (typeof raw === 'string') return raw;
                if (raw.lrc && typeof raw.lrc === 'object') return extractLrcText(raw.lrc);
                if (raw.lyric && typeof raw.lyric === 'object') return extractLrcText(raw.lyric);
                if (raw.data && typeof raw.data === 'object') return extractLrcText(raw.data);
                return raw.lrc || raw.lyric || raw.lyrics || raw.text || raw.data || '';
            }

            async function resolveLrc(raw) {
                if (!raw) return '';
                if (typeof raw !== 'string') return extractLrcText(raw);
                if (!/^https?:\/\//i.test(raw)) return raw;
                var res = await fetch(raw);
                if (!res.ok) throw new Error('HTTP ' + res.status);
                var text = await res.text();
                try {
                    return extractLrcText(JSON.parse(text)) || text;
                } catch (error) {
                    return text;
                }
            }

            function renderLyrics(song) {
                if (!lyrics) return;
                lyrics.innerHTML = '';
                var loading = document.createElement('p');
                loading.textContent = '歌词加载中...';
                lyrics.appendChild(loading);

                resolveLrc(song.lrc || '')
                    .then(function (raw) {
                        var rows = parseLrc(raw);
                        lyrics.innerHTML = '';
                        if (!rows.length) {
                            var empty = document.createElement('p');
                            empty.textContent = '暂无歌词';
                            lyrics.appendChild(empty);
                            return;
                        }
                        rows.forEach(function (row) {
                            var line = document.createElement('p');
                            line.textContent = row.text;
                            line.dataset.time = row.time;
                            lyrics.appendChild(line);
                        });
                    })
                    .catch(function () {
                        lyrics.innerHTML = '';
                        var empty = document.createElement('p');
                        empty.textContent = '歌词加载失败';
                        lyrics.appendChild(empty);
                    });
            }

            async function fetchPlaylist() {
                var fallbacks = [];
                try {
                    fallbacks = JSON.parse(player.dataset.fallbackApis || '[]');
                } catch (e) {
                    fallbacks = [];
                }
                var apis = [player.dataset.api || ''].concat(fallbacks).filter(Boolean);
                for (var i = 0; i < apis.length; i++) {
                    try {
                        var res = await fetch(apiUrl(apis[i]));
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        var data = await res.json();
                        if (Array.isArray(data) && data.length) {
                            return data.map(normalizeSong).filter(function (song) {
                                return song.url;
                            });
                        }
                    } catch (e) {
                        console.warn('Meting API failed:', apis[i], e);
                    }
                }
                throw new Error('Meting API unavailable');
            }

            function renderSong(song) {
                if (!song) return;
                state.index = sharedMusic.index;
                title.textContent = song.name;
                artist.textContent = song.artist;
                if (song.pic) {
                    cover.src = song.pic;
                    cover.alt = song.name;
                    coverBox.classList.add('has-cover');
                } else {
                    cover.removeAttribute('src');
                    coverBox.classList.remove('has-cover');
                }
                if (progress) progress.style.width = '0%';
                renderLyrics(song);

                list.querySelectorAll('button').forEach(function (button, idx) {
                    button.classList.toggle('active', idx === state.index);
                });
            }
            state.renderSong = renderSong;

            function renderPlaylist() {
                list.innerHTML = '';
                sharedMusic.playlist.forEach(function (song, idx) {
                    var item = document.createElement('button');
                    item.type = 'button';
                    item.innerHTML = '<strong></strong><span></span>';
                    item.querySelector('strong').textContent = song.name;
                    item.querySelector('span').textContent = song.artist;
                    item.addEventListener('click', function () {
                        load(idx, true);
                    });
                    list.appendChild(item);
                });
            }
            state.renderPlaylist = renderPlaylist;

            function randomIndex() {
                if (sharedMusic.playlist.length <= 1) return sharedMusic.index;
                var nextIndex = sharedMusic.index;
                while (nextIndex === sharedMusic.index) {
                    nextIndex = Math.floor(Math.random() * sharedMusic.playlist.length);
                }
                return nextIndex;
            }

            function setButtonIcon(button, icon) {
                if (!button) return;
                button.innerHTML = '<i data-lucide="' + icon + '"></i>';
                if (window.lucide) window.lucide.createIcons();
            }

            function updateModeButton() {
                if (!modeButton) return;
                state.mode = sharedMusic.mode;
                var labels = {
                    list: { icon: 'repeat', title: '顺序播放' },
                    random: { icon: 'shuffle', title: '随机播放' },
                    single: { icon: 'repeat-1', title: '单曲循环' }
                };
                var current = labels[state.mode] || labels.list;
                setButtonIcon(modeButton, current.icon);
                modeButton.title = current.title;
                modeButton.setAttribute('aria-label', current.title);
                modeButton.dataset.mode = state.mode;
            }
            state.updateModeButton = updateModeButton;

            function updatePlayingButton() {
                setButtonIcon(play, !audio.paused && state.owner ? 'pause' : 'play');
            }
            state.setPlaying = updatePlayingButton;

            function cycleMode() {
                var modes = ['list', 'random', 'single'];
                var current = modes.indexOf(sharedMusic.mode);
                sharedMusic.mode = modes[(current + 1) % modes.length];
                localStorage.setItem('firefly-music-mode', sharedMusic.mode);
                allStates.forEach(function (item) {
                    item.mode = sharedMusic.mode;
                    if (item.updateModeButton) item.updateModeButton();
                });
            }

            function load(index, autoplay) {
                if (!sharedMusic.playlist[index]) return;
                allStates.forEach(function (item) {
                    item.owner = true;
                });
                sharedMusic.index = index;
                var song = sharedMusic.playlist[index];
                if (audio.src !== song.url) {
                    audio.src = song.url;
                    audio.load();
                }
                allStates.forEach(function (item) {
                    item.index = index;
                    if (item.renderSong) item.renderSong(song);
                });
                if (autoplay) {
                    audio.play().then(function () {
                        allStates.forEach(function (item) {
                            item.playing = true;
                            if (item.setPlaying) item.setPlaying();
                        });
                    }).catch(function (e) {
                        console.warn('Music play failed:', e);
                    });
                } else {
                    allStates.forEach(function (item) {
                        item.playing = false;
                        if (item.setPlaying) item.setPlaying();
                    });
                }
            }

            function playNext() {
                if (!sharedMusic.playlist.length) return;
                if (sharedMusic.mode === 'random') {
                    load(randomIndex(), true);
                    return;
                }
                load((sharedMusic.index + 1) % sharedMusic.playlist.length, true);
            }

            function playPrev() {
                if (!sharedMusic.playlist.length) return;
                if (sharedMusic.mode === 'random') {
                    load(randomIndex(), true);
                    return;
                }
                load((sharedMusic.index - 1 + sharedMusic.playlist.length) % sharedMusic.playlist.length, true);
            }

            play.addEventListener('click', function () {
                if (!sharedMusic.playlist.length) return;
                if (!state.owner || !audio.src) {
                    load(sharedMusic.index, true);
                    return;
                }
                if (audio.paused) {
                    audio.play().then(function () {
                        allStates.forEach(function (item) {
                            item.playing = true;
                            if (item.setPlaying) item.setPlaying();
                        });
                    });
                } else {
                    audio.pause();
                    allStates.forEach(function (item) {
                        item.playing = false;
                        if (item.setPlaying) item.setPlaying();
                    });
                }
            });
            if (modeButton) modeButton.addEventListener('click', cycleMode);
            prev.addEventListener('click', playPrev);
            next.addEventListener('click', playNext);
            listToggle.addEventListener('click', function () {
                list.classList.toggle('open');
                if (lyrics) lyrics.classList.remove('open');
            });
            if (lyricsToggle && lyrics) {
                lyricsToggle.addEventListener('click', function () {
                    lyrics.classList.toggle('open');
                    list.classList.remove('open');
                });
            }

            audio.addEventListener('timeupdate', function () {
                if (!audio.duration || !state.owner) return;
                document.querySelectorAll('[data-meting-player] .music-progress-bar').forEach(function (bar) {
                    bar.style.width = ((audio.currentTime / audio.duration) * 100) + '%';
                });
                if (lyrics) {
                    document.querySelectorAll('[data-meting-player] .music-lyrics').forEach(function (lyricsBox) {
                        var active = null;
                        lyricsBox.querySelectorAll('p[data-time]').forEach(function (line) {
                            if (audio.currentTime >= parseFloat(line.dataset.time)) active = line;
                        });
                        if (active && !active.classList.contains('active')) {
                            lyricsBox.querySelectorAll('p').forEach(function (line) {
                                line.classList.remove('active');
                            });
                            active.classList.add('active');
                            lyricsBox.scrollTo({
                                top: Math.max(0, active.offsetTop - lyricsBox.clientHeight / 2 + active.offsetHeight / 2),
                                behavior: 'smooth'
                            });
                        }
                    });
                }
            });
            audio.addEventListener('ended', function () {
                if (!state.owner) return;
                if (sharedMusic.mode === 'single') {
                    audio.currentTime = 0;
                    audio.play();
                    return;
                }
                playNext();
            });
            audio.addEventListener('pause', function () {
                if (state.owner) {
                    allStates.forEach(function (item) {
                        item.playing = false;
                        if (item.setPlaying) item.setPlaying();
                    });
                }
            });

            fetchPlaylist()
                .then(function (playlist) {
                    state.playlist = playlist;
                    if (!sharedMusic.playlist.length) {
                        sharedMusic.playlist = playlist;
                    }
                    updateModeButton();
                    renderPlaylist();
                    if (sharedMusic.playlist.length) {
                        var initialIndex = sharedMusic.playlist[sharedMusic.index] ? sharedMusic.index : 0;
                        sharedMusic.index = initialIndex;
                        state.owner = true;
                        if (!audio.src) {
                            audio.src = sharedMusic.playlist[initialIndex].url;
                            audio.load();
                        }
                        renderSong(sharedMusic.playlist[initialIndex]);
                        updatePlayingButton();
                    }
                })
                .catch(function () {
                    title.textContent = '歌单加载失败';
                    artist.textContent = '请检查 Meting API 或歌单 ID';
                });
        });
    }

    function initBangumiPage() {
        var page = document.querySelector('[data-bangumi-page]');
        if (!page) return;
        var perPage = parseInt(page.dataset.itemsPerPage || '12', 10) || 12;
        var tabButtons = page.querySelectorAll('[data-bangumi-tab]');
        var sections = page.querySelectorAll('[data-bangumi-section]');

        function statusKey(type) {
            return ({ 1: 'wish', 2: 'collect', 3: 'doing', 4: 'on_hold', 5: 'dropped' })[parseInt(type, 10)] || 'unknown';
        }

        function statusLabel(type, subjectType) {
            type = parseInt(type, 10);
            subjectType = parseInt(subjectType, 10);
            if (type === 1) return subjectType === 1 ? '想读' : subjectType === 3 ? '想听' : subjectType === 4 ? '想玩' : '想看';
            if (type === 2) return subjectType === 1 ? '读过' : subjectType === 3 ? '听过' : subjectType === 4 ? '玩过' : '看过';
            if (type === 3) return subjectType === 1 ? '在读' : subjectType === 3 ? '在听' : subjectType === 4 ? '在玩' : '在看';
            if (type === 4) return '搁置';
            if (type === 5) return '抛弃';
            return '未知';
        }

        function filterLabel(status, subjectType) {
            if (status === 'all') return '全部';
            if (status === 'collect') return statusLabel(2, subjectType);
            if (status === 'doing') return statusLabel(3, subjectType);
            if (status === 'wish') return statusLabel(1, subjectType);
            if (status === 'on_hold') return '搁置';
            if (status === 'dropped') return '抛弃';
            return '未知';
        }

        function escapeHtml(text) {
            return String(text == null ? '' : text).replace(/[&<>"']/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
            });
        }

        function subjectName(subject) {
            return subject && (subject.name_cn || subject.name) || '未知条目';
        }

        function subjectCover(subject) {
            var images = subject && subject.images || {};
            return images.medium || images.large || images.common || '';
        }

        function itemTags(item) {
            if (Array.isArray(item.tags) && item.tags.length) return item.tags.slice(0, 6);
            var tags = item.subject && Array.isArray(item.subject.tags) ? item.subject.tags : [];
            return tags.map(function (tag) { return tag.name; }).filter(Boolean).slice(0, 6);
        }

        function renderClientBangumi(categories, dataMap) {
            var panel = page.querySelector('.bangumi-panel') || page;
            var oldEmpty = page.querySelector('.bangumi-empty');
            if (oldEmpty) oldEmpty.remove();
            page.querySelectorAll('.bangumi-tabs, [data-bangumi-section]').forEach(function (node) {
                node.remove();
            });

            var tabs = document.createElement('nav');
            tabs.className = 'bangumi-tabs';
            tabs.setAttribute('aria-label', '番组分类');
            var activeId = '';
            categories.forEach(function (category) {
                var items = dataMap[category.id] || [];
                if (!items.length) return;
                if (!activeId) activeId = category.id;
                var button = document.createElement('button');
                button.type = 'button';
                button.dataset.bangumiTab = category.id;
                button.className = category.id === activeId ? 'active' : '';
                button.innerHTML = escapeHtml(category.name) + '<span>' + items.length + '</span>';
                tabs.appendChild(button);
            });
            if (!activeId) {
                panel.insertAdjacentHTML('beforeend', '<div class="bangumi-empty"><strong>暂无数据</strong><p>该用户公开收藏里没有可显示的条目。</p></div>');
                return;
            }
            panel.appendChild(tabs);

            categories.forEach(function (category) {
                var items = dataMap[category.id] || [];
                if (!items.length) return;
                var counts = { all: items.length };
                items.forEach(function (item) {
                    var key = statusKey(item.type);
                    counts[key] = (counts[key] || 0) + 1;
                });
                var section = document.createElement('section');
                section.className = 'bangumi-section' + (category.id === activeId ? ' active' : '');
                section.dataset.bangumiSection = category.id;

                var filters = document.createElement('div');
                filters.className = 'bangumi-filters';
                ['all', 'collect', 'doing', 'wish', 'on_hold', 'dropped'].forEach(function (status) {
                    if (status !== 'all' && !counts[status]) return;
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.bangumiFilter = status;
                    button.className = status === 'all' ? 'active' : '';
                    button.innerHTML = escapeHtml(filterLabel(status, category.subjectType)) + '<span>' + counts[status] + '</span>';
                    filters.appendChild(button);
                });
                section.appendChild(filters);

                var grid = document.createElement('div');
                grid.className = 'bangumi-grid';
                items.forEach(function (item, index) {
                    var subject = item.subject || {};
                    var name = subjectName(subject);
                    var cover = subjectCover(subject);
                    var year = subject.date ? String(subject.date).slice(0, 4) : '';
                    var tags = itemTags(item);
                    var visibleTags = tags.slice(0, 3);
                    var hiddenCount = Math.max(0, tags.length - visibleTags.length);
                    var itemStatus = statusKey(item.type);
                    var url = subject.id ? 'https://bgm.tv/subject/' + encodeURIComponent(subject.id) : '#';
                    var tagHtml = visibleTags.map(function (tag) { return '<b>' + escapeHtml(tag) + '</b>'; }).join('');
                    if (hiddenCount > 0) tagHtml += '<b>+' + hiddenCount + '</b>';
                    var card = document.createElement('a');
                    card.className = 'bangumi-card' + (index >= perPage ? ' paged-hidden' : '');
                    card.href = url;
                    card.target = '_blank';
                    card.rel = 'noopener noreferrer nofollow';
                    card.dataset.bangumiItem = '';
                    card.dataset.status = itemStatus;
                    card.innerHTML =
                        '<div class="bangumi-cover">' +
                        (cover ? '<img src="' + escapeHtml(cover) + '" alt="' + escapeHtml(name) + '" loading="lazy">' : '<span class="bangumi-no-cover">BOOK</span>') +
                        '<span class="bangumi-status status-' + escapeHtml(itemStatus) + '">' + escapeHtml(statusLabel(item.type, category.subjectType)) + '</span>' +
                        (subject.score ? '<span class="bangumi-score">★ ' + escapeHtml(subject.score) + '</span>' : '') +
                        '<span class="bangumi-card-mask"></span>' +
                        '<span class="bangumi-info"><strong>' + escapeHtml(name) + '</strong>' +
                        (year ? '<em>' + escapeHtml(year) + '</em>' : '') +
                        (item.comment ? '<small title="' + escapeHtml(item.comment) + '">' + escapeHtml(item.comment) + '</small>' : '') +
                        (tagHtml ? '<span class="bangumi-tag-row">' + tagHtml + '</span>' : '') +
                        '</span></div>';
                    grid.appendChild(card);
                });
                section.appendChild(grid);
                section.insertAdjacentHTML('beforeend', '<div class="bangumi-pagination" data-bangumi-pagination><button type="button" data-page-prev>‹</button><span data-page-label>1 / 1</span><button type="button" data-page-next>›</button></div>');
                panel.appendChild(section);
            });

            tabButtons = page.querySelectorAll('[data-bangumi-tab]');
            sections = page.querySelectorAll('[data-bangumi-section]');
            bindBangumiControls();
        }

        function fetchClientBangumi() {
            var username = page.dataset.bangumiUser || '';
            var api = (page.dataset.bangumiApi || 'https://api.bgm.tv').replace(/\/v0\/?$/, '').replace(/\/$/, '');
            var proxy = page.dataset.bangumiProxy || '';
            var categories = [];
            try {
                categories = JSON.parse(page.dataset.bangumiCategories || '[]');
            } catch (error) {
                categories = [];
            }
            if (!username || !categories.length || !window.fetch) return;
            var loadingTimer = setTimeout(function () {
                var empty = page.querySelector('.bangumi-empty p');
                var stillEmpty = page.querySelector('.bangumi-empty');
                var hasItems = page.querySelector('[data-bangumi-item]');
                if (empty && stillEmpty && !hasItems) {
                    empty.textContent = '番组数据加载超时，请刷新重试或检查 Bangumi API 地址。';
                }
            }, 18000);
            var cacheKey = 'firefly-bangumi:' + username + ':' + api + ':' + categories.map(function (category) {
                return category.id + '-' + category.subjectType;
            }).join(',');
            try {
                var cached = JSON.parse(localStorage.getItem(cacheKey) || 'null');
                if (cached && cached.dataMap) {
                    renderClientBangumi(categories, cached.dataMap);
                }
            } catch (error) {}

            function fetchWithTimeout(url, options, timeout) {
                if (!window.AbortController) {
                    return Promise.race([
                        fetch(url, options),
                        new Promise(function (_, reject) {
                            setTimeout(function () { reject(new Error('请求超时')); }, timeout);
                        })
                    ]);
                }
                var controller = new AbortController();
                var timer = setTimeout(function () { controller.abort(); }, timeout);
                options = options || {};
                options.signal = controller.signal;
                return fetch(url, options).then(function (response) {
                    clearTimeout(timer);
                    return response;
                }, function (error) {
                    clearTimeout(timer);
                    if (error && error.name === 'AbortError') throw new Error('请求超时');
                    throw error;
                });
            }

            function fetchCategory(category) {
                function fetchDirect() {
                    var url = api + '/v0/users/' + encodeURIComponent(username) + '/collections?subject_type=' + encodeURIComponent(category.subjectType) + '&limit=50&offset=0';
                    return fetchWithTimeout(url, { headers: { 'Accept': 'application/json' } }, 12000)
                        .then(function (response) {
                            if (!response.ok) throw new Error('HTTP ' + response.status);
                            return response.json();
                        })
                        .then(function (json) {
                            return [category.id, Array.isArray(json.data) ? json.data : []];
                        });
                }

                if (proxy) {
                    var separator = proxy.indexOf('?') === -1 ? '?' : '&';
                    var proxyUrl = proxy + separator + 'user=' + encodeURIComponent(username) + '&category=' + encodeURIComponent(category.id);
                    return fetchWithTimeout(proxyUrl, { headers: { 'Accept': 'application/json' } }, 15000)
                        .then(function (response) {
                            if (!response.ok) throw new Error('HTTP ' + response.status);
                            return response.json();
                        })
                        .then(function (json) {
                            if (!json || json.success === false) throw new Error(json && json.message ? json.message : '代理请求失败');
                            return [category.id, Array.isArray(json.items) ? json.items : []];
                        })
                        .catch(function () {
                            return fetchDirect();
                        });
                }

                return fetchDirect();
            }

            Promise.all(categories.map(function (category) {
                return fetchCategory(category).catch(function (error) {
                    return [category.id, [], error && error.message ? error.message : '请求失败'];
                });
            })).then(function (entries) {
                var dataMap = {};
                var errors = [];
                entries.forEach(function (entry) {
                    dataMap[entry[0]] = entry[1];
                    if (entry[2]) errors.push(entry[2]);
                });
                try {
                    localStorage.setItem(cacheKey, JSON.stringify({ time: Date.now(), dataMap: dataMap }));
                } catch (error) {}
                clearTimeout(loadingTimer);
                renderClientBangumi(categories, dataMap);
                var hasAnyData = false;
                categories.forEach(function (category) {
                    if (dataMap[category.id] && dataMap[category.id].length) hasAnyData = true;
                });
                if (errors.length && !hasAnyData) {
                    throw new Error(errors[0]);
                }
            }).catch(function (error) {
                clearTimeout(loadingTimer);
                var empty = page.querySelector('.bangumi-empty p');
                if (empty) empty.textContent = '番组数据加载失败：' + error.message + '。请稍后刷新，或在后台开启服务端缓存。';
            });
        }

        function visibleItems(section) {
            return Array.prototype.slice.call(section.querySelectorAll('[data-bangumi-item]')).filter(function (item) {
                return !item.classList.contains('is-hidden');
            });
        }

        function updatePagination(section) {
            var items = visibleItems(section);
            var current = parseInt(section.dataset.page || '1', 10) || 1;
            var totalPages = Math.max(1, Math.ceil(items.length / perPage));
            current = Math.min(Math.max(current, 1), totalPages);
            section.dataset.page = current;

            items.forEach(function (item, index) {
                item.classList.toggle('paged-hidden', index < (current - 1) * perPage || index >= current * perPage);
            });

            var pagination = section.querySelector('[data-bangumi-pagination]');
            if (!pagination) return;
            pagination.hidden = totalPages <= 1;
            var label = pagination.querySelector('[data-page-label]');
            var prev = pagination.querySelector('[data-page-prev]');
            var next = pagination.querySelector('[data-page-next]');
            if (label) label.textContent = current + ' / ' + totalPages;
            if (prev) prev.disabled = current <= 1;
            if (next) next.disabled = current >= totalPages;
        }

        function activateSection(id, syncHash) {
            if (!id) return;
            var matched = false;
            tabButtons.forEach(function (button) {
                var active = button.dataset.bangumiTab === id;
                button.classList.toggle('active', active);
                if (active) matched = true;
            });
            if (!matched) return;
            sections.forEach(function (section) {
                var active = section.dataset.bangumiSection === id;
                section.classList.toggle('active', active);
                if (active) updatePagination(section);
            });
            if (syncHash) {
                var nextHash = '#' + encodeURIComponent(id);
                if (window.location.hash !== nextHash) window.history.replaceState(null, '', nextHash);
            }
        }

        function bindBangumiControls() {
        tabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                activateSection(button.dataset.bangumiTab, true);
            });
        });

        sections.forEach(function (section) {
            section.dataset.page = '1';
            section.querySelectorAll('[data-bangumi-filter]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var filter = button.dataset.bangumiFilter || 'all';
                    section.querySelectorAll('[data-bangumi-filter]').forEach(function (filterButton) {
                        filterButton.classList.toggle('active', filterButton === button);
                    });
                    section.querySelectorAll('[data-bangumi-item]').forEach(function (item) {
                        item.classList.toggle('is-hidden', filter !== 'all' && item.dataset.status !== filter);
                    });
                    section.dataset.page = '1';
                    updatePagination(section);
                });
            });

            var pagination = section.querySelector('[data-bangumi-pagination]');
            if (pagination) {
                var prev = pagination.querySelector('[data-page-prev]');
                var next = pagination.querySelector('[data-page-next]');
                if (prev) {
                    prev.addEventListener('click', function () {
                        section.dataset.page = String((parseInt(section.dataset.page || '1', 10) || 1) - 1);
                        updatePagination(section);
                    });
                }
                if (next) {
                    next.addEventListener('click', function () {
                        section.dataset.page = String((parseInt(section.dataset.page || '1', 10) || 1) + 1);
                        updatePagination(section);
                    });
                }
            }
            updatePagination(section);
        });

        var hash = window.location.hash.replace(/^#/, '');
        if (hash) {
            try {
                activateSection(decodeURIComponent(hash), false);
            } catch (error) {
                activateSection(hash, false);
            }
        }
        }

        if (!tabButtons.length || !sections.length) {
            fetchClientBangumi();
            return;
        }
        bindBangumiControls();
    }

    function initGalleryPage() {
        var galleryList = document.querySelector('[data-gallery-list]');
        if (galleryList) {
            var selectedTag = 'all';
            var input = galleryList.querySelector('[data-gallery-search]');
            var cards = Array.prototype.slice.call(galleryList.querySelectorAll('.gallery-album-card'));
            var empty = galleryList.querySelector('.gallery-no-results');

            function applyFilters() {
                var query = input ? input.value.trim().toLowerCase() : '';
                var visible = 0;
                cards.forEach(function (card) {
                    var tags = (card.dataset.tags || '').split(',').filter(Boolean);
                    var text = (card.dataset.searchText || '').toLowerCase();
                    var tagMatch = selectedTag === 'all' || tags.indexOf(selectedTag) !== -1;
                    var searchMatch = !query || text.indexOf(query) !== -1;
                    var show = tagMatch && searchMatch;
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                if (empty) empty.hidden = visible > 0;
            }

            galleryList.querySelectorAll('[data-gallery-tag]').forEach(function (button) {
                button.addEventListener('click', function () {
                    selectedTag = button.dataset.galleryTag || 'all';
                    galleryList.querySelectorAll('[data-gallery-tag]').forEach(function (item) {
                        item.classList.toggle('active', item === button);
                    });
                    applyFilters();
                });
            });

            if (input) input.addEventListener('input', applyFilters);
            applyFilters();
        }

        var detail = document.querySelector('[data-gallery-detail]');
        if (!detail) return;

        var albumId = detail.dataset.albumId || 'album';
        var password = detail.dataset.password || '';
        var storageKey = 'firefly-gallery-unlocked-' + albumId;
        var lock = detail.querySelector('[data-gallery-lock]');
        var photosWrap = detail.querySelector('[data-gallery-photos]');
        var passwordInput = detail.querySelector('[data-gallery-password-input]');
        var unlockButton = detail.querySelector('[data-gallery-unlock]');
        var lockMessage = detail.querySelector('[data-gallery-lock-message]');

        function unlock() {
            if (lock) lock.style.display = 'none';
            if (photosWrap) photosWrap.classList.remove('is-locked');
            try {
                window.sessionStorage.setItem(storageKey, '1');
            } catch (error) {}
        }

        if (password) {
            try {
                if (window.sessionStorage.getItem(storageKey) === '1') unlock();
            } catch (error) {}
            if (unlockButton) {
                unlockButton.addEventListener('click', function () {
                    if (passwordInput && passwordInput.value === password) {
                        unlock();
                    } else if (lockMessage) {
                        lockMessage.textContent = '密码不正确。';
                    }
                });
            }
            if (passwordInput) {
                passwordInput.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter' && unlockButton) unlockButton.click();
                });
            }
        }

        var photoLinks = Array.prototype.slice.call(detail.querySelectorAll('[data-gallery-photo]'));
        if (!photoLinks.length) return;

        var lightbox = document.createElement('div');
        lightbox.className = 'gallery-lightbox';
        lightbox.innerHTML = '<button type="button" class="gallery-lightbox-close" aria-label="关闭">×</button><button type="button" class="gallery-lightbox-prev" aria-label="上一张">‹</button><img alt=""><button type="button" class="gallery-lightbox-next" aria-label="下一张">›</button>';
        document.body.appendChild(lightbox);

        var lightboxImg = lightbox.querySelector('img');
        var closeButton = lightbox.querySelector('.gallery-lightbox-close');
        var prevButton = lightbox.querySelector('.gallery-lightbox-prev');
        var nextButton = lightbox.querySelector('.gallery-lightbox-next');
        var currentIndex = 0;

        function showPhoto(index) {
            currentIndex = (index + photoLinks.length) % photoLinks.length;
            lightboxImg.src = photoLinks[currentIndex].href;
            lightbox.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closePhoto() {
            lightbox.classList.remove('open');
            document.body.style.overflow = '';
        }

        photoLinks.forEach(function (link, index) {
            link.addEventListener('click', function (event) {
                event.preventDefault();
                showPhoto(index);
            });
        });

        if (closeButton) closeButton.addEventListener('click', closePhoto);
        if (prevButton) prevButton.addEventListener('click', function () { showPhoto(currentIndex - 1); });
        if (nextButton) nextButton.addEventListener('click', function () { showPhoto(currentIndex + 1); });
        lightbox.addEventListener('click', function (event) {
            if (event.target === lightbox) closePhoto();
        });
        document.addEventListener('keydown', function (event) {
            if (!lightbox.classList.contains('open')) return;
            if (event.key === 'Escape') closePhoto();
            if (event.key === 'ArrowLeft') showPhoto(currentIndex - 1);
            if (event.key === 'ArrowRight') showPhoto(currentIndex + 1);
        });
    }

    function initTimelineComposeImage() {
        var input = document.querySelector('[data-timeline-image-file]');
        if (!input) return;
        var form = input.closest('form');
        var status = document.querySelector('[data-timeline-image-status]');
        var clear = document.querySelector('[data-timeline-image-clear]');
        var selectedFiles = [];

        function syncInputFiles() {
            if (typeof DataTransfer === 'undefined') return;
            var transfer = new DataTransfer();
            selectedFiles.forEach(function (file) {
                transfer.items.add(file);
            });
            input.files = transfer.files;
        }

        function updateStatus() {
            if (selectedFiles.length) {
                if (status) {
                    status.textContent = selectedFiles.length === 1
                        ? '已选择图片：' + selectedFiles[0].name
                        : '已选择 ' + selectedFiles.length + ' 张图片：' + selectedFiles.map(function (file) { return file.name; }).join('、');
                    status.classList.add('has-file');
                }
                if (clear) clear.hidden = false;
            } else {
                if (status) {
                    status.textContent = '未选择图片';
                    status.classList.remove('has-file');
                }
                if (clear) clear.hidden = true;
            }
        }

        input.addEventListener('change', function () {
            var files = input.files ? Array.prototype.slice.call(input.files) : [];
            files.forEach(function (file) {
                var exists = selectedFiles.some(function (selected) {
                    return selected.name === file.name && selected.size === file.size && selected.lastModified === file.lastModified;
                });
                if (!exists) selectedFiles.push(file);
            });
            syncInputFiles();
            updateStatus();
        });
        if (clear) {
            clear.addEventListener('click', function () {
                selectedFiles = [];
                input.value = '';
                updateStatus();
            });
        }
        if (form) {
            form.addEventListener('submit', syncInputFiles);
        }
        updateStatus();
    }

    initMetingPlayers();
    initBangumiPage();
    initGalleryPage();
    initTimelineComposeImage();
    if (window.lucide) window.lucide.createIcons();
})();
