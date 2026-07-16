<style>
    body.app-confirm-lock {
        overflow: hidden;
    }

    .app-confirm[hidden] {
        display: none !important;
    }

    .app-confirm {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: grid;
        place-items: center;
        padding: 20px;
        opacity: 0;
        visibility: hidden;
        transition: opacity 180ms ease, visibility 180ms ease;
    }

    .app-confirm.is-open {
        opacity: 1;
        visibility: visible;
    }

    .app-confirm__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.58);
        backdrop-filter: blur(3px);
    }

    .app-confirm__panel {
        position: relative;
        width: min(100%, 440px);
        overflow: hidden;
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 20px;
        background: #ffffff;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.24);
        color: #172033;
        font-family: inherit;
        transform: translateY(12px) scale(0.97);
        transition: transform 180ms ease;
    }

    .app-confirm.is-open .app-confirm__panel {
        transform: translateY(0) scale(1);
    }

    .app-confirm__body {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 26px 26px 20px;
    }

    .app-confirm__icon {
        display: grid;
        flex: 0 0 48px;
        width: 48px;
        height: 48px;
        place-items: center;
        border-radius: 15px;
        background: #fff1f2;
        color: #e11d48;
    }

    .app-confirm__icon svg {
        width: 24px;
        height: 24px;
    }

    .app-confirm__copy {
        min-width: 0;
        padding-top: 1px;
    }

    .app-confirm__title {
        margin: 0;
        color: #111827;
        font-size: 19px;
        font-weight: 750;
        line-height: 1.35;
        letter-spacing: -0.01em;
    }

    .app-confirm__message {
        margin: 8px 0 0;
        color: #64748b;
        font-size: 14px;
        font-weight: 450;
        line-height: 1.65;
    }

    .app-confirm__close {
        position: absolute;
        top: 14px;
        right: 14px;
        display: grid;
        width: 32px;
        height: 32px;
        place-items: center;
        padding: 0;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #94a3b8;
        cursor: pointer;
        transition: background-color 150ms ease, color 150ms ease;
    }

    .app-confirm__close:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .app-confirm__close svg {
        width: 18px;
        height: 18px;
    }

    .app-confirm__actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 26px 22px;
        border-top: 1px solid #f1f5f9;
        background: #fbfcfe;
    }

    .app-confirm__button {
        min-width: 104px;
        min-height: 42px;
        padding: 10px 18px;
        border: 1px solid transparent;
        border-radius: 10px;
        font-family: inherit;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.2;
        cursor: pointer;
        transition: transform 150ms ease, box-shadow 150ms ease, background-color 150ms ease, border-color 150ms ease;
    }

    .app-confirm__button:focus-visible,
    .app-confirm__close:focus-visible {
        outline: 3px solid rgba(59, 130, 246, 0.24);
        outline-offset: 2px;
    }

    .app-confirm__button:active {
        transform: translateY(1px);
    }

    .app-confirm__button--cancel {
        border-color: #dbe2ea;
        background: #ffffff;
        color: #475569;
    }

    .app-confirm__button--cancel:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .app-confirm__button--confirm {
        background: #e11d48;
        color: #ffffff;
        box-shadow: 0 8px 20px rgba(225, 29, 72, 0.22);
    }

    .app-confirm__button--confirm:hover {
        background: #be123c;
        box-shadow: 0 10px 24px rgba(225, 29, 72, 0.28);
    }

    .app-confirm--warning .app-confirm__icon {
        background: #fff7ed;
        color: #ea580c;
    }

    .app-confirm--warning .app-confirm__button--confirm {
        background: #ea580c;
        box-shadow: 0 8px 20px rgba(234, 88, 12, 0.22);
    }

    .app-confirm--warning .app-confirm__button--confirm:hover {
        background: #c2410c;
    }

    .app-confirm--primary .app-confirm__icon {
        background: #eff6ff;
        color: #2563eb;
    }

    .app-confirm--primary .app-confirm__button--confirm {
        background: #2563eb;
        box-shadow: 0 8px 20px rgba(37, 99, 235, 0.22);
    }

    .app-confirm--primary .app-confirm__button--confirm:hover {
        background: #1d4ed8;
    }

    @media (max-width: 520px) {
        .app-confirm {
            align-items: end;
            padding: 12px;
        }

        .app-confirm__panel {
            border-radius: 18px;
        }

        .app-confirm__body {
            padding: 24px 20px 18px;
        }

        .app-confirm__actions {
            padding: 14px 20px 20px;
        }

        .app-confirm__button {
            flex: 1;
            min-width: 0;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .app-confirm,
        .app-confirm__panel,
        .app-confirm__button {
            transition: none;
        }
    }
</style>

<div id="app-confirm-dialog" class="app-confirm app-confirm--danger" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="app-confirm-title" aria-describedby="app-confirm-message" hidden>
    <div class="app-confirm__backdrop" data-confirm-cancel></div>
    <section class="app-confirm__panel" role="document">
        <button type="button" class="app-confirm__close" aria-label="Đóng" data-confirm-cancel>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M18 6 6 18M6 6l12 12" />
            </svg>
        </button>

        <div class="app-confirm__body">
            <div class="app-confirm__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.3 2.9 1.8 17a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 2.9a2 2 0 0 0-3.4 0Z" />
                    <path d="M12 9v4M12 17h.01" />
                </svg>
            </div>
            <div class="app-confirm__copy">
                <h2 id="app-confirm-title" class="app-confirm__title">Xác nhận thao tác</h2>
                <p id="app-confirm-message" class="app-confirm__message"></p>
            </div>
        </div>

        <div class="app-confirm__actions">
            <button type="button" class="app-confirm__button app-confirm__button--cancel" data-confirm-cancel>Hủy</button>
            <button type="button" class="app-confirm__button app-confirm__button--confirm" data-confirm-accept>Xác nhận</button>
        </div>
    </section>
</div>

<script>
    (() => {
        if (window.AppConfirm) return;

        const dialog = document.getElementById('app-confirm-dialog');
        if (!dialog) return;

        const titleElement = dialog.querySelector('#app-confirm-title');
        const messageElement = dialog.querySelector('#app-confirm-message');
        const cancelButton = dialog.querySelector('.app-confirm__button--cancel');
        const confirmButton = dialog.querySelector('[data-confirm-accept]');
        const approvedForms = new WeakSet();
        let resolveCurrent = null;
        let previouslyFocused = null;
        let closeTimer = null;

        const close = (accepted) => {
            if (!resolveCurrent) return;

            const resolve = resolveCurrent;
            resolveCurrent = null;
            dialog.classList.remove('is-open');
            dialog.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('app-confirm-lock');

            window.clearTimeout(closeTimer);
            closeTimer = window.setTimeout(() => {
                dialog.hidden = true;
                if (previouslyFocused && document.contains(previouslyFocused)) {
                    previouslyFocused.focus({ preventScroll: true });
                }
                previouslyFocused = null;
            }, 180);

            resolve(accepted);
        };

        const open = (options = {}) => {
            if (resolveCurrent) close(false);

            window.clearTimeout(closeTimer);
            previouslyFocused = document.activeElement;
            titleElement.textContent = options.title || 'Xác nhận thao tác';
            messageElement.textContent = options.message || 'Bạn có chắc chắn muốn tiếp tục?';
            cancelButton.textContent = options.cancelLabel || 'Hủy';
            confirmButton.textContent = options.confirmLabel || 'Xác nhận';
            const showCancel = options.showCancel !== false;
            cancelButton.hidden = !showCancel;

            const variant = ['danger', 'warning', 'primary'].includes(options.variant)
                ? options.variant
                : 'danger';
            dialog.classList.remove('app-confirm--danger', 'app-confirm--warning', 'app-confirm--primary');
            dialog.classList.add(`app-confirm--${variant}`);
            dialog.hidden = false;
            dialog.setAttribute('aria-hidden', 'false');
            document.body.classList.add('app-confirm-lock');

            window.requestAnimationFrame(() => {
                dialog.classList.add('is-open');
                (showCancel ? cancelButton : confirmButton).focus({ preventScroll: true });
            });

            return new Promise((resolve) => {
                resolveCurrent = resolve;
            });
        };

        dialog.addEventListener('click', (event) => {
            if (event.target.closest('[data-confirm-cancel]')) close(false);
            if (event.target.closest('[data-confirm-accept]')) close(true);
        });

        document.addEventListener('keydown', (event) => {
            if (!resolveCurrent) return;

            if (event.key === 'Escape') {
                event.preventDefault();
                close(false);
                return;
            }

            if (event.key !== 'Tab') return;

            const focusable = [...dialog.querySelectorAll('button:not([disabled]):not([hidden])')];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];

            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;

            if (approvedForms.has(form)) {
                approvedForms.delete(form);
                return;
            }

            if (!form.matches('[data-confirm]')) return;

            event.preventDefault();
            const submitter = event.submitter;

            open({
                title: form.dataset.confirmTitle,
                message: form.dataset.confirm,
                confirmLabel: form.dataset.confirmLabel,
                cancelLabel: form.dataset.confirmCancelLabel,
                variant: form.dataset.confirmVariant,
            }).then((accepted) => {
                if (!accepted) return;

                approvedForms.add(form);
                if (typeof form.requestSubmit === 'function') {
                    submitter && form.contains(submitter) ? form.requestSubmit(submitter) : form.requestSubmit();
                } else {
                    HTMLFormElement.prototype.submit.call(form);
                }
            });
        });

        const alertDialog = (options = {}) => open({
            title: 'Thông báo',
            confirmLabel: 'Đã hiểu',
            variant: 'warning',
            ...options,
            showCancel: false,
        });

        window.AppConfirm = { open, alert: alertDialog };
    })();
</script>
