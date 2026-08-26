<div
    id="maxball-chatbot"
    class="maxball-chatbot"
    data-endpoint="{{ route('api.chatbot') }}"
    data-csrf-token="{{ csrf_token() }}"
>
    <section
        id="maxball-chatbot-panel"
        class="maxball-chatbot__panel"
        role="dialog"
        aria-label="Trợ lý mua sắm MaxBall AI"
        aria-modal="false"
        hidden
    >
        <header class="maxball-chatbot__header">
            <div class="maxball-chatbot__avatar" aria-hidden="true">
                <i class="fa-solid fa-futbol"></i>
            </div>
            <div>
                <strong>MaxBall AI</strong>
                <span><i class="fa-solid fa-circle"></i> Trợ lý mua sắm</span>
            </div>
            <button type="button" class="maxball-chatbot__close" aria-label="Đóng cửa sổ chat">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </header>

        <div class="maxball-chatbot__messages" aria-live="polite" aria-relevant="additions">
            <div class="maxball-chatbot__message maxball-chatbot__message--bot">
                Chào bạn! Mình là trợ lý MaxBall AI. Bạn đang tìm sản phẩm, thương hiệu, mức giá hay size nào?
            </div>
        </div>

        <form class="maxball-chatbot__form" novalidate>
            <label class="sr-only" for="maxball-chatbot-input">Nhập câu hỏi cho MaxBall AI</label>
            <textarea
                id="maxball-chatbot-input"
                rows="1"
                maxlength="1000"
                placeholder="Ví dụ: Có áo Real Madrid size L không?"
                autocomplete="off"
            ></textarea>
            <button type="submit" class="maxball-chatbot__send" aria-label="Gửi tin nhắn">
                <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
            </button>
        </form>
        <p class="maxball-chatbot__note">Thông tin sản phẩm và cửa hàng được đối chiếu từ MaxBall.</p>
    </section>

    <button
        type="button"
        class="maxball-chatbot__toggle"
        aria-label="Mở trợ lý MaxBall AI"
        aria-controls="maxball-chatbot-panel"
        aria-expanded="false"
    >
        <i class="fa-solid fa-comments" aria-hidden="true"></i>
        <span>Chat với MaxBall AI</span>
    </button>
</div>

<style>
    .maxball-chatbot {
        position: fixed;
        right: 22px;
        bottom: 22px;
        z-index: 9990;
        font-family: 'DM Sans', sans-serif;
    }

    .maxball-chatbot[hidden],
    .maxball-chatbot__panel[hidden] {
        display: none !important;
    }

    .maxball-chatbot__toggle {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        min-height: 54px;
        padding: 0 20px;
        border: 0;
        border-radius: 999px;
        background: #d92525;
        color: #fff;
        box-shadow: 0 16px 38px rgba(7, 19, 14, .28);
        cursor: pointer;
        font-size: 14px;
        font-weight: 800;
        transition: transform .2s ease, box-shadow .2s ease, background .2s ease;
    }

    .maxball-chatbot__toggle:hover {
        transform: translateY(-2px);
        background: #bd1f1f;
        box-shadow: 0 19px 44px rgba(7, 19, 14, .34);
    }

    .maxball-chatbot__toggle:focus-visible,
    .maxball-chatbot button:focus-visible,
    .maxball-chatbot textarea:focus-visible,
    .maxball-chatbot a:focus-visible {
        outline: 3px solid rgba(217, 37, 37, .3);
        outline-offset: 3px;
    }

    .maxball-chatbot__toggle i {
        font-size: 19px;
    }

    .maxball-chatbot__panel {
        position: absolute;
        right: 0;
        bottom: 70px;
        display: flex;
        width: min(390px, calc(100vw - 32px));
        height: min(600px, calc(100vh - 120px));
        min-height: 430px;
        overflow: hidden;
        flex-direction: column;
        border: 1px solid rgba(16, 39, 29, .1);
        border-radius: 22px;
        background: #fff;
        box-shadow: 0 24px 70px rgba(7, 19, 14, .25);
        transform-origin: bottom right;
        animation: maxball-chatbot-in .2s ease-out;
    }

    @keyframes maxball-chatbot-in {
        from { opacity: 0; transform: translateY(12px) scale(.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .maxball-chatbot__header {
        display: flex;
        align-items: center;
        gap: 11px;
        flex: 0 0 auto;
        padding: 15px 16px;
        background: #10271d;
        color: #fff;
    }

    .maxball-chatbot__avatar {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border-radius: 14px;
        background: #d92525;
        font-size: 19px;
    }

    .maxball-chatbot__header strong,
    .maxball-chatbot__header span {
        display: block;
    }

    .maxball-chatbot__header strong {
        font-size: 15px;
        line-height: 1.3;
    }

    .maxball-chatbot__header span {
        margin-top: 2px;
        color: rgba(255, 255, 255, .72);
        font-size: 11px;
    }

    .maxball-chatbot__header span i {
        margin-right: 4px;
        color: #57d287;
        font-size: 7px;
        vertical-align: 1px;
    }

    .maxball-chatbot__close {
        display: grid;
        width: 36px;
        height: 36px;
        margin-left: auto;
        place-items: center;
        border: 0;
        border-radius: 11px;
        background: rgba(255, 255, 255, .09);
        color: #fff;
        cursor: pointer;
    }

    .maxball-chatbot__messages {
        display: flex;
        min-height: 0;
        flex: 1 1 auto;
        flex-direction: column;
        gap: 10px;
        overflow-y: auto;
        padding: 16px;
        background: #f7f8f6;
        overscroll-behavior: contain;
        scrollbar-width: thin;
        scrollbar-color: #bdc7c1 transparent;
    }

    .maxball-chatbot__message {
        width: fit-content;
        max-width: 86%;
        padding: 10px 13px;
        border-radius: 15px;
        font-size: 13px;
        line-height: 1.5;
        overflow-wrap: anywhere;
        white-space: pre-wrap;
    }

    .maxball-chatbot__message--bot {
        align-self: flex-start;
        border: 1px solid #e3e8e5;
        border-bottom-left-radius: 5px;
        background: #fff;
        color: #24352e;
    }

    .maxball-chatbot__message--user {
        align-self: flex-end;
        border-bottom-right-radius: 5px;
        background: #10271d;
        color: #fff;
    }

    .maxball-chatbot__message--error {
        border-color: #fecaca;
        background: #fff1f1;
        color: #9d1c1c;
    }

    .maxball-chatbot__typing {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        min-height: 18px;
    }

    .maxball-chatbot__typing::before {
        content: 'Đang trả lời';
        margin-right: 3px;
    }

    .maxball-chatbot__typing i {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #819087;
        animation: maxball-chatbot-dot 1.1s infinite ease-in-out;
    }

    .maxball-chatbot__typing i:nth-child(2) { animation-delay: .15s; }
    .maxball-chatbot__typing i:nth-child(3) { animation-delay: .3s; }

    @keyframes maxball-chatbot-dot {
        0%, 60%, 100% { transform: translateY(0); opacity: .5; }
        30% { transform: translateY(-3px); opacity: 1; }
    }

    .maxball-chatbot__products {
        display: grid;
        width: 100%;
        gap: 9px;
    }

    .maxball-chatbot__product {
        display: grid;
        grid-template-columns: 70px minmax(0, 1fr);
        gap: 11px;
        overflow: hidden;
        padding: 9px;
        border: 1px solid #e1e7e3;
        border-radius: 14px;
        background: #fff;
    }

    .maxball-chatbot__product img {
        width: 70px;
        height: 76px;
        border-radius: 10px;
        background: #eef1ef;
        object-fit: cover;
    }

    .maxball-chatbot__product-body {
        display: flex;
        min-width: 0;
        flex-direction: column;
        align-items: flex-start;
    }

    .maxball-chatbot__product strong {
        display: -webkit-box;
        overflow: hidden;
        color: #10271d;
        font-size: 12px;
        line-height: 1.35;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .maxball-chatbot__product-price {
        margin-top: 4px;
        color: #d92525;
        font-size: 12px;
        font-weight: 800;
    }

    .maxball-chatbot__product a {
        margin-top: auto;
        color: #10271d;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
    }

    .maxball-chatbot__product a:hover {
        color: #d92525;
    }

    .maxball-chatbot__form {
        display: flex;
        align-items: flex-end;
        gap: 8px;
        flex: 0 0 auto;
        padding: 12px 13px 8px;
        border-top: 1px solid #e9edea;
        background: #fff;
    }

    .maxball-chatbot__form textarea {
        width: 100%;
        max-height: 104px;
        min-height: 42px;
        resize: none;
        overflow-y: auto;
        border: 1px solid #dce3df;
        border-radius: 13px;
        padding: 10px 12px;
        background: #f8faf9;
        color: #17251f;
        font: inherit;
        font-size: 13px;
        line-height: 1.45;
    }

    .maxball-chatbot__form textarea::placeholder {
        color: #8a9690;
    }

    .maxball-chatbot__send {
        display: grid;
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        place-items: center;
        border: 0;
        border-radius: 13px;
        background: #d92525;
        color: #fff;
        cursor: pointer;
    }

    .maxball-chatbot__send:disabled {
        cursor: wait;
        opacity: .5;
    }

    .maxball-chatbot__note {
        flex: 0 0 auto;
        margin: 0;
        padding: 0 14px 9px;
        background: #fff;
        color: #87928c;
        text-align: center;
        font-size: 9px;
    }

    @media (max-width: 520px) {
        .maxball-chatbot {
            right: 12px;
            bottom: 12px;
        }

        .maxball-chatbot__toggle {
            width: 54px;
            padding: 0;
            justify-content: center;
        }

        .maxball-chatbot__toggle span {
            display: none;
        }

        .maxball-chatbot__panel {
            position: fixed;
            right: 12px;
            bottom: 78px;
            left: 12px;
            width: auto;
            height: min(680px, calc(100dvh - 96px));
            min-height: 390px;
            border-radius: 18px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .maxball-chatbot__panel,
        .maxball-chatbot__toggle,
        .maxball-chatbot__typing i {
            animation: none;
            transition: none;
        }
    }
</style>

<script>
    (() => {
        const root = document.getElementById('maxball-chatbot');
        if (!root) return;

        const panel = root.querySelector('.maxball-chatbot__panel');
        const toggle = root.querySelector('.maxball-chatbot__toggle');
        const closeButton = root.querySelector('.maxball-chatbot__close');
        const messages = root.querySelector('.maxball-chatbot__messages');
        const form = root.querySelector('.maxball-chatbot__form');
        const input = root.querySelector('textarea');
        const sendButton = root.querySelector('.maxball-chatbot__send');
        const endpoint = root.dataset.endpoint;
        const csrfToken = root.dataset.csrfToken;
        let isSending = false;

        const getConversationId = () => {
            const storageKey = 'maxball_chatbot_conversation_id';
            try {
                let id = sessionStorage.getItem(storageKey);
                if (!id) {
                    id = window.crypto?.randomUUID?.().replaceAll('-', '')
                        || `${Date.now()}_${Math.random().toString(36).slice(2)}`;
                    sessionStorage.setItem(storageKey, id);
                }
                return id;
            } catch (_) {
                return `chat_${Date.now()}_${Math.random().toString(36).slice(2)}`;
            }
        };

        const conversationId = getConversationId();

        const scrollToLatest = () => {
            requestAnimationFrame(() => {
                messages.scrollTop = messages.scrollHeight;
            });
        };

        const appendFormattedBotText = (container, text) => {
            const normalizedText = String(text || '')
                .replace(/^\s*[-*]\s+/gm, '• ');
            const boldPattern = /\*\*(.+?)\*\*/gs;
            let cursor = 0;
            let match;

            while ((match = boldPattern.exec(normalizedText)) !== null) {
                const plainText = normalizedText.slice(cursor, match.index).replaceAll('**', '');
                container.append(document.createTextNode(plainText));

                const strong = document.createElement('strong');
                strong.textContent = match[1];
                container.append(strong);
                cursor = boldPattern.lastIndex;
            }

            container.append(document.createTextNode(
                normalizedText.slice(cursor).replaceAll('**', '')
            ));
        };

        const appendMessage = (text, type = 'bot') => {
            const bubble = document.createElement('div');
            bubble.className = `maxball-chatbot__message maxball-chatbot__message--${type}`;

            if (type === 'bot') {
                appendFormattedBotText(bubble, text);
            } else {
                bubble.textContent = String(text || '');
            }

            messages.appendChild(bubble);
            scrollToLatest();
            return bubble;
        };

        const appendLoading = () => {
            const bubble = document.createElement('div');
            bubble.className = 'maxball-chatbot__message maxball-chatbot__message--bot';
            bubble.setAttribute('aria-label', 'Đang trả lời');

            const typing = document.createElement('span');
            typing.className = 'maxball-chatbot__typing';
            typing.append(document.createElement('i'), document.createElement('i'), document.createElement('i'));
            bubble.appendChild(typing);
            messages.appendChild(bubble);
            scrollToLatest();
            return bubble;
        };

        const appendProducts = (products) => {
            if (!Array.isArray(products) || products.length === 0) return;

            const list = document.createElement('div');
            list.className = 'maxball-chatbot__products';

            products.slice(0, 10).forEach((product) => {
                const card = document.createElement('article');
                card.className = 'maxball-chatbot__product';

                const image = document.createElement('img');
                image.loading = 'lazy';
                image.alt = String(product.name || 'Sản phẩm MaxBall');
                image.src = String(product.image || '');
                image.addEventListener('error', () => image.style.visibility = 'hidden', { once: true });

                const body = document.createElement('div');
                body.className = 'maxball-chatbot__product-body';

                const name = document.createElement('strong');
                name.textContent = String(product.name || 'Sản phẩm MaxBall');

                const price = document.createElement('span');
                price.className = 'maxball-chatbot__product-price';
                const amount = Number(product.price);
                price.textContent = Number.isFinite(amount)
                    ? `${new Intl.NumberFormat('vi-VN').format(amount)} đ`
                    : 'Liên hệ';

                const link = document.createElement('a');
                link.href = String(product.url || '#');
                link.textContent = 'Xem sản phẩm →';

                body.append(name, price, link);
                card.append(image, body);
                list.appendChild(card);
            });

            messages.appendChild(list);
            scrollToLatest();
        };

        const resizeInput = () => {
            input.style.height = 'auto';
            input.style.height = `${Math.min(input.scrollHeight, 104)}px`;
        };

        const setOpen = (open) => {
            panel.hidden = !open;
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Đóng trợ lý MaxBall AI' : 'Mở trợ lý MaxBall AI');
            if (open) setTimeout(() => input.focus(), 50);
        };

        const setSending = (sending) => {
            isSending = sending;
            sendButton.disabled = sending;
            input.setAttribute('aria-busy', String(sending));
        };

        const sendMessage = async () => {
            const message = input.value.trim();
            if (!message || isSending) return;

            appendMessage(message, 'user');
            input.value = '';
            resizeInput();
            setSending(true);
            const loading = appendLoading();
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 55000);

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    credentials: 'same-origin',
                    signal: controller.signal,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        message,
                        conversation_id: conversationId,
                    }),
                });

                let data = null;
                try {
                    data = await response.json();
                } catch (_) {
                    data = null;
                }

                loading.remove();

                if (!response.ok || !data?.success) {
                    appendMessage(data?.message || 'Không thể kết nối trợ lý AI. Vui lòng thử lại sau.', 'error');
                    return;
                }

                appendMessage(data.message, 'bot');
                appendProducts(data.products);
            } catch (error) {
                loading.remove();
                const message = error?.name === 'AbortError'
                    ? 'Trợ lý phản hồi quá lâu. Vui lòng thử lại sau.'
                    : 'Mạng đang gián đoạn. Vui lòng kiểm tra kết nối và thử lại.';
                appendMessage(message, 'error');
            } finally {
                clearTimeout(timeout);
                setSending(false);
                input.focus();
            }
        };

        toggle.addEventListener('click', () => setOpen(panel.hidden));
        closeButton.addEventListener('click', () => setOpen(false));
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            sendMessage();
        });
        input.addEventListener('input', resizeInput);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
                event.preventDefault();
                sendMessage();
            }
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !panel.hidden) setOpen(false);
        });
    })();
</script>
