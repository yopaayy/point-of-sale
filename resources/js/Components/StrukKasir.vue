<script setup>
import { ref, watch, nextTick } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import JsBarcode from 'jsbarcode';

const props = defineProps({
    show: Boolean,
    strukData: Object,
    pengaturan: Object,
});

const emit = defineEmits(['close']);

// Reference to SVG barcode element
const barcodeRef = ref(null);

watch(() => props.show, async (newVal) => {
    if (newVal && props.strukData?.no_struk) {
        await nextTick();
        if (barcodeRef.value) {
            JsBarcode(barcodeRef.value, props.strukData.no_struk, {
                format: "CODE128",
                width: 1.5,
                height: 40,
                displayValue: true,
                fontSize: 12,
                margin: 0
            });
        }
    }
});

const formatRupiah = (num) => {
    return new Intl.NumberFormat('id-ID').format(num);
};

const close = () => {
    emit('close');
};

const printStruk = () => {
    const printContent = document.getElementById('struk-content').innerHTML;
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    printWindow.document.write(`
        <html>
            <head>
                <title>Cetak Struk</title>
                <style>
                    @page { margin: 0; size: 80mm auto; }
                    body { 
                        font-family: 'Courier New', Courier, monospace; 
                        font-size: 12px; 
                        margin: 0; 
                        padding: 10px; 
                        width: 80mm; 
                        color: #000; 
                    }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    .uppercase { text-transform: uppercase; }
                    .text-sm { font-size: 11px; }
                    .text-xs { font-size: 10px; }
                    .mb-2 { margin-bottom: 0.5rem; }
                    .mb-4 { margin-bottom: 1rem; }
                    .mt-2 { margin-top: 0.5rem; }
                    .mt-4 { margin-top: 1rem; }
                    .pb-2 { padding-bottom: 0.5rem; }
                    .border-b-dashed { border-bottom: 1px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
                    .flex { display: flex; }
                    .justify-between { justify-content: space-between; }
                    .w-full { width: 100%; }
                    table { width: 100%; border-collapse: collapse; }
                    td { vertical-align: top; padding: 2px 0; }
                    .text-right { text-align: right; }
                    .barcode-container { text-align: center; margin-top: 10px; margin-bottom: 10px; }
                    .logo-container { text-align: center; margin-bottom: 10px; }
                    .logo-container img { max-height: 50px; }
                </style>
            </head>
            <body>
                ${printContent}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
        printWindow.print();
        printWindow.close();
    }, 250);
};
</script>

<template>
    <Modal :show="show" maxWidth="sm" @close="close">
        <div class="p-6 bg-white rounded-lg">
            <!-- Preview Struk (yang akan di-copy ke window print) -->
            <div id="struk-content" v-if="strukData" class="font-mono text-sm text-gray-800">
                <!-- Header Struk -->
                <div class="text-center mb-4 border-b-dashed">
                    <div class="logo-container">
                        <img src="/img/logo.png" alt="Logo" style="margin: 0 auto; max-height: 50px;" onerror="this.style.display='none'">
                    </div>
                    <h2 class="font-bold uppercase text-lg mb-2">{{ pengaturan?.nama_toko || 'ALFA-POS MARKET' }}</h2>
                    <div class="text-xs">
                        <div>{{ pengaturan?.alamat_toko || 'Jl. Kebenaran No. 123, Jakarta' }}</div>
                        <div>Telp: {{ pengaturan?.telepon_toko || '08123456789' }}</div>
                    </div>
                </div>

                <!-- Info Transaksi -->
                <div class="text-xs mb-4 border-b-dashed">
                    <div class="flex justify-between">
                        <span>No:</span>
                        <span>{{ strukData.no_struk }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tgl:</span>
                        <span>{{ new Date(strukData.created_at).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Ksr:</span>
                        <span>{{ strukData.user?.name || 'Admin' }}</span>
                    </div>
                    <div class="flex justify-between" v-if="strukData.pelanggan">
                        <span>Plg:</span>
                        <span>{{ strukData.pelanggan.nama }}</span>
                    </div>
                </div>

                <!-- Item Belanja -->
                <div class="text-xs mb-4 border-b-dashed">
                    <table class="w-full">
                        <tr v-for="item in strukData.detail_penjualan" :key="item.id">
                            <td>
                                <div class="font-bold">{{ item.produk?.nama || 'Produk' }}</div>
                                <div>{{ item.qty }} {{ item.satuan?.nama_pendek || 'PCS' }} x {{ formatRupiah(item.harga_satuan) }}</div>
                                <div v-if="item.diskon > 0">- Diskon: {{ formatRupiah(item.diskon) }}</div>
                            </td>
                            <td class="text-right">
                                {{ formatRupiah(item.subtotal - item.diskon) }}
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Totalan -->
                <div class="text-xs mb-4 border-b-dashed">
                    <div class="flex justify-between">
                        <span>Subtotal:</span>
                        <span>{{ formatRupiah(strukData.subtotal) }}</span>
                    </div>
                    <div v-if="strukData.diskon_tambahan > 0" class="flex justify-between">
                        <span>Diskon Member/Promo:</span>
                        <span>-{{ formatRupiah(strukData.diskon_tambahan) }}</span>
                    </div>
                    <div v-if="strukData.pajak > 0" class="flex justify-between">
                        <span>Pajak:</span>
                        <span>+{{ formatRupiah(strukData.pajak) }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-sm mt-2">
                        <span>TOTAL:</span>
                        <span>{{ formatRupiah(strukData.total_akhir) }}</span>
                    </div>
                </div>

                <!-- Pembayaran -->
                <div class="text-xs mb-4 border-b-dashed" v-if="strukData.pembayaran && strukData.pembayaran.length > 0">
                    <div class="flex justify-between">
                        <span class="uppercase">{{ strukData.pembayaran[0].metode }}:</span>
                        <span>{{ formatRupiah(strukData.pembayaran[0].nominal) }}</span>
                    </div>
                    <div class="flex justify-between font-bold">
                        <span>KEMBALI:</span>
                        <span>{{ formatRupiah(Math.max(0, strukData.pembayaran[0].nominal - strukData.total_akhir)) }}</span>
                    </div>
                </div>

                <!-- Barcode & Footer -->
                <div class="text-center text-xs mt-4">
                    <div class="barcode-container">
                        <svg ref="barcodeRef" style="width: 100%; max-width: 250px;"></svg>
                    </div>
                    <div class="font-bold mt-2">*** TERIMA KASIH ***</div>
                    <div style="white-space: pre-wrap" class="mt-1">{{ pengaturan?.catatan_struk || 'Barang yang sudah dibeli\ntidak dapat ditukar/dikembalikan' }}</div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <SecondaryButton @click="close">Tutup</SecondaryButton>
                <PrimaryButton @click="printStruk" class="bg-blue-600 hover:bg-blue-700">
                    🖨️ Cetak Struk
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.border-b-dashed {
    border-bottom: 1px dashed #d1d5db;
    padding-bottom: 0.5rem;
    margin-bottom: 0.5rem;
}
</style>
