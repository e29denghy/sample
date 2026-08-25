<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '../AdminLayout.vue';

const props = defineProps({
    assets: { type: Object, required: true },
    uploadAction: { type: String, required: true },
    maxMegabytes: { type: Number, required: true },
    storageDisk: { type: String, required: true },
});

const fileInput = ref(null);
const copiedId = ref(null);
const form = useForm({
    image: null,
    alt_text: '',
    copyright: '',
});

const submit = () => {
    form.post(props.uploadAction, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) fileInput.value.value = '';
        },
    });
};

const copyMarkdown = async (asset) => {
    await navigator.clipboard.writeText(asset.markdown);
    copiedId.value = asset.id;
    window.setTimeout(() => { copiedId.value = null; }, 1600);
};

const formatBytes = (bytes) => {
    if (!bytes) return '未知大小';
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
};
</script>

<template>
    <Head title="图片素材" />
    <AdminLayout>
        <div class="admin-heading media-heading">
            <div>
                <p class="eyebrow">MEDIA LIBRARY / 图片素材</p>
                <h1>上传一次，多端复用</h1>
                <p>图片进入 {{ storageDisk === 'oss' ? '阿里云 OSS' : '本地公开磁盘' }}，复制 Markdown 后可直接插入文章。</p>
            </div>
            <span class="status-pill">JPEG · PNG · WebP · GIF / ≤ {{ maxMegabytes }} MB</span>
        </div>

        <form class="admin-panel media-upload-form" @submit.prevent="submit">
            <div class="editor-section-title"><span>01</span><div><strong>上传图片</strong><small>SVG 不允许上传，避免脚本与外链风险</small></div></div>
            <label>
                图片文件
                <input ref="fileInput" type="file" accept="image/jpeg,image/png,image/webp,image/gif" required @change="form.image = $event.target.files[0]">
                <small v-if="form.errors.image" class="alert-danger">{{ form.errors.image }}</small>
            </label>
            <label>替代文字<input v-model="form.alt_text" maxlength="255" placeholder="说明图片表达的内容，兼顾无障碍与 SEO"></label>
            <label>来源 / 版权<input v-model="form.copyright" maxlength="255" placeholder="原创、品牌素材或授权来源"></label>
            <button class="button button-primary" type="submit" :disabled="form.processing">{{ form.processing ? '上传中…' : '上传并生成地址' }}</button>
        </form>

        <section class="media-library">
            <div class="editor-section-title"><span>02</span><div><strong>素材库</strong><small>内容哈希相同的图片会自动复用</small></div></div>
            <div v-if="assets.data.length" class="media-grid">
                <article v-for="asset in assets.data" :key="asset.id" class="media-card admin-panel">
                    <a :href="asset.url" target="_blank" rel="noreferrer" class="media-preview">
                        <img :src="asset.url" :alt="asset.alt_text || asset.original_name" loading="lazy">
                    </a>
                    <div class="media-card-body">
                        <strong>{{ asset.alt_text || asset.original_name }}</strong>
                        <small>{{ asset.width }} × {{ asset.height }} · {{ formatBytes(asset.size) }} · {{ asset.disk }}</small>
                        <code>{{ asset.url }}</code>
                        <button class="button button-quiet" type="button" @click="copyMarkdown(asset)">{{ copiedId === asset.id ? '已复制' : '复制 Markdown' }}</button>
                    </div>
                </article>
            </div>
            <div v-else class="empty-state">还没有图片素材。上传第一张图片后会显示在这里。</div>
            <nav v-if="assets.links?.length > 3" class="media-pagination" aria-label="素材分页">
                <Link v-for="link in assets.links" :key="link.label" :href="link.url || ''" :class="{ 'is-active': link.active, 'is-disabled': !link.url }" v-html="link.label" />
            </nav>
        </section>
    </AdminLayout>
</template>
