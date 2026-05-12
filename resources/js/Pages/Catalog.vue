<script setup>
import { Head } from '@inertiajs/vue3';
import { nextTick, onMounted, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import ProductCreateForm from '../Components/ProductCreateForm.vue';
import ProductDeleteConfirmModal from '../Components/ProductDeleteConfirmModal.vue';
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
    softDeleteProduct,
    forceDeleteProduct,
    restoreProduct,
} = useProductApi({ defaultPerPage: 15 });

const selectedProductId = ref(null);
const showCreateForm = ref(false);
const showEditForm = ref(false);
const detailLoading = ref(false);
const detailError = ref(null);
/** @type {import('vue').Ref<Record<string, unknown>|null>} */
const detailProduct = ref(null);

const deleteModalVisible = ref(false);
/** @type {import('vue').Ref<number|null>} */
const deleteTargetId = ref(null);
const deleteFromDetail = ref(false);
/** @type {import('vue').Ref<'normal'|'restore'|'force'>} */
const deleteModalVariant = ref('normal');
const deleteProcessing = ref(false);

function onPage(event) {
    loadProducts(event);
}

function reloadProductList() {
    const page = rows.value ? Math.floor(first.value / rows.value) : 0;
    return loadProducts({ page, rows: rows.value });
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
    if (!detailProduct.value || detailProduct.value.deleted_at) {
        return;
    }
    showEditForm.value = true;
}

function closeEditForm() {
    showEditForm.value = false;
}

async function onProductUpdated() {
    showEditForm.value = false;
    await reloadProductList();
    if (selectedProductId.value != null) {
        await openProduct(selectedProductId.value);
    }
}

function onDeleteFromDetail() {
    const id = selectedProductId.value;
    if (id == null) {
        return;
    }
    if (detailProduct.value?.deleted_at) {
        openForceDeleteModal(id, true);
    } else {
        openDeleteModal(id, true);
    }
}

function onRestoreFromDetail() {
    const id = selectedProductId.value;
    if (id == null) {
        return;
    }
    openRestoreModal(id, true);
}

async function openProductForEdit(id) {
    await openProduct(id);
    await nextTick();
    if (detailProduct.value && !detailProduct.value.deleted_at) {
        showEditForm.value = true;
    }
}

function onDeleteFromList(id) {
    const row = products.value.find((p) => p.id === id);
    if (row?.deleted_at) {
        openForceDeleteModal(id, false);
    } else {
        openDeleteModal(id, false);
    }
}

function onManageRestoreFromList(id) {
    openRestoreModal(id, false);
}

function openDeleteModal(id, fromDetail) {
    deleteModalVariant.value = 'normal';
    deleteTargetId.value = id;
    deleteFromDetail.value = fromDetail;
    detailError.value = null;
    deleteModalVisible.value = true;
}

function openRestoreModal(id, fromDetail) {
    deleteModalVariant.value = 'restore';
    deleteTargetId.value = id;
    deleteFromDetail.value = fromDetail;
    detailError.value = null;
    deleteModalVisible.value = true;
}

function openForceDeleteModal(id, fromDetail) {
    deleteModalVariant.value = 'force';
    deleteTargetId.value = id;
    deleteFromDetail.value = fromDetail;
    detailError.value = null;
    deleteModalVisible.value = true;
}

async function confirmSoftDelete() {
    const id = deleteTargetId.value;
    if (id == null) {
        return;
    }
    deleteProcessing.value = true;
    try {
        await softDeleteProduct(id);
        deleteModalVisible.value = false;
        deleteTargetId.value = null;
        deleteModalVariant.value = 'normal';
        if (deleteFromDetail.value) {
            backToList();
        }
        reloadProductList();
    } catch (e) {
        const msg =
            e?.response?.data?.message ?? 'Не удалось скрыть товар';
        if (deleteFromDetail.value) {
            detailError.value = msg;
        } else {
            window.alert(msg);
        }
    } finally {
        deleteProcessing.value = false;
    }
}

async function confirmForceDelete() {
    const id = deleteTargetId.value;
    if (id == null) {
        return;
    }
    deleteProcessing.value = true;
    try {
        await forceDeleteProduct(id);
        deleteModalVisible.value = false;
        deleteTargetId.value = null;
        deleteModalVariant.value = 'normal';
        if (deleteFromDetail.value) {
            backToList();
        }
        reloadProductList();
    } catch (e) {
        const msg =
            e?.response?.data?.message ?? 'Не удалось удалить товар';
        if (deleteFromDetail.value) {
            detailError.value = msg;
        } else {
            window.alert(msg);
        }
    } finally {
        deleteProcessing.value = false;
    }
}

async function confirmRestore() {
    const id = deleteTargetId.value;
    if (id == null) {
        return;
    }
    deleteProcessing.value = true;
    try {
        await restoreProduct(id);
        deleteModalVisible.value = false;
        deleteTargetId.value = null;
        deleteModalVariant.value = 'normal';
        if (deleteFromDetail.value) {
            await openProduct(id);
        }
        reloadProductList();
    } catch (e) {
        const msg =
            e?.response?.data?.message ?? 'Не удалось восстановить товар';
        if (deleteFromDetail.value) {
            detailError.value = msg;
        } else {
            window.alert(msg);
        }
    } finally {
        deleteProcessing.value = false;
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
            @restore="onRestoreFromDetail"
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
            @manage-restore="onManageRestoreFromList"
        />
        <ProductDeleteConfirmModal
            v-model:visible="deleteModalVisible"
            :processing="deleteProcessing"
            :variant="deleteModalVariant"
            @soft="confirmSoftDelete"
            @restore="confirmRestore"
            @force="confirmForceDelete"
        />
    </AppLayout>
</template>
