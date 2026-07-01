<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    stokMenipis: Array
});

// Format numeric values
const formatNumber = (number) => {
    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2
    }).format(number);
};
</script>

<template>
    <AppLayout title="Laporan Stok Menipis">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Laporan Stok Menipis
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Info Alert -->
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Perhatian: Stok Menipis!</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>Terdapat {{ stokMenipis.length }} produk yang jumlah stoknya sudah menyentuh atau berada di bawah ambang batas stok minimum. Segera lakukan pengadaan (pembelian) untuk menghindari kehabisan barang di toko.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Section -->
                <div class="bg-white shadow-xl sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900">Daftar Produk Menipis</h3>
                        <Link :href="route('transaksi.pembelian.index')" class="bg-primary-600 hover:bg-primary-700 text-white font-bold py-2 px-4 rounded text-sm transition">
                            + Restock Sekarang
                        </Link>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Batas Minimum</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider text-red-600">Stok Saat Ini</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="produk in stokMenipis" :key="produk.id" class="hover:bg-red-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ produk.kode_produk }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">{{ produk.nama }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ produk.kategori ? produk.kategori.nama : '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">
                                        {{ formatNumber(produk.minimum_stok) }} {{ produk.base_unit ? produk.base_unit.singkatan : '' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right text-red-600">
                                        {{ formatNumber(produk.stok_saat_ini) }} {{ produk.base_unit ? produk.base_unit.singkatan : '' }}
                                    </td>
                                </tr>
                                <tr v-if="stokMenipis.length === 0">
                                    <td colspan="5" class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="mt-2 text-green-600 font-medium">Bagus! Tidak ada stok yang menipis saat ini.</p>
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
