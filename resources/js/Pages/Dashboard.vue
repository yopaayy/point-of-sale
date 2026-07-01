<script setup>
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js';
import { Line } from 'vue-chartjs';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend
);

const props = defineProps({
    overview: Object,
    transaksi_terakhir: Array,
    grafik_mingguan: Object
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatTime = (dateString) => {
    return new Date(dateString).toLocaleTimeString('id-ID', {
        hour: '2-digit', minute: '2-digit'
    });
};

const stats = computed(() => [
    { name: 'Total Penjualan Hari Ini', stat: formatRupiah(props.overview.total_penjualan), icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', color: 'text-primary-600', bg: 'bg-primary-100' },
    { name: 'Total Transaksi Hari Ini', stat: props.overview.total_transaksi.toString(), icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', color: 'text-accent-600', bg: 'bg-accent-100' },
    { name: 'Produk Terjual Hari Ini', stat: props.overview.produk_terjual.toString(), icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', color: 'text-emerald-600', bg: 'bg-emerald-100' },
    { name: 'Stok Menipis', stat: props.overview.stok_menipis.toString(), icon: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', color: 'text-rose-600', bg: 'bg-rose-100' },
]);

const currentFilter = ref('7_hari');
const customStartDate = ref('');
const customEndDate = ref('');
const chartLabels = ref(props.grafik_mingguan.labels);
const chartDataValues = ref(props.grafik_mingguan.data);

const chartTitle = computed(() => {
    switch (currentFilter.value) {
        case 'hari_ini': return 'Grafik Penjualan (Hari Ini)';
        case '7_hari': return 'Grafik Penjualan (7 Hari Terakhir)';
        case '1_bulan': return 'Grafik Penjualan (1 Bulan Terakhir)';
        case '1_tahun': return 'Grafik Penjualan (1 Tahun Terakhir)';
        case 'custom': return 'Grafik Penjualan (Kustom)';
        default: return 'Grafik Penjualan';
    }
});

const chartData = computed(() => {
    return {
        labels: chartLabels.value,
        datasets: [
            {
                label: 'Penjualan',
                backgroundColor: '#3b82f6',
                borderColor: '#3b82f6',
                data: chartDataValues.value,
                tension: 0.4,
                fill: false,
            }
        ]
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                callback: function(value) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumSignificantDigits: 3 }).format(value);
                }
            }
        }
    }
};

const updateChartData = async () => {
    if (currentFilter.value === 'custom' && (!customStartDate.value || !customEndDate.value)) {
        return; // wait for both dates
    }

    try {
        const response = await axios.get('/api/dashboard/chart', {
            params: {
                filter: currentFilter.value,
                start_date: customStartDate.value,
                end_date: customEndDate.value
            }
        });
        chartLabels.value = response.data.labels;
        chartDataValues.value = response.data.data;
    } catch (error) {
        console.error("Failed to fetch chart data:", error);
    }
};

const handleFilterChange = (event) => {
    currentFilter.value = event.target.value;
    if (currentFilter.value !== 'custom') {
        updateChartData();
    }
};

</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard Overview
            </h2>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="item in stats" :key="item.name" class="bg-white overflow-hidden shadow-sm rounded-xl transition duration-300 hover:shadow-md hover:-translate-y-1">
                        <div class="p-6 flex items-center">
                            <div :class="[item.bg, item.color, 'p-4 rounded-full mr-4 flex-shrink-0']">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500 truncate">{{ item.name }}</p>
                                <p class="text-2xl font-bold text-gray-900 mt-1">{{ item.stat }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Row -->
                <div class="bg-white shadow-sm rounded-xl p-6 border border-gray-100">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <h3 class="text-lg font-semibold text-gray-800">{{ chartTitle }}</h3>
                        
                        <!-- Chart Filters -->
                        <div class="flex flex-col sm:flex-row gap-3 mt-4 sm:mt-0 items-center">
                            <div v-if="currentFilter === 'custom'" class="flex items-center gap-2">
                                <TextInput v-model="customStartDate" type="date" class="text-sm py-1 px-2 h-9 w-36" />
                                <span class="text-gray-500">-</span>
                                <TextInput v-model="customEndDate" type="date" class="text-sm py-1 px-2 h-9 w-36" />
                                <PrimaryButton @click="updateChartData" class="!px-3 !py-1.5 h-9">
                                    Terapkan
                                </PrimaryButton>
                            </div>
                            
                            <select @change="handleFilterChange" :value="currentFilter" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm text-sm h-9">
                                <option value="hari_ini">Hari Ini</option>
                                <option value="7_hari">7 Hari Terakhir</option>
                                <option value="1_bulan">1 Bulan Terakhir</option>
                                <option value="1_tahun">1 Tahun Terakhir</option>
                                <option value="custom">Custom Tanggal</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="w-full h-80">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Recent Transactions & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Recent Transactions -->
                    <div class="lg:col-span-2 bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
                        <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white">
                            <h3 class="text-lg font-semibold text-gray-800">Transaksi Terakhir</h3>
                            <Link :href="route('transaksi.penjualan.index')" class="text-sm text-primary-600 hover:text-primary-800 font-medium">Lihat Semua</Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50/50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Transaksi</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    <tr v-for="trx in transaksi_terakhir" :key="trx.id" class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ trx.no_struk }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ trx.pelanggan?.nama || 'Umum' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatTime(trx.tanggal) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatRupiah(trx.total_akhir) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                                {{ trx.status }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="transaksi_terakhir.length === 0">
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada transaksi hari ini.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-100">
                        <div class="px-6 py-5 border-b border-gray-100 bg-white">
                            <h3 class="text-lg font-semibold text-gray-800">Aksi Cepat</h3>
                        </div>
                        <div class="p-6 grid grid-cols-2 gap-4">
                            <Link :href="route('transaksi.shift-kasir.index')" class="flex flex-col items-center justify-center p-4 bg-primary-50 rounded-xl hover:bg-primary-100 transition duration-200 border border-primary-100 group">
                                <div class="p-3 bg-white rounded-full shadow-sm text-primary-600 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                                <span class="mt-3 text-sm font-medium text-primary-900">Buka Kasir</span>
                            </Link>
                            <Link :href="route('master.produk.index')" class="flex flex-col items-center justify-center p-4 bg-accent-50 rounded-xl hover:bg-accent-100 transition duration-200 border border-accent-100 group">
                                <div class="p-3 bg-white rounded-full shadow-sm text-accent-600 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                <span class="mt-3 text-sm font-medium text-accent-900">Produk</span>
                            </Link>
                            <Link :href="route('laporan.stok-menipis')" class="flex flex-col items-center justify-center p-4 bg-emerald-50 rounded-xl hover:bg-emerald-100 transition duration-200 border border-emerald-100 group">
                                <div class="p-3 bg-white rounded-full shadow-sm text-emerald-600 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <span class="mt-3 text-sm font-medium text-emerald-900">Stok Peringatan</span>
                            </Link>
                            <Link :href="route('laporan.penjualan')" class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-xl hover:bg-purple-100 transition duration-200 border border-purple-100 group">
                                <div class="p-3 bg-white rounded-full shadow-sm text-purple-600 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                </div>
                                <span class="mt-3 text-sm font-medium text-purple-900">Laporan</span>
                            </Link>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>
