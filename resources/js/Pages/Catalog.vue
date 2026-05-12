<script setup>
import { Head } from '@inertiajs/vue3';
import { nextTick, onMounted, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import ProductCreateForm from '../Components/ProductCreateForm.vue';
import ProductDetailView from '../Components/ProductDetailView.vue';
import ProductEditForm from '../Components/ProductEditForm.vue';
import ProductsCard from '../Components/ProductsCard.vue';
import { loadAuthUserFromStorage, useAuth } from '../composables/useAuth';
import { useProductApi } from '../composables/useProductApi';

loadAuthUserFromStorage();
const { isLoggedIn } = useAuth();

const {
    products,
    totalRecords,
    first,
    rows,
    loading,
    loadError,
    loadProducts,
    fetchProduct,
    createProduct,
    updateProduct,
    deleteProduct,
} = useProductApi({ defaultPerPage: 15 });

const selectedProductId = ref(null);
const showCreateForm = ref(false);
const showEditForm = ref(false);
const detailLoading = ref(false);
const detailError = ref(null);
/** @type {import('vue').Ref<Record<string, unknown>|null>} */
const detailProduct = ref(null);

function onPage(event) {
    loadProducts(event);
}

function reloadProductList() {
    const page = rows.value ? Math.floor(first.value / rows.value) : 0;
    loadProducts({ page, rows: rows.value });
}

async function openProduct(id) {
    showCreateForm.value = false;
    showEditForm.value = false;
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
    showEditForm.value = false;
}

function openCreateForm() {
    selectedProductId.value = null;
    detailProduct.value = null;
    detailError.value = null;
    showEditForm.value = false;
    showCreateForm.value = true;
}

function onEditFromDetail() {
    if (!detailProduct.value) {
        return;
    }
    showEditForm.value = true;
}

function closeEditForm() {
    showEditForm.value = false;
}

async function onProductUpdated() {
    showEditForm.value = false;
    if (selectedProductId.value != null) {
        await openProduct(selectedProductId.value);
    }
}

async function onDeleteFromDetail() {
    const id = selectedProductId.value;
    if (id == null) {
        return;
    }
    if (!window.confirm('Удалить этот товар?')) {
        return;
    }
    try {
        await deleteProduct(id);
        backToList();
        reloadProductList();
    } catch (e) {
        detailError.value =
            e?.response?.data?.message ?? 'Не удалось удалить товар';
    }
}

async function openProductForEdit(id) {
    await openProduct(id);
    await nextTick();
    if (detailProduct.value) {
        showEditForm.value = true;
    }
}

async function onDeleteFromList(id) {
    if (!window.confirm('Удалить этот товар?')) {
        return;
    }
    try {
        await deleteProduct(id);
        reloadProductList();
    } catch (e) {
        window.alert(
            e?.response?.data?.message ?? 'Не удалось удалить товар',
        );
    }
}

function closeCreateForm() {
    showCreateForm.value = false;
}

function onProductCreated() {
    showCreateForm.value = false;
    reloadProductList();
}

onMounted(() => {
    loadProducts({ page: 0, rows: rows.value });
});
</script>

<template>
    <AppLayout>
        <Head title="Каталог" />
        <ProductDetailView
            v-if="selectedProductId != null && !showEditForm"
            :loading="detailLoading"
            :load-error="detailError"
            :product="detailProduct"
            :can-manage="isLoggedIn"
            @back="backToList"
            @edit="onEditFromDetail"
            @delete="onDeleteFromDetail"
        />
        <ProductEditForm
            v-else-if="showEditForm && detailProduct"
            :initial-product="detailProduct"
            :update-product="updateProduct"
            @cancel="closeEditForm"
            @saved="onProductUpdated"
        />
        <ProductCreateForm
            v-else-if="showCreateForm"
            :create-product="createProduct"
            @cancel="closeCreateForm"
            @saved="onProductCreated"
        />
        <ProductsCard
            v-else
            v-model:first="first"
            :products="products"
            :total-records="totalRecords"
            :rows="rows"
            :loading="loading"
            :load-error="loadError"
            :show-add-button="isLoggedIn"
            :show-id-column="isLoggedIn"
            :show-manage-column="isLoggedIn"
            @page="onPage"
            @select-product="openProduct"
            @add="openCreateForm"
            @manage-edit="openProductForEdit"
            @manage-delete="onDeleteFromList"
        />
    </AppLayout>
</template>
