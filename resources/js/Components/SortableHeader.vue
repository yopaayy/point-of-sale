<script setup>
defineProps({
    label: String,
    field: String,
    params: Object
});

const emit = defineEmits(['sort']);

const onClick = (field) => {
    emit('sort', field);
};
</script>

<template>
    <th @click="onClick(field)" class="cursor-pointer px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider select-none hover:bg-gray-100 transition-colors">
        <div class="flex items-center space-x-1">
            <span>{{ label }}</span>
            <span v-if="params.sort === field" class="text-primary-600">
                <svg v-if="params.dir === 'asc'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </span>
            <span v-else class="text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                </svg>
            </span>
        </div>
    </th>
</template>
