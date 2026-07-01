<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';

const props = defineProps({
    suppliers: Array,
    gudangs: Array,
    produks: Array
});

// Format currency
const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(number);
};

// Cart State
const cart = ref([]);
const searchProduct = ref('');
const showProductDropdown = ref(false);

const filteredProducts = computed(() => {
    if (!searchProduct.value) return [];
    const term = searchProduct.value.toLowerCase();
    return props.produks.filter(p => 
        p.nama.toLowerCase().includes(term) || 
        (p.kode_produk && p.kode_produk.toLowerCase().includes(term))
    );
});

const addToCart = (product) => {
    // Default to base unit
    const defaultUnit = product.satuans.find(s => s.is_base) || product.satuans[0];
    
    // Cek jika sudah ada di cart dengan satuan yang sama
    const existing = cart.value.find(item => item.produk_id === product.id && item.satuan_id === defaultUnit.id);
    
    if (existing) {
        existing.qty += 1;
    } else {
        cart.value.push({
            produk_id: product.id,
            kode_produk: product.kode_produk,
            nama: product.nama,
            satuan_id: defaultUnit.id,
            satuans: product.satuans,
            qty: 1,
            harga_satuan: 0,
            konversi_ke_base: defaultUnit.konversi,
            allow_fraction: product.allow_fraction
        });
    }
    
    searchProduct.value = '';
    showProductDropdown.value = false;
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const updateSatuan = (item, event) => {
    const newSatuanId = parseInt(event.target.value);
    const newSatuan = item.satuans.find(s => s.id === newSatuanId);
    item.satuan_id = newSatuanId;
    item.konversi_ke_base = newSatuan.konversi;
};

const totalHarga = computed(() => {
    return cart.value.reduce((total, item) => total + (item.qty * item.harga_satuan), 0);
});

// Submit Form
const form = useForm({
    supplier_id: '',
    gudang_id: '',
    tanggal: new Date().toISOString().split('T')[0],
    no_faktur: '',
    items: []
});

const submit = () => {
    if (cart.value.length === 0) {
        alert('Keranjang belanja masih kosong!');
        return;
    }
    
    form.items = cart.value.map(item => ({
        produk_id: item.produk_id,
        satuan_id: item.satuan_id,
        qty: item.qty,
        harga_satuan: item.harga_satuan,
        konversi_ke_base: item.konversi_ke_base
    }));
    
    form.post(route('transaksi.pembelian.store'), {
        onSuccess: () => {
            // Success handled by redirect
        }
    });
};
</script>

<template>
    <AppLayout title="Buat Pembelian (PO)">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Buat Transaksi Pembelian Baru
                </h2>
                <Link :href="route('transaksi.pembelian.index')" class="text-sm text-gray-500 hover:text-gray-700">
                    &larr; Kembali ke Riwayat
                </Link>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <!-- Left: Cart & Product Search -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Product Search Bar -->
                        <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100 relative">
                            <InputLabel value="Cari & Tambah Produk" class="mb-2" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input 
                                    v-model="searchProduct" 
                                    @focus="showProductDropdown = true"
                                    type="text" 
                                    placeholder="Ketik nama atau kode produk (Barcode)..." 
                                    class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-md leading-5 bg-gray-50 focus:bg-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-150 ease-in-out text-lg" 
                                />
                            </div>
                            
                            <!-- Dropdown Search Results -->
                            <div v-if="searchProduct && showProductDropdown" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm left-0 border border-gray-100">
                                <ul v-if="filteredProducts.length > 0">
                                    <li v-for="product in filteredProducts" :key="product.id" @click="addToCart(product)" class="cursor-pointer select-none relative py-3 pl-4 pr-9 hover:bg-primary-50 transition-colors">
                                        <div class="flex items-center justify-between">
                                            <span class="font-medium truncate">{{ product.nama }}</span>
                                            <span class="text-xs text-gray-500">{{ product.kode_produk || 'No Kode' }}</span>
                                        </div>
                                    </li>
                                </ul>
                                <div v-else class="py-3 px-4 text-sm text-gray-500">
                                    Produk tidak ditemukan.
                                </div>
                            </div>
                        </div>

                        <!-- Cart Table -->
                        <div class="bg-white shadow-sm sm:rounded-xl overflow-hidden border border-gray-100">
                            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                                <h3 class="font-semibold text-gray-800">Daftar Item Dibeli</h3>
                                <span class="bg-primary-100 text-primary-800 text-xs font-bold px-2.5 py-0.5 rounded-full">{{ cart.length }} Item</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Satuan</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Qty</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Harga Beli / Satuan</th>
                                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Subtotal</th>
                                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-100">
                                        <tr v-for="(item, index) in cart" :key="index" class="hover:bg-gray-50/50">
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="font-medium text-gray-900">{{ item.nama }}</div>
                                                <div class="text-xs text-gray-500">{{ item.kode_produk }}</div>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <select :value="item.satuan_id" @change="updateSatuan(item, $event)" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                                                    <option v-for="s in item.satuans" :key="s.id" :value="s.id">
                                                        {{ s.nama }}
                                                    </option>
                                                </select>
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <input :type="item.allow_fraction ? 'number' : 'number'" :step="item.allow_fraction ? '0.01' : '1'" min="0.01" v-model="item.qty" class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-center" />
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <CurrencyInput v-model="item.harga_satuan" input-class="block w-full border-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm text-right" placeholder="0" />
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-right font-semibold text-gray-900">
                                                {{ formatRupiah(item.qty * item.harga_satuan) }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-center">
                                                <button @click="removeFromCart(index)" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-md hover:bg-red-100 transition-colors" title="Hapus">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr v-if="cart.length === 0">
                                            <td colspan="6" class="px-4 py-12 text-center text-gray-500 bg-gray-50/30">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                Keranjang belanja masih kosong. Cari produk di atas untuk menambah.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Checkout Details -->
                    <div class="space-y-6">
                        <!-- Summary -->
                        <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-3">Ringkasan Total</h3>
                            <div class="flex justify-between items-end mb-2">
                                <span class="text-gray-500">Total Pembelian</span>
                                <span class="text-3xl font-bold text-gray-900">{{ formatRupiah(totalHarga) }}</span>
                            </div>
                        </div>

                        <!-- Form Details -->
                        <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 border-b border-gray-100 pb-3">Detail Referensi</h3>
                            
                            <form @submit.prevent="submit" class="space-y-4">
                                <div>
                                    <InputLabel for="supplier_id" value="Supplier / Pemasok" />
                                    <select id="supplier_id" v-model="form.supplier_id" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm" required>
                                        <option value="" disabled>Pilih Supplier</option>
                                        <option v-for="sup in suppliers" :key="sup.id" :value="sup.id">{{ sup.nama }}</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.supplier_id" />
                                </div>

                                <div>
                                    <InputLabel for="gudang_id" value="Gudang Penerima (Stok Masuk)" />
                                    <select id="gudang_id" v-model="form.gudang_id" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm" required>
                                        <option value="" disabled>Pilih Gudang</option>
                                        <option v-for="gud in gudangs" :key="gud.id" :value="gud.id">{{ gud.nama }} ({{ gud.jenis }})</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.gudang_id" />
                                </div>

                                <div>
                                    <InputLabel for="tanggal" value="Tanggal Pembelian" />
                                    <TextInput id="tanggal" v-model="form.tanggal" type="date" class="mt-1 block w-full" required />
                                    <InputError class="mt-2" :message="form.errors.tanggal" />
                                </div>

                                <div>
                                    <InputLabel for="no_faktur" value="No. Faktur (Opsional)" />
                                    <TextInput id="no_faktur" v-model="form.no_faktur" type="text" class="mt-1 block w-full" placeholder="INV-2023..." />
                                    <InputError class="mt-2" :message="form.errors.no_faktur" />
                                </div>
                                
                                <InputError class="mt-2" :message="form.errors.items" />

                                <div class="pt-4 border-t border-gray-100 mt-6">
                                    <PrimaryButton class="w-full justify-center py-3 bg-primary-600 hover:bg-primary-700" :class="{ 'opacity-25': form.processing }" :disabled="form.processing || cart.length === 0">
                                        SIMPAN & TERIMA STOK
                                    </PrimaryButton>
                                    <p class="text-xs text-center text-gray-500 mt-2">
                                        *Menyimpan transaksi ini akan langsung menambah stok pada gudang yang dipilih.
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </AppLayout>
</template>
