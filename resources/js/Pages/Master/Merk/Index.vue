<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import SortableHeader from '@/Components/SortableHeader.vue';
import { router } from '@inertiajs/vue3';
import debounce from 'lodash/debounce';
import { reactive, watch } from 'vue';

const props = defineProps({
    merk: Object,
    filters: Object,
});

const params = reactive({
    search: props.filters.search || '',
    sort: props.filters.sort || 'id',
    dir: props.filters.dir || 'desc'
});

watch(params, debounce(function (value) {
    router.get(route('master.merk.index'), value, { preserveState: true, replace: true });
}, 300), { deep: true });

const handleSort = (field) => {
    if (params.sort === field) {
        params.dir = params.dir === 'asc' ? 'desc' : 'asc';
    } else {
        params.sort = field;
        params.dir = 'asc';
    }
};

const form = useForm({
    nama: '',
});

const isModalOpen = ref(false);
const isEditing = ref(false);
const editingId = ref(null);
const itemToDelete = ref(null);
const isDeleteModalOpen = ref(false);

const openCreateModal = () => {
    isEditing.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (item) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.nama = item.nama;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeDialog = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(route('master.merk.update', editingId.value), {
            onSuccess: () => closeDialog(),
        });
    } else {
        form.post(route('master.merk.store'), {
            onSuccess: () => closeDialog(),
        });
    }
};

const openDeleteModal = (item) => {
    itemToDelete.value = item;
    isDeleteModalOpen.value = true;
};

const deleteItem = () => {
    form.delete(route('master.merk.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            itemToDelete.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Master Merk">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Master Merk
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
                    
                    <!-- Header & Actions -->
                    <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white space-y-3 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Merk</h3>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input v-model="params.search" type="text" placeholder="Cari merk..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out" />
                            </div>
                            <PrimaryButton @click="openCreateModal" class="bg-primary-600 hover:bg-primary-700">
                                Tambah
                            </PrimaryButton>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <SortableHeader label="ID" field="id" :params="params" @sort="handleSort" class="w-16" />
                                    <SortableHeader label="Nama Merk" field="nama" :params="params" @sort="handleSort" />
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="item in merk.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ item.nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button @click="openEditModal(item)" class="text-accent-600 hover:text-accent-900 transition-colors">Edit</button>
                                        <button @click="openDeleteModal(item)" class="text-rose-600 hover:text-rose-900 transition-colors">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="merk.data.length === 0">
                                    <td colspan="3" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Tidak ada data merk ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-100 sm:px-6 flex items-center justify-between" v-if="merk.links && merk.data.length > 0">
                        <div class="flex-1 flex justify-between sm:hidden">
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan <span class="font-medium">{{ merk.from }}</span> sampai <span class="font-medium">{{ merk.to }}</span> dari <span class="font-medium">{{ merk.total }}</span> hasil
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <component 
                                        :is="link.url ? 'a' : 'span'"
                                        v-for="(link, k) in merk.links" 
                                        :key="k" 
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            link.active ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer',
                                            k === 0 ? 'rounded-l-md' : '',
                                            k === merk.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                        @click.prevent="$inertia.get(link.url)"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create / Edit Modal -->
        <Modal :show="isModalOpen" @close="closeDialog" max-width="md">
            <div class="px-6 py-4">
                <div class="text-lg font-medium text-gray-900">
                    {{ isEditing ? 'Edit Merk' : 'Tambah Merk' }}
                </div>
                <div class="mt-4">
                    <form @submit.prevent="submitForm">
                        <div class="mb-4">
                            <InputLabel for="nama" value="Nama Merk" />
                            <TextInput
                                id="nama"
                                ref="namaInput"
                                v-model="form.nama"
                                type="text"
                                class="mt-1 block w-full"
                                placeholder="Contoh: Indofood"
                            />
                            <InputError :message="form.errors.nama" class="mt-2" />
                        </div>
                        <div class="flex justify-end mt-6">
                            <SecondaryButton @click="closeDialog">Batal</SecondaryButton>
                            <PrimaryButton class="ml-3 bg-primary-600" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Simpan
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="sm">
            <div class="px-6 py-4">
                <div class="text-lg font-medium text-gray-900">Konfirmasi Hapus</div>
                <div class="mt-4 text-sm text-gray-600">
                    Apakah Anda yakin ingin menghapus merk <b>{{ itemToDelete?.nama }}</b>? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="flex justify-end mt-6 space-x-2">
                    <SecondaryButton @click="isDeleteModalOpen = false">Batal</SecondaryButton>
                    <DangerButton @click="deleteItem" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AppLayout>
</template>
