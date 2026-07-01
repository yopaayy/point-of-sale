<script setup>
import { useForm, Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    pengaturan: Object
});

const form = useForm({
    nama_toko: props.pengaturan.nama_toko || '',
    alamat_toko: props.pengaturan.alamat_toko || '',
    telepon_toko: props.pengaturan.telepon_toko || '',
    catatan_struk: props.pengaturan.catatan_struk || '',
    pajak_persen: props.pengaturan.pajak_persen || 0,
});

const submit = () => {
    form.post(route('pengaturan.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Pengaturan Toko">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pengaturan Toko
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div v-if="$page.props.flash?.success" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                </div>

                <div class="bg-white shadow-xl sm:rounded-lg">
                    <div class="p-6 sm:p-10 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">
                                Profil & Informasi Toko
                            </h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Informasi ini akan ditampilkan secara otomatis pada layar kasir dan cetakan struk pembayaran.
                            </p>
                        </div>

                        <form @submit.prevent="submit" class="space-y-6 max-w-2xl">
                            <div>
                                <InputLabel for="nama_toko" value="Nama Toko" />
                                <TextInput
                                    id="nama_toko"
                                    v-model="form.nama_toko"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.nama_toko" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="alamat_toko" value="Alamat Toko" />
                                <textarea
                                    id="alamat_toko"
                                    v-model="form.alamat_toko"
                                    class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm"
                                    rows="3"
                                    required
                                ></textarea>
                                <InputError :message="form.errors.alamat_toko" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="telepon_toko" value="Telepon Toko" />
                                <TextInput
                                    id="telepon_toko"
                                    v-model="form.telepon_toko"
                                    type="text"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <InputError :message="form.errors.telepon_toko" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="pajak_persen" value="Pajak Toko PPN (%)" />
                                <TextInput
                                    id="pajak_persen"
                                    v-model="form.pajak_persen"
                                    type="number"
                                    min="0"
                                    max="100"
                                    step="0.01"
                                    class="mt-1 block w-full"
                                    required
                                />
                                <p class="mt-1 text-xs text-gray-500">Isi 0 jika toko Anda tidak memungut pajak.</p>
                                <InputError :message="form.errors.pajak_persen" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="catatan_struk" value="Catatan Bawah Struk (Footer)" />
                                <textarea
                                    id="catatan_struk"
                                    v-model="form.catatan_struk"
                                    class="mt-1 block w-full border-gray-300 focus:border-primary-500 focus:ring-primary-500 rounded-md shadow-sm font-mono text-sm"
                                    rows="3"
                                    placeholder="Contoh: Terima kasih atas kunjungannya!"
                                ></textarea>
                                <InputError :message="form.errors.catatan_struk" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Simpan Pengaturan
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
