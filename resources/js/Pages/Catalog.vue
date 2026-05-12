<script setup>
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import ProductDetailView from '../Components/ProductDetailView.vue';
import ProductsCard from '../Components/ProductsCard.vue';
import { useProductApi } from '../composables/useProductApi';

const {
    products,
    totalRecords,
    first,
    rows,
    loading,
    loadError,
    loadProducts,
    fetchProduct,
} = useProductApi({ defaultPerPage: 10 });

const selectedProductId = ref(null);
const detailLoading = ref(false);
const detailError = ref(null);
/** @type {import('vue').Ref<Record<string, unknown>|null>} */
const detailProduct = ref(null);

function onPage(event) {
    loadProducts(event);
}

async function openProduct(id) {
    selectedProductId.value = id;
    detailLoading.value = true;
    detailError.value = null;
    detailProduct.value = null;

    try {
        detailProduct.value = await fetchProduct(id);
    } catch (e) {
        detailError.value =
            e?.response?.data?.message ?? 'Не удалось загрузить товар';
    } finally {
        detailLoading.value = false;
    }
}

function backToList() {
    selectedProductId.value = null;
    detailProduct.value = null;
    detailError.value = null;
}

onMounted(() => {
    loadProducts({ page: 0, rows: rows.value });
});
</script>

<template>
    <AppLayout>
        <Head title="Каталог" />
        <ProductDetailView
            v-if="selectedProductId != null"
            :loading="detailLoading"
            :load-error="detailError"
            :product="detailProduct"
            @back="backToList"
        />
        <ProductsCard
            v-else
            v-model:first="first"
            :products="products"
            :total-records="totalRecords"
            :rows="rows"
            :loading="loading"
            :load-error="loadError"
            @page="onPage"
            @select-product="openProduct"
        />
    </AppLayout>
</template>
