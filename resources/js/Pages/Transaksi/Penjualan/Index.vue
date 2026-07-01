<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import debounce from 'lodash/debounce';
import StrukKasir from '@/Components/StrukKasir.vue';

const props = defineProps({
    penjualans: Object,
    filters: Object,
    pengaturan: Object
});

const search = ref(props.filters.search || '');

watch(search, debounce(function (value) {
    router.get(route('transaksi.penjualan.index'), { search: value }, {
        preserveState: true,
        replace: true,
    });
}, 300));

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

const formatDateOnly = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const formatTimeOnly = (dateString) => {
    const options = { hour: '2-digit', minute: '2-digit', timeZoneName: 'short' };
    return new Date(dateString).toLocaleTimeString('id-ID', options).replace(/\./g, ':');
};

// Struk Modal
const isStrukModalOpen = ref(false);
const strukData = ref(null);

const bukaStruk = (penjualan) => {
    strukData.value = penjualan;
    isStrukModalOpen.value = true;
};

const closeStruk = () => {
    isStrukModalOpen.value = false;
    strukData.value = null;
};
</script>

<template>
    <AppLayout title="Riwayat Penjualan">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Riwayat Penjualan (POS)
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="flex justify-between items-center mb-6">
                    <div class="w-1/3">
                        <input 
                            type="text" 
                            v-model="search" 
                            placeholder="Cari berdasarkan No Struk..." 
                            class="border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm"
                        >
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Struk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Belanja</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="penjualan in penjualans.data" :key="penjualan.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ penjualan.no_struk }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateOnly(penjualan.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatTimeOnly(penjualan.created_at) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ penjualan.user?.name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ penjualan.pelanggan?.nama || 'Umum' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-semibold">Rp {{ formatRupiah(penjualan.total_akhir) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <button @click="bukaStruk(penjualan)" class="text-indigo-600 hover:text-indigo-900 mx-2">
                                        Lihat Struk
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="penjualans.data.length === 0">
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada riwayat transaksi.</td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div class="px-6 py-4 border-t border-gray-200" v-if="penjualans.links.length > 3">
                        <Pagination :links="penjualans.links" />
                    </div>
                </div>
            </div>
        </div>

        <!-- MODAL CETAK STRUK menggunakan komponen eksternal -->
        <StrukKasir 
            :show="isStrukModalOpen" 
            :strukData="strukData" 
            :pengaturan="pengaturan" 
            @close="closeStruk" 
        />

    </AppLayout>
</template>
