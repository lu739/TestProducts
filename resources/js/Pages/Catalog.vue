<script setup>
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import InputText from 'primevue/inputtext';
import CategoryFilterSelect from 'primevue/select';
import { route } from 'ziggy-js';
import { nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
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

/** @type {import('vue').Ref<number|null>} */
const filterCategoryId = ref(null);
const searchQuery = ref('');
const categories = ref([]);
const categoriesLoading = ref(true);
const categoriesError = ref(null);

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
} = useProductApi({
    defaultPerPage: 15,
    categoryFilterId: filterCategoryId,
    searchQuery,
});

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

watch(filterCategoryId, () => {
    first.value = 0;
    loadProducts({ page: 0, rows: rows.value });
});

const SEARCH_DEBOUNCE_MS = 350;
/** @type {ReturnType<typeof setTimeout>|null} */
let searchDebounceId = null;

watch(searchQuery, () => {
    if (searchDebounceId != null) {
        clearTimeout(searchDebounceId);
    }
    searchDebounceId = setTimeout(() => {
        searchDebounceId = null;
        first.value = 0;
        loadProducts({ page: 0, rows: rows.value });
    }, SEARCH_DEBOUNCE_MS);
});

onUnmounted(() => {
    if (searchDebounceId != null) {
        clearTimeout(searchDebounceId);
    }
});

async function loadCategoriesForFilter() {
    categoriesLoading.value = true;
    categoriesError.value = null;
    try {
        const { data } = await axios.get(route('categories.index'));
        categories.value = Array.isArray(data?.data) ? data.data : [];
    } catch (e) {
        categoriesError.value =
            e?.response?.data?.message ?? 'Не удалось загрузить категории';
        categories.value = [];
    } finally {
        categoriesLoading.value = false;
    }
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
    loadCategoriesForFilter();
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
        <div v-else class="catalog-list">
            <div class="catalog-list__toolbar">
                <label class="catalog-list__filter catalog-list__filter--search">
                    <span class="catalog-list__filter-label">Поиск</span>
                    <InputText
                        v-model="searchQuery"
                        class="catalog-list__search-input"
                        type="search"
                        placeholder="Название или описание"
                        autocomplete="off"
                    />
                </label>
                <label class="catalog-list__filter">
                    <span class="catalog-list__filter-label">Категория</span>
                    <CategoryFilterSelect
                        v-model="filterCategoryId"
                        class="catalog-list__filter-select"
                        :options="categories"
                        option-label="name"
                        option-value="id"
                        placeholder="Все категории"
                        :loading="categoriesLoading"
                        filter
                        show-clear
                    />
                </label>
                <p v-if="categoriesError" class="catalog-list__filter-error">
                    {{ categoriesError }}
                </p>
            </div>
            <ProductsCard
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
        </div>
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

<style scoped>
.catalog-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.catalog-list__toolbar {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.75rem 1.25rem;
}

.catalog-list__filter {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    min-width: min(100%, 16rem);
}

.catalog-list__filter-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #475569;
}

.catalog-list__filter--search {
    flex: 1 1 12rem;
    min-width: min(100%, 12rem);
    max-width: 28rem;
}

.catalog-list__search-input {
    width: 100%;
}

.catalog-list__filter-select {
    width: 100%;
    max-width: 20rem;
}

.catalog-list__filter-error {
    margin: 0;
    flex: 1 1 100%;
    font-size: 0.8125rem;
    color: #b91c1c;
}
</style>
