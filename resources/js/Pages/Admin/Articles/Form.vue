<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
});

const tagText = ref(props.article?.tags?.map((tag) => tag.name).join(', ') || '');
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
</script>

<template>
    <Head :title="article ? '编辑文章' : '新建文章'" />
    <AdminLayout>
        <div class="admin-heading">
            <div><p class="eyebrow">ARTICLE EDITOR</p><h1>{{ article ? '编辑文章' : '新建文章' }}</h1></div>
            <a v-if="publicUrl" class="button button-quiet" :href="publicUrl">查看公开页</a>
        </div>
        <form class="editor-form" @submit.prevent="submit">
            <div class="editor-main">
                <label>标题<input v-model="form.title" required><small class="alert-danger">{{ error('title') }}</small></label>
                <label>Slug<input v-model="form.slug" placeholder="laravel-release-notes" required><small>只使用小写字母、数字和连字符；变更后旧地址会写入 301 重定向。</small><small class="alert-danger">{{ error('slug') }}</small></label>
                <label>摘要<textarea v-model="form.excerpt" rows="3" /><small class="alert-danger">{{ error('excerpt') }}</small></label>
                <label>正文 Markdown<textarea v-model="form.markdown" rows="24" required /><small class="alert-danger">{{ error('markdown') }}</small></label>
            </div>
            <aside class="editor-side">
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
