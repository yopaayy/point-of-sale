<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    produks: Array,
    gudangs: Array,
    pergerakanStok: Object,
    filters: Object,
    stokSaatIni: Number
});

const filterForm = ref({
    produk_id: props.filters.produk_id || '',
    gudang_id: props.filters.gudang_id || ''
});

const getProdukName = () => {
    if (!filterForm.value.produk_id) return '';
    const produk = props.produks.find(p => p.id == filterForm.value.produk_id);
    return produk ? `${produk.nama} (${produk.kode_produk})` : '';
};

// Handle Filter Change
const submitFilter = () => {
    if(!filterForm.value.produk_id) {
        alert("Pilih Produk terlebih dahulu!");
        return;
    }
    router.get(route('laporan.kartu-stok.index'), filterForm.value, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>

<template>
    <AppLayout title="Laporan Kartu Stok">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kartu Stok (Riwayat Mutasi)
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filter Section -->
                <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Filter Laporan</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <InputLabel for="produk_id" value="Pilih Produk" />
                            <select id="produk_id" v-model="filterForm.produk_id" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                                <option value="" disabled>Pilih Produk</option>
                                <option v-for="p in produks" :key="p.id" :value="p.id">{{ p.kode_produk }} - {{ p.nama }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel for="gudang_id" value="Gudang (Opsional)" />
                            <select id="gudang_id" v-model="filterForm.gudang_id" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                                <option value="">Semua Gudang</option>
                                <option v-for="g in gudangs" :key="g.id" :value="g.id">{{ g.nama }}</option>
                            </select>
                        </div>
                        <div>
                            <button @click="submitFilter" class="w-full inline-flex justify-center items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 transition ease-in-out duration-150 shadow-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Tampilkan Data
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div v-if="pergerakanStok" class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
                    <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-gray-50/50">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">{{ getProdukName() }}</h3>
                            <p class="text-sm text-gray-500">Stok Saat Ini (Base Unit): <span class="font-bold text-gray-900 text-base">{{ stokSaatIni }}</span></p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal & Waktu</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe Transaksi</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gudang</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Masuk/Keluar</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Mutasi (Base)</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Stok Gudang</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="item in pergerakanStok.data" :key="item.id" class="hover:bg-gray-50/50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ new Date(item.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-medium text-gray-900 uppercase">{{ item.referensi_tipe }}</span>
                                            <span class="text-xs text-gray-500" v-if="item.keterangan">{{ item.keterangan }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ item.gudang?.nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span v-if="item.tipe === 'masuk'" class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800">
                                            MASUK
                                        </span>
                                        <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-red-100 text-red-800">
                                            KELUAR
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold" :class="item.tipe === 'masuk' ? 'text-emerald-600' : 'text-red-600'">
                                        {{ item.tipe === 'masuk' ? '+' : '-' }}{{ item.qty }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900 bg-gray-50">
                                        {{ item.stok_akhir }}
                                    </td>
                                </tr>
                                <tr v-if="pergerakanStok.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada riwayat pergerakan stok untuk filter ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-100 sm:px-6 flex items-center justify-between" v-if="pergerakanStok.links && pergerakanStok.data.length > 0">
                        <Pagination :links="pergerakanStok.links" :from="pergerakanStok.from" :to="pergerakanStok.to" :total="pergerakanStok.total" />
                    </div>
                </div>

                <div v-else class="bg-white shadow-sm sm:rounded-xl p-12 border border-gray-100 text-center flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <h4 class="text-lg font-medium text-gray-900 mb-1">Silakan Pilih Produk</h4>
                    <p class="text-sm text-gray-500">Gunakan filter di atas untuk melihat riwayat keluar/masuk stok produk.</p>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
