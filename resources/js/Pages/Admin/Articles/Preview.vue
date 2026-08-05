<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '../AdminLayout.vue';

defineProps({
    title: { type: String, required: true },
    excerpt: { type: String, default: '' },
    html: { type: String, required: true },
});
</script>

<template>
    <Head :title="`预览：${title}`" />
    <AdminLayout>
        <article class="shell article-page preview-page">
            <header class="article-header"><p class="eyebrow">PREVIEW</p><h1>{{ title }}</h1><p class="article-lede">{{ excerpt }}</p></header>
            <div class="article-body prose" v-html="html" />
        </article>
    </AdminLayout>
</template>
