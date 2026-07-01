<script setup>
import { ref, watch, onMounted } from 'vue';

const props = defineProps({
    modelValue: {
        type: [Number, String],
        default: 0,
    },
    id: String,
    placeholder: {
        type: String,
        default: '0'
    },
    disabled: Boolean,
    required: Boolean,
    min: [Number, String],
    inputClass: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['update:modelValue']);

const displayValue = ref('');

const formatNumber = (num) => {
    if (num === null || num === undefined || num === '') return '';
    const number = parseInt(String(num).replace(/\D/g, ''), 10);
    if (isNaN(number)) return '';
    return new Intl.NumberFormat('id-ID').format(number);
};

const parseNumber = (formatted) => {
    if (!formatted) return 0;
    const cleaned = String(formatted).replace(/\./g, '').replace(/\D/g, '');
    return parseInt(cleaned, 10) || 0;
};

const onInput = (event) => {
    const cursorPos = event.target.selectionStart;
    const oldLength = event.target.value.length;
    
    const rawValue = event.target.value.replace(/\./g, '').replace(/\D/g, '');
    const numericValue = parseInt(rawValue, 10) || 0;
    
    displayValue.value = formatNumber(numericValue);
    emit('update:modelValue', numericValue);
    
    // Restore cursor position
    const newLength = displayValue.value.length;
    const diff = newLength - oldLength;
    
    requestAnimationFrame(() => {
        const newPos = Math.max(0, cursorPos + diff);
        event.target.setSelectionRange(newPos, newPos);
    });
};

const onKeydown = (event) => {
    // Allow: navigation & editing keys
    const allowed = [
        'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
        'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
        'Home', 'End'
    ];
    if (allowed.includes(event.key)) return;

    // Allow Ctrl/Cmd combos (copy, paste, select all, undo, redo)
    if ((event.ctrlKey || event.metaKey) && ['a','c','v','x','z'].includes(event.key.toLowerCase())) return;

    // Block anything that is NOT a digit
    if (!/^[0-9]$/.test(event.key)) {
        event.preventDefault();
    }
};

const onPaste = (event) => {
    event.preventDefault();
    const pastedText = (event.clipboardData || window.clipboardData).getData('text');
    // Strip all non-digit characters from pasted content
    const digitsOnly = pastedText.replace(/\D/g, '');
    if (digitsOnly) {
        const numericValue = parseInt(digitsOnly, 10) || 0;
        displayValue.value = formatNumber(numericValue);
        emit('update:modelValue', numericValue);
    }
};

// Initialize display from model
watch(() => props.modelValue, (newVal) => {
    const currentParsed = parseNumber(displayValue.value);
    const newParsed = parseInt(String(newVal).replace(/\D/g, ''), 10) || 0;
    if (currentParsed !== newParsed) {
        displayValue.value = formatNumber(newParsed);
    }
}, { immediate: true });

onMounted(() => {
    displayValue.value = formatNumber(props.modelValue);
});
</script>

<template>
    <input
        type="text"
        inputmode="numeric"
        :id="id"
        :value="displayValue"
        @input="onInput"
        @keydown="onKeydown"
        @paste="onPaste"
        :placeholder="placeholder"
        :disabled="disabled"
        :required="required"
        :class="[
            'border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm',
            inputClass
        ]"
        autocomplete="off"
    />
</template>
