<script setup>
import { ref, computed, watch } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import Checkbox from '@/Components/Checkbox.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';

const props = defineProps({
    produk: Object,
    kategoris: Array,
    merks: Array,
    satuans: Array,
    units: Array
});

const isEditing = computed(() => !!props.produk);

// Initialize form
const form = useForm({
    kode_produk: props.produk?.kode_produk || '',
    barcode: props.produk?.barcode || '',
    nama: props.produk?.nama || '',
    kategori_id: props.produk?.kategori_id || '',
    merk_id: props.produk?.merk_id || '',
    base_unit_id: props.produk?.base_unit_id || '',
    allow_fraction: props.produk?.allow_fraction || false,
    deskripsi: props.produk?.deskripsi || '',
    gambar: null,
    units: props.units?.length ? props.units : [
        { satuan_id: '', konversi: 1, harga_modal: 0, harga_jual: 0 }
    ]
});

// Image preview
const photoPreview = ref(null);
const photoInput = ref(null);

const selectNewPhoto = () => {
    photoInput.value.click();
};

const updatePhotoPreview = () => {
    const photo = photoInput.value.files[0];

    if (!photo) return;

    const reader = new FileReader();
    reader.onload = (e) => {
        photoPreview.value = e.target.result;
    };
    reader.readAsDataURL(photo);
    form.gambar = photo;
};

// Units logic
const addUnit = () => {
    form.units.push({ satuan_id: '', konversi: 1, harga_modal: 0, harga_jual: 0 });
};

const removeUnit = (index) => {
    if (form.units.length > 1) {
        form.units.splice(index, 1);
    }
};

// Sync base unit with the first unit
watch(() => form.base_unit_id, (newVal) => {
    if (newVal && form.units.length > 0) {
        form.units[0].satuan_id = newVal;
        form.units[0].konversi = 1;
    }
});

const submitForm = () => {
    if (isEditing.value) {
        form.post(`/master/produk/${props.produk.id}/update`, {
            forceFormData: true
        });
    } else {
        form.post(route('master.produk.store'), {
            forceFormData: true
        });
    }
};
</script>

<template>
    <AppLayout :title="isEditing ? 'Edit Produk' : 'Tambah Produk'">
        <template #header>
            <div class="flex items-center">
                <Link :href="route('master.produk.index')" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ isEditing ? 'Edit Produk' : 'Tambah Produk Baru' }}
                </h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submitForm">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Main Info Column -->
                        <div class="md:col-span-2 space-y-6">
                            <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                                <h3 class="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Informasi Utama</h3>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div class="col-span-1">
                                        <InputLabel for="kode_produk" value="Kode Produk (SKU)" />
                                        <TextInput id="kode_produk" v-model="form.kode_produk" type="text" class="mt-1 block w-full bg-gray-100 text-gray-500 cursor-not-allowed" disabled :placeholder="isEditing ? form.kode_produk : 'Otomatis (Misal: PRD000001)'" />
                                        <p class="text-xs text-gray-500 mt-1">Kode otomatis digenerate oleh sistem.</p>
                                    </div>
                                    <div class="col-span-1">
                                        <InputLabel for="barcode" value="Barcode (Scanner)" />
                                        <TextInput id="barcode" v-model="form.barcode" type="text" class="mt-1 block w-full" placeholder="Kosongkan untuk otomatis" />
                                        <InputError :message="form.errors.barcode" class="mt-2" />
                                        <p class="text-xs text-gray-500 mt-1">Bisa scan langsung ke sini.</p>
                                    </div>
                                    <div class="col-span-1 sm:col-span-2">
                                        <InputLabel for="nama" value="Nama Produk *" />
                                        <TextInput id="nama" v-model="form.nama" type="text" class="mt-1 block w-full" required />
                                        <InputError :message="form.errors.nama" class="mt-2" />
                                    </div>

                                    <div class="col-span-1">
                                        <InputLabel for="kategori_id" value="Kategori *" />
                                        <select id="kategori_id" v-model="form.kategori_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm mt-1 block w-full" required>
                                            <option value="" disabled>Pilih Kategori</option>
                                            <option v-for="cat in kategoris" :key="cat.id" :value="cat.id">{{ cat.nama }}</option>
                                        </select>
                                        <InputError :message="form.errors.kategori_id" class="mt-2" />
                                    </div>

                                    <div class="col-span-1">
                                        <InputLabel for="merk_id" value="Merk" />
                                        <select id="merk_id" v-model="form.merk_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm mt-1 block w-full">
                                            <option value="">Tanpa Merk</option>
                                            <option v-for="merk in merks" :key="merk.id" :value="merk.id">{{ merk.nama }}</option>
                                        </select>
                                        <InputError :message="form.errors.merk_id" class="mt-2" />
                                    </div>

                                    <div class="col-span-1 sm:col-span-2">
                                        <InputLabel for="deskripsi" value="Deskripsi" />
                                        <textarea id="deskripsi" v-model="form.deskripsi" rows="3" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm mt-1 block w-full"></textarea>
                                        <InputError :message="form.errors.deskripsi" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing & Units -->
                            <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                                <h3 class="text-lg font-medium text-gray-900 mb-6 border-b pb-2">Manajemen Satuan & Harga</h3>
                                
                                <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-6 items-start">
                                    <div>
                                        <InputLabel for="base_unit_id" value="Satuan Dasar (Base Unit) *" />
                                        <select id="base_unit_id" v-model="form.base_unit_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm mt-1 block w-full" required>
                                            <option value="" disabled>Pilih Satuan Dasar</option>
                                            <option v-for="sat in satuans" :key="sat.id" :value="sat.id">{{ sat.nama }}</option>
                                        </select>
                                        <p class="text-xs text-gray-500 mt-1">Satuan terkecil yang digunakan untuk stok. Contoh: Pcs, Botol.</p>
                                        <InputError :message="form.errors.base_unit_id" class="mt-2" />
                                    </div>

                                    <div class="flex items-center pt-6">
                                        <Checkbox id="allow_fraction" v-model:checked="form.allow_fraction" />
                                        <label for="allow_fraction" class="ml-2 text-sm text-gray-600">
                                            Izinkan penjualan dengan pecahan (Desimal)?
                                        </label>
                                    </div>
                                </div>

                                <div v-if="form.base_unit_id">
                                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                        <h4 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wider">Daftar Konversi & Harga</h4>
                                        
                                        <div class="space-y-4">
                                            <div v-for="(unit, index) in form.units" :key="index" class="grid grid-cols-12 gap-3 items-center bg-white p-3 rounded shadow-sm border border-gray-100 relative">
                                                
                                                <div class="col-span-12 sm:col-span-3">
                                                    <InputLabel value="Satuan" class="text-xs mb-1" />
                                                    <select v-model="unit.satuan_id" class="border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm w-full text-sm" :disabled="index === 0" required>
                                                        <option v-for="sat in satuans" :key="sat.id" :value="sat.id">{{ sat.nama }}</option>
                                                    </select>
                                                </div>

                                                <div class="col-span-12 sm:col-span-2">
                                                    <InputLabel value="Isi (Konversi)" class="text-xs mb-1" />
                                                    <TextInput v-model="unit.konversi" type="number" step="0.001" min="0.001" class="w-full text-sm" :disabled="index === 0" required />
                                                </div>

                                                <div class="col-span-12 sm:col-span-3">
                                                    <InputLabel value="Harga Modal" class="text-xs mb-1" />
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">Rp</span>
                                                        <CurrencyInput v-model="unit.harga_modal" input-class="w-full pl-9 text-sm" required />
                                                    </div>
                                                </div>

                                                <div class="col-span-12 sm:col-span-3">
                                                    <InputLabel value="Harga Jual" class="text-xs mb-1" />
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-sm">Rp</span>
                                                        <CurrencyInput v-model="unit.harga_jual" input-class="w-full pl-9 text-sm" required />
                                                    </div>
                                                </div>

                                                <div class="col-span-12 sm:col-span-1 flex justify-center mt-4 sm:mt-0">
                                                    <button v-if="index > 0" @click.prevent="removeUnit(index)" type="button" class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-full">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>

                                                <div class="col-span-12 text-xs text-red-500" v-if="form.errors[`units.${index}.satuan_id`] || form.errors[`units.${index}.konversi`]">
                                                    Periksa kembali isian satuan dan konversi.
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <button @click.prevent="addUnit" type="button" class="text-sm text-primary-600 font-medium hover:text-primary-800 flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Tambah Satuan Konversi (Misal: Dus, Pack)
                                            </button>
                                            <p class="text-xs text-gray-500 mt-2 italic">Contoh Konversi: 1 Dus = 40 Pcs (Base Unit). Maka isi Konversi dengan 40.</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-sm text-amber-600 bg-amber-50 p-4 rounded-lg mt-4 border border-amber-200">
                                    Silakan pilih Satuan Dasar terlebih dahulu.
                                </div>
                                
                                <InputError :message="form.errors.units" class="mt-2" />
                            </div>
                        </div>

                        <!-- Sidebar Column -->
                        <div class="space-y-6">
                            <!-- Image Upload -->
                            <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100">
                                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Gambar Produk</h3>
                                
                                <input type="file" class="hidden" ref="photoInput" @change="updatePhotoPreview" accept="image/*">
                                
                                <div class="mt-2 flex flex-col items-center">
                                    <div class="w-full h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden relative cursor-pointer" @click="selectNewPhoto">
                                        <template v-if="photoPreview">
                                            <img :src="photoPreview" class="w-full h-full object-cover" />
                                        </template>
                                        <template v-else-if="produk?.gambar">
                                            <img :src="'/storage/' + produk.gambar" class="w-full h-full object-cover" />
                                        </template>
                                        <template v-else>
                                            <div class="text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <p class="mt-1 text-sm text-gray-600">Klik untuk upload foto</p>
                                            </div>
                                        </template>
                                    </div>
                                    <InputError :message="form.errors.gambar" class="mt-2" />
                                    
                                    <SecondaryButton class="mt-4 w-full justify-center" @click.prevent="selectNewPhoto">
                                        Pilih Foto
                                    </SecondaryButton>
                                </div>
                            </div>
                            
                            <!-- Submit Block -->
                            <div class="bg-white shadow-sm sm:rounded-xl p-6 border border-gray-100 flex flex-col gap-3">
                                <PrimaryButton class="w-full justify-center py-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    {{ isEditing ? 'Simpan Perubahan' : 'Simpan Produk' }}
                                </PrimaryButton>
                                <Link :href="route('master.produk.index')" class="w-full">
                                    <SecondaryButton class="w-full justify-center py-3">
                                        Batal
                                    </SecondaryButton>
                                </Link>
                            </div>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
