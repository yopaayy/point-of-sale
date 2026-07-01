<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
    title: String,
});

const isSidebarOpen = ref(false);

const logout = () => {
    router.post(route('logout'));
};

// Realtime Clock
const currentTime = ref(new Date());
let timer = null;

const formattedTime = computed(() => {
    return currentTime.value.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZoneName: 'short'
    });
});
const formattedDate = computed(() => {
    return currentTime.value.toLocaleDateString('id-ID', {
        weekday: 'short',
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
});

onMounted(() => {
    timer = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-50 flex">
            <!-- Sidebar -->
            <div 
                :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto flex flex-col"
            >
                <div class="flex items-center justify-center h-16 border-b border-gray-100 px-4">
                    <Link :href="route('dashboard')" class="flex items-center space-x-2">
                        <ApplicationMark class="block h-8 w-auto text-primary-600" />
                        <span class="font-bold text-xl text-gray-800 tracking-tight">{{ $page.props.toko?.nama || 'Toko Ku' }}</span>
                    </Link>
                </div>

                <div class="overflow-y-auto overflow-x-hidden flex-grow p-4 space-y-1">
                    <!-- Dashboard -->
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-2">Main Menu</p>
                    <Link :href="route('dashboard')" 
                          :class="route().current('dashboard') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('dashboard') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </Link>

                    <!-- Master Data -->
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Master Data</p>
                    <Link :href="route('master.kategori.index')" 
                          :class="route().current('master.kategori.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('master.kategori.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Kategori
                    </Link>
                    <Link :href="route('master.merk.index')" 
                          :class="route().current('master.merk.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('master.merk.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        Merk
                    </Link>
                    <Link :href="route('master.satuan.index')" 
                          :class="route().current('master.satuan.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('master.satuan.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                        </svg>
                        Satuan (Base Unit)
                    </Link>
                    <Link :href="route('master.produk.index')" 
                          :class="route().current('master.produk.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('master.produk.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        Produk
                    </Link>
                    
                    <!-- Transaksi -->
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Transaksi</p>
                    <Link :href="route('transaksi.shift-kasir.index')" 
                          :class="route().current('transaksi.shift-kasir.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('transaksi.shift-kasir.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Shift Kasir
                    </Link>
                    
                    <Link :href="route('transaksi.kasir.index')" 
                          :class="route().current('transaksi.kasir.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('transaksi.kasir.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Penjualan (POS)
                    </Link>

                    <Link :href="route('transaksi.penjualan.index')" 
                          :class="route().current('transaksi.penjualan.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('transaksi.penjualan.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Riwayat Penjualan
                    </Link>

                    <Link :href="route('transaksi.pembelian.index')" 
                          :class="route().current('transaksi.pembelian.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('transaksi.pembelian.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Pembelian (Restock)
                    </Link>

                    <!-- Laporan & Analitik -->
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Laporan & Analitik</p>
                    <Link :href="route('laporan.penjualan')" 
                          :class="route().current('laporan.penjualan') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('laporan.penjualan') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                        Laporan Penjualan
                    </Link>
                    <Link :href="route('laporan.stok-menipis')" 
                          :class="route().current('laporan.stok-menipis') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('laporan.stok-menipis') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Stok Menipis
                    </Link>
                    <Link :href="route('laporan.kartu-stok.index')" 
                          :class="route().current('laporan.kartu-stok.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('laporan.kartu-stok.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Kartu Stok
                    </Link>

                    <!-- Pengaturan -->
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Sistem</p>
                    <Link :href="route('pengaturan.index')" 
                          :class="route().current('pengaturan.*') ? 'bg-primary-50 text-primary-600' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                          class="group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors">
                        <svg class="mr-3 h-5 w-5 flex-shrink-0" :class="route().current('pengaturan.*') ? 'text-primary-600' : 'text-gray-400 group-hover:text-gray-500'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Pengaturan Toko
                    </Link>
                </div>
            </div>

            <!-- Mobile Overlay -->
            <div 
                v-if="isSidebarOpen" 
                @click="isSidebarOpen = false" 
                class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden transition-opacity"
            ></div>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Top Navbar -->
                <header class="bg-white border-b border-gray-100 flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Hamburger / Mobile Sidebar Toggle -->
                    <button 
                        @click="isSidebarOpen = true" 
                        class="text-gray-500 hover:text-gray-700 focus:outline-none lg:hidden"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    <!-- Page Title / Spacer -->
                    <div class="hidden lg:block text-xl font-semibold text-gray-800">
                        <slot name="header" />
                    </div>

                    <!-- Right Side Dropdown & Clock -->
                    <div class="flex items-center space-x-4 ml-auto lg:ml-0">
                        <!-- Realtime Clock -->
                        <div class="hidden md:flex flex-col items-end border-r border-gray-200 pr-4 mr-2">
                            <span class="text-xs text-gray-500 font-medium">{{ formattedDate }}</span>
                            <span class="text-sm font-bold text-primary-600 font-mono tracking-wider">{{ formattedTime }}</span>
                        </div>

                        <div class="relative">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <img class="h-8 w-8 rounded-full object-cover mr-2" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name" v-if="$page.props.jetstream.managesProfilePhotos">
                                        <div>{{ $page.props.auth.user.name }}</div>

                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </template>

                                <template #content>
                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Manage Account
                                    </div>
                                    <DropdownLink :href="route('profile.show')">
                                        Profile
                                    </DropdownLink>
                                    <div class="border-t border-gray-100"></div>
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button">
                                            Log Out
                                        </DropdownLink>
                                    </form>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                    <!-- Mobile Header (Shown when Top Navbar space is limited) -->
                    <div class="block lg:hidden bg-white shadow-sm border-b px-4 py-4 sm:px-6">
                        <slot name="header" />
                    </div>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
