import axios from 'axios';
import { ref } from 'vue';
import { route } from 'ziggy-js';

/**
 * Состояние и запросы к API товаров (публичный список + CRUD под Sanctum).
 *
 * @param {{ defaultPerPage?: number }} [options]
 */
export function useProductApi(options = {}) {
    const defaultPerPage = options.defaultPerPage ?? 15;

    const products = ref([]);
    const totalRecords = ref(0);
    const first = ref(0);
    const rows = ref(defaultPerPage);
    const loading = ref(false);
    const loadError = ref(null);

    /**
     * @param {{ page?: number; rows?: number }|undefined} event — как у PrimeVue DataTable @page
     */
    async function loadProducts(event) {
        loading.value = true;
        loadError.value = null;

        const page = (event?.page ?? 0) + 1;
        const perPage = Math.min(
            Math.max(event?.rows ?? rows.value, 10),
            15,
        );

        try {
            const { data } = await axios.get(route('products.index'), {
                params: {
                    page,
                    per_page: perPage,
                },
            });

            products.value = data.data;
            totalRecords.value = data.meta.total;
            rows.value = data.meta.per_page;
            first.value = (data.meta.current_page - 1) * data.meta.per_page;
        } catch (e) {
            loadError.value =
                e?.response?.data?.message ?? 'Не удалось загрузить товары';
            products.value = [];
            totalRecords.value = 0;
        } finally {
            loading.value = false;
        }
    }

    /** @param {number|string} id */
    async function fetchProduct(id) {
        const { data } = await axios.get(
            route('products.show', { product: id }),
        );

        return data?.data ?? data;
    }

    /** @param {Record<string, unknown>} payload */
    async function createProduct(payload) {
        const { data } = await axios.post(route('products.store'), payload);

        return data?.data ?? data;
    }

    /** @param {number|string} id @param {Record<string, unknown>} payload */
    async function updateProduct(id, payload) {
        const { data } = await axios.put(
            route('products.update', { product: id }),
            payload,
        );

        return data?.data ?? data;
    }

    /** @param {number|string} id */
    async function deleteProduct(id) {
        await axios.delete(route('products.destroy', { product: id }));
    }

    return {
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
    };
}
