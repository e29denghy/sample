<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';
import AdminLayout from '../AdminLayout.vue';

const props = defineProps({
    article: { type: Object, default: null },
    revision: { type: Object, default: null },
    formAction: { type: String, required: true },
    formMethod: { type: String, required: true },
    previewAction: { type: String, required: true },
    publishAction: { type: String, default: null },
    archiveAction: { type: String, default: null },
    publicUrl: { type: String, default: null },
    mediaUploadAction: { type: String, required: true },
});

const tagText = ref(props.article?.tags?.map((tag) => tag.name).join(', ') || '');
const markdownTextarea = ref(null);
const mediaFile = ref(null);
const mediaAlt = ref('');
const mediaUploading = ref(false);
const mediaError = ref('');
const form = useForm({
    title: props.revision?.title || '',
    slug: props.article?.slug || '',
    excerpt: props.revision?.excerpt || '',
    markdown: props.revision?.markdown || '',
    seo_title: props.revision?.seo_title || '',
    seo_description: props.revision?.seo_description || '',
    verification_status: props.revision?.verification_status || 'review_required',
    tags: [tagText.value],
});

const syncTags = () => { form.tags = [tagText.value]; };
const submit = () => {
    syncTags();
    form[props.formMethod](props.formAction);
};
const preview = () => {
    syncTags();
    form.post(props.previewAction);
};
const publish = () => {
    if (props.publishAction && window.confirm('确认发布当前草稿修订？')) router.post(props.publishAction);
};
const archive = () => {
    if (props.archiveAction && window.confirm('确认归档？')) router.post(props.archiveAction);
};
const error = (key) => form.errors[key] || '';

const insertMarkdown = async (snippet) => {
    const textarea = markdownTextarea.value;
    const start = textarea?.selectionStart ?? form.markdown.length;
    const end = textarea?.selectionEnd ?? start;
    const before = form.markdown.slice(0, start);
    const after = form.markdown.slice(end);
    const prefix = before && !before.endsWith('\n') ? '\n\n' : '';
    const suffix = after && !after.startsWith('\n') ? '\n\n' : '';

    form.markdown = `${before}${prefix}${snippet}${suffix}${after}`;
    await nextTick();
    textarea?.focus();
    const cursor = before.length + prefix.length + snippet.length;
    textarea?.setSelectionRange(cursor, cursor);
};

const uploadMedia = async () => {
    const file = mediaFile.value?.files?.[0];
    if (!file) {
        mediaError.value = '请先选择图片。';
        return;
    }

    mediaUploading.value = true;
    mediaError.value = '';
    const body = new FormData();
    body.append('image', file);
    body.append('alt_text', mediaAlt.value);

    try {
        const response = await fetch(props.mediaUploadAction, {
            method: 'POST',
            body,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
        });
        const payload = await response.json();

        if (!response.ok) {
            const message = payload.message || Object.values(payload.errors || {}).flat()[0];
            throw new Error(message || '图片上传失败。');
        }

        await insertMarkdown(payload.asset.markdown);
        mediaFile.value.value = '';
        mediaAlt.value = '';
    } catch (uploadError) {
        mediaError.value = uploadError.message || '图片上传失败。';
    } finally {
        mediaUploading.value = false;
    }
};
</script>

<template>
    <Head :title="article ? '编辑文章' : '新建文章'" />
    <AdminLayout>
        <div class="admin-heading">
            <div><p class="eyebrow">ARTICLE EDITOR / 文章编辑器</p><h1>{{ article ? '编辑文章' : '新建文章' }}</h1><p>先保存草稿和预览，再决定是否发布当前修订。</p></div>
            <a v-if="publicUrl" class="button button-quiet" :href="publicUrl">查看公开页</a>
        </div>
        <form class="editor-form" @submit.prevent="submit">
            <div class="editor-main admin-panel">
                <div class="editor-section-title"><span>01</span><div><strong>正文内容</strong><small>公开页面的核心事实来源</small></div></div>
                <label>标题<input v-model="form.title" required><small class="alert-danger">{{ error('title') }}</small></label>
                <label>Slug<input v-model="form.slug" placeholder="laravel-release-notes" required><small>只使用小写字母、数字和连字符；变更后旧地址会写入 301 重定向。</small><small class="alert-danger">{{ error('slug') }}</small></label>
                <label>摘要<textarea v-model="form.excerpt" rows="3" /><small class="alert-danger">{{ error('excerpt') }}</small></label>
                <label>正文 Markdown<textarea ref="markdownTextarea" v-model="form.markdown" rows="24" required /><small class="alert-danger">{{ error('markdown') }}</small></label>
                <div class="media-inline-uploader">
                    <div><strong>插入远程图片</strong><small>上传后会在当前光标处插入 Markdown，不占用小程序发布包。</small></div>
                    <input ref="mediaFile" type="file" accept="image/jpeg,image/png,image/webp,image/gif">
                    <input v-model="mediaAlt" maxlength="255" placeholder="图片说明（建议填写）">
                    <button class="button button-quiet" type="button" :disabled="mediaUploading" @click="uploadMedia">{{ mediaUploading ? '上传中…' : '上传并插入' }}</button>
                    <Link href="/admin/media" class="text-link">打开图片素材库 →</Link>
                    <small v-if="mediaError" class="alert-danger">{{ mediaError }}</small>
                </div>
            </div>
            <aside class="editor-side admin-panel">
                <div class="editor-section-title"><span>02</span><div><strong>发布设置</strong><small>元数据与验证边界</small></div></div>
                <label>标签<input v-model="tagText" placeholder="Laravel, 部署, AI"></label>
                <label>SEO 标题<input v-model="form.seo_title"></label>
                <label>SEO 描述<textarea v-model="form.seo_description" rows="5" /></label>
                <label>验证状态<select v-model="form.verification_status"><option value="review_required">需复核</option><option value="valid">有效</option><option value="outdated">已过时</option></select></label>
                <button class="button button-primary" type="submit" :disabled="form.processing">{{ form.processing ? '保存中…' : '保存草稿' }}</button>
                <button class="button button-quiet" type="button" @click="preview">预览</button>
                <button v-if="publishAction" class="button button-publish" type="button" @click="publish">发布当前修订</button>
            </aside>
        </form>
        <button v-if="archiveAction" class="danger-form link-button" type="button" @click="archive">归档文章</button>
    </AdminLayout>
</template>
