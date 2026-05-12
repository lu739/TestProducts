/**
 * Клиентская валидация полей товара по тем же правилам, что StoreProductRequest / UpdateProductRequest:
 * name: required, string, max:255
 * description: nullable, string
 * price: required, numeric, gt:0
 * category_id: required, integer, exists:categories,id
 */

export const PRODUCT_NAME_MAX_LENGTH = 255;

/**
 * @param {{ name: unknown, description: unknown, price: unknown, category_id: unknown }} form
 * @param {Array<{ id: number|string }>} categories — список с сервера; проверка exists только если массив не пуст
 * @returns {Record<string, string[]>} формат как у Laravel validation errors
 */
export function validateProductForm(form, categories) {
    /** @type {Record<string, string[]>} */
    const errors = {};

    const name =
        typeof form.name === 'string' ? form.name.trim() : String(form.name ?? '').trim();
    if (name === '') {
        errors.name = ['Поле «Название» обязательно для заполнения.'];
    } else if (name.length > PRODUCT_NAME_MAX_LENGTH) {
        errors.name = [
            `Длина поля «Название» не должна превышать ${PRODUCT_NAME_MAX_LENGTH} символов.`,
        ];
    }

    const desc = form.description;
    if (desc != null && desc !== '' && typeof desc !== 'string') {
        errors.description = ['Поле «Описание» должно быть строкой.'];
    }

    const price = form.price;
    if (price === null || price === undefined || price === '') {
        errors.price = ['Поле «Цена» обязательно для заполнения.'];
    } else {
        const n =
            typeof price === 'number' ? price : Number.parseFloat(String(price));
        if (Number.isNaN(n) || !Number.isFinite(n)) {
            errors.price = ['Поле «Цена» должно быть числом.'];
        } else if (n <= 0) {
            errors.price = ['Поле «Цена» должно быть больше 0.'];
        }
    }

    const cid = form.category_id;
    if (cid === null || cid === undefined || cid === '') {
        errors.category_id = ['Поле «Категория» обязательно для заполнения.'];
    } else {
        const idNum = Number(cid);
        if (!Number.isInteger(idNum)) {
            errors.category_id = ['Поле «Категория» должно быть целым числом.'];
        } else if (categories.length > 0) {
            const ok = categories.some((c) => Number(c.id) === idNum);
            if (!ok) {
                errors.category_id = [
                    'Выбранное значение для «Категория» недопустимо.',
                ];
            }
        }
    }

    return errors;
}
