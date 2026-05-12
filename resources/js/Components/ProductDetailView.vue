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
    canManage: {
        type: Boolean,
        default: false,
    },
    /** @type {import('vue').PropType<Record<string, unknown>|null>} */
    product: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['back', 'edit', 'delete', 'restore']);
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
            <div
                v-if="canManage && product && !loading && !loadError"
                class="product-detail-page__actions"
            >
                <Button
                    v-if="!product.deleted_at"
                    type="button"
                    label="Редактировать"
                    severity="secondary"
                    outlined
                    size="small"
                    class="product-detail-page__edit-btn"
                    @click="emit('edit')"
                />
                <Button
                    v-if="product.deleted_at"
                    type="button"
                    label="Восстановить"
                    severity="success"
                    outlined
                    size="small"
                    class="product-detail-page__restore-btn"
                    @click="emit('restore')"
                />
                <Button
                    type="button"
                    label="Удалить"
                    severity="danger"
                    outlined
                    size="small"
                    @click="emit('delete')"
                />
            </div>
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
            <div class="product-detail-page__title-row">
                <h1 class="product-detail-page__title">{{ product.name }}</h1>
                <span
                    v-if="product.deleted_at"
                    class="product-detail-page__hidden-badge"
                    >Скрыт</span>
            </div>
            <dl class="product-detail-page__grid">
                <template v-if="canManage">
                    <dt>ID</dt>
                    <dd>{{ product.id }}</dd>
                </template>
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
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem 1rem;
    margin-bottom: 1.25rem;
}

.product-detail-page__back {
    font-weight: 600;
}

.product-detail-page__actions {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem;
}

.product-detail-page__edit-btn :deep(.p-button-label) {
    color: #7c3aed;
    font-weight: 600;
}

.product-detail-page__edit-btn:hover :deep(.p-button-label) {
    color: #6d28d9;
}

.product-detail-page__restore-btn :deep(.p-button-label) {
    color: #15803d;
    font-weight: 600;
}

.product-detail-page__restore-btn:hover :deep(.p-button-label) {
    color: #166534;
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

.product-detail-page__title-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 0.5rem 0.75rem;
    margin-bottom: 1.25rem;
}

.product-detail-page__title-row .product-detail-page__title {
    margin: 0;
    flex: 1;
    min-width: 0;
}

.product-detail-page__hidden-badge {
    flex-shrink: 0;
    border-radius: 9999px;
    padding: 0.2rem 0.6rem;
    font-size: 0.75rem;
    font-weight: 600;
    line-height: 1.25;
    background: #e2e8f0;
    color: #475569;
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
