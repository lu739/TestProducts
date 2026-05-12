<script setup>
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';

const visible = defineModel('visible', { type: Boolean, default: false });

defineProps({
    processing: {
        type: Boolean,
        default: false,
    },
    /** normal — обычный товар; restore — только восстановление; force — только безвозвратное удаление (скрытый товар) */
    variant: {
        type: String,
        default: 'normal',
        validator: (v) => ['normal', 'restore', 'force'].includes(v),
    },
});

const emit = defineEmits(['soft', 'force', 'restore']);
</script>

<template>
    <Dialog
        v-model:visible="visible"
        :header="
            variant === 'restore' ? 'Восстановление товара' : 'Удаление товара'
        "
        modal
        class="product-delete-dialog"
        :style="{ width: 'min(40rem, 96vw)' }"
        :closable="!processing"
        :close-on-escape="!processing"
        :dismissable-mask="!processing"
    >
        <p class="product-delete-dialog__text">
            <template v-if="variant === 'restore'">
                Товар скрыт. Вы можете восстановить его.
            </template>
            <template v-else-if="variant === 'force'">
                Товар скрыт. Он будет удалён навсегда без возможности восстановления.
            </template>
            <template v-else>
                Подтвердите удаление и выберите его тип.
            </template>
        </p>
        <template #footer>
            <div
                class="product-delete-dialog__footer"
                :class="{
                    'product-delete-dialog__footer--two':
                        variant === 'restore' || variant === 'force',
                }"
            >
                <Button
                    type="button"
                    label="Отмена"
                    severity="secondary"
                    outlined
                    class="product-delete-dialog__footer-btn"
                    :disabled="processing"
                    @click="visible = false"
                />
                <template v-if="variant === 'normal'">
                    <Button
                        type="button"
                        label="Скрыть"
                        outlined
                        class="product-delete-dialog__footer-btn product-delete-dialog__btn-soft"
                        :disabled="processing"
                        @click="emit('soft')"
                    />
                    <Button
                        type="button"
                        label="Удалить насовсем"
                        severity="danger"
                        class="product-delete-dialog__footer-btn"
                        :disabled="processing"
                        @click="emit('force')"
                    />
                </template>
                <Button
                    v-else-if="variant === 'restore'"
                    type="button"
                    label="Восстановить"
                    outlined
                    class="product-delete-dialog__footer-btn product-delete-dialog__btn-soft"
                    :disabled="processing"
                    @click="emit('restore')"
                />
                <Button
                    v-else-if="variant === 'force'"
                    type="button"
                    label="Удалить навсегда"
                    severity="danger"
                    class="product-delete-dialog__footer-btn"
                    :disabled="processing"
                    @click="emit('force')"
                />
            </div>
        </template>
    </Dialog>
</template>

<style scoped>
.product-delete-dialog__text {
    margin: 0;
    font-size: 1.0625rem;
    line-height: 1.55;
    color: #334155;
}

.product-delete-dialog__footer {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: stretch;
    justify-content: flex-end;
    gap: 0.75rem;
    width: 100%;
}

.product-delete-dialog__footer--two {
    flex-wrap: wrap;
}

.product-delete-dialog__footer--two .product-delete-dialog__footer-btn {
    flex: 1 1 calc(50% - 0.375rem);
    min-width: min(12rem, 100%);
}

.product-delete-dialog__footer-btn {
    flex: 1 1 0;
    min-width: 0;
}

.product-delete-dialog :deep(.product-delete-dialog__btn-soft) {
    background: transparent !important;
    border-color: #7c3aed !important;
    color: #7c3aed !important;
}

.product-delete-dialog
    :deep(.product-delete-dialog__btn-soft:not(:disabled):hover) {
    background: rgba(124, 58, 237, 0.12) !important;
    border-color: #6d28d9 !important;
    color: #6d28d9 !important;
}

.product-delete-dialog :deep(.p-dialog-content) {
    padding: 1.35rem 1.75rem 1.15rem;
}

.product-delete-dialog :deep(.p-dialog-header) {
    padding: 1.1rem 1.75rem;
    font-size: 1.125rem;
}

.product-delete-dialog :deep(.p-dialog-footer) {
    padding: 1rem 1.75rem 1.35rem;
}
</style>
