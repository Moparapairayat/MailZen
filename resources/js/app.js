import './bootstrap';
import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import '@hotwired/turbo';

Alpine.plugin(collapse);
window.Alpine = Alpine;

if (window.Turbo && window.Turbo.session) {
    window.Turbo.session.drive = true;
}

if (window.Turbo && typeof window.Turbo.setProgressBarDelay === 'function') {
    window.Turbo.setProgressBarDelay(0);
}

Alpine.data('templateImportModal', (opts = {}) => ({
    importOpen: false,
    canImport: !!opts.canImport,
    canAi: !!opts.canAi,
    tab: opts.initialTab || 'templates',
    loading: false,
    previewLoading: false,
    importing: false,
    error: '',
    gallery: [],
    categories: [],
    activeCategory: 'all',
    selected: null,
    preview: null,
    previewModalOpen: false,
    previewModalItem: null,
    previewModalLoading: false,
    previewModalData: null,
    ai: {
        provider: 'chatgpt',
        model: '',
        prompt: '',
        loading: false,
        error: '',
    },

    init() {
        if (this.$watch) {
            this.$watch('ai.provider', () => {
                const allowed = this.aiModelsForProvider();
                if (this.ai.model && !allowed.includes(this.ai.model)) {
                    this.ai.model = '';
                }
            });
        }
    },

    aiModelsForProvider() {
        const provider = (this.ai && typeof this.ai.provider === 'string') ? this.ai.provider : 'chatgpt';

        if (provider === 'gemini') {
            return ['gemini-2.0-flash', 'gemini-2.0-pro', 'gemini-1.5-flash-latest', 'gemini-1.5-pro-latest', 'gemini-1.0-pro-latest'];
        }

        if (provider === 'claude') {
            return ['claude-3-5-sonnet-20241022', 'claude-3-5-haiku-20241022', 'claude-3-opus-20240229'];
        }

        return ['gpt-5', 'gpt-5-mini', 'gpt-5.2', 'gpt-5-nano', 'gpt-4.1'];
    },

    open() {
        this.importOpen = true;
        this.error = '';
        this.selected = null;
        this.preview = null;
        this.activeCategory = 'all';
        this.previewModalOpen = false;
        this.previewModalItem = null;
        this.previewModalData = null;
        this.ai.error = '';

        if (this.tab !== 'ai' && !this.canImport) {
            this.tab = 'ai';
        }

        if (this.tab !== 'ai' && this.canImport) {
            this.loadGallery();
        }
    },

    close() {
        this.importOpen = false;
        this.error = '';
    },

    setTab(tab) {
        if (tab === 'templates' && !this.canImport) {
            this.tab = 'ai';
            return;
        }
        if (tab === 'ai' && !this.canAi) {
            return;
        }

        this.tab = tab;
        this.error = '';
        this.selected = null;
        this.preview = null;
        this.activeCategory = 'all';
        this.ai.error = '';

        if (this.tab !== 'ai' && this.canImport) {
            this.loadGallery();
        }
    },

    setCategory(categoryId) {
        this.activeCategory = categoryId;
    },

    get filteredGallery() {
        if (this.activeCategory === 'all') {
            return this.gallery;
        }
        return this.gallery.filter(item => item.category === this.activeCategory);
    },

    get visibleCategories() {
        const usedCategories = new Set(this.gallery.map(item => item.category || 'other'));
        return this.categories.filter(cat => cat.id === 'all' || usedCategories.has(cat.id));
    },

    loadGallery() {
        if (!this.canImport || !opts.galleryUrl) {
            this.gallery = [];
            return;
        }

        this.loading = true;
        this.error = '';

        const tabParam = 'templates';
        const builderParam = opts.builder || 'grapesjs';
        const url = (opts.galleryUrl || '') + '?tab=' + encodeURIComponent(tabParam) + '&builder=' + encodeURIComponent(builderParam);
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.ok ? r.json() : Promise.reject(r))
            .then((data) => {
                this.gallery = Array.isArray(data.templates) ? data.templates : [];
                this.categories = Array.isArray(data.categories) ? data.categories : [];
            })
            .catch(() => {
                this.error = 'Failed to load templates.';
            })
            .finally(() => {
                this.loading = false;
            });
    },

    openPreviewModal(item) {
        if (!item) return;
        this.previewModalItem = item;
        this.previewModalOpen = true;
        this.previewModalLoading = true;
        this.previewModalData = null;

        const url = (item && typeof item.content_url === 'string' && item.content_url)
            ? item.content_url
            : ((opts.contentUrlBase || '') + '/' + item.id + '/content');

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.ok ? r.json() : Promise.reject(r))
            .then((data) => {
                this.previewModalData = data;
            })
            .catch(() => {
                this.error = 'Failed to load template preview.';
            })
            .finally(() => {
                this.previewModalLoading = false;
            });
    },

    closePreviewModal() {
        this.previewModalOpen = false;
        this.previewModalItem = null;
        this.previewModalData = null;
    },

    insertFromPreviewModal() {
        if (!this.previewModalData) return;
        this.preview = this.previewModalData;
        this.closePreviewModal();
        this.doImport();
    },

    insertTemplate(item) {
        if (!item) return;
        this.previewLoading = true;
        this.error = '';

        const url = (item && typeof item.content_url === 'string' && item.content_url)
            ? item.content_url
            : ((opts.contentUrlBase || '') + '/' + item.id + '/content');

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.ok ? r.json() : Promise.reject(r))
            .then((data) => {
                this.preview = data;
                this.doImport();
            })
            .catch(() => {
                this.error = 'Failed to load template.';
            })
            .finally(() => {
                this.previewLoading = false;
            });
    },

    select(item) {
        if (!this.canImport || !opts.contentUrlBase) {
            return;
        }

        this.selected = item;
        this.previewLoading = true;
        this.error = '';

        const url = (item && typeof item.content_url === 'string' && item.content_url)
            ? item.content_url
            : ((opts.contentUrlBase || '') + '/' + item.id + '/content');

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.ok ? r.json() : Promise.reject(r))
            .then((data) => {
                this.preview = data;
            })
            .catch(() => {
                this.error = 'Failed to load template preview.';
            })
            .finally(() => {
                this.previewLoading = false;
            });
    },

    doImport() {
        if (!this.preview) {
            return;
        }
        this.importing = true;
        this.error = '';

        const builder = this.preview.builder || null;

        const originInput = document.querySelector('input[name="settings[origin]"]');
        if (originInput) {
            originInput.value = this.tab === 'ai' ? 'ai' : '';
        }

        if (opts.builder === 'unlayer' && builder === 'unlayer' && this.preview.builder_data) {
            const waitForUnlayer = (timeoutMs = 20000) => new Promise((resolve, reject) => {
                const start = Date.now();
                const tick = () => {
                    if (window.__mailpurseUnlayerReady === true) {
                        resolve();
                        return;
                    }
                    if (Date.now() - start >= timeoutMs) {
                        reject(new Error('Unlayer editor is not ready yet.'));
                        return;
                    }
                    setTimeout(tick, 75);
                };
                tick();
            });

            Promise.resolve()
                .then(() => ensureUnlayerScriptLoaded())
                .then(() => {
                    try {
                        setupUnlayerEditors();
                    } catch (e) {
                    }
                })
                .then(() => {
                    const rawDesign = this.preview.builder_data;
                    const unwrapped = (window.Alpine && typeof window.Alpine.raw === 'function')
                        ? window.Alpine.raw(rawDesign)
                        : rawDesign;

                    let design = unwrapped;
                    if (typeof design === 'string') {
                        const trimmed = design.trim();
                        if (trimmed) {
                            try {
                                design = JSON.parse(trimmed);
                            } catch (e) {
                                throw new Error('Template design is not valid JSON.');
                            }
                        }
                    }

                    if (!design || typeof design !== 'object') {
                        throw new Error('Template design is invalid.');
                    }

                    if (!design.body && (design.rows || design.values)) {
                        design = { counters: {}, body: design };
                    }

                    if (!design.body || typeof design.body !== 'object') {
                        throw new Error('Template design is missing body.');
                    }

                    try {
                        if (typeof structuredClone === 'function') {
                            design = structuredClone(design);
                        } else {
                            design = JSON.parse(JSON.stringify(design));
                        }
                    } catch (e) {
                        try {
                            design = JSON.parse(JSON.stringify(design));
                        } catch (e2) {
                            const msg = (e2 && e2.message) ? e2.message : ((e && e.message) ? e.message : 'Unknown error');
                            throw new Error('Template design could not be cloned: ' + msg);
                        }
                    }

                    if (window.__mailpurseUnlayerReady === true && window.unlayer && typeof window.unlayer.loadDesign === 'function') {
                        window.unlayer.loadDesign(design);
                        this.importOpen = false;
                        return;
                    }

                    window.__mailpurseUnlayerPendingDesign = design;
                    return waitForUnlayer().then(() => {
                        if (window.unlayer && typeof window.unlayer.loadDesign === 'function') {
                            window.unlayer.loadDesign(design);
                        }
                        this.importOpen = false;
                    });
                })
                .catch((e) => {
                    const msg = (e && e.message) ? e.message : null;
                    this.error = msg ? ('Failed to import this template: ' + msg) : 'Failed to import this template.';
                })
                .finally(() => {
                    this.importing = false;
                });
            return;
        }

        if (opts.builder === 'grapesjs' && builder === 'grapesjs' && window.__mailpurseGrapesEditor) {
            try {
                const editor = window.__mailpurseGrapesEditor;
                const builderData = this.preview.builder_data || null;
                const components = builderData && builderData.components ? builderData.components : null;
                const styles = builderData && builderData.styles ? builderData.styles : null;
                const html = this.preview.html_content || null;

                if (components) {
                    editor.setComponents(components);
                } else if (html) {
                    editor.setComponents(html);
                }
                if (styles) {
                    editor.setStyle(styles);
                }

                this.importOpen = false;
            } catch (e) {
                this.error = 'Failed to import this template.';
            } finally {
                this.importing = false;
            }
            return;
        }

        this.importing = false;
        this.error = 'This template is not compatible with the current builder.';
    },

    importSelected() {
        this.doImport();
    },

    aiGenerate() {
        this.ai.loading = true;
        this.ai.error = '';
        this.error = '';

        fetch(opts.aiUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': opts.csrfToken,
            },
            body: JSON.stringify({
                provider: this.ai.provider,
                model: this.ai.model || null,
                prompt: this.ai.prompt,
                builder: opts.builder || 'grapesjs',
            }),
        })
            .then(async (r) => {
                if (!r.ok) {
                    const payload = await r.json().catch(() => null);
                    const message = payload && payload.message ? payload.message : 'Failed to generate template.';
                    throw new Error(message);
                }
                return r.json();
            })
            .then((data) => {
                this.preview = data;
            })
            .catch((e) => {
                this.ai.error = (e && e.message) ? e.message : 'Failed to generate template.';
            })
            .finally(() => {
                this.ai.loading = false;
            });
    },
}));

Alpine.data('headerSearch', (config = {}) => ({
    query: config.initialQuery || '',
    open: false,
    loading: false,
    items: [],
    activeIndex: -1,
    debounceMs: config.debounceMs || 200,
    minChars: config.minChars || 2,
    suggestUrl: config.suggestUrl || '',
    searchUrl: config.searchUrl || '',
    variant: config.variant || 'admin',
    _debounceTimer: null,
    _abortController: null,

    get hasItems() {
        return Array.isArray(this.items) && this.items.length > 0;
    },

    get searchUrlWithQuery() {
        if (!this.searchUrl) {
            return '#';
        }
        const q = (this.query || '').trim();
        return q ? `${this.searchUrl}?q=${encodeURIComponent(q)}` : this.searchUrl;
    },

    get dropdownBgClass() {
        return this.variant === 'customer' ? 'bg-admin-sidebar border-gray-500' : 'bg-admin-sidebar border-gray-500';
    },

    get itemHoverClass() {
        return this.variant === 'customer' ? 'hover:bg-white/5' : 'hover:bg-white/5';
    },

    reset() {
        this.items = [];
        this.activeIndex = -1;
        this.loading = false;
    },

    close() {
        this.open = false;
        this.activeIndex = -1;
    },

    onFocus() {
        const q = (this.query || '').trim();
        if (q.length >= this.minChars) {
            if (this.hasItems) {
                this.open = true;
            } else {
                this.fetchSuggestions();
            }
        }
    },

    onInput() {
        clearTimeout(this._debounceTimer);
        this._debounceTimer = setTimeout(() => {
            this.fetchSuggestions();
        }, this.debounceMs);
    },

    async fetchSuggestions() {
        const q = (this.query || '').trim();

        if (!this.suggestUrl || q.length < this.minChars) {
            this.reset();
            this.open = false;
            return;
        }

        if (this._abortController) {
            this._abortController.abort();
        }

        this._abortController = new AbortController();
        this.loading = true;
        this.open = true;

        try {
            const res = await window.axios.get(this.suggestUrl, {
                params: { q },
                signal: this._abortController.signal,
            });
            this.items = res?.data?.items || [];
            this.activeIndex = this.items.length ? 0 : -1;
        } catch (e) {
            if (e?.name !== 'CanceledError' && e?.code !== 'ERR_CANCELED') {
                this.items = [];
                this.activeIndex = -1;
            }
        } finally {
            this.loading = false;
        }
    },

    onKeydown(e) {
        if (!this.open) {
            return;
        }

        if (e.key === 'Escape') {
            e.preventDefault();
            this.close();
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (!this.hasItems) {
                return;
            }
            this.activeIndex = Math.min(this.items.length - 1, this.activeIndex + 1);
            return;
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (!this.hasItems) {
                return;
            }
            this.activeIndex = Math.max(0, this.activeIndex - 1);
            return;
        }

        if (e.key === 'Enter') {
            if (this.activeIndex >= 0 && this.items[this.activeIndex]?.url) {
                e.preventDefault();
                window.location.href = this.items[this.activeIndex].url;
            }
        }
    },

    select(item) {
        if (item?.url) {
            window.location.href = item.url;
        }
    },
}));

Alpine.start();

function escapeHtml(unsafe) {
    if (unsafe === null || unsafe === undefined) {
        return '';
    }
    return String(unsafe)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function getNotificationsUiVariant(feedUrl) {
    if (!feedUrl) {
        return 'admin';
    }
    return feedUrl.includes('/customer/') ? 'customer' : 'admin';
}

function getNotificationsUiTokens(variant) {
    if (variant === 'customer') {
        return {
            emptyTextClass: 'text-gray-500 dark:text-gray-400',
            itemTitleClass: 'text-gray-900 dark:text-gray-100 font-medium',
            itemMessageClass: 'mt-1 text-xs text-gray-600 dark:text-gray-300',
            itemTimeClass: 'mt-1 text-[11px] text-gray-400',
            itemReadBgClass: 'bg-white dark:bg-gray-800',
            itemUnreadBgClass: 'bg-primary-50/70 dark:bg-primary-900/20',
        };
    }

    return {
        emptyTextClass: 'text-admin-text-secondary',
        itemTitleClass: 'text-admin-text-primary font-medium',
        itemMessageClass: 'mt-1 text-xs text-admin-text-secondary',
        itemTimeClass: 'mt-1 text-[11px] text-admin-text-secondary/80',
        itemReadBgClass: 'bg-admin-sidebar',
        itemUnreadBgClass: 'bg-white/5',
    };
}

function renderNotificationsList(listEl, notifications, variant) {
    if (!listEl) {
        return;
    }

    const t = getNotificationsUiTokens(variant);

    if (!Array.isArray(notifications) || notifications.length === 0) {
        listEl.innerHTML = `
            <div class="px-4 py-6 text-center text-sm ${t.emptyTextClass}">
                No notifications yet.
            </div>
        `;
        return;
    }

    listEl.innerHTML = notifications
        .map((n) => {
            const title = escapeHtml(n.title || 'Notification');
            const message = n.message ? `<p class="${t.itemMessageClass}">${escapeHtml(n.message)}</p>` : '';
            const time = escapeHtml(n.created_at_human || '');
            const isRead = !!n.is_read;
            const bgClass = isRead ? t.itemReadBgClass : t.itemUnreadBgClass;

            const url = (n && typeof n === 'object' && n.data && typeof n.data === 'object' && typeof n.data.url === 'string')
                ? n.data.url
                : null;

            const wrapperTagOpen = url
                ? `<a href="${escapeHtml(url)}" class="block px-4 py-3 text-sm ${bgClass}">`
                : `<div class="px-4 py-3 text-sm ${bgClass}">`;
            const wrapperTagClose = url ? '</a>' : '</div>';

            return `
                ${wrapperTagOpen}
                    <p class="${t.itemTitleClass}">${title}</p>
                    ${message}
                    <p class="${t.itemTimeClass}">${time}</p>
                ${wrapperTagClose}
            `;
        })
        .join('');
}

function updateBadge(badgeEl, unreadCount) {
    if (!badgeEl) {
        return;
    }
    const count = Number(unreadCount) || 0;
    if (count <= 0) {
        badgeEl.remove();
        return;
    }
    badgeEl.textContent = count > 9 ? '9+' : String(count);
}

function ensureBadge(rootButtonEl, unreadCount) {
    const count = Number(unreadCount) || 0;
    const existing = rootButtonEl?.querySelector('[data-notifications-badge]') || null;
    if (count <= 0) {
        if (existing) {
            existing.remove();
        }
        return;
    }
    if (existing) {
        existing.textContent = count > 9 ? '9+' : String(count);
        return;
    }

    const span = document.createElement('span');
    span.setAttribute('data-notifications-badge', '');
    span.className = 'absolute -top-0.5 -right-0.5 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-red-500 text-white';
    span.textContent = count > 9 ? '9+' : String(count);
    rootButtonEl.appendChild(span);
}

function updateUnreadLabel(labelEl, unreadCount) {
    if (!labelEl) {
        return;
    }
    const count = Number(unreadCount) || 0;
    labelEl.textContent = count > 0 ? `${count} unread` : 'Up to date';
}

async function refreshNotifications(rootButtonEl, containerEl) {
    const feedUrl = rootButtonEl?.getAttribute('data-notifications-feed-url');
    if (!feedUrl) {
        return;
    }

    const scope = containerEl || document;
    const listEl = scope.querySelector('[data-notifications-list]');
    const labelEl = scope.querySelector('[data-notifications-unread-label]');

    try {
        const res = await window.axios.get(feedUrl);
        const unreadCount = res?.data?.unread_count ?? 0;
        const notifications = res?.data?.notifications ?? [];

        ensureBadge(rootButtonEl, unreadCount);
        updateUnreadLabel(labelEl, unreadCount);

        const variant = getNotificationsUiVariant(feedUrl);
        renderNotificationsList(listEl, notifications, variant);
    } catch (e) {
        // Silent failure: avoid spamming console in production
    }
}

function parseInlineConfirmMessage(code) {
    if (!code || typeof code !== 'string') {
        return '';
    }

    const idx = code.indexOf('confirm(');
    if (idx === -1) {
        return '';
    }

    const slice = code.slice(idx);
    const m = slice.match(/confirm\(\s*(['"])([\s\S]*?)\1\s*\)/);
    if (!m) {
        return '';
    }

    return (m[2] || '').toString();
}

function setupCustomConfirmModal() {
    if (window.__mailpurseCustomConfirmModalInit) {
        return;
    }
    window.__mailpurseCustomConfirmModalInit = true;

    document.addEventListener(
        'submit',
        (e) => {
            const form = e.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (form.dataset && form.dataset.mpSkipConfirm === '1') {
                return;
            }

            const onsubmit = form.getAttribute('onsubmit') || '';
            if (!onsubmit.includes('confirm(')) {
                return;
            }

            const hasConfirmModal = !!document.querySelector('[data-confirm-modal-root]');
            if (!hasConfirmModal) {
                return;
            }

            const message = parseInlineConfirmMessage(onsubmit) || 'This action cannot be undone.';

            const methodInput = form.querySelector('input[name="_method"]');
            const methodOverride = methodInput ? String(methodInput.value || '').toUpperCase() : '';
            const isDelete = methodOverride === 'DELETE';

            window.dispatchEvent(
                new CustomEvent('open-confirm-modal', {
                    detail: {
                        form,
                        title: 'Are you sure?',
                        message,
                        confirmText: isDelete ? 'Delete' : 'Confirm',
                        cancelText: 'Cancel',
                        variant: isDelete ? 'danger' : 'default',
                    },
                })
            );

            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
        },
        true
    );
}

function setupNotificationWidget(rootButtonEl, allWidgets) {
    if (!rootButtonEl || !window.axios) {
        return;
    }

    const containerEl = rootButtonEl.parentElement;
    const markAllReadButton = (containerEl || document).querySelector('[data-notifications-mark-all-read]');
    const markAllReadUrl = rootButtonEl.getAttribute('data-notifications-mark-all-read-url');

    if (markAllReadButton && markAllReadUrl) {
        markAllReadButton.addEventListener('click', async (e) => {
            e.preventDefault();
            try {
                await window.axios.post(markAllReadUrl);
            } finally {
                (allWidgets || []).forEach((btn) => {
                    refreshNotifications(btn, btn.parentElement);
                });
            }
        });
    }

    // Refresh when the dropdown is opened
    rootButtonEl.addEventListener('click', () => {
        refreshNotifications(rootButtonEl, containerEl);
    });
}

function setupNotificationPolling() {
    const rootButtons = document.querySelectorAll('[data-notifications-root]');
    if (!rootButtons.length) {
        return;
    }

    const widgets = Array.from(rootButtons);
    widgets.forEach((btn) => setupNotificationWidget(btn, widgets));

    // Initial fetch + periodic refresh (single interval for all widgets)
    const refreshAll = () => {
        widgets.forEach((btn) => {
            refreshNotifications(btn, btn.parentElement);
        });
    };

    refreshAll();

    if (window.__mailpurseNotificationsIntervalId) {
        clearInterval(window.__mailpurseNotificationsIntervalId);
    }
    window.__mailpurseNotificationsIntervalId = setInterval(refreshAll, 15000);
}

function ensureUnlayerScriptLoaded() {
    if (window.unlayer && typeof window.unlayer.init === 'function') {
        return Promise.resolve();
    }

    if (window.__mailpurseUnlayerEmbedPromise) {
        return window.__mailpurseUnlayerEmbedPromise;
    }

    window.__mailpurseUnlayerEmbedPromise = new Promise((resolve, reject) => {
        try {
            const existing = document.querySelector('script[src="https://editor.unlayer.com/embed.js"]');
            if (existing) {
                const tick = (timeoutMs = 12000) => {
                    const started = Date.now();
                    const wait = () => {
                        if (window.unlayer && typeof window.unlayer.init === 'function') {
                            resolve();
                            return;
                        }
                        if (Date.now() - started >= timeoutMs) {
                            reject(new Error('Unlayer embed.js did not load in time.'));
                            return;
                        }
                        setTimeout(wait, 75);
                    };
                    wait();
                };
                tick();
                return;
            }

            const script = document.createElement('script');
            script.src = 'https://editor.unlayer.com/embed.js';
            script.async = true;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Failed to load Unlayer embed.js.'));
            document.head.appendChild(script);
        } catch (e) {
            reject(e);
        }
    });

    return window.__mailpurseUnlayerEmbedPromise;
}

// Load custom tools from external file
async function loadUnlayerCustomTools() {
    // Simple test code to verify customJS is working
    return `
        console.error('[MailZen Unlayer customJS] SIMPLE TEST - Script loaded at', new Date().toISOString());
        document.body.style.background = 'rgba(255,0,0,0.3)';
        
        // Test if we can register anything
        setTimeout(function() {
            console.log('[MailZen Unlayer customJS] Checking for unlayer...', typeof window.unlayer);
            if (window.unlayer) {
                console.log('[MailZen Unlayer customJS] Unlayer found!');
                console.log('[MailZen Unlayer customJS] Methods available:', Object.getOwnPropertyNames(window.unlayer).filter(n => typeof window.unlayer[n] === 'function'));
            }
        }, 1000);
    `;
}

const MAILPURSE_UNLAYER_CUSTOM_TOOLS_JS = `(function(){
  try {
    var attempts = 0;

    // Force visible proof that customJS is running inside the iframe
    document.body.style.background = 'rgba(255,0,0,0.3)';
    console.error('[MailZen Unlayer customJS] Injected and executing at', new Date().toISOString());

    function escAttr(v){
      return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function escHtml(v){
      return String(v == null ? '' : v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function post(type, payload){
      try {
        if (window.top && window.top !== window) {
          window.top.postMessage({ type: type, payload: payload || null }, '*');
        }
        if (window.parent && window.parent !== window && (!window.top || window.top === window || window.parent !== window.top)) {
          window.parent.postMessage({ type: type, payload: payload || null }, '*');
        }
      } catch (e) {}
    }

    function getUnlayer(){
      try {
        if (typeof unlayer !== 'undefined') return unlayer;
      } catch (e) {}
      try {
        if (window && window.unlayer) return window.unlayer;
      } catch (e) {}
      return null;
    }

    function boot(){
      try {
        post('mailpurse:unlayer:customjs:tick', { attempts: attempts });
        var u = getUnlayer();
        if (!u || typeof u.registerTool !== 'function' || typeof u.createViewer !== 'function') {
          attempts++;
          if (attempts < 160) {
            setTimeout(boot, 50);
          } else {
            post('mailpurse:unlayer:customjs:timeout', { attempts: attempts });
          }
          return;
        }

        if (window.__mailpurseUnlayerToolsRegistered) {
          post('mailpurse:unlayer:customjs:already-registered');
          return;
        }

        post('mailpurse:unlayer:customjs:unlayer-ready');

        function registerFormTool(){
          function exportForm(){
            return '<div style="padding:16px;border:1px solid #e5e7eb;border-radius:12px;">{{fields}}{{gdpr}}{{submit}}</div>';
          }

          try {
            u.registerTool({
              name: 'mp_form',
              label: 'Form',
              icon: 'fa-wpforms',
              supportedDisplayModes: ['email','web','popup'],
              options: {},
              values: {},
              renderer: {
                Viewer: u.createViewer({
                  render: function(){
                    return '<div style="padding:16px;border:1px dashed #cbd5e1;border-radius:12px;font-size:13px;">Form Container</div>';
                  }
                }),
                exporters: {
                  web: exportForm,
                  popup: exportForm,
                  email: exportForm
                },
                head: { css: function(){}, js: function(){} }
              }
            });
          } catch (e) {
            post('mailpurse:unlayer:customjs:error', { tool: 'mp_form', message: String(e && e.message ? e.message : e), stack: e && e.stack ? String(e.stack) : null });
          }
        }

        function registerInputTool(){
          function exportInput(values){
            var fk = values && values.fieldKey ? String(values.fieldKey) : 'email';
            var ph = values && values.placeholder ? String(values.placeholder) : '';
            var req = values && (values.required === true || values.required === 'true' || values.required === 1 || values.required === '1');
            return '<div data-mailpurse-field="' + escAttr(fk) + '" data-mailpurse-placeholder="' + escAttr(ph) + '"' + (req ? ' data-mailpurse-required="1"' : '') + '></div>';
          }

          u.registerTool({
            name: 'mp_input',
            label: 'Input',
            icon: 'fa-i-cursor',
            supportedDisplayModes: ['email','web','popup'],
            options: {
              field: {
                title: 'Field',
                position: 1,
                options: {
                  fieldKey: { label: 'Field', defaultValue: 'email', widget: 'dropdown' },
                  placeholder: { label: 'Placeholder', defaultValue: '', widget: 'text' },
                  required: { label: 'Required', defaultValue: false, widget: 'toggle' }
                }
              }
            },
            values: {},
            renderer: {
              Viewer: u.createViewer({
                render: function(values){
                  var fk = values && values.fieldKey ? String(values.fieldKey) : 'email';
                  return '<div style="padding:10px;border:1px dashed #cbd5e1;border-radius:10px;font-size:13px;">Input: ' + escHtml(fk) + '</div>';
                }
              }),
              exporters: {
                web: exportInput,
                email: exportInput,
                popup: exportInput
              },
              head: { css: function(){}, js: function(){} }
            }
          });
        }

        function registerTextareaTool(){
          function exportTextarea(values){
            var fk = values && values.fieldKey ? String(values.fieldKey) : '';
            var ph = values && values.placeholder ? String(values.placeholder) : '';
            var req = values && (values.required === true || values.required === 'true' || values.required === 1 || values.required === '1');
            var rows = values && values.rows ? parseInt(values.rows, 10) : 3;
            if (isNaN(rows) || rows < 1) rows = 3;
            return '<div data-mailpurse-textarea="' + escAttr(fk) + '" data-mailpurse-placeholder="' + escAttr(ph) + '" data-mailpurse-rows="' + escAttr(rows) + '"' + (req ? ' data-mailpurse-required="1"' : '') + '></div>';
          }

          u.registerTool({
            name: 'mp_textarea',
            label: 'Textarea',
            icon: 'fa-align-left',
            supportedDisplayModes: ['email','web','popup'],
            options: {
              field: {
                title: 'Field',
                position: 1,
                options: {
                  fieldKey: { label: 'Field', defaultValue: '', widget: 'dropdown' },
                  placeholder: { label: 'Placeholder', defaultValue: '', widget: 'text' },
                  required: { label: 'Required', defaultValue: false, widget: 'toggle' },
                  rows: { label: 'Rows', defaultValue: 3, widget: 'number' }
                }
              }
            },
            values: {},
            renderer: {
              Viewer: u.createViewer({
                render: function(values){
                  var fk = values && values.fieldKey ? String(values.fieldKey) : '';
                  return '<div style="padding:10px;border:1px dashed #cbd5e1;border-radius:10px;font-size:13px;">Textarea: ' + escHtml(fk) + '</div>';
                }
              }),
              exporters: {
                web: exportTextarea,
                email: exportTextarea,
                popup: exportTextarea
              },
              head: { css: function(){}, js: function(){} }
            }
          });
        }

        function registerSubmitTool(){
          function exportSubmit(values){
            var text = values && values.text ? String(values.text) : 'Subscribe';
            var fullWidth = values && (values.fullWidth === true || values.fullWidth === 'true' || values.fullWidth === 1 || values.fullWidth === '1');
            var bg = values && values.backgroundColor ? String(values.backgroundColor) : '#6366f1';
            var tc = values && values.textColor ? String(values.textColor) : '#ffffff';
            var br = values && values.borderRadius != null ? parseInt(values.borderRadius, 10) : 12;
            if (isNaN(br) || br < 0) br = 12;
            var py = values && values.paddingY != null ? parseInt(values.paddingY, 10) : 12;
            if (isNaN(py) || py < 0) py = 12;
            var px = values && values.paddingX != null ? parseInt(values.paddingX, 10) : 16;
            if (isNaN(px) || px < 0) px = 16;
            var style = 'border:0;cursor:pointer;font-weight:700;';
            style += 'background:' + escAttr(bg) + ';color:' + escAttr(tc) + ';';
            style += 'border-radius:' + escAttr(br) + 'px;';
            style += 'padding:' + escAttr(py) + 'px ' + escAttr(px) + 'px;';
            if (fullWidth) style += 'width:100%;display:block;';
            return '<button type="submit" data-mailpurse-submit="1" style="' + style + '">' + escHtml(text) + '</button>';
          }

          u.registerTool({
            name: 'mp_submit',
            label: 'Submit Button',
            icon: 'fa-paper-plane',
            supportedDisplayModes: ['email','web','popup'],
            options: {
              content: {
                title: 'Content',
                position: 1,
                options: {
                  text: { label: 'Text', defaultValue: 'Subscribe', widget: 'text' }
                }
              },
              style: {
                title: 'Style',
                position: 2,
                options: {
                  fullWidth: { label: 'Full Width', defaultValue: true, widget: 'toggle' },
                  backgroundColor: { label: 'Background', defaultValue: '#6366f1', widget: 'color_picker' },
                  textColor: { label: 'Text Color', defaultValue: '#ffffff', widget: 'color_picker' },
                  borderRadius: { label: 'Border Radius', defaultValue: 12, widget: 'number' },
                  paddingY: { label: 'Padding Y', defaultValue: 12, widget: 'number' },
                  paddingX: { label: 'Padding X', defaultValue: 16, widget: 'number' }
                }
              }
            },
            values: {},
            renderer: {
              Viewer: u.createViewer({
                render: function(values){
                  var t = values && values.text ? String(values.text) : 'Subscribe';
                  return '<div style="padding:10px;border:1px dashed #cbd5e1;border-radius:10px;font-size:13px;">Submit Button: ' + escHtml(t) + '</div>';
                }
              }),
              exporters: {
                web: exportSubmit,
                email: exportSubmit,
                popup: exportSubmit
              },
              head: { css: function(){}, js: function(){} }
            }
          });
        }

        registerInputTool();
        registerTextareaTool();
        registerSubmitTool();
        registerFormTool();

        window.__mailpurseUnlayerToolsRegistered = true;
        post('mailpurse:unlayer:tools-registered');
      } catch (e) {}
    }

    post('mailpurse:unlayer:customjs:booted');
    boot();
  } catch (e) {}
})();`;

function registerMailZenUnlayerTools() {
    if (window.__mailpurseUnlayerToolsRegistered) {
        return;
    }

    if (!window.unlayer || typeof window.unlayer.registerTool !== 'function' || typeof window.unlayer.createViewer !== 'function') {
        return;
    }

    const escAttr = (v) => String(v == null ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const escHtml = (v) => String(v == null ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

    const exportInput = (values) => {
        const fk = values && values.fieldKey ? String(values.fieldKey) : 'email';
        const ph = values && values.placeholder ? String(values.placeholder) : '';
        const req = !!(values && (values.required === true || values.required === 'true' || values.required === 1 || values.required === '1'));
        return `<div data-mailpurse-field="${escAttr(fk)}" data-mailpurse-placeholder="${escAttr(ph)}"${req ? ' data-mailpurse-required="1"' : ''}></div>`;
    };

    const exportTextarea = (values) => {
        const fk = values && values.fieldKey ? String(values.fieldKey) : '';
        const ph = values && values.placeholder ? String(values.placeholder) : '';
        const req = !!(values && (values.required === true || values.required === 'true' || values.required === 1 || values.required === '1'));
        let rows = values && values.rows != null ? parseInt(values.rows, 10) : 3;
        if (Number.isNaN(rows) || rows < 1) {
            rows = 3;
        }
        return `<div data-mailpurse-textarea="${escAttr(fk)}" data-mailpurse-placeholder="${escAttr(ph)}" data-mailpurse-rows="${escAttr(rows)}"${req ? ' data-mailpurse-required="1"' : ''}></div>`;
    };

    const exportSubmit = (values) => {
        const text = values && values.text ? String(values.text) : 'Subscribe';
        const fullWidth = !!(values && (values.fullWidth === true || values.fullWidth === 'true' || values.fullWidth === 1 || values.fullWidth === '1'));
        const bg = values && values.backgroundColor ? String(values.backgroundColor) : '#6366f1';
        const tc = values && values.textColor ? String(values.textColor) : '#ffffff';

        let br = values && values.borderRadius != null ? parseInt(values.borderRadius, 10) : 12;
        if (Number.isNaN(br) || br < 0) br = 12;
        let py = values && values.paddingY != null ? parseInt(values.paddingY, 10) : 12;
        if (Number.isNaN(py) || py < 0) py = 12;
        let px = values && values.paddingX != null ? parseInt(values.paddingX, 10) : 16;
        if (Number.isNaN(px) || px < 0) px = 16;

        let style = 'border:0;cursor:pointer;font-weight:700;';
        style += `background:${escAttr(bg)};color:${escAttr(tc)};`;
        style += `border-radius:${escAttr(br)}px;`;
        style += `padding:${escAttr(py)}px ${escAttr(px)}px;`;
        if (fullWidth) style += 'width:100%;display:block;';
        return `<button type="submit" data-mailpurse-submit="1" style="${style}">${escHtml(text)}</button>`;
    };

    try {
        window.unlayer.registerTool({
            name: 'mp_input',
            label: 'Input',
            icon: 'fa-i-cursor',
            supportedDisplayModes: ['email', 'web', 'popup'],
            options: {
                field: {
                    title: 'Field',
                    position: 1,
                    options: {
                        fieldKey: { label: 'Field', defaultValue: 'email', widget: 'dropdown' },
                        placeholder: { label: 'Placeholder', defaultValue: '', widget: 'text' },
                        required: { label: 'Required', defaultValue: false, widget: 'toggle' },
                    },
                },
            },
            values: {},
            renderer: {
                Viewer: window.unlayer.createViewer({
                    render(values) {
                        const fk = values && values.fieldKey ? String(values.fieldKey) : 'email';
                        return `<div style='padding:10px;border:1px dashed #cbd5e1;border-radius:10px;font-size:13px;'>Input: ${escHtml(fk)}</div>`;
                    },
                }),
                exporters: {
                    web: exportInput,
                    email: exportInput,
                    popup: exportInput,
                },
                head: { css() {}, js() {} },
            },
        });

        window.unlayer.registerTool({
            name: 'mp_textarea',
            label: 'Textarea',
            icon: 'fa-align-left',
            supportedDisplayModes: ['email', 'web', 'popup'],
            options: {
                field: {
                    title: 'Field',
                    position: 1,
                    options: {
                        fieldKey: { label: 'Field', defaultValue: '', widget: 'dropdown' },
                        placeholder: { label: 'Placeholder', defaultValue: '', widget: 'text' },
                        required: { label: 'Required', defaultValue: false, widget: 'toggle' },
                        rows: { label: 'Rows', defaultValue: 3, widget: 'number' },
                    },
                },
            },
            values: {},
            renderer: {
                Viewer: window.unlayer.createViewer({
                    render(values) {
                        const fk = values && values.fieldKey ? String(values.fieldKey) : '';
                        return `<div style='padding:10px;border:1px dashed #cbd5e1;border-radius:10px;font-size:13px;'>Textarea: ${escHtml(fk)}</div>`;
                    },
                }),
                exporters: {
                    web: exportTextarea,
                    email: exportTextarea,
                    popup: exportTextarea,
                },
                head: { css() {}, js() {} },
            },
        });

        window.unlayer.registerTool({
            name: 'mp_submit',
            label: 'Submit Button',
            icon: 'fa-paper-plane',
            supportedDisplayModes: ['email', 'web', 'popup'],
            options: {
                content: {
                    title: 'Content',
                    position: 1,
                    options: {
                        text: { label: 'Text', defaultValue: 'Subscribe', widget: 'text' },
                    },
                },
                style: {
                    title: 'Style',
                    position: 2,
                    options: {
                        fullWidth: { label: 'Full Width', defaultValue: true, widget: 'toggle' },
                        backgroundColor: { label: 'Background', defaultValue: '#6366f1', widget: 'color_picker' },
                        textColor: { label: 'Text Color', defaultValue: '#ffffff', widget: 'color_picker' },
                        borderRadius: { label: 'Border Radius', defaultValue: 12, widget: 'number' },
                        paddingY: { label: 'Padding Y', defaultValue: 12, widget: 'number' },
                        paddingX: { label: 'Padding X', defaultValue: 16, widget: 'number' },
                    },
                },
            },
            values: {},
            renderer: {
                Viewer: window.unlayer.createViewer({
                    render(values) {
                        const t = values && values.text ? String(values.text) : 'Subscribe';
                        return `<div style='padding:10px;border:1px dashed #cbd5e1;border-radius:10px;font-size:13px;'>Submit Button: ${escHtml(t)}</div>`;
                    },
                }),
                exporters: {
                    web: exportSubmit,
                    email: exportSubmit,
                    popup: exportSubmit,
                },
                head: { css() {}, js() {} },
            },
        });

        window.__mailpurseUnlayerToolsRegistered = true;
    } catch (e) {
    }
}

function ensureMailZenUnlayerToolsRegistered(retriesLeft = 80) {
    try {
        if (window.__mailpurseUnlayerToolsRegistered) {
            return;
        }
        if (!window.unlayer || typeof window.unlayer.registerTool !== 'function' || typeof window.unlayer.createViewer !== 'function') {
            if (retriesLeft <= 0) {
                return;
            }
            setTimeout(() => ensureMailZenUnlayerToolsRegistered(retriesLeft - 1), 75);
            return;
        }
        registerMailZenUnlayerTools();
    } catch (e) {
        // ignore
    }
}

function waitForMailZenUnlayerToolsRegistered(timeoutMs = 6000) {
    const started = Date.now();

    return new Promise((resolve) => {
        const tick = () => {
            try {
                ensureMailZenUnlayerToolsRegistered();
            } catch (e) {
                // ignore
            }

            if (window.__mailpurseUnlayerToolsRegistered) {
                resolve(true);
                return;
            }

            if (Date.now() - started >= timeoutMs) {
                resolve(false);
                return;
            }

            setTimeout(tick, 50);
        };

        tick();
    });
}

function readJsonScriptTag(scriptId) {
    if (!scriptId) {
        return null;
    }
    const el = document.getElementById(scriptId);
    if (!el) {
        return null;
    }
    const raw = (el.textContent || '').trim();
    if (!raw) {
        return null;
    }
    try {
        return JSON.parse(raw);
    } catch (e) {
        return null;
    }
}

function getMailZenSupportedGoogleFontFamilies() {
    try {
        const raw = window.__mailpurseSupportedGoogleFontFamilies;
        if (!Array.isArray(raw)) {
            return [];
        }
        return raw
            .map((v) => (typeof v === 'string' ? v.trim() : ''))
            .filter((v) => v);
    } catch (e) {
        return [];
    }
}

function buildMailZenUnlayerFontsConfig() {
    const families = getMailZenSupportedGoogleFontFamilies();
    if (!families.length) {
        return null;
    }

    const buildGoogleFontUrl = (family) => {
        const encoded = encodeURIComponent(String(family || '')).replace(/%20/g, '+');
        return `https://fonts.googleapis.com/css2?family=${encoded}:wght@400;500;600;700&display=swap`;
    };

    const makeWeights = () => ([
        { label: 'Regular', value: 400 },
        { label: 'Medium', value: 500 },
        { label: 'Semi Bold', value: 600 },
        { label: 'Bold', value: 700 },
    ]);

    const customFonts = families.map((family) => {
        const safeFamily = String(family).replace(/'/g, "\\'");
        return {
            label: String(family),
            value: `'${safeFamily}', sans-serif`,
            url: buildGoogleFontUrl(family),
            weights: makeWeights(),
        };
    });

    return {
        showDefaultFonts: true,
        customFonts,
    };
}

function setupUnlayerEditors() {
    const editorEls = document.querySelectorAll('[data-unlayer-editor]');
    if (!editorEls.length) {
        return;
    }

    ensureUnlayerScriptLoaded()
        .then(() => {
            editorEls.forEach((el) => {
                const id = el.getAttribute('id') || 'editor-container';
                const initKey = '__mailpurseUnlayerInitDone:' + id;

                if (window[initKey]) {
                    return;
                }

                const waitForVisibleAndInit = async (timeoutMs = 12000) => {
                    const started = Date.now();
                    const tick = async () => {
                        if (window[initKey]) {
                            return;
                        }

                        // Turbo can fire turbo:load before layout settles; avoid init on 0-height containers.
                        if (el.offsetParent === null || el.offsetHeight === 0) {
                            if (Date.now() - started >= timeoutMs) {
                                return;
                            }
                            setTimeout(tick, 75);
                            return;
                        }

                        const projectIdRaw = el.getAttribute('data-unlayer-project-id');
                        const projectId = projectIdRaw ? Number(projectIdRaw) : null;
                        const displayMode = el.getAttribute('data-unlayer-display-mode') || 'email';

                        const fieldsScriptId = el.getAttribute('data-unlayer-fields-script-id');
                        const fieldsCfg = fieldsScriptId ? readJsonScriptTag(fieldsScriptId) : null;
                        const inputFieldOptions = (fieldsCfg && fieldsCfg.input && Array.isArray(fieldsCfg.input.options)) ? fieldsCfg.input.options : [];
                        const textareaFieldOptions = (fieldsCfg && fieldsCfg.textarea && Array.isArray(fieldsCfg.textarea.options)) ? fieldsCfg.textarea.options : [];

                        const formActionUrl = (el.getAttribute('data-unlayer-form-action-url') || '').trim();

                        // Load custom tools and initialize Unlayer
                        const customToolsCode = await loadUnlayerCustomTools();
                        
                        console.log('[MailZen Unlayer] Custom tools code length:', customToolsCode ? customToolsCode.length : 0);
                        console.log('[MailZen Unlayer] First 100 chars:', customToolsCode ? customToolsCode.substring(0, 100) + '...' : 'null');

                        const options = {
                            id,
                            displayMode,
                            fonts: undefined,
                            editor: {
                                autoSelectOnDrop: true,
                            },
                            tools: {
                                form: {
                                    enabled: true,
                                    properties: formActionUrl
                                        ? {
                                            action: {
                                                editor: {
                                                    data: {
                                                        actions: [
                                                            {
                                                                label: 'Subscribe',
                                                                method: 'POST',
                                                                url: formActionUrl,
                                                            },
                                                        ],
                                                    },
                                                },
                                            },
                                        }
                                        : undefined,
                                },
                                'custom#mp_input': {
                                    enabled: true,
                                    properties: {
                                        fieldKey: {
                                            editor: {
                                                data: {
                                                    options: inputFieldOptions,
                                                },
                                            },
                                        },
                                    },
                                },
                                mp_input: {
                                    enabled: true,
                                    properties: {
                                        fieldKey: {
                                            editor: {
                                                data: {
                                                    options: inputFieldOptions,
                                                },
                                            },
                                        },
                                    },
                                },
                                'custom#mp_textarea': {
                                    enabled: true,
                                    properties: {
                                        fieldKey: {
                                            editor: {
                                                data: {
                                                    options: textareaFieldOptions,
                                                },
                                            },
                                        },
                                    },
                                },
                                mp_textarea: {
                                    enabled: true,
                                    properties: {
                                        fieldKey: {
                                            editor: {
                                                data: {
                                                    options: textareaFieldOptions,
                                                },
                                            },
                                        },
                                    },
                                },
                                'custom#mp_submit': {
                                    enabled: true,
                                },
                                mp_submit: {
                                    enabled: true,
                                },
                                'custom#my_tool': {
                                    enabled: true,
                                },
                                my_tool: {
                                    enabled: true,
                                },
                                'custom#mp_form': {
                                    enabled: true,
                                },
                                mp_form: {
                                    enabled: true,
                                },
                            },
                        };

                        const fontsCfg = buildMailZenUnlayerFontsConfig();
                        if (fontsCfg) {
                            options.fonts = fontsCfg;
                        } else {
                            delete options.fonts;
                        }

                        if (projectId) {
                            options.projectId = projectId;
                        }

                        try {
                            window.__mailpurseLastUnlayerInitOptions = options;
                        } catch (e) {
                        }

                        window.__mailpurseUnlayerReady = false;
                        let readyHandled = false;

                        const onReady = () => {
                            if (readyHandled) {
                                return;
                            }
                            readyHandled = true;
                            window.__mailpurseUnlayerReady = true;

                            const designScriptId = el.getAttribute('data-unlayer-design-script-id');
                            if (designScriptId) {
                                const designEl = document.getElementById(designScriptId);
                                const raw = designEl ? (designEl.textContent || '') : '';
                                if (raw && raw.trim()) {
                                    try {
                                        const design = JSON.parse(raw);
                                        if (design && typeof design === 'object') {
                                            window.unlayer.loadDesign(design);
                                        }
                                    } catch (e) {
                                        // ignore invalid design
                                    }
                                }
                            }

                            if (window.__mailpurseUnlayerPendingDesign && typeof window.__mailpurseUnlayerPendingDesign === 'object') {
                                try {
                                    window.unlayer.loadDesign(window.__mailpurseUnlayerPendingDesign);
                                } catch (e) {
                                }
                                window.__mailpurseUnlayerPendingDesign = null;
                            }
                        };

                        ensureMailZenUnlayerToolsRegistered();

                        try {
                            window.unlayer.addEventListener('editor:ready', onReady);
                        } catch (e) {
                        }

                        window.unlayer.init(options);

                        try {
                            window.unlayer.addEventListener('editor:ready', onReady);
                        } catch (e) {
                        }

                        window[initKey] = true;
                        
                        // Debug: Check if customJS was actually passed
                        console.log('[MailZen Unlayer] Options passed to init:', {
                            id: options.id,
                            displayMode: options.displayMode,
                            hasCustomJS: !!options.customJS,
                            customJSLength: options.customJS ? options.customJS.length : 0,
                            customJSType: typeof options.customJS
                        });
                        
                        // Listen for messages from the iframe
                        window.addEventListener('message', (event) => {
                            if (event.data && event.data.type && event.data.type.startsWith('mailpurse:unlayer:')) {
                                console.log('[MailZen Unlayer] Received message from iframe:', event.data);
                                
                                if (event.data.type === 'mailpurse:unlayer:tools-registered') {
                                    console.log('[MailZen Unlayer] ✓ Custom tools registered successfully!');
                                }
                            }
                        });
                    };
                    tick();
                };

                waitForVisibleAndInit();
            });
        })
        .catch(() => {
            // silent
        });
}

try {
    window.__mailpurseSetupUnlayerEditors = setupUnlayerEditors;
} catch (e) {
}

function setupUnlayerMessageBridge() {
    if (window.__mailpurseUnlayerMessageBridgeBound) {
        return;
    }
    window.__mailpurseUnlayerMessageBridgeBound = true;

    if (!Array.isArray(window.__mailpurseUnlayerToolsDebug)) {
        window.__mailpurseUnlayerToolsDebug = [];
    }

    window.addEventListener('message', (event) => {
        try {
            const data = event && event.data ? event.data : null;
            if (!data || typeof data.type !== 'string') {
                return;
            }

            if (data.type.startsWith('mailpurse:unlayer:')) {
                try {
                    window.__mailpurseUnlayerToolsDebug.push({
                        type: data.type,
                        payload: data.payload ?? null,
                        at: Date.now(),
                    });
                } catch (e) {
                    // ignore
                }
            }

            if (data.type === 'mailpurse:unlayer:tools-registered') {
                window.__mailpurseUnlayerToolsRegistered = true;
            }
        } catch (e) {
            // ignore
        }
    });
}

function setupUnlayerSaveBindings() {
    const form = document.getElementById('unlayer-form');
    const saveButton = document.getElementById('btn-save');

    if (!form || !saveButton) {
        return;
    }

    if (saveButton.dataset.unlayerBound === '1') {
        return;
    }
    saveButton.dataset.unlayerBound = '1';

    saveButton.addEventListener('click', () => {
        if (!window.unlayer || typeof window.unlayer.exportHtml !== 'function') {
            form.submit();
            return;
        }

        window.unlayer.exportHtml((data) => {
            const htmlInput = document.getElementById('html_content');
            const plainInput = document.getElementById('plain_text_content');
            const dataInput = document.getElementById('grapesjs_data');

            if (htmlInput) {
                htmlInput.value = (data && data.html) ? data.html : '';
            }

            if (dataInput) {
                dataInput.value = JSON.stringify((data && data.design) ? data.design : null);
            }

            const plainText = String((data && data.html) ? data.html : '')
                .replace(/<style[\s\S]*?<\/style>/gi, ' ')
                .replace(/<script[\s\S]*?<\/script>/gi, ' ')
                .replace(/<[^>]+>/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            if (plainInput) {
                plainInput.value = plainText;
            }

            form.submit();
        });
    });
}

function teardownUnlayerEditorsBeforeCache() {
    const editorEls = document.querySelectorAll('[data-unlayer-editor]');
    editorEls.forEach((el) => {
        const id = el.getAttribute('id') || 'editor-container';
        const initKey = '__mailpurseUnlayerInitDone:' + id;
        try {
            delete window[initKey];
        } catch (e) {
        }
        el.innerHTML = '';
    });

    const saveButton = document.getElementById('btn-save');
    if (saveButton && saveButton.dataset) {
        delete saveButton.dataset.unlayerBound;
    }

    window.__mailpurseUnlayerReady = false;
}

function getToastTheme(type) {
    const t = String(type || '').toLowerCase();
    if (t === 'success') {
        return {
            ring: 'bg-green-500/20',
            iconBg: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-200',
            title: 'text-green-900 dark:text-green-100',
            message: 'text-green-800/80 dark:text-green-200/80',
            border: 'border border-green-200/70 dark:border-green-800/60',
            panel: 'bg-white/90 dark:bg-gray-900/70',
            icon: '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>',
        };
    }
    if (t === 'warning') {
        return {
            ring: 'bg-yellow-500/20',
            iconBg: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
            title: 'text-yellow-950 dark:text-yellow-100',
            message: 'text-yellow-900/80 dark:text-yellow-200/80',
            border: 'border border-yellow-200/70 dark:border-yellow-800/60',
            panel: 'bg-white/90 dark:bg-gray-900/70',
            icon: '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.721-1.36 3.486 0l6.518 11.59c.75 1.334-.214 3.011-1.742 3.011H3.48c-1.528 0-2.492-1.677-1.742-3.011l6.519-11.59zM11 14a1 1 0 10-2 0 1 1 0 002 0zm-1-2a.75.75 0 01-.75-.75V7a.75.75 0 011.5 0v4.25A.75.75 0 0110 12z" clip-rule="evenodd"/></svg>',
        };
    }
    if (t === 'error' || t === 'danger') {
        return {
            ring: 'bg-red-500/20',
            iconBg: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200',
            title: 'text-red-900 dark:text-red-100',
            message: 'text-red-800/80 dark:text-red-200/80',
            border: 'border border-red-200/70 dark:border-red-800/60',
            panel: 'bg-white/90 dark:bg-gray-900/70',
            icon: '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm2.53-10.53a.75.75 0 00-1.06-1.06L10 8.94 8.53 7.47a.75.75 0 10-1.06 1.06L8.94 10l-1.47 1.47a.75.75 0 101.06 1.06L10 11.06l1.47 1.47a.75.75 0 001.06-1.06L11.06 10l1.47-1.47z" clip-rule="evenodd"/></svg>',
        };
    }
    return {
        ring: 'bg-blue-500/20',
        iconBg: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200',
        title: 'text-blue-900 dark:text-blue-100',
        message: 'text-blue-800/80 dark:text-blue-200/80',
        border: 'border border-blue-200/70 dark:border-blue-800/60',
        panel: 'bg-white/90 dark:bg-gray-900/70',
        icon: '<svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10A8 8 0 11 2 10a8 8 0 0116 0zM10 8a1 1 0 100-2 1 1 0 000 2zm-1 2a1 1 0 012 0v4a1 1 0 11-2 0v-4z" clip-rule="evenodd"/></svg>',
    };
}

function createToastElement(toast, onClose) {
    const t = toast && typeof toast === 'object' ? toast : {};
    const theme = getToastTheme(t.type);
    const titleText = typeof t.title === 'string' ? t.title : '';
    const messageText = typeof t.message === 'string' ? t.message : '';

    const wrapper = document.createElement('div');
    wrapper.className = `relative ${theme.ring} rounded-2xl p-1 shadow-lg`;

    const panel = document.createElement('div');
    panel.className = `backdrop-blur ${theme.panel} ${theme.border} rounded-[14px] px-4 py-3 transition duration-300 ease-out opacity-0 translate-y-2`;

    const row = document.createElement('div');
    row.className = 'flex items-start gap-3';

    const iconWrap = document.createElement('div');
    iconWrap.className = `shrink-0 w-9 h-9 rounded-full flex items-center justify-center ${theme.iconBg}`;
    iconWrap.innerHTML = theme.icon;

    const content = document.createElement('div');
    content.className = 'flex-1 min-w-0';

    if (titleText) {
        const title = document.createElement('div');
        title.className = `text-sm font-semibold ${theme.title}`;
        title.textContent = titleText;
        content.appendChild(title);
    }

    if (messageText) {
        const msg = document.createElement('div');
        msg.className = `mt-0.5 text-xs leading-5 whitespace-pre-line ${theme.message}`;
        msg.textContent = messageText;
        content.appendChild(msg);
    }

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'shrink-0 -mr-1 -mt-1 p-2 rounded-lg text-gray-500 hover:text-gray-800 hover:bg-black/5 dark:text-gray-300 dark:hover:text-white dark:hover:bg-white/10';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.innerHTML = '<svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>';

    row.appendChild(iconWrap);
    row.appendChild(content);
    row.appendChild(closeBtn);
    panel.appendChild(row);
    wrapper.appendChild(panel);

    const animateIn = () => {
        requestAnimationFrame(() => {
            panel.classList.remove('opacity-0', 'translate-y-2');
            panel.classList.add('opacity-100', 'translate-y-0');
        });
    };

    const close = () => {
        panel.classList.remove('opacity-100', 'translate-y-0');
        panel.classList.add('opacity-0', 'translate-y-2');
        window.setTimeout(() => {
            wrapper.remove();
            if (typeof onClose === 'function') {
                onClose();
            }
        }, 220);
    };

    closeBtn.addEventListener('click', close);
    animateIn();

    return { el: wrapper, close };
}

function showToast(toast, opts = {}) {
    const root = document.querySelector('[data-toast-root]');
    if (!root) {
        return;
    }

    const durationMs = Number(opts.durationMs ?? toast?.durationMs) || 4500;

    const created = createToastElement(toast, null);
    root.appendChild(created.el);

    if (durationMs > 0) {
        window.setTimeout(() => created.close(), durationMs);
    }
}

function setupToasts() {
    const root = document.querySelector('[data-toast-root]');
    if (!root) {
        return;
    }

    if (typeof window.mailpurseToast !== 'function') {
        window.mailpurseToast = (toast, opts = {}) => showToast(toast, opts);
    }

    const toasts = window.__mailpursePageToasts;
    if (Array.isArray(toasts) && toasts.length) {
        toasts.forEach((t) => showToast(t));
    }

    try {
        window.__mailpursePageToasts = [];
    } catch (e) {
    }
}

const runAppBoot = () => {
    setupNotificationPolling();
    setupToasts();
    setupCustomConfirmModal();
    setupUnlayerMessageBridge();
    setupUnlayerEditors();
    setupUnlayerSaveBindings();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', runAppBoot);
} else {
    runAppBoot();
}

document.addEventListener('turbo:before-cache', () => {
    if (window.Alpine && typeof window.Alpine.destroyTree === 'function') {
        window.Alpine.destroyTree(document.body);
    }

    const toastRoot = document.querySelector('[data-toast-root]');
    if (toastRoot) {
        toastRoot.innerHTML = '';
    }

    teardownUnlayerEditorsBeforeCache();
});

document.addEventListener('turbo:load', () => {
    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
        if (!document.documentElement.__x) {
            window.Alpine.initTree(document.documentElement);
        }
        window.Alpine.initTree(document.body);
    }
    runAppBoot();
});

