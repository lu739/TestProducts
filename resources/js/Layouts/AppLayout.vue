<script setup>
import { Link } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import { useAuth } from '../composables/useAuth';

const {
    user: currentUser,
    primaryLabel,
    emailLine,
    ensureRemoteUser,
    logout,
} = useAuth();

onMounted(() => {
    void ensureRemoteUser();
});
</script>

<template>
    <div class="layout">
        <header class="layout__header">
            <div class="layout__header-inner">
                <div class="layout__brand-wrap">
                    <div class="layout__logo">
                        <img
                            src="/images/products-wordmark.jpg"
                            alt="products"
                            class="layout__logo-img"
                            decoding="async"
                        />
                    </div>
                    <nav class="layout__site-links" aria-label="Разделы">
                        <Link href="/" class="layout__site-link">Главная</Link>
                    </nav>
                </div>
                <nav class="layout__auth" aria-label="Аккаунт">
                    <template v-if="!currentUser">
                        <div class="layout__auth-actions">
                            <Link href="/login" class="layout__auth-btn layout__auth-btn--ghost">
                                Вход
                            </Link>
                            <Link
                                href="/register"
                                class="layout__auth-btn layout__auth-btn--primary"
                            >
                                Регистрация
                            </Link>
                        </div>
                    </template>
                    <template v-else>
                        <div class="layout__user-panel">
                            <span class="layout__user-name">{{ primaryLabel }}</span>
                            <span v-if="emailLine" class="layout__user-email">{{
                                emailLine
                            }}</span>
                        </div>
                        <button
                            type="button"
                            class="layout__site-link layout__site-link--reset"
                            @click="logout"
                        >
                            Выйти
                        </button>
                    </template>
                </nav>
            </div>
        </header>

        <div class="layout__body">
            <main class="layout__main">
                <slot />
            </main>
        </div>

        <footer class="layout__footer">
            <span class="layout__footer-text">TestProducts by LiDusha 2026</span>
        </footer>
    </div>
</template>

<style scoped>
.layout {
    --layout-primary: #7c3aed;
    --layout-primary-hover: #6d28d9;
    --layout-border: #d5d7dd;
    --layout-muted: #64748b;
    --layout-user-text: #334155;

    display: flex;
    flex-direction: column;
    min-height: 100vh;
    background: #f8fafc;
    color: #0f172a;
}

:global(.dark) .layout {
    background: #0f172a;
    color: #f1f5f9;
    --layout-border: #334155;
    --layout-muted: #94a3b8;
    --layout-user-text: #cbd5e1;
}

.layout__header {
    flex-shrink: 0;
    position: sticky;
    top: 0;
    z-index: 50;
    background: #fff;
    border-bottom: 1px solid var(--layout-border);
    box-shadow: 0 1px 0 rgba(0, 0, 0, 0.04);
}

:global(.dark) .layout__header {
    background: #1e293b;
}

.layout__header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    max-width: 112rem;
    margin: 0 auto;
    padding: 0.65rem clamp(1rem, 2vw, 1.5rem);
    min-height: 5.35rem;
    box-sizing: border-box;
}

.layout__brand-wrap {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    min-width: 0;
}

.layout__logo {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    user-select: none;
    pointer-events: none;
}

.layout__logo-img {
    display: block;
    height: clamp(4.45rem, 11.5vw, 6.45rem);
    width: auto;
    max-width: min(30rem, 94vw);
    object-fit: contain;
}

.layout__site-links {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
}

.layout__site-link {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--layout-primary);
    text-decoration: none;
}

.layout__site-link:hover {
    color: var(--layout-primary-hover);
}

.layout__site-link--reset {
    border: none;
    background: none;
    padding: 0;
    cursor: pointer;
    font: inherit;
}

.layout__auth {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.5rem 0.75rem;
    flex-wrap: wrap;
}

.layout__auth-actions {
    display: flex;
    align-items: center;
    gap: 0.35rem 0.5rem;
    flex-wrap: wrap;
}

.layout__auth-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.95rem;
    border-radius: 0.5rem;
    font-size: 0.9375rem;
    font-weight: 600;
    text-decoration: none;
    line-height: 1.2;
    border: 1px solid transparent;
    transition:
        background 0.15s,
        color 0.15s,
        border-color 0.15s;
}

.layout__auth-btn--ghost {
    color: var(--layout-primary);
    background: transparent;
    border-color: transparent;
}

.layout__auth-btn--ghost:hover {
    color: var(--layout-primary-hover);
    background: rgba(124, 58, 237, 0.08);
}

.layout__auth-btn--primary {
    color: #fff;
    background: var(--layout-primary);
    border-color: var(--layout-primary);
}

.layout__auth-btn--primary:hover {
    background: var(--layout-primary-hover);
    border-color: var(--layout-primary-hover);
}

.layout__user-panel {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 0.1rem;
    margin-inline-end: 1.35rem;
    min-width: 0;
    max-width: min(20rem, 50vw);
    text-align: right;
}

.layout__user-name {
    font-size: 0.9375rem;
    font-weight: 600;
    color: var(--layout-user-text);
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.layout__user-email {
    font-size: 0.8125rem;
    font-weight: 500;
    color: var(--layout-user-text);
    opacity: 0.92;
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.layout__body {
    flex: 1;
    width: 100%;
    max-width: 112rem;
    margin: 0 auto;
    padding: 1.25rem clamp(1rem, 2vw, 1.5rem) 2rem;
    box-sizing: border-box;
}

.layout__main {
    min-width: 0;
}

.layout__footer {
    flex-shrink: 0;
    padding: 1rem 1rem 1.25rem;
    text-align: center;
    color: var(--layout-muted);
    border-top: 1px solid var(--layout-border);
    background: #fff;
}

.layout__footer-text {
    font-size: 1.0625rem;
    font-weight: 500;
    letter-spacing: 0.01em;
}

:global(.dark) .layout__footer {
    background: #1e293b;
}

@media (max-width: 520px) {
    .layout__auth-btn {
        padding: 0.4rem 0.65rem;
        font-size: 0.8125rem;
    }

    .layout__user-panel {
        max-width: min(12rem, 42vw);
    }
}
</style>
