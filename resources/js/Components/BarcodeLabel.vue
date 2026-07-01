<script setup>
import { ref, onMounted, watch, nextTick } from 'vue';
import JsBarcode from 'jsbarcode';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    produk: Object,
    pengaturan: Object
});

const emit = defineEmits(['close']);

const closeLabel = () => {
    emit('close');
};

const printLabel = () => {
    const printContent = document.getElementById('barcode-label-content').innerHTML;
    const printWindow = window.open('', '_blank', 'width=400,height=400');
    printWindow.document.write(`
        <html>
            <head>
                <title>Cetak Barcode Label</title>
                <style>
                    /* Standard thermal sticker size 50mm x 30mm or similar */
                    @page { margin: 0; size: 50mm 30mm; }
                    body { 
                        font-family: 'Courier New', Courier, monospace; 
                        margin: 0; 
                        padding: 2mm; 
                        width: 46mm; 
                        height: 26mm;
                        color: #000; 
                        display: flex;
                        flex-direction: column;
                        justify-content: center;
                        align-items: center;
                        text-align: center;
                    }
                    .store-name { font-size: 10px; font-weight: bold; margin-bottom: 2px; }
                    .product-name { font-size: 9px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 46mm; margin-bottom: 2px; }
                    .price { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
                    svg { width: 40mm; height: 12mm; }
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

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID').format(value);
};

// Auto-generate barcode when modal opens
watch(() => props.show, async (newValue) => {
    if (newValue && props.produk) {
        await nextTick();
        setTimeout(() => {
            if (document.getElementById('product-barcode')) {
                JsBarcode('#product-barcode', props.produk.barcode || props.produk.kode_produk, {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 2,
                    height: 40,
                    displayValue: true,
                    fontSize: 14,
                    margin: 0
                });
            }
        }, 100);
    }
});

// Harga display (ambil dari base unit harga jual)
const getHarga = () => {
    if (props.produk && props.produk.harga_produk) {
        const baseHarga = props.produk.harga_produk.find(h => h.satuan_id === props.produk.base_unit_id);
        if (baseHarga) return baseHarga.harga_jual;
    }
    return 0;
};
</script>

<template>
    <Modal :show="show" maxWidth="sm" @close="closeLabel">
        <div class="p-6 bg-white rounded-lg">
            <h2 class="text-lg font-bold mb-4">Preview Barcode Label</h2>
            
            <div class="flex justify-center bg-gray-100 p-4 rounded-lg border border-gray-200">
                <!-- Preview Stiker -->
                <div id="barcode-label-content" class="bg-white" style="width: 50mm; height: 30mm; padding: 2mm; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div class="store-name" style="font-size: 10px; font-weight: bold; margin-bottom: 2px; font-family: monospace;">{{ pengaturan?.nama_toko || 'ALFA-POS MARKET' }}</div>
                    <div class="product-name" style="font-size: 9px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 46mm; margin-bottom: 2px; font-family: monospace;">{{ produk?.nama }}</div>
                    <div class="price" style="font-size: 12px; font-weight: bold; margin-bottom: 2px; font-family: monospace;">Rp {{ formatRupiah(getHarga()) }}</div>
                    <svg id="product-barcode"></svg>
                </div>
            </div>
            
            <div class="mt-4 text-xs text-center text-gray-500">
                Ukuran cetak otomatis disesuaikan untuk kertas thermal label (50x30mm).
            </div>
            
            <div class="mt-6 flex justify-end space-x-3 pt-4 border-t border-gray-200">
                <SecondaryButton @click="closeLabel">Tutup</SecondaryButton>
                <PrimaryButton @click="printLabel" class="bg-blue-600 hover:bg-blue-700">
                    🖨️ Cetak Label
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
