<script setup>
import Button from 'primevue/button';
import ProgressSpinner from 'primevue/progressspinner';

defineProps({
    loading: {
        type: Boolean,
        required: true,
    },
    loadError: {
        default: null,
        validator: (v) => v == null || typeof v === 'string',
    },
    /** @type {import('vue').PropType<Record<string, unknown>|null>} */
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['back']);
</script>

<template>
    <section class="product-detail-page">
        <div class="product-detail-page__toolbar">
            <Button
                type="button"
                label="← Назад к списку"
                severity="secondary"
                outlined
                class="product-detail-page__back"
                @click="emit('back')"
            />
        </div>

        <div v-if="loading" class="product-detail-page__loading">
            <ProgressSpinner
                stroke-width="4"
                class="product-detail-page__spinner"
                aria-label="Загрузка"
            />
        </div>
        <p v-else-if="loadError" class="product-detail-page__error">
            {{ loadError }}
        </p>
        <div v-else-if="product" class="product-detail-page__card">
            <h1 class="product-detail-page__title">{{ product.name }}</h1>
            <dl class="product-detail-page__grid">
                <dt>ID</dt>
                <dd>{{ product.id }}</dd>
                <dt>Категория</dt>
                <dd>
                    <span v-if="product.category?.name" class="category-badge">{{
                        product.category.name
                    }}</span>
                    <span v-else class="category-empty">—</span>
                </dd>
                <dt>Цена</dt>
                <dd class="product-detail-page__price">{{ product.price }}</dd>
                <dt class="product-detail-page__full">Описание</dt>
                <dd class="product-detail-page__full product-detail-page__description">
                    {{ product.description ?? '—' }}
                </dd>
                <dt v-if="product.created_at">Создан</dt>
                <dd v-if="product.created_at">{{ product.created_at }}</dd>
                <dt v-if="product.updated_at">Обновлён</dt>
                <dd v-if="product.updated_at">{{ product.updated_at }}</dd>
            </dl>
        </div>
    </section>
</template>

<style scoped>
.product-detail-page {
    max-width: 40rem;
    margin: 0 auto;
}

.product-detail-page__toolbar {
    margin-bottom: 1.25rem;
}

.product-detail-page__back {
    font-weight: 600;
}

.product-detail-page__loading {
    display: flex;
    justify-content: center;
    padding: 3rem 0;
}

.product-detail-page__spinner {
    width: 3rem;
    height: 3rem;
}

.product-detail-page__error {
    margin: 0;
    border-radius: 0.5rem;
    border: 1px solid #fecaca;
    background: #fef2f2;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    line-height: 1.45;
    color: #991b1b;
}

.product-detail-page__card {
    overflow: hidden;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}

.product-detail-page__title {
    margin: 0 0 1.25rem;
    font-size: 1.375rem;
    font-weight: 700;
    line-height: 1.25;
    color: #0f172a;
}

.product-detail-page__grid {
    display: grid;
    grid-template-columns: 7rem 1fr;
    gap: 0.5rem 1rem;
    margin: 0;
    font-size: 0.875rem;
}

.product-detail-page__grid dt {
    margin: 0;
    font-weight: 600;
    color: #64748b;
}

.product-detail-page__grid dd {
    margin: 0;
    color: #0f172a;
    word-break: break-word;
}

.product-detail-page__price {
    font-weight: 600;
    text-align: right;
}

.product-detail-page__full {
    grid-column: 1 / -1;
}

.product-detail-page__description {
    white-space: pre-wrap;
}

.category-badge {
    display: inline-block;
    max-width: 100%;
    padding: 0.2rem 0.55rem;
    border-radius: 9999px;
    background: #ede9fe;
    color: #5b21b6;
    font-size: 0.8125rem;
    font-weight: 600;
    line-height: 1.25;
}

.category-empty {
    color: #94a3b8;
    font-size: 0.875rem;
}
</style>
