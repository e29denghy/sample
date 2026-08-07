<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from './AdminLayout.vue';

defineProps({
    articleCounts: { type: Object, default: () => ({}) },
    projectCount: { type: Number, default: 0 },
    pendingEvents: { type: Number, default: 0 },
    urls: { type: Object, required: true },
});
</script>

<template>
    <Head title="概览" />
    <AdminLayout>
        <div class="admin-heading">
            <div><p class="eyebrow">CONTROL ROOM / 概览</p><h1>内容发布概览</h1><p>查看当前内容状态，并从草稿到公开版本完成发布。</p></div>
            <div class="hero-actions">
                <Link class="button button-quiet" :href="urls.projectsCreate">新建项目</Link>
                <Link class="button button-primary" :href="urls.articlesCreate">新建文章</Link>
            </div>
        </div>
        <div class="stat-grid">
            <div class="stat-card"><span>草稿</span><strong>{{ articleCounts.draft || 0 }}</strong></div>
            <div class="stat-card"><span>已发布</span><strong>{{ articleCounts.published || 0 }}</strong></div>
            <div class="stat-card"><span>项目</span><strong>{{ projectCount }}</strong></div>
            <div class="stat-card"><span>待处理事件</span><strong>{{ pendingEvents }}</strong></div>
        </div>
        <section class="admin-panel release-panel">
            <div><p class="eyebrow">RELEASE CONTRACT</p><h2>发布原则</h2><p>网站数据库是正文事实来源；文章修订冻结后才进入公开页面、RSS、Atom 和只读 API。公众号投递仍需单独人工确认。</p></div>
            <ol class="release-steps"><li><span>01</span>保存草稿</li><li><span>02</span>人工预览</li><li><span>03</span>确认发布</li><li><span>04</span>下游回读</li></ol>
        </section>
    </AdminLayout>
</template>
