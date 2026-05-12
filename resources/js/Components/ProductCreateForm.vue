<script setup>
import axios from 'axios';
import Button from 'primevue/button';
import InputNumber from 'primevue/inputnumber';
import InputText from 'primevue/inputtext';
import CategorySelect from 'primevue/select';
import Textarea from 'primevue/textarea';
import { route } from 'ziggy-js';
import { onMounted, reactive, ref } from 'vue';
import { validateProductForm } from '../validation/productForm';

const props = defineProps({
    createProduct: {
        type: Function,
        required: true,
    },
});

const emit = defineEmits(['cancel', 'saved']);

const form = reactive({
    name: '',
    description: '',
    price: null,
    category_id: null,
});

const categories = ref([]);
const categoriesLoading = ref(true);
const categoriesError = ref(null);
const processing = ref(false);
const message = ref('');
const fieldErrors = ref({});

onMounted(async () => {
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
});

function resetErrors() {
    message.value = '';
    fieldErrors.value = {};
}

async function submit() {
    resetErrors();
    const clientErrors = validateProductForm(form, categories.value);
    if (Object.keys(clientErrors).length > 0) {
        fieldErrors.value = clientErrors;

        return;
    }

    processing.value = true;

    try {
        await props.createProduct({
            name: form.name.trim(),
            description: form.description.trim() || null,
            price: form.price,
            category_id: form.category_id,
        });
        emit('saved');
    } catch (e) {
        if (e?.response?.status === 422) {
            fieldErrors.value = e.response.data.errors ?? {};
        } else {
            message.value =
                e?.response?.data?.message ?? 'Не удалось создать товар';
        }
    } finally {
        processing.value = false;
    }
}

function onCancel() {
    emit('cancel');
}
</script>

<template>
    <section class="create-form">
        <div class="create-form__head">
            <h1 class="create-form__title">Добавить новый товар</h1>
        </div>

        <p v-if="categoriesError" class="create-form__banner create-form__banner--error">
            {{ categoriesError }}
        </p>
        <p v-if="message" class="create-form__banner create-form__banner--error">
            {{ message }}
        </p>

        <form class="create-form__body" novalidate @submit.prevent="submit">
            <label class="create-form__field">
                <span class="create-form__label">Название</span>
                <InputText
                    v-model="form.name"
                    class="create-form__control"
                    :invalid="!!fieldErrors.name?.[0]"
                    maxlength="255"
                    autocomplete="off"
                />
                <span v-if="fieldErrors.name?.[0]" class="create-form__err">{{
                    fieldErrors.name[0]
                }}</span>
            </label>

            <label class="create-form__field">
                <span class="create-form__label">Категория</span>
                <CategorySelect
                    v-model="form.category_id"
                    class="create-form__control create-form__select"
                    :invalid="!!fieldErrors.category_id?.[0]"
                    :options="categories"
                    option-label="name"
                    option-value="id"
                    placeholder="Выберите категорию"
                    :loading="categoriesLoading"
                    filter
                    show-clear
                />
                <span v-if="fieldErrors.category_id?.[0]" class="create-form__err">{{
                    fieldErrors.category_id[0]
                }}</span>
            </label>

            <label class="create-form__field">
                <span class="create-form__label">Цена</span>
                <InputNumber
                    v-model="form.price"
                    class="create-form__control create-form__number"
                    :invalid="!!fieldErrors.price?.[0]"
                    :min-fraction-digits="2"
                    :max-fraction-digits="2"
                    :min="0.01"
                    mode="decimal"
                    locale="ru-RU"
                    input-id="product-price"
                />
                <span v-if="fieldErrors.price?.[0]" class="create-form__err">{{
                    fieldErrors.price[0]
                }}</span>
            </label>

            <label class="create-form__field">
                <span class="create-form__label">Описание</span>
                <Textarea
                    v-model="form.description"
                    class="create-form__control"
                    :invalid="!!fieldErrors.description?.[0]"
                    rows="4"
                />
                <span v-if="fieldErrors.description?.[0]" class="create-form__err">{{
                    fieldErrors.description[0]
                }}</span>
            </label>

            <div class="create-form__actions">
                <Button
                    type="button"
                    label="Отмена"
                    severity="secondary"
                    outlined
                    @click="onCancel"
                />
                <Button
                    type="submit"
                    label="Сохранить"
                    :loading="processing"
                    :disabled="categoriesLoading || !!categoriesError"
                />
            </div>
        </form>
    </section>
</template>

<style scoped>
.create-form {
    overflow: hidden;
    max-width: 32rem;
    margin: 0 auto;
    border-radius: 0.75rem;
    border: 1px solid #e2e8f0;
    background: #fff;
    padding: 1.25rem 1.5rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
}

.create-form__head {
    margin-bottom: 1.25rem;
}

.create-form__title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.25;
    color: #0f172a;
}

.create-form__banner {
    margin: 0 0 1rem;
    border-radius: 0.5rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.875rem;
    line-height: 1.4;
}

.create-form__banner--error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #991b1b;
}

.create-form__body {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.create-form__field {
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.create-form__label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #475569;
}

.create-form__err {
    font-size: 0.75rem;
    color: #b91c1c;
}

.create-form__actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-top: 0.25rem;
}

.create-form__control {
    width: 100%;
}

.create-form__select,
.create-form__number {
    width: 100%;
}

.create-form__number :deep(.p-inputnumber),
.create-form__number :deep(.p-inputnumber-input) {
    width: 100%;
}
</style>
