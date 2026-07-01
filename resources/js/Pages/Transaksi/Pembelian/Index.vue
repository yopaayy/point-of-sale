<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    pembelian: Object,
});

// Fungsi format Rupiah
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};
</script>

<template>
    <AppLayout title="Transaksi Pembelian">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Transaksi Pembelian (PO)
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
                    
                    <!-- Header & Actions -->
                    <div class="px-6 py-5 border-b border-gray-100 flex flex-col sm:flex-row sm:justify-between sm:items-center bg-white space-y-3 sm:space-y-0">
                        <h3 class="text-lg font-semibold text-gray-800">Riwayat Pembelian</h3>
                        <div class="flex items-center space-x-3">
                            <Link :href="route('transaksi.pembelian.create')" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Buat Pembelian Baru
                            </Link>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">TANGGAL</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NO. FAKTUR</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SUPPLIER</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GUDANG MASUK</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">TOTAL HARGA</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">STATUS</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                <tr v-for="item in pembelian.data" :key="item.id" class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ new Date(item.tanggal).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold bg-primary-100 text-primary-800 border border-primary-200 shadow-sm">
                                            {{ item.no_faktur || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                        {{ item.supplier?.nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ item.gudang?.nama }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                                        {{ formatRupiah(item.total_harga) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 capitalize">
                                            {{ item.status }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="pembelian.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                        Belum ada riwayat transaksi pembelian.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-100 sm:px-6 flex items-center justify-between" v-if="pembelian.links && pembelian.data.length > 0">
                        <Pagination :links="pembelian.links" :from="pembelian.from" :to="pembelian.to" :total="pembelian.total" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
