@php
    $hasPromotions = $promotionAnnouncements->isNotEmpty();
    $promotionCount = $promotionAnnouncements->count();
@endphp

<div class="maxball-client-actions" aria-label="Liên hệ và khuyến mãi">
    @if ($hasPromotions)
        <button
            id="maxball-promotion-toggle"
            type="button"
            class="maxball-client-action maxball-client-action--gift"
            aria-label="Xem thông báo khuyến mãi"
            aria-controls="maxball-promotion-modal"
            aria-expanded="false"
        >
            <i class="fa-solid fa-gift" aria-hidden="true"></i>
            <span class="maxball-client-action__label">{{ $promotionCount }} khuyến mãi</span>
        </button>
    @endif

    <a
        id="maxball-zalo-link"
        class="maxball-client-action maxball-client-action--zalo"
        href="https://zalo.me/0383846482"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Chat với MaxBall qua Zalo số 0383846482"
    >
        <span class="maxball-client-action__zalo-mark" aria-hidden="true">Zalo</span>
        <span class="maxball-client-action__label">Chat Zalo</span>
    </a>
</div>

@if ($hasPromotions)
    <div id="maxball-promotion-modal" class="maxball-promotion-modal" hidden>
        <section
            class="maxball-promotion-card"
            role="dialog"
            aria-modal="true"
            aria-label="Thông báo khuyến mãi MaxBall"
        >
            <button type="button" class="maxball-promotion-card__close" aria-label="Đóng thông báo khuyến mãi">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>

            <div class="maxball-promotion-card__icon" aria-hidden="true">
                <i class="fa-solid fa-gift"></i>
            </div>

            <div class="maxball-promotion-card__slides" aria-live="polite">
                @foreach ($promotionAnnouncements as $announcement)
                    <article
                        class="maxball-promotion-card__slide"
                        data-promotion-slide
                        aria-hidden="{{ $loop->first ? 'false' : 'true' }}"
                        @if (! $loop->first) hidden @endif
                    >
                        <p class="maxball-promotion-card__eyebrow">Khuyến mãi MaxBall · {{ $loop->iteration }}/{{ $promotionCount }}</p>
                        <h2>{{ $announcement->title }}</h2>
                        <div class="maxball-promotion-card__content">{{ $announcement->content }}</div>
                    </article>
                @endforeach
            </div>

            @if ($promotionCount > 1)
                <div class="maxball-promotion-card__navigation" aria-label="Chuyển thông báo khuyến mãi">
                    <button type="button" data-promotion-previous aria-label="Thông báo trước">
                        <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                    </button>
                    <span data-promotion-counter>1 / {{ $promotionCount }}</span>
                    <button type="button" data-promotion-next aria-label="Thông báo tiếp theo">
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            @endif
        </section>
    </div>
@endif

<style>
    body.maxball-promotion-open {
        overflow: hidden;
    }

    .maxball-client-actions {
        position: fixed;
        right: 22px;
        bottom: 86px;
        z-index: 9985;
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 10px;
        font-family: 'Be Vietnam Pro', sans-serif;
    }

    .maxball-client-action {
        position: relative;
        display: grid;
        width: 54px;
        height: 54px;
        place-items: center;
        border: 0;
        border-radius: 50%;
        box-shadow: 0 14px 32px rgba(7, 19, 14, .25);
        color: #fff;
        cursor: pointer;
        text-decoration: none;
        transition: transform .2s ease, box-shadow .2s ease;
    }

    .maxball-client-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 17px 36px rgba(7, 19, 14, .31);
    }

    .maxball-client-action:focus-visible,
    .maxball-promotion-card button:focus-visible {
        outline: 3px solid rgba(217, 37, 37, .3);
        outline-offset: 3px;
    }

    .maxball-client-action--gift {
        background: linear-gradient(145deg, #f7b928, #e75520);
        font-size: 21px;
    }

    .maxball-client-action--zalo {
        background: #0877e8;
    }

    .maxball-client-action__zalo-mark {
        font-size: 12px;
        font-weight: 900;
        letter-spacing: -.4px;
    }

    .maxball-client-action__label {
        position: absolute;
        right: 64px;
        top: 50%;
        width: max-content;
        max-width: 180px;
        padding: 7px 10px;
        border-radius: 9px;
        background: #10271d;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        opacity: 0;
        pointer-events: none;
        transform: translate(6px, -50%);
        transition: opacity .2s ease, transform .2s ease;
    }

    .maxball-client-action:hover .maxball-client-action__label,
    .maxball-client-action:focus-visible .maxball-client-action__label {
        opacity: 1;
        transform: translate(0, -50%);
    }

    .maxball-promotion-modal {
        position: fixed;
        inset: 0;
        z-index: 9992;
        display: grid;
        place-items: center;
        padding: 20px;
        background: rgba(7, 19, 14, .62);
        backdrop-filter: blur(5px);
        font-family: 'Be Vietnam Pro', sans-serif;
        animation: maxball-promotion-backdrop-in .2s ease-out;
    }

    .maxball-promotion-modal[hidden],
    .maxball-promotion-card__slide[hidden] {
        display: none !important;
    }

    .maxball-promotion-card {
        position: relative;
        width: min(500px, 100%);
        max-height: calc(100dvh - 40px);
        padding: 32px 32px 26px;
        overflow-y: auto;
        border: 1px solid rgba(217, 37, 37, .13);
        border-radius: 26px;
        background: #fffdf8;
        box-shadow: 0 28px 80px rgba(0, 0, 0, .36);
        color: #24352e;
        transform-origin: center;
        animation: maxball-promotion-card-in .24s ease-out;
    }

    .maxball-promotion-card::before {
        position: absolute;
        inset: 0 0 auto;
        height: 6px;
        background: linear-gradient(90deg, #d92525, #f7b928);
        content: '';
    }

    .maxball-promotion-card__close {
        position: absolute;
        top: 16px;
        right: 16px;
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border: 0;
        border-radius: 11px;
        background: #f4eee5;
        color: #65726c;
        cursor: pointer;
    }

    .maxball-promotion-card__icon {
        display: grid;
        width: 54px;
        height: 54px;
        margin-bottom: 17px;
        place-items: center;
        border-radius: 16px;
        background: #fff0d0;
        color: #d92525;
        font-size: 23px;
    }

    .maxball-promotion-card__eyebrow {
        margin: 0 0 7px;
        color: #d92525;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .13em;
        text-transform: uppercase;
    }

    .maxball-promotion-card h2 {
        margin: 0;
        padding-right: 30px;
        color: #10271d;
        font-size: 24px;
        font-weight: 800;
        line-height: 1.4;
    }

    .maxball-promotion-card__content {
        margin-top: 13px;
        color: #59665f;
        font-size: 14px;
        line-height: 1.75;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .maxball-promotion-card__navigation {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid #eee6da;
    }

    .maxball-promotion-card__navigation button {
        display: grid;
        width: 38px;
        height: 38px;
        place-items: center;
        border: 1px solid #e4dbcf;
        border-radius: 50%;
        background: #fff;
        color: #10271d;
        cursor: pointer;
        transition: background .2s ease, color .2s ease;
    }

    .maxball-promotion-card__navigation button:hover {
        background: #10271d;
        color: #fff;
    }

    .maxball-promotion-card__navigation span {
        min-width: 54px;
        color: #7a847f;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
    }

    @keyframes maxball-promotion-backdrop-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes maxball-promotion-card-in {
        from { opacity: 0; transform: translateY(14px) scale(.96); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    @media (max-width: 520px) {
        .maxball-client-actions {
            right: 12px;
            bottom: 78px;
        }

        .maxball-promotion-modal {
            padding: 12px;
        }

        .maxball-promotion-card {
            max-height: calc(100dvh - 24px);
            padding: 27px 22px 22px;
            border-radius: 22px;
        }

        .maxball-promotion-card h2 {
            font-size: 21px;
        }

        .maxball-client-action__label {
            display: none;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .maxball-client-action,
        .maxball-promotion-modal,
        .maxball-promotion-card {
            animation: none;
            transition: none;
        }
    }
</style>

@if ($hasPromotions)
    <script>
        (() => {
            const toggle = document.getElementById('maxball-promotion-toggle');
            const modal = document.getElementById('maxball-promotion-modal');
            const closeButton = modal?.querySelector('.maxball-promotion-card__close');
            const slides = [...(modal?.querySelectorAll('[data-promotion-slide]') || [])];
            const previousButton = modal?.querySelector('[data-promotion-previous]');
            const nextButton = modal?.querySelector('[data-promotion-next]');
            const counter = modal?.querySelector('[data-promotion-counter]');
            let currentIndex = 0;

            if (!toggle || !modal || !closeButton || slides.length === 0) return;

            const showSlide = (index) => {
                currentIndex = (index + slides.length) % slides.length;
                slides.forEach((slide, slideIndex) => {
                    const isCurrent = slideIndex === currentIndex;
                    slide.hidden = !isCurrent;
                    slide.setAttribute('aria-hidden', String(!isCurrent));
                });
                if (counter) counter.textContent = `${currentIndex + 1} / ${slides.length}`;
            };

            const setOpen = (open) => {
                modal.hidden = !open;
                toggle.setAttribute('aria-expanded', String(open));
                document.body.classList.toggle('maxball-promotion-open', open);
                if (open) {
                    showSlide(0);
                    closeButton.focus();
                }
            };

            toggle.addEventListener('click', () => setOpen(modal.hidden));
            closeButton.addEventListener('click', () => {
                setOpen(false);
                toggle.focus();
            });
            previousButton?.addEventListener('click', () => showSlide(currentIndex - 1));
            nextButton?.addEventListener('click', () => showSlide(currentIndex + 1));
            modal.addEventListener('click', (event) => {
                if (event.target === modal) setOpen(false);
            });
            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    setOpen(false);
                    toggle.focus();
                }
            });
        })();
    </script>
@endif
