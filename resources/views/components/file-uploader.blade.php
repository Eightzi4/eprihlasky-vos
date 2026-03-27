@props([
    'fieldName',
    'savedFiles' => [],
    'multiple' => false,
    'locked' => false,
    'accept' => '.pdf,.jpg,.jpeg,.png',
])

@php
    $isMultiple = $multiple || str_ends_with($fieldName, '[]');
    $uploadIcon = $isMultiple ? 'library_add' : 'cloud_upload';
    $uploadPrompt = $isMultiple
        ? 'Klikněte pro přidání souborů nebo je přetáhněte sem'
        : 'Klikněte pro výběr souboru nebo jej přetáhněte sem';

    $savedJson = collect($savedFiles)
        ->map(
            fn($f) => [
                'attachmentId' => $f->id,
                'name' => $f->filename,
                'size' => round($f->size / 1024) . ' KB',
                'type' => $f->mime_type,
                'previewUrl' => str_starts_with($f->mime_type, 'image/') ? asset('storage/' . $f->disk_path) : null,
                'url' => asset('storage/' . $f->disk_path),
            ],
        )
        ->values()
        ->toJson();
@endphp

<div x-data="{
    uploadUrl: FILE_UPLOAD_URL,
    csrfToken: CSRF_TOKEN,
    fieldName: '{{ $fieldName }}',
    multiple: {{ $isMultiple ? 'true' : 'false' }},
    locked: {{ $locked ? 'true' : 'false' }},
    uploadedFiles: {{ $savedJson }},
    isDragging: false,
    isUploading: false,
    uploadError: null,

    async handleFiles(files) {
        if (this.locked) return;
        const list = Array.from(files);
        if (!list.length) return;
        if (!this.multiple && list.length > 1) {
            this.uploadError = 'Lze nahrát pouze jeden soubor.';
            return;
        }
        this.uploadError = null;
        this.isUploading = true;
        for (const file of list) {
            const fd = new FormData();
            fd.append('file', file);
            fd.append('field_name', this.fieldName);
            fd.append('_token', this.csrfToken);
            try {
                const res = await fetch(this.uploadUrl, { method: 'POST', body: fd });
                const data = await res.json();
                if (!res.ok) { this.uploadError = data.message || 'Chyba při nahrávání.'; continue; }
                const entry = {
                    attachmentId: data.attachmentId,
                    name: data.filename,
                    size: this.formatSize(data.size),
                    type: data.mime_type,
                    previewUrl: data.mime_type.startsWith('image/') ? data.url : null,
                    url: data.url,
                };
                if (!this.multiple) { this.uploadedFiles = [entry]; } else { this.uploadedFiles.push(entry); }
                window.dispatchEvent(new CustomEvent('file-uploaded'));
            } catch { this.uploadError = 'Nepodařilo se nahrát soubor. Zkuste to znovu.'; }
        }
        this.isUploading = false;
        this.$refs.fileInput.value = '';
    },

    async deleteFile(attachmentId) {
        if (this.locked) return;
        const url = FILE_DELETE_URL.replace('__ID__', attachmentId);
        try {
            await fetch(url, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' } });
            this.uploadedFiles = this.uploadedFiles.filter(f => f.attachmentId !== attachmentId);
            window.dispatchEvent(new CustomEvent('file-deleted'));
        } catch { this.uploadError = 'Soubor se nepodařilo odstranit.'; }
    },

    formatSize(bytes) {
        if (!bytes) return '0 B';
        const k = 1024,
            sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    },

    getIcon(type) {
        if (!type) return 'description';
        if (type.startsWith('image/')) return 'image';
        if (type === 'application/pdf') return 'picture_as_pdf';
        return 'description';
    },
}" class="space-y-3">

    @if (!$locked)
        <div class="relative group cursor-pointer transition-all duration-300"
            x-bind:class="{
                'bg-red-50/50 border-school-primary ring-2 ring-school-primary/20': isDragging || isUploading,
                'hover:border-school-primary hover:bg-red-50/30': !isDragging && !isUploading
            }"
            @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
            @drop.prevent="isDragging = false; handleFiles($event.dataTransfer.files)" @click="$refs.fileInput.click()">

            <input type="file" x-ref="fileInput" class="hidden" accept="{{ $accept }}"
                @if ($isMultiple) multiple @endif @change="handleFiles($event.target.files)">

            <div class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center flex flex-col items-center justify-center transition-colors"
                x-bind:class="{ 'border-transparent': isDragging || isUploading }">
                <div class="h-12 w-12 bg-gray-100 rounded-full flex items-center justify-center mb-3 text-gray-400 transition-colors"
                    x-bind:class="{ 'bg-white text-school-primary': isDragging || isUploading }">
                    <span class="material-symbols-rounded text-[24px]" x-show="!isUploading">{{ $uploadIcon }}</span>
                    <span class="material-symbols-rounded text-[24px] animate-spin" x-show="isUploading"
                        style="display:none">progress_activity</span>
                </div>
                <p class="text-sm font-bold text-gray-700 transition-colors"
                    x-bind:class="{ 'text-school-primary': isDragging || isUploading }">
                    <span x-show="!isDragging && !isUploading">{{ $uploadPrompt }}</span>
                    <span x-show="isDragging" style="display:none">Pusťte soubory zde</span>
                    <span x-show="isUploading" style="display:none">Nahrávám…</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">PDF, JPG, PNG (Max 10 MB)</p>
            </div>
        </div>
    @endif

    <div x-show="uploadError" class="flex items-center gap-1 mt-1.5 ml-1 text-school-warning" style="display:none">
        <span class="material-symbols-rounded text-[16px]">error</span>
        <p class="text-xs font-medium" x-text="uploadError"></p>
    </div>

    <div class="space-y-2">
        <template x-for="file in uploadedFiles" :key="file.attachmentId">
            <div
                class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded-xl shadow-sm gap-3">
                <a :href="file.url" target="_blank"
                    class="flex items-center gap-3 overflow-hidden group/file flex-grow min-w-0">
                    <template x-if="file.previewUrl">
                        <div class="h-10 w-10 rounded-lg overflow-hidden border border-gray-100 flex-shrink-0">
                            <img :src="file.previewUrl" class="w-full h-full object-cover">
                        </div>
                    </template>
                    <template x-if="!file.previewUrl">
                        <div
                            class="h-10 w-10 bg-green-50 rounded-lg flex items-center justify-center text-green-600 border border-green-100 flex-shrink-0">
                            <span class="material-symbols-rounded text-[20px]" x-text="getIcon(file.type)"></span>
                        </div>
                    </template>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-900 truncate group-hover/file:text-school-primary transition-colors"
                            x-text="file.name"></p>
                        <p class="text-xs text-green-600">Uloženo &bull; <span x-text="file.size"></span></p>
                    </div>
                </a>
                @if (!$locked)
                    <button type="button" @click.prevent="deleteFile(file.attachmentId)"
                        class="text-gray-400 hover:text-red-500 transition-colors p-2 flex-shrink-0">
                        <span class="material-symbols-rounded text-[20px]">delete</span>
                    </button>
                @endif
            </div>
        </template>
    </div>
</div>
