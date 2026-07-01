<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';

import StrukKasir from '@/Components/StrukKasir.vue';

const props = defineProps({
    activeShift: Object,
    pelanggans: Array,
    members: Array,
    promos: Array,
    produks: Array,
    pengaturan: Object
});

const page = usePage();

// UI State
const searchQuery = ref('');
const barcodeInput = ref(null);
const cart = ref([]);
const selectedPelanggan = ref('');
const selectedPromo = ref('');
const isCheckoutModalOpen = ref(false);
const nominalBayar = ref(0);

// Struk Printing
const isStrukModalOpen = ref(false);
const strukData = ref(null);

watch(() => page.props.flash?.cetak_struk, (newStruk) => {
    if (newStruk) {
        strukData.value = newStruk;
        isStrukModalOpen.value = true;
    }
}, { immediate: true });

const closeStruk = () => {
    isStrukModalOpen.value = false;
    strukData.value = null;
    if (page.props.flash) page.props.flash.cetak_struk = null;
    if (barcodeInput.value) barcodeInput.value.focus();
};

// Filter Products
const filteredProduks = computed(() => {
    if (!searchQuery.value) return props.produks;
    const query = searchQuery.value.trim().toLowerCase();
    return props.produks.filter(p => 
        p.nama.toLowerCase().includes(query) || 
        p.kode_produk.toLowerCase().includes(query) ||
        (p.barcode && String(p.barcode).toLowerCase().includes(query))
    );
});

// Watcher untuk auto-add saat user melakukan copy-paste barcode secara presisi
watch(searchQuery, (newVal) => {
    if (!newVal) return;
    const query = newVal.trim().toLowerCase();
    // Cari produk yang barcodenya SAMA PERSIS dengan teks yang di-paste
    const exactMatch = props.produks.find(p => 
        p.kode_produk.toLowerCase() === query || 
        (p.barcode && String(p.barcode).toLowerCase() === query)
    );
    if (exactMatch) {
        addAndClear(exactMatch);
    }
});

// Cart Logic
const addToCart = (produk) => {
    // Cari apakah sudah ada di cart dengan satuan base (default)
    const existing = cart.value.find(item => item.produk_id === produk.id);
    const baseSatuan = produk.satuans.find(s => s.is_base) || produk.satuans[0];
    
    if (existing) {
        existing.qty++;
    } else {
        cart.value.push({
            produk_id: produk.id,
            kode_produk: produk.kode_produk,
            nama: produk.nama,
            satuan_id: baseSatuan.id,
            satuans: produk.satuans,
            qty: 1,
            harga_satuan: baseSatuan.harga_jual,
            konversi_ke_base: baseSatuan.konversi,
            diskon: 0
        });
    }
};

const handleSearchSubmit = () => {
    const query = searchQuery.value.trim().toLowerCase();
    const exactMatch = props.produks.find(p => 
        p.kode_produk.toLowerCase() === query || 
        (p.barcode && String(p.barcode).toLowerCase() === query)
    );
    if (exactMatch) {
        addAndClear(exactMatch);
        return;
    }
    if (filteredProduks.value.length === 1) {
        addAndClear(filteredProduks.value[0]);
    }
};

const addAndClear = (produk) => {
    addToCart(produk);
    searchQuery.value = '';
    if (barcodeInput.value) barcodeInput.value.focus();
};

const updateItemSatuan = (item, newSatuanId) => {
    const satuan = item.satuans.find(s => s.id === newSatuanId);
    if (satuan) {
        item.satuan_id = satuan.id;
        item.harga_satuan = satuan.harga_jual;
        item.konversi_ke_base = satuan.konversi;
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

// --- Scanner Auto-Detection Logic ---
let barcodeBuffer = '';
let barcodeTimeout = null;

const handleScannerDetection = (e) => {
    // Abaikan jika user menekan spasi, shift, ctrl, dll
    if (e.key.length !== 1 && e.key !== 'Enter') return;
    
    // Abaikan jika fokus ada di dalam input selain pencarian agar tidak mengganggu (kecuali kalau memang mau global 100%)
    // Pengecualian: jika fokus ada di dalam textarea catatan, jangan hijack.
    if (document.activeElement && document.activeElement.tagName === 'TEXTAREA') return;

    if (e.key === 'Enter' && barcodeBuffer.trim().length > 2) {
        // Cari produk berdasarkan barcode
        const query = barcodeBuffer.trim().toLowerCase();
        const exactMatch = props.produks.find(p => 
            (p.barcode && String(p.barcode).toLowerCase() === query) || 
            p.kode_produk.toLowerCase() === query
        );
        
        if (exactMatch) {
            addToCart(exactMatch);
            // Beri notifikasi visual/suara jika perlu (disini kita auto focus saja)
            if (barcodeInput.value) barcodeInput.value.focus();
            e.preventDefault(); // Mencegah form submit tak terduga
        }
        barcodeBuffer = '';
    } else if (e.key.length === 1) {
        barcodeBuffer += e.key;
        if (barcodeTimeout) clearTimeout(barcodeTimeout);
        // Scanner USB biasa mengetik 1 karakter dalam ~10-20ms. 
        // 50ms adalah batas aman membedakan ketikan manusia vs scanner.
        barcodeTimeout = setTimeout(() => {
            barcodeBuffer = '';
        }, 50);
    }
};

onMounted(() => {
    if (barcodeInput.value) barcodeInput.value.focus();
    window.addEventListener('keydown', handleScannerDetection);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleScannerDetection);
});
// -------------------------------------

// Calculations
const subtotalCart = computed(() => {
    return cart.value.reduce((total, item) => total + (item.qty * item.harga_satuan) - item.diskon, 0);
});

const calculatedPromo = computed(() => {
    if (!selectedPromo.value) return 0;
    const promo = props.promos.find(p => p.id === selectedPromo.value);
    if (!promo) return 0;

    if (subtotalCart.value < Number(promo.minimal_belanja)) return 0; // Tidak penuhi syarat

    if (promo.jenis === 'persentase') {
        return (subtotalCart.value * promo.nilai) / 100;
    } else if (promo.jenis === 'nominal') {
        return Number(promo.nilai);
    }
    return 0;
});

const pajakPersen = computed(() => {
    return Number(props.pengaturan?.pajak_persen || 0);
});

const calculatedPajak = computed(() => {
    const dpp = Math.max(0, subtotalCart.value - calculatedPromo.value);
    return (dpp * pajakPersen.value) / 100;
});

const totalAkhir = computed(() => {
    return Math.max(0, subtotalCart.value - calculatedPromo.value) + calculatedPajak.value;
});

const kembalian = computed(() => {
    return Math.max(0, nominalBayar.value - totalAkhir.value);
});

// Formatting
const formatRupiah = (num) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(num);
};

// Checkout
const checkoutForm = useForm({
    pelanggan_id: null,
    member_id: null,
    promo_id: null,
    items: [],
    metode_pembayaran: 'tunai',
    nominal_bayar: 0,
    diskon_tambahan: 0,
    pajak: 0,
    total_akhir: 0
});

const openCheckout = () => {
    if (cart.value.length === 0) return;
    nominalBayar.value = totalAkhir.value; // set default
    isCheckoutModalOpen.value = true;
};

const processCheckout = () => {
    if (nominalBayar.value < totalAkhir.value) {
        alert('Nominal bayar kurang dari total belanja!');
        return;
    }

    checkoutForm.pelanggan_id = selectedPelanggan.value;
    checkoutForm.promo_id = selectedPromo.value;
    checkoutForm.items = cart.value;
    checkoutForm.nominal_bayar = nominalBayar.value;
    checkoutForm.diskon_tambahan = calculatedPromo.value;
    checkoutForm.pajak = calculatedPajak.value;
    checkoutForm.total_akhir = totalAkhir.value;

    checkoutForm.post(route('transaksi.kasir.store'), {
        onSuccess: () => {
            isCheckoutModalOpen.value = false;
            cart.value = [];
            selectedPelanggan.value = '';
            selectedPromo.value = '';
            nominalBayar.value = 0;
            searchQuery.value = '';
            if (barcodeInput.value) barcodeInput.value.focus();
        },
    });
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
    if (barcodeInput.value) barcodeInput.value.focus();
    timer = setInterval(() => {
        currentTime.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});
</script>

<template>
    <Head title="Point of Sales (Kasir)" />

    <!-- Full Screen POS Layout (No Sidebar) -->
    <div class="h-screen bg-gray-100 flex flex-col font-sans overflow-hidden">
        
        <!-- Navbar Kasir -->
        <header class="bg-gray-900 text-white h-14 flex items-center justify-between px-4 shadow-md shrink-0 z-10">
            <div class="flex items-center space-x-4">
                <Link :href="route('dashboard')" class="text-gray-300 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </Link>
                <h1 class="text-xl font-bold tracking-wider">POS <span class="font-light text-primary-400">KASIR</span></h1>
                
                <!-- Realtime Clock -->
                <div class="ml-6 pl-6 border-l border-gray-700 text-gray-300 font-mono flex flex-col justify-center">
                    <div class="text-xs text-gray-400 leading-none mb-1">{{ formattedDate }}</div>
                    <div class="text-sm font-semibold tracking-widest leading-none text-primary-200">{{ formattedTime }}</div>
                </div>
            </div>
            
            <div class="flex items-center space-x-4 text-sm">
                <div class="bg-gray-800 px-3 py-1 rounded-full flex items-center border border-gray-700">
                    <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                    Kasir: <span class="font-semibold ml-1">{{ $page.props.auth.user.name }}</span>
                </div>
                <!-- Shift Info -->
                <div class="bg-primary-900/50 text-primary-200 px-3 py-1 rounded-md border border-primary-800">
                    Shift Aktif
                </div>
            </div>
        </header>

        <!-- Main Workspace -->
        <main class="flex-1 flex overflow-hidden">
            
            <!-- Left Panel (Data Table & Search) -->
            <div class="w-3/4 flex flex-col bg-white border-r border-gray-200">
                <!-- Search Bar -->
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex space-x-2 shrink-0">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input 
                            ref="barcodeInput"
                            v-model="searchQuery"
                            type="text" 
                            class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-lg transition" 
                            placeholder="Scan Barcode atau ketik nama produk (Tekan Enter)..."
                            @keydown.enter.prevent="handleSearchSubmit"
                        >
                        <!-- Autocomplete Dropdown -->
                        <ul v-if="searchQuery && filteredProduks.length > 0" class="absolute z-50 w-full bg-white border border-gray-200 mt-1 max-h-60 overflow-auto shadow-xl rounded-md">
                            <li v-for="produk in filteredProduks" :key="produk.id" @click="addAndClear(produk)" class="p-3 hover:bg-primary-50 cursor-pointer flex justify-between border-b items-center transition">
                                <div>
                                    <div class="font-bold text-gray-900">{{ produk.nama }}</div>
                                    <div class="text-xs text-gray-500">{{ produk.kode_produk }}</div>
                                </div>
                                <div class="font-semibold text-primary-600">
                                    {{ formatRupiah(produk.satuans[0]?.harga_jual || 0) }}
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Cart Table -->
                <div class="flex-1 overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100 sticky top-0 z-10 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-12">No</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32">Kode</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Produk</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-24">Qty</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider w-32">Satuan</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider w-32">Harga</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-gray-600 uppercase tracking-wider w-36">Subtotal</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-600 uppercase tracking-wider w-16">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <tr v-for="(item, index) in cart" :key="index" class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">{{ index + 1 }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-mono">{{ item.kode_produk }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ item.nama }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <input type="number" v-model="item.qty" min="0.01" step="any" class="w-20 border-gray-300 rounded-md text-center text-sm p-1 focus:ring-primary-500 focus:border-primary-500">
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    <select :value="item.satuan_id" @change="updateItemSatuan(item, parseInt($event.target.value))" class="block w-full border-gray-300 rounded-md text-sm p-1 focus:ring-primary-500 focus:border-primary-500">
                                        <option v-for="s in item.satuans" :key="s.id" :value="s.id">{{ s.nama }}</option>
                                    </select>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ formatRupiah(item.harga_satuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-bold text-primary-700 text-right">{{ formatRupiah(item.qty * item.harga_satuan) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-center">
                                    <button @click="removeFromCart(index)" class="text-red-400 hover:text-red-600 transition p-1 bg-red-50 hover:bg-red-100 rounded-full">
                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="cart.length === 0">
                                <td colspan="8" class="px-4 py-16 text-center">
                                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="text-gray-500 font-medium">Keranjang masih kosong.</p>
                                    <p class="text-gray-400 text-sm mt-1">Scan barcode atau ketik nama produk pada kolom pencarian di atas.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel (Summary & Checkout) -->
            <div class="w-1/4 flex flex-col bg-gray-50 shadow-inner z-20">
                
                <!-- Customer & Promo Selectors -->
                <div class="p-4 border-b border-gray-200 bg-white space-y-4 shrink-0">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Pelanggan</label>
                        <select v-model="selectedPelanggan" class="block w-full border-gray-300 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500 shadow-sm bg-gray-50 focus:bg-white transition">
                            <option value="">Umum / Walk-in</option>
                            <option v-for="p in pelanggans" :key="p.id" :value="p.id">{{ p.nama }} {{ p.is_member ? '(Member)' : '' }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Promo / Diskon</label>
                        <select v-model="selectedPromo" class="block w-full border-gray-300 rounded-lg text-sm focus:border-primary-500 focus:ring-primary-500 shadow-sm bg-gray-50 focus:bg-white transition">
                            <option value="">-- Pilih Promo (Opsional) --</option>
                            <option v-for="pr in promos" :key="pr.id" :value="pr.id">
                                {{ pr.nama }} (Min. {{ formatRupiah(pr.minimal_belanja || 0) }})
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex-1 bg-gray-50"></div>

                <!-- Payment Summary & Action -->
                <div class="bg-white border-t border-gray-200 p-6 shrink-0 shadow-2xl">
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between text-gray-500">
                            <span class="font-medium">Subtotal</span>
                            <span class="font-bold text-gray-800">{{ formatRupiah(subtotalCart) }}</span>
                        </div>
                        <div v-if="calculatedPromo > 0" class="flex justify-between text-green-600">
                            <span class="font-medium">Potongan Promo</span>
                            <span class="font-bold">- {{ formatRupiah(calculatedPromo) }}</span>
                        </div>
                        <div v-if="pajakPersen > 0" class="flex justify-between text-red-500">
                            <span class="font-medium">Pajak ({{ pajakPersen }}%)</span>
                            <span class="font-bold">+ {{ formatRupiah(calculatedPajak) }}</span>
                        </div>
                        
                        <div class="pt-4 border-t border-gray-200">
                            <div class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Total Tagihan</div>
                            <div class="text-4xl font-black text-primary-700 break-all leading-tight">
                                {{ formatRupiah(totalAkhir) }}
                            </div>
                        </div>
                    </div>
                    
                    <button 
                        @click="openCheckout" 
                        :disabled="cart.length === 0"
                        class="w-full bg-primary-600 text-white font-black tracking-widest text-xl rounded-xl py-5 hover:bg-primary-700 active:bg-primary-800 focus:ring-4 focus:ring-primary-300 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-md hover:shadow-lg"
                    >
                        BAYAR (F9)
                    </button>
                </div>
            </div>
        </main>

        <!-- Modal Pembayaran (Checkout) -->
        <Modal :show="isCheckoutModalOpen" @close="isCheckoutModalOpen = false" maxWidth="md">
            <div class="p-6 bg-gray-50 rounded-lg">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 border-b pb-4 text-center">Pembayaran Kasir</h2>
                
                <div class="bg-primary-900 text-white rounded-xl p-4 text-center mb-6 shadow-inner">
                    <p class="text-primary-200 text-sm font-medium uppercase tracking-wider mb-1">Total Tagihan</p>
                    <p class="text-4xl font-black">{{ formatRupiah(totalAkhir) }}</p>
                </div>

                <form @submit.prevent="processCheckout" class="space-y-5">
                    
                    <div v-if="Object.keys(checkoutForm.errors).length > 0" class="p-4 bg-red-100 text-red-700 rounded-md mb-4 text-sm font-medium">
                        Ada kesalahan:
                        <ul class="list-disc pl-5 mt-1 text-xs">
                            <li v-for="(error, key) in checkoutForm.errors" :key="key">{{ error }}</li>
                        </ul>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" v-model="checkoutForm.metode_pembayaran" value="tunai" class="peer sr-only">
                                <div class="rounded-lg border-2 border-gray-200 p-3 text-center peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700 font-semibold transition">
                                    Tunai Cash
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" v-model="checkoutForm.metode_pembayaran" value="qris" class="peer sr-only">
                                <div class="rounded-lg border-2 border-gray-200 p-3 text-center peer-checked:border-primary-500 peer-checked:bg-primary-50 peer-checked:text-primary-700 font-semibold transition">
                                    QRIS / EDC
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Uang Diterima / Nominal (Rp)</label>
                        <CurrencyInput 
                            v-model="nominalBayar" 
                            input-class="block w-full text-2xl font-bold border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 p-3" 
                            required
                        />
                        <!-- Quick Cash Buttons -->
                        <div class="grid grid-cols-4 gap-2 mt-2" v-if="checkoutForm.metode_pembayaran === 'tunai'">
                            <button type="button" @click="nominalBayar = totalAkhir" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold py-2 rounded">Uang Pas</button>
                            <button type="button" @click="nominalBayar = 50000" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold py-2 rounded">50rb</button>
                            <button type="button" @click="nominalBayar = 100000" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold py-2 rounded">100rb</button>
                            <button type="button" @click="nominalBayar = 200000" class="bg-gray-200 hover:bg-gray-300 text-gray-800 text-xs font-bold py-2 rounded">200rb</button>
                        </div>
                    </div>

                    <div v-if="checkoutForm.metode_pembayaran === 'tunai'" class="bg-white border border-gray-200 p-4 rounded-lg flex justify-between items-center">
                        <span class="text-gray-600 font-medium text-sm">Kembalian</span>
                        <span class="text-2xl font-bold" :class="kembalian < 0 ? 'text-red-500' : 'text-green-600'">
                            {{ formatRupiah(kembalian) }}
                        </span>
                    </div>

                    <div class="pt-4 flex space-x-3">
                        <SecondaryButton @click="isCheckoutModalOpen = false" class="w-1/3 justify-center py-3">
                            Batal
                        </SecondaryButton>
                        <PrimaryButton 
                            class="w-2/3 justify-center py-3 text-lg" 
                            :class="{ 'opacity-50': checkoutForm.processing || nominalBayar < totalAkhir }" 
                            :disabled="checkoutForm.processing || nominalBayar < totalAkhir"
                        >
                            Selesaikan Transaksi
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- MODAL CETAK STRUK menggunakan komponen eksternal -->
        <StrukKasir 
            :show="isStrukModalOpen" 
            :strukData="strukData" 
            :pengaturan="pengaturan" 
            @close="closeStruk" 
        />

    </div>
</template>
