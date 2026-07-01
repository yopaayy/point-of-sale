<script setup>
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    penjualan: Array,
    summary: Object,
    filter: String,
    startDate: String,
    endDate: String,
});

const selectedFilter = ref(props.filter);
const customStartDate = ref(props.startDate || '');
const customEndDate = ref(props.endDate || '');

const applyFilter = () => {
    let params = { filter: selectedFilter.value };
    if (selectedFilter.value === 'custom') {
        if (!customStartDate.value || !customEndDate.value) {
            alert('Pilih tanggal awal dan akhir untuk filter kustom.');
            return;
        }
        params.start_date = customStartDate.value;
        params.end_date = customEndDate.value;
    }
    
    router.get(route('laporan.penjualan'), params, { preserveState: true });
};

// Format currency
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// Format date
const formatDateOnly = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('id-ID', options);
};

const formatTimeOnly = (dateString) => {
    const options = { hour: '2-digit', minute: '2-digit', timeZoneName: 'short' };
    return new Date(dateString).toLocaleTimeString('id-ID', options).replace(/\./g, ':');
};
</script>

<template>
    <AppLayout title="Laporan Penjualan">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Laporan Penjualan
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filter Section -->
                <div class="bg-white p-6 shadow-sm sm:rounded-lg mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Filter Laporan</h3>
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Rentang Waktu</label>
                            <select v-model="selectedFilter" class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                                <option value="today">Hari Ini</option>
                                <option value="week">Minggu Ini</option>
                                <option value="month">Bulan Ini</option>
                                <option value="year">Tahun Ini</option>
                                <option value="custom">Kustom (Pilih Tanggal)</option>
                            </select>
                        </div>
                        
                        <div v-if="selectedFilter === 'custom'" class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                            <input type="date" v-model="customStartDate" class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                        </div>
                        
                        <div v-if="selectedFilter === 'custom'" class="w-full md:w-1/4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                            <input type="date" v-model="customEndDate" class="w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm">
                        </div>

                        <div>
                            <PrimaryButton @click="applyFilter">
                                Terapkan Filter
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Summary Widget Section -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg border-l-4 border-primary-500">
                        <div class="text-sm text-gray-500 font-medium">Total Omset</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ formatRupiah(summary.total_omset) }}</div>
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg border-l-4 border-green-500">
                        <div class="text-sm text-gray-500 font-medium">Total Transaksi</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ summary.total_transaksi }} Struk</div>
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                        <div class="text-sm text-gray-500 font-medium">Total Diskon Diberikan</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ formatRupiah(summary.total_diskon) }}</div>
                    </div>
                    <div class="bg-white p-6 shadow-sm sm:rounded-lg border-l-4 border-red-500">
                        <div class="text-sm text-gray-500 font-medium">Pajak (PPN) Terkumpul</div>
                        <div class="text-2xl font-bold text-gray-900 mt-1">{{ formatRupiah(summary.total_pajak) }}</div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Rincian Riwayat Transaksi</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Struk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kasir</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Diskon</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pajak</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Akhir</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="jual in penjualan" :key="jual.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDateOnly(jual.tanggal) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatTimeOnly(jual.tanggal) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ jual.no_struk }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ jual.user ? jual.user.name : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-red-500">{{ formatRupiah(jual.diskon) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-yellow-600">{{ formatRupiah(jual.pajak) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-green-600">{{ formatRupiah(jual.total_akhir) }}</td>
                                </tr>
                                <tr v-if="penjualan.length === 0">
                                    <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500">
                                        Tidak ada transaksi pada periode waktu ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
