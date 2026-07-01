<script setup>
import { ref, reactive, watch } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SortableHeader from '@/Components/SortableHeader.vue';
import BarcodeLabel from '@/Components/BarcodeLabel.vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    produk: Object,
    filters: Object,
});

const params = reactive({
    search: props.filters.search || '',
    sort: props.filters.sort || 'id',
    dir: props.filters.dir || 'desc'
});

watch(params, debounce(function (value) {
    router.get(route('master.produk.index'), value, { preserveState: true, replace: true });
}, 300), { deep: true });

const handleSort = (field) => {
    if (params.sort === field) {
        params.dir = params.dir === 'asc' ? 'desc' : 'asc';
    } else {
        params.sort = field;
        params.dir = 'asc';
    }
};

const deleteForm = useForm({});
const itemToDelete = ref(null);
const isDeleteModalOpen = ref(false);

const openDeleteModal = (item) => {
    itemToDelete.value = item;
    isDeleteModalOpen.value = true;
};

const deleteItem = () => {
    deleteForm.delete(route('master.produk.destroy', itemToDelete.value.id), {
        onSuccess: () => {
            isDeleteModalOpen.value = false;
            itemToDelete.value = null;
        },
    });
};

const itemToPrint = ref(null);
const isBarcodeModalOpen = ref(false);

const openBarcodeModal = (item) => {
    itemToPrint.value = item;
    isBarcodeModalOpen.value = true;
};
</script>

<template>
    <AppLayout title="Master Produk">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Master Produk
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
                    
                    <!-- Header & Actions -->
                    <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white space-y-3 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Produk</h3>
                        <div class="flex items-center space-x-3">
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input v-model="params.search" type="text" placeholder="Cari produk..." class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm transition duration-150 ease-in-out" />
                            </div>
                            <Link :href="route('master.produk.create')" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Tambah
                            </Link>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <SortableHeader label="KODE" field="kode_produk" :params="params" @sort="handleSort" />
                                    <SortableHeader label="PRODUK" field="nama" :params="params" @sort="handleSort" />
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">KATEGORI & MERK</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">HARGA DASAR</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="item in produk.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-primary-100 text-primary-800 border border-primary-200 shadow-sm">
                                            {{ item.kode_produk }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <div class="flex items-center">
                                            <div v-if="item.gambar" class="h-10 w-10 shrink-0 mr-3">
                                                <img class="h-10 w-10 rounded-full object-cover" :src="'/storage/' + item.gambar" alt="" />
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ item.nama }}</div>
                                                <div class="text-xs text-gray-500" v-if="item.merk">{{ item.merk.nama }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.kategori?.nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ item.base_unit?.nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                                        <button @click="openBarcodeModal(item)" class="text-indigo-600 hover:text-indigo-900 transition-colors" title="Cetak Barcode">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                        </button>
                                        <Link :href="route('master.produk.edit', item.id)" class="text-accent-600 hover:text-accent-900 transition-colors">Edit</Link>
                                        <button @click="openDeleteModal(item)" class="text-rose-600 hover:text-rose-900 transition-colors">Hapus</button>
                                    </td>
                                </tr>
                                <tr v-if="produk.data.length === 0">
                                    <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Tidak ada data produk ditemukan.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-100 sm:px-6 flex items-center justify-between" v-if="produk.links && produk.data.length > 0">
                        <div class="flex-1 flex justify-between sm:hidden">
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan <span class="font-medium">{{ produk.from }}</span> sampai <span class="font-medium">{{ produk.to }}</span> dari <span class="font-medium">{{ produk.total }}</span> hasil
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <component 
                                        :is="link.url ? 'Link' : 'span'"
                                        v-for="(link, k) in produk.links" 
                                        :key="k" 
                                        :href="link.url"
                                        v-html="link.label"
                                        :class="[
                                            link.active ? 'z-10 bg-primary-50 border-primary-500 text-primary-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                            'relative inline-flex items-center px-4 py-2 border text-sm font-medium cursor-pointer',
                                            k === 0 ? 'rounded-l-md' : '',
                                            k === produk.links.length - 1 ? 'rounded-r-md' : ''
                                        ]"
                                    />
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <Modal :show="isDeleteModalOpen" @close="isDeleteModalOpen = false" max-width="sm">
            <div class="px-6 py-4">
                <div class="text-lg font-medium text-gray-900">Konfirmasi Hapus</div>
                <div class="mt-4 text-sm text-gray-600">
                    Apakah Anda yakin ingin menghapus produk <b>{{ itemToDelete?.nama }}</b>? Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="flex justify-end mt-6 space-x-2">
                    <SecondaryButton @click="isDeleteModalOpen = false">Batal</SecondaryButton>
                    <DangerButton @click="deleteItem" :class="{ 'opacity-25': deleteForm.processing }" :disabled="deleteForm.processing">
                        Hapus
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Barcode Label Modal -->
        <BarcodeLabel 
            :show="isBarcodeModalOpen" 
            :produk="itemToPrint" 
            @close="isBarcodeModalOpen = false" 
        />
    </AppLayout>
</template>
