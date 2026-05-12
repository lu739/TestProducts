<script setup>
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';

const priceAlign = { textAlign: 'right' };

defineProps({
    title: {
        type: String,
        default: 'Товары',
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

const emit = defineEmits(['page', 'select-product']);

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
        <h2 class="product-card__title">{{ title }}</h2>
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
            :rows-per-page-options="rowsPerPageOptions"
            paginator-template="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink RowsPerPageDropdown CurrentPageReport"
            current-page-report-template="{first}–{last} из {totalRecords}"
            class="product-card__table product-card__table--clickable text-sm"
            @page="emit('page', $event)"
            @row-click="onRowClick"
        >
            <Column field="id" header="ID" style="width: 4rem" />
            <Column field="name" header="Название" />
            <Column field="description" header="Описание">
                <template #body="{ data }">
                    <span class="line-clamp-2 max-w-md">{{
                        data.description ?? '—'
                    }}</span>
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
                header="Цена"
                style="width: 7rem"
                :header-style="priceAlign"
                :body-style="priceAlign"
            >
                <template #body="{ data }">
                    {{ data.price }}
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

.product-card__title {
    margin: 0 0 1rem;
    font-size: 1.125rem;
    font-weight: 600;
    line-height: 1.3;
    color: #0f172a;
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

.product-card__table :deep(.p-datatable-thead > tr > th) {
    padding-top: 0.35rem;
    padding-bottom: 0.35rem;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
    font-size: 0.8125rem;
    line-height: 1.2;
    vertical-align: middle;
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
</style>
