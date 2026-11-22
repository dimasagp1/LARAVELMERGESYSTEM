<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import ModuleLayout from '@/Layouts/ModuleLayout.vue';
import {
    Save,
    ArrowLeft,
    CheckCircle2,
    Clock,
    FileText,
    PenTool,
    RotateCcw,
    Upload,
    Calendar,
    DollarSign,
    Hash,
    Layers,
} from 'lucide-vue-next';

const props = defineProps({
    module: {
        type: Object,
        required: true,
    },
    entity: {
        type: Object,
        required: true,
    },
    fields: {
        type: Array,
        default: () => [],
    },
    record: {
        type: Object,
        default: null,
    },
    mode: {
        type: String,
        default: 'create',
    },
    relationOptions: {
        type: Object,
        default: () => ({}),
    },
});

// Initialize form data
const initialData = {};
props.fields.forEach((f) => {
    initialData[f.field_key] = props.record?.data_json?.[f.field_key] ?? '';
});

const form = useForm({
    ...initialData,
    status: props.record?.status ?? 'submitted',
});

// Digital signature canvas references
const signatureCanvases = ref({});
const isDrawing = ref(false);

function startDrawing(e, fieldKey) {
    isDrawing.value = true;
    const canvas = signatureCanvases.value[fieldKey];
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const rect = canvas.getBoundingClientRect();
    ctx.beginPath();
    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
}

function draw(e, fieldKey) {
    if (!isDrawing.value) return;
    const canvas = signatureCanvases.value[fieldKey];
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const rect = canvas.getBoundingClientRect();
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#059669'; // Emerald-600
    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    ctx.stroke();
}

function stopDrawing(fieldKey) {
    if (!isDrawing.value) return;
    isDrawing.value = false;
    const canvas = signatureCanvases.value[fieldKey];
    if (canvas) {
        form[fieldKey] = canvas.toDataURL('image/png');
    }
}

function clearSignature(fieldKey) {
    const canvas = signatureCanvases.value[fieldKey];
    if (canvas) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        form[fieldKey] = '';
    }
}

function setCanvasRef(el, fieldKey) {
    if (el) {
        signatureCanvases.value[fieldKey] = el;
        // If existing signature exists, draw it
        if (form[fieldKey] && form[fieldKey].startsWith('data:image')) {
            const img = new Image();
            img.onload = () => {
                const ctx = el.getContext('2d');
                ctx.drawImage(img, 0, 0);
            };
            img.src = form[fieldKey];
        }
    }
}

function submit(targetStatus) {
    if (targetStatus) {
        form.status = targetStatus;
    }

    if (props.mode === 'create') {
        form.post(`/modules/${props.module.slug}/entities/${props.entity.slug}`);
    } else {
        form.put(`/modules/${props.module.slug}/entities/${props.entity.slug}/${props.record.id}`);
    }
}
</script>

<template>
    <ModuleLayout
        :title="`${mode === 'create' ? 'Tambah Data' : 'Edit ' + record.record_number} - ${entity.name}`"
        :module-slug="module.slug"
        :module-name="module.name"
        :color-theme="module.color_theme"
        :menus="module.menus"
        :breadcrumbs="['Entitas', entity.name, mode === 'create' ? 'Tambah Data' : record.record_number]"
    >
        <Head :title="`${mode === 'create' ? 'Input' : 'Edit'} ${entity.name}`" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Top Action Bar -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <Link
                        :href="`/modules/${module.slug}/entities/${entity.slug}`"
                        class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-500 hover:text-slate-900 dark:hover:text-white transition shadow-2xs"
                    >
                        <ArrowLeft class="w-4 h-4" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {{ mode === 'create' ? `Input Dokumen ${entity.name}` : `Edit ${record.record_number}` }}
                            </h1>
                            <span
                                v-if="record"
                                :class="[
                                    'px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border',
                                    record.status === 'approved'
                                        ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                                        : 'bg-amber-50 text-amber-700 border-amber-200',
                                ]"
                            >
                                {{ record.status }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Entitas: {{ entity.name }} ({{ entity.slug }}) &bull; Tersimpan di No-Code Dynamic Engine
                        </p>
                    </div>
                </div>

                <!-- Save Buttons -->
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="submit('draft')"
                        :disabled="form.processing"
                        class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-semibold transition"
                    >
                        Simpan Draft
                    </button>
                    <button
                        type="button"
                        @click="submit('submitted')"
                        :disabled="form.processing"
                        class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 transition disabled:opacity-50"
                    >
                        <Save class="w-4 h-4" />
                        <span>Submit Dokumen</span>
                    </button>
                </div>
            </div>

            <!-- Dynamic Form Card -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-xs p-6 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">
                        Informasi & Atribut Dokumen
                    </h2>
                    <span class="text-xs font-mono text-emerald-600 dark:text-emerald-400">
                        {{ fields.length }} Field Form Dinamis
                    </span>
                </div>

                <form @submit.prevent="submit('submitted')" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div
                            v-for="field in fields"
                            :key="field.id"
                            :class="[
                                field.field_type === 'signature' || field.field_type === 'file' ? 'md:col-span-2' : '',
                            ]"
                        >
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-200 mb-1.5">
                                {{ field.label }}
                                <span v-if="field.is_required" class="text-rose-500">*</span>
                                <span class="text-[10px] font-mono font-normal text-slate-400 ml-1">({{ field.field_key }})</span>
                            </label>

                            <!-- TEXT INPUT -->
                            <template v-if="field.field_type === 'text'">
                                <input
                                    v-model="form[field.field_key]"
                                    type="text"
                                    :required="field.is_required"
                                    :placeholder="`Masukkan ${field.label}`"
                                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500"
                                />
                            </template>

                            <!-- NUMBER INPUT -->
                            <template v-else-if="field.field_type === 'number'">
                                <div class="relative">
                                    <Hash class="w-3.5 h-3.5 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
                                    <input
                                        v-model="form[field.field_key]"
                                        type="number"
                                        step="any"
                                        :required="field.is_required"
                                        placeholder="0"
                                        class="w-full pl-9 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </template>

                            <!-- CURRENCY INPUT -->
                            <template v-else-if="field.field_type === 'currency'">
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-mono font-bold text-slate-400">
                                        Rp
                                    </span>
                                    <input
                                        v-model="form[field.field_key]"
                                        type="number"
                                        step="1"
                                        :required="field.is_required"
                                        placeholder="0"
                                        class="w-full pl-10 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs font-mono text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </template>

                            <!-- DATE INPUT -->
                            <template v-else-if="field.field_type === 'date'">
                                <div class="relative">
                                    <input
                                        v-model="form[field.field_key]"
                                        type="date"
                                        :required="field.is_required"
                                        class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </template>

                            <!-- SELECT DROPDOWN -->
                            <template v-else-if="field.field_type === 'select'">
                                <select
                                    v-model="form[field.field_key]"
                                    :required="field.is_required"
                                    class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-emerald-500 focus:border-emerald-500"
                                >
                                    <option value="">-- Pilih {{ field.label }} --</option>
                                    <option
                                        v-for="(opt, oIdx) in field.options_json"
                                        :key="oIdx"
                                        :value="opt"
                                    >
                                        {{ opt }}
                                    </option>
                                </select>
                            </template>

                            <!-- RELATION INPUT -->
                            <template v-else-if="field.field_type === 'relation'">
                                <div v-if="relationOptions && relationOptions[field.field_key] && relationOptions[field.field_key].length > 0">
                                    <select
                                        v-model="form[field.field_key]"
                                        :required="field.is_required"
                                        class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-emerald-500 focus:border-emerald-500"
                                    >
                                        <option value="">-- Pilih {{ field.label }} --</option>
                                        <option
                                            v-for="rel in relationOptions[field.field_key]"
                                            :key="rel.id"
                                            :value="rel.title"
                                        >
                                            {{ rel.title }} {{ rel.code ? `[${rel.code}]` : '' }} {{ rel.subtitle ? `- ${rel.subtitle}` : '' }}
                                        </option>
                                    </select>
                                </div>
                                <div v-else>
                                    <input
                                        v-model="form[field.field_key]"
                                        type="text"
                                        :placeholder="`ID atau Referensi ${field.label}`"
                                        class="w-full bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100 focus:ring-emerald-500 focus:border-emerald-500"
                                    />
                                </div>
                            </template>

                            <!-- FILE INPUT -->
                            <template v-else-if="field.field_type === 'file'">
                                <div class="flex items-center gap-3">
                                    <input
                                        v-model="form[field.field_key]"
                                        type="text"
                                        placeholder="URL atau Jalur file lampiran (Contoh: /storage/uploads/doc.pdf)"
                                        class="flex-1 bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-800 dark:text-slate-100"
                                    />
                                    <button
                                        type="button"
                                        class="px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-600 dark:text-slate-300 flex items-center gap-1.5"
                                    >
                                        <Upload class="w-3.5 h-3.5" />
                                        <span>Browse</span>
                                    </button>
                                </div>
                            </template>

                            <!-- DIGITAL SIGNATURE CANVAS -->
                            <template v-else-if="field.field_type === 'signature'">
                                <div class="space-y-2">
                                    <div class="border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800/40 p-3">
                                        <div class="flex items-center justify-between mb-2 text-[11px] text-slate-400">
                                            <span class="flex items-center gap-1">
                                                <PenTool class="w-3.5 h-3.5 text-emerald-500" />
                                                <span>Gunakan mouse atau layar sentuh untuk menandatangani dokumen ini:</span>
                                            </span>
                                            <button
                                                type="button"
                                                @click="clearSignature(field.field_key)"
                                                class="text-rose-500 hover:text-rose-600 flex items-center gap-1 font-semibold"
                                            >
                                                <RotateCcw class="w-3 h-3" />
                                                <span>Hapus Tanda Tangan</span>
                                            </button>
                                        </div>

                                        <canvas
                                            :ref="(el) => setCanvasRef(el, field.field_key)"
                                            width="700"
                                            height="140"
                                            @mousedown="startDrawing($event, field.field_key)"
                                            @mousemove="draw($event, field.field_key)"
                                            @mouseup="stopDrawing(field.field_key)"
                                            @mouseleave="stopDrawing(field.field_key)"
                                            class="w-full h-36 bg-white dark:bg-slate-900 border border-dashed border-slate-300 dark:border-slate-700 rounded-lg cursor-crosshair touch-none"
                                        ></canvas>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bottom Submit Row -->
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <Link
                            :href="`/modules/${module.slug}/entities/${entity.slug}`"
                            class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-white"
                        >
                            &larr; Batalkan dan kembali ke tabel
                        </Link>

                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="submit('draft')"
                                :disabled="form.processing"
                                class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold transition"
                            >
                                Simpan sebagai Draft
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-2 shadow-lg shadow-emerald-600/20 transition disabled:opacity-50"
                            >
                                <Save class="w-4 h-4" />
                                <span>Simpan & Kirim Persetujuan</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </ModuleLayout>
</template>
