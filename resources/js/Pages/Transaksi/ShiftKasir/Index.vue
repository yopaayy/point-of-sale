<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { Head, useForm, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import DialogModal from '@/Components/DialogModal.vue';
import Pagination from '@/Components/Pagination.vue';
import CurrencyInput from '@/Components/CurrencyInput.vue';

const props = defineProps({
    activeShift: Object,
    riwayatShift: Object,
    cabangs: Array
});

const openForm = useForm({
    modal_awal: 0,
    cabang_id: props.cabangs.length > 0 ? props.cabangs[0].id : ''
});

const closeForm = useForm({
    uang_fisik: 0
});

const page = usePage();
const errorModalOpen = ref(false);
const errorMessage = ref('');

onMounted(() => {
    if (page.props.errors && page.props.errors.error) {
        errorMessage.value = page.props.errors.error;
        errorModalOpen.value = true;
    }
});

watch(() => page.props.errors, (newErrors) => {
    if (newErrors && newErrors.error) {
        errorMessage.value = newErrors.error;
        errorModalOpen.value = true;
    }
}, { deep: true });

const openShift = () => {
    openForm.post(route('transaksi.shift-kasir.store'), {
        preserveScroll: true
    });
};

const confirmingShiftClose = ref(false);

const confirmCloseShift = () => {
    confirmingShiftClose.value = true;
};

const closeShift = () => {
    closeForm.put(route('transaksi.shift-kasir.close', props.activeShift.id), {
        preserveScroll: true,
        onSuccess: () => {
            confirmingShiftClose.value = false;
        }
    });
};

const formatRupiah = (number) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number);
};

const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID');
};

const expectedCash = computed(() => {
    if (!props.activeShift) return 0;
    return Number(props.activeShift.modal_awal) + Number(props.activeShift.total_pemasukan) - Number(props.activeShift.total_pengeluaran);
});
</script>

<template>
    <Head title="Manajemen Shift Kasir" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Shift Kasir
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Buka Shift / Info Shift Aktif -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div v-if="!activeShift">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Buka Shift Baru</h3>
                        <p class="text-sm text-gray-500 mb-6">Anda harus membuka shift sebelum dapat melakukan transaksi penjualan.</p>
                        
                        <form @submit.prevent="openShift" class="space-y-4 max-w-md">
                            <div>
                                <InputLabel for="cabang_id" value="Pilih Cabang / Lokasi" />
                                <select v-model="openForm.cabang_id" id="cabang_id" class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm" required>
                                    <option v-for="cabang in cabangs" :key="cabang.id" :value="cabang.id">
                                        {{ cabang.nama }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <InputLabel for="modal_awal" value="Modal Awal (Uang di Laci Kasir)" />
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <CurrencyInput
                                        id="modal_awal"
                                        input-class="mt-1 block w-full pl-10"
                                        v-model="openForm.modal_awal"
                                        required
                                    />
                                </div>
                            </div>
                            
                            <div class="pt-2">
                                <PrimaryButton :class="{ 'opacity-25': openForm.processing }" :disabled="openForm.processing">
                                    Buka Shift Sekarang
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>

                    <div v-else>
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="text-lg font-bold text-primary-600 mb-1">Shift Aktif</h3>
                                <p class="text-sm text-gray-500 mb-4">Waktu Buka: {{ formatDate(activeShift.waktu_buka) }}</p>
                            </div>
                            
                            <!-- Akses Kasir Button -->
                            <Link :href="route('transaksi.kasir.index')" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                Buka Halaman Mesin Kasir
                            </Link>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <p class="text-xs text-gray-500 uppercase font-semibold">Modal Awal</p>
                                <p class="text-lg font-bold text-gray-800">{{ formatRupiah(activeShift.modal_awal) }}</p>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                <p class="text-xs text-green-600 uppercase font-semibold">Total Pemasukan</p>
                                <p class="text-lg font-bold text-green-800">{{ formatRupiah(activeShift.total_pemasukan) }}</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="text-xs text-blue-600 uppercase font-semibold">Sistem Mengestimasi Total</p>
                                <p class="text-xl font-black text-blue-800">{{ formatRupiah(expectedCash) }}</p>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6 mt-6">
                            <h4 class="text-md font-semibold text-gray-800 mb-4">Tutup Shift</h4>
                            <form @submit.prevent="confirmCloseShift" class="max-w-md space-y-4">
                                <div>
                                    <InputLabel for="uang_fisik" value="Hitung Uang Fisik Aktual di Laci" />
                                    <div class="relative mt-1 rounded-md shadow-sm">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <CurrencyInput
                                            id="uang_fisik"
                                            input-class="mt-1 block w-full pl-10"
                                            v-model="closeForm.uang_fisik"
                                            required
                                        />
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Sistem menghitung seharusnya ada {{ formatRupiah(expectedCash) }}.
                                    </p>
                                </div>
                                
                                <div>
                                    <SecondaryButton type="submit" class="bg-red-50 text-red-600 border-red-200 hover:bg-red-100">
                                        Akhiri / Tutup Shift
                                    </SecondaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Shift -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Shift Anda</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Buka</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Tutup</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modal Awal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pemasukan</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selisih</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="shift in riwayatShift.data" :key="shift.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(shift.waktu_buka) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(shift.waktu_tutup) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatRupiah(shift.modal_awal) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatRupiah(shift.total_pemasukan) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span :class="Number(shift.selisih) < 0 ? 'text-red-600' : (Number(shift.selisih) > 0 ? 'text-green-600' : 'text-gray-900')">
                                            {{ formatRupiah(shift.selisih) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" 
                                              :class="shift.status === 'buka' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                            {{ shift.status.toUpperCase() }}
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="riwayatShift.data.length === 0">
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500 text-sm">Belum ada riwayat shift.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        <Pagination :links="riwayatShift.links" />
                    </div>
                </div>

            </div>
        </div>

        <!-- Close Shift Confirmation Modal -->
        <ConfirmationModal :show="confirmingShiftClose" @close="confirmingShiftClose = false">
            <template #title>
                Konfirmasi Tutup Shift
            </template>

            <template #content>
                Apakah Anda yakin ingin menutup shift saat ini?
                <div class="mt-2 text-sm text-gray-600">
                    Pastikan jumlah uang fisik ({{ formatRupiah(closeForm.uang_fisik) }}) yang Anda hitung sudah sesuai. Data shift yang telah ditutup tidak dapat diubah kembali.
                </div>
            </template>

            <template #footer>
                <SecondaryButton @click="confirmingShiftClose = false">
                    Batal
                </SecondaryButton>

                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': closeForm.processing }"
                    :disabled="closeForm.processing"
                    @click="closeShift"
                >
                    Tutup Shift
                </DangerButton>
            </template>
        </ConfirmationModal>

        <!-- Error Notification Modal -->
        <DialogModal :show="errorModalOpen" @close="errorModalOpen = false">
            <template #title>
                <div class="flex items-center text-red-600 font-bold">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Akses Ditolak
                </div>
            </template>
            
            <template #content>
                <div class="text-base text-gray-700">
                    {{ errorMessage }}
                </div>
                <div class="mt-4 text-sm text-gray-500">
                    Silakan isi form di atas dan klik <strong>Buka Shift Sekarang</strong> untuk mulai melayani penjualan.
                </div>
            </template>

            <template #footer>
                <PrimaryButton @click="errorModalOpen = false">
                    Mengerti
                </PrimaryButton>
            </template>
        </DialogModal>
    </AppLayout>
</template>
