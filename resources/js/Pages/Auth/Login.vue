<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import { route } from 'ziggy-js';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import { setAuthApiUser } from '../../composables/useAuth';

const form = ref({
    email: '',
    password: '',
});

const errors = ref({});
const message = ref('');
const processing = ref(false);

async function submit() {
    errors.value = {};
    message.value = '';
    processing.value = true;

    try {
        const { data } = await axios.post(route('api.login'), {
            email: form.value.email,
            password: form.value.password,
        });

        if (data.token) {
            localStorage.setItem('api_token', data.token);
            window.axios.defaults.headers.common.Authorization = `Bearer ${data.token}`;
        }
        if (data.user) {
            setAuthApiUser(data.user);
        }

        router.visit('/');
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors ?? {};
        } else {
            message.value =
                e.response?.data?.message ?? 'Не удалось выполнить вход.';
        }
    } finally {
        processing.value = false;
    }
}
</script>

<template>
    <AppLayout>
        <Head title="Вход" />
        <div class="auth-card">
            <h1 class="auth-card__title">Вход</h1>
            <p v-if="message" class="auth-card__message auth-card__message--error">
                {{ message }}
            </p>
            <form class="auth-card__form" @submit.prevent="submit">
                <label class="auth-field">
                    <span class="auth-field__label">Email</span>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        class="auth-field__input"
                    />
                    <span v-if="errors.email" class="auth-field__error">{{
                        errors.email[0]
                    }}</span>
                </label>
                <label class="auth-field">
                    <span class="auth-field__label">Пароль</span>
                    <input
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                        class="auth-field__input"
                    />
                    <span v-if="errors.password" class="auth-field__error">{{
                        errors.password[0]
                    }}</span>
                </label>
                <Button
                    type="submit"
                    label="Войти"
                    class="auth-card__submit"
                    :loading="processing"
                />
            </form>
            <p class="auth-card__footer">
                Нет аккаунта?
                <Link href="/register" class="auth-card__link">Регистрация</Link>
            </p>
        </div>
    </AppLayout>
</template>

<style scoped>
.auth-card {
    max-width: 24rem;
    margin: 0 auto;
    padding: 1.75rem;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

:global(.dark) .auth-card {
    border-color: #334155;
    background: #1e293b;
}

.auth-card__title {
    margin: 0 0 1.25rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
}

:global(.dark) .auth-card__title {
    color: #f1f5f9;
}

.auth-card__message {
    margin: 0 0 1rem;
    font-size: 0.875rem;
}

.auth-card__message--error {
    color: #b91c1c;
}

.auth-card__form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.auth-field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.auth-field__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #475569;
}

:global(.dark) .auth-field__label {
    color: #94a3b8;
}

.auth-field__input {
    width: 100%;
    box-sizing: border-box;
    padding: 0.5rem 0.65rem;
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    font-size: 0.9375rem;
}

:global(.dark) .auth-field__input {
    border-color: #475569;
    background: #0f172a;
    color: #f1f5f9;
}

.auth-field__error {
    font-size: 0.75rem;
    color: #b91c1c;
}

.auth-card__submit {
    width: 100%;
    margin-top: 0.25rem;
}

.auth-card__footer {
    margin: 1.25rem 0 0;
    font-size: 0.875rem;
    color: #64748b;
}

.auth-card__link {
    color: #7c3aed;
    font-weight: 600;
    text-decoration: none;
}

.auth-card__link:hover {
    text-decoration: underline;
}
</style>
