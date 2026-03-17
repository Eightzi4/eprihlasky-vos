import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

const FORMAT_VALIDATORS = {
    email(value) {
        if (!value) return null;
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
            ? null : 'E-mail nemá správný formát.';
    },
    phone(value) {
        if (!value) return null;
        return /^(\+420)?\s?[1-9][0-9]{2}\s?[0-9]{3}\s?[0-9]{3}$/.test(value)
            ? null : 'Telefonní číslo nemá správný formát (+420 777 123 456).';
    },
    zip(value) {
        if (!value) return null;
        return /^\d{3}\s?\d{2}$/.test(value)
            ? null : 'PSČ nemá správný formát (686 01).';
    },
    birth_number(value) {
        if (!value) return null;
        const stripped = value.replace('/', '');
        if (!/^\d{9,10}$/.test(stripped))
            return 'Rodné číslo nemá správný formát (000101/1234).';

        const yy = parseInt(stripped.substring(0, 2), 10);
        let month = parseInt(stripped.substring(2, 4), 10);
        const day = parseInt(stripped.substring(4, 6), 10);

        if (month > 70) month -= 70;
        else if (month > 50) month -= 50;
        else if (month > 20) month -= 20;

        if (month < 1 || month > 12) return 'Rodné číslo obsahuje neplatný měsíc.';
        if (day < 1 || day > 31) return 'Rodné číslo obsahuje neplatný den.';

        if (stripped.length === 10) {
            if (parseInt(stripped, 10) % 11 !== 0)
                return 'Rodné číslo není platné (chybný kontrolní součet).';
        }
        return null;
    },
    graduation_year(value) {
        if (!value) return null;
        if (!/^\d{4}$/.test(value)) return 'Rok maturity musí být čtyřciferné číslo.';
        const y = parseInt(value, 10);
        const maxYear = new Date().getFullYear() + 1;
        if (y < 1950 || y > maxYear)
            return `Rok maturity musí být v rozmezí 1950–${maxYear}.`;
        return null;
    },
    grade_average(value) {
        if (!value) return null;
        const n = parseFloat(value.replace(',', '.'));
        if (isNaN(n)) return 'Průměr musí být číslo (např. 1.50).';
        if (n < 1.0 || n > 5.0) return 'Průměr musí být v rozmezí 1,00 až 5,00.';
        return null;
    },
    izo(value) {
        if (!value) return null;
        return /^\d{6,9}$/.test(value.trim())
            ? null : 'IZO musí být 6–9 číslic.';
    },
    previous_study_field_code(value) {
        if (!value) return null;
        return /^\d{2}-\d{2}-[A-Z]\/\d{2}$/.test(value.trim())
            ? null : 'Kód oboru musí být ve formátu 18-20-M/01.';
    },
};

document.addEventListener('alpine:init', () => {

    Alpine.data('stepValidator', (config) => ({
        stepNumber: config.step || 1,
        fields: config.fields || [],
        errors: {},
        touched: {},
        serverErrorFields: config.serverErrorFields || [],
        serverMessages: config.serverMessages || {},

        init() {
            this.serverErrorFields.forEach(name => {
                this.errors[name] = this.serverMessages[name] || 'Toto pole je neplatné.';
            });

            this.fields.forEach(field => {
                this.$nextTick(() => {
                    const el = document.querySelector(`[name="${field.name}"]`);
                    if (!el || el.readOnly || el.disabled) return;

                    const isBinary = el.tagName === 'SELECT'
                        || ['date', 'checkbox', 'file'].includes(el.type);

                    const onChange = () => {
                        this.touched[field.name] = true;
                        this.validateField(field);
                        this.dispatchCompletionEvent();
                    };

                    if (isBinary) {
                        el.addEventListener('change', onChange);
                    } else {
                        el.addEventListener('input', onChange);
                        el.addEventListener('blur', onChange);
                    }
                });
            });

            this.$nextTick(() => this.dispatchCompletionEvent());

            window.addEventListener('autosave-error', (e) => {
                const field = this.fields.find(f => f.name === e.detail.field);
                if (field) {
                    this.errors[e.detail.field] = e.detail.message;
                    this.touched[e.detail.field] = true;
                    this.dispatchCompletionEvent();
                }
            });

            window.addEventListener('autosave-ok', (e) => {
                const field = this.fields.find(f => f.name === e.detail.field);
                if (field && !FORMAT_VALIDATORS[e.detail.field]) {
                    delete this.errors[e.detail.field];
                    this.dispatchCompletionEvent();
                }
            });
        },

        getFieldValue(name) {
            const el = document.querySelector(`[name="${name}"]`);
            if (!el) return '';
            if (el.type === 'checkbox') return el.checked ? 'on' : '';
            if (el.type === 'file') return el.files?.length ? 'present' : '';
            return el.value ?? '';
        },

        validateField(field) {
            const value = this.getFieldValue(field.name);
            const trimmed = value?.trim() ?? '';

            if (!trimmed) {
                this.errors[field.name] = field.message || 'Toto pole je povinné.';
                return false;
            }

            const fmt = FORMAT_VALIDATORS[field.name];
            if (fmt) {
                const err = fmt(trimmed);
                if (err) {
                    this.errors[field.name] = err;
                    return false;
                }
            }

            delete this.errors[field.name];
            return true;
        },

        validateAll() {
            let valid = true;
            this.fields.forEach(f => {
                this.touched[f.name] = true;
                if (!this.validateField(f)) valid = false;
            });
            return valid;
        },

        isComplete() {
            return this.fields.every(field => {
                const value = this.getFieldValue(field.name);
                if (!value?.trim()) return false;
                const fmt = FORMAT_VALIDATORS[field.name];
                return !(fmt && fmt(value.trim()));
            });
        },

        dispatchCompletionEvent() {
            window.dispatchEvent(new CustomEvent('step-complete', {
                detail: { step: this.stepNumber, complete: this.isComplete() },
            }));
        },

        fieldHasError(name) { return !!this.errors[name]; },
        hasError(name) { return !!(this.touched[name] && this.errors[name]); },
        showServerError(name) {
            return this.serverErrorFields.includes(name) && !this.touched[name];
        },
    }));

});

Alpine.start();
