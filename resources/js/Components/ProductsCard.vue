<script setup>
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

const priceAlign = { textAlign: 'right' };

defineProps({
    title: {
        type: String,
        default: 'Товары',
    },
    showAddButton: {
        type: Boolean,
        default: false,
    },
    showIdColumn: {
        type: Boolean,
        default: false,
    },
    showManageColumn: {
        type: Boolean,
        default: false,
    },
    loadError: {
        default: null,
        validator: (v) => v == null || typeof v === 'string',
    },
    products: {
        type: Array,
        required: true,
    },
    totalRecords: {
        type: Number,
        required: true,
    },
    loading: {
        type: Boolean,
        required: true,
    },
    rows: {
        type: Number,
        required: true,
    },
    rowsPerPageOptions: {
        type: Array,
        default: () => [10, 12, 15],
    },
});

const first = defineModel('first', { type: Number, required: true });

const emit = defineEmits([
    'page',
    'select-product',
    'add',
    'manage-edit',
    'manage-delete',
    'manage-restore',
]);

/** @param {Record<string, unknown>} data */
function rowClass(data) {
    return data?.deleted_at ? 'product-card__row--inactive' : '';
}

function onRowClick(event) {
    const id = event.data?.id;
    if (id == null) {
        return;
    }
    emit('select-product', id);
}
</script>

<template>
    <section class="product-card">
        <div class="product-card__head">
            <h2 class="product-card__title">{{ title }}</h2>
            <Button
                v-if="showAddButton"
                type="button"
                label="Добавить"
                size="small"
                class="product-card__add-btn"
                @click="emit('add')"
            />
        </div>
        <p v-if="loadError" class="product-card__error">
            {{ loadError }}
        </p>
        <DataTable
            v-model:first="first"
            :value="products"
            lazy
            paginator
            :rows="rows"
            :total-records="totalRecords"
            :loading="loading"
            data-key="id"
            striped-rows
            :row-class="rowClass"
            :rows-per-page-options="rowsPerPageOptions"
            paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
            current-page-report-template="{first}–{last} из {totalRecords}"
            class="product-card__table product-card__table--clickable text-sm"
            @page="emit('page', $event)"
            @row-click="onRowClick"
        >
            <Column
                v-if="showIdColumn"
                field="id"
                header="ID"
                style="width: 4rem"
            />
            <Column field="name" header="Название">
                <template #body="{ data }">
                    <div class="product-card__name-cell">
                        <span class="product-card__name-text">{{ data.name }}</span>
                        <span
                            v-if="data.deleted_at"
                            class="product-card__hidden-badge"
                            >Скрыт</span>
                    </div>
                </template>
            </Column>
            <Column header="Категория" style="width: 10rem">
                <template #body="{ data }">
                    <span v-if="data.category?.name" class="category-badge">{{
                        data.category.name
                    }}</span>
                    <span v-else class="category-empty">—</span>
                </template>
            </Column>
            <Column
                field="price"
                style="width: 7rem"
                :body-style="priceAlign"
            >
                <template #header>
                    <span class="product-card__price-header">Цена</span>
                </template>
                <template #body="{ data }">
                    {{ data.price }}
                </template>
            </Column>
            <Column field="description" header="Описание">
                <template #body="{ data }">
                    <span class="line-clamp-2 max-w-md">{{
                        data.description ?? '—'
                    }}</span>
                </template>
            </Column>
            <Column
                v-if="showManageColumn"
                header="Управление товарами"
                style="width: 6.5rem"
                :body-style="{ textAlign: 'left', verticalAlign: 'middle' }"
            >
                <template #body="{ data }">
                    <div class="product-card__manage" @click.stop>
                        <div class="product-card__manage-cell">
                            <button
                                v-if="!data.deleted_at"
                                type="button"
                                class="product-card__icon-btn product-card__icon-btn--edit"
                                v-tooltip.top="'Редактировать'"
                                aria-label="Редактировать"
                                @click.stop="emit('manage-edit', data.id)"
                            >
                                <i class="pi pi-pencil" aria-hidden="true" />
                            </button>
                            <button
                                v-else
                                type="button"
                                class="product-card__icon-btn product-card__icon-btn--restore"
                                v-tooltip.top="'Вернуть товар в каталог (отменить скрытие)'"
                                aria-label="Восстановить товар"
                                @click.stop="emit('manage-restore', data.id)"
                            >
                                <i class="pi pi-undo" aria-hidden="true" />
                            </button>
                        </div>
                        <div class="product-card__manage-cell">
                            <button
                                type="button"
                                class="product-card__icon-btn product-card__icon-btn--delete"
                                v-tooltip.top="'Удалить'"
                                aria-label="Удалить"
                                @click.stop="emit('manage-delete', data.id)"
                            >
                                <i class="pi pi-trash" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </template>
            </Column>
        </DataTable>
    </section>
</template>

<style scoped>
.product-card {
    overflow: hidden;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 1rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}

.product-card__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.product-card__title {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 700;
    line-height: 1.2;
    color: #0f172a;
    flex: 1;
    min-width: 0;
}

.product-card__add-btn {
    flex-shrink: 0;
}

.product-card__error {
    margin: 0 0 0.75rem;
    border-radius: 0.5rem;
    border: 1px solid #fecaca;
    background: #fef2f2;
    padding: 0.5rem 0.75rem;
    font-size: 0.875rem;
    line-height: 1.4;
    color: #991b1b;
}

.product-card__table {
    border-radius: 0.5rem;
}

/* лишняя линия под блоком пагинации (тема PrimeVue / Aura) */
.product-card__table :deep(.p-datatable-table-container) {
    border-bottom: none;
}

.product-card__table :deep(.p-datatable-paginator-bottom) {
    border-top: none;
    border-bottom: none;
}

.product-card__table :deep(.p-datatable-paginator-bottom .p-paginator) {
    border-top: none;
    border-bottom: none;
    box-shadow: none;
}

.product-card__table :deep(.p-datatable) {
    border-bottom: none;
}

.product-card__table--clickable :deep(.p-datatable-tbody > tr) {
    cursor: pointer;
}

.product-card__table--clickable :deep(.p-datatable-tbody > tr:hover) {
    background: #f8fafc;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive) {
    cursor: default;
    color: #9ca3af;
    background: #e8eaed;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive td) {
    color: #9ca3af;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive:hover) {
    background: #e8eaed;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive .category-badge) {
    opacity: 1;
    background: #d8dce4;
    color: #6b7280;
    border: 1px solid #c4c9d4;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive .category-empty) {
    color: #b0b8c4;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive .product-card__icon-btn--delete) {
    color: #c45c5c;
    opacity: 0.92;
}

.product-card__table :deep(.p-datatable-thead > tr > th) {
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    font-size: 0.8125rem;
    line-height: 1.2;
    vertical-align: middle;
}

.product-card__price-header {
    display: block;
    width: 100%;
    text-align: right;
    font-weight: 600;
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
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

.category-empty {
    color: #94a3b8;
    font-size: 0.875rem;
}

.product-card__name-cell {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.35rem 0.5rem;
    min-width: 0;
}

.product-card__name-text {
    word-break: break-word;
    min-width: 0;
}

.product-card__hidden-badge {
    display: inline-flex;
    flex-shrink: 0;
    align-items: center;
    padding: 0.18rem 0.5rem;
    border-radius: 9999px;
    border: 1px solid #cbd5e1;
    background: #e2e8f0;
    font-size: 0.6875rem;
    font-weight: 600;
    line-height: 1.2;
    letter-spacing: 0.02em;
    color: #64748b;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive .product-card__hidden-badge) {
    border-color: #c4c9d4;
    background: #dce1e8;
    color: #5b6573;
}

.product-card__manage {
    display: inline-grid;
    grid-template-columns: 2.1rem 2.1rem;
    align-items: center;
    gap: 0.35rem;
}

.product-card__manage-cell {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 2.1rem;
    min-height: 2.1rem;
}

.product-card__icon-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0;
    padding: 0.35rem;
    border: none;
    border-radius: 0.35rem;
    background: none;
    cursor: pointer;
    line-height: 1;
    transition: background 0.15s ease;
}

.product-card__icon-btn .pi {
    font-size: 1.05rem;
}

.product-card__icon-btn--edit {
    color: #7c3aed;
}

.product-card__icon-btn--edit:hover {
    background: rgba(124, 58, 237, 0.12);
}

.product-card__icon-btn--restore {
    color: #15803d;
}

.product-card__icon-btn--restore:hover {
    background: rgba(22, 163, 74, 0.14);
    color: #166534;
}

.product-card__table :deep(.p-datatable-tbody > tr.product-card__row--inactive .product-card__icon-btn--restore) {
    color: #16a34a;
}

.product-card__table
    :deep(
        .p-datatable-tbody
            > tr.product-card__row--inactive
            .product-card__icon-btn--restore:hover
    ) {
    background: rgba(22, 163, 74, 0.18);
    color: #14532d;
}

.product-card__icon-btn--delete {
    color: #b91c1c;
}

.product-card__icon-btn--delete:hover {
    background: rgba(185, 28, 28, 0.1);
}

</style>
