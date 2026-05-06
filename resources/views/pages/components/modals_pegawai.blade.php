<?php
/**
 * Pegawai-specific Modals
 */
?>

<!-- WFA Reason Modal -->
<div id="wfa-reason-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
        <h3 class="text-xl font-bold mb-3">Alasan Kerja di Luar Kantor</h3>
        <p class="text-sm text-gray-600 mb-3">Anda berada di luar wilayah Telkom University. Silakan isi alasan bekerja di luar kantor untuk melanjutkan presensi (WFA).</p>
        <textarea id="wfa-reason-input" class="w-full p-3 border rounded mb-4" rows="4" placeholder="Tulis alasan Anda..."></textarea>
        <div class="flex justify-end gap-2">
            <button id="wfa-reason-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
            <button id="wfa-reason-submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Kirim</button>
        </div>
    </div>
</div>

<!-- Early Leave Reason Modal -->
<div id="early-leave-reason-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
        <h3 class="text-xl font-bold mb-3">Alasan Pulang Awal</h3>
        <p class="text-sm text-gray-600 mb-3">Anda pulang sebelum jam yang ditentukan. Silakan isi alasan pulang awal untuk melanjutkan presensi pulang.</p>
        <textarea id="early-leave-reason-input" class="w-full p-3 border rounded mb-4" rows="4" placeholder="Tulis alasan pulang awal Anda..."></textarea>
        <div class="flex justify-end gap-2">
            <button id="early-leave-reason-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
            <button id="early-leave-reason-submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded">Kirim</button>
        </div>
    </div>
</div>

<!-- Izin/Sakit Input Modal -->
<div id="izin-sakit-modal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden p-4">
    <div class="bg-white p-6 rounded-lg shadow-2xl w-full max-w-md">
        <h3 class="text-xl font-bold mb-4">Input Keterangan</h3>
        <form id="izin-sakit-form">
            <div class="mb-4">
                <label class="block text-sm text-gray-600 mb-2">Jenis Keterangan</label>
                <select id="izin-sakit-type" class="w-full p-2 border rounded-lg" required>
                    <option value="">Pilih jenis...</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-gray-600 mb-2">Keterangan</label>
                <textarea id="izin-sakit-alasan" class="w-full p-3 border rounded" rows="4" placeholder="Tulis keterangan izin/sakit..." required></textarea>
            </div>
            <div class="mb-4">
                <label class="block text-sm text-gray-600 mb-2">Upload Bukti</label>
                <input type="file" id="izin-sakit-bukti" accept="image/*" class="w-full p-2 border rounded" required>
                <p class="text-xs text-gray-500 mt-1">Maksimal 5MB. Format: JPG, PNG, GIF</p>
                <div id="izin-sakit-preview" class="mt-2 hidden">
                    <img id="izin-sakit-preview-img" src="" alt="Preview" class="w-full h-32 object-cover rounded border">
                </div>
                <div id="izin-sakit-error" class="mt-2 text-red-600 text-sm hidden"></div>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" id="izin-sakit-cancel" class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">Batal</button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>
