<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../AdminLayout.vue';

defineProps({
    articles: { type: Object, required: true },
    createUrl: { type: String, required: true },
});
</script>

<template>
    <Head title="文章" />
    <AdminLayout>
        <div class="admin-heading">
            <div><p class="eyebrow">ARTICLES / 内容管理</p><h1>文章修订</h1><p>草稿可持续编辑，只有确认发布的修订会进入公开渠道。</p></div>
            <Link class="button button-primary" :href="createUrl">新建文章</Link>
        </div>
        <div class="admin-panel table-wrap">
            <table class="admin-table">
                <thead><tr><th>标题</th><th>状态</th><th>公开版本</th><th>更新时间</th><th></th></tr></thead>
                <tbody>
                    <tr v-for="article in articles.data" :key="article.id">
                        <td><Link :href="`/admin/articles/${article.id}/edit`">{{ article.draft_revision?.title || article.published_revision?.title || '未命名' }}</Link><small>{{ article.slug }}</small></td>
                        <td><span class="status-pill" :class="`status-${article.status}`">{{ article.status }}</span></td>
                        <td>{{ article.published_revision ? `v${article.published_revision.version}` : '—' }}</td>
                        <td>{{ article.updated_at?.slice(0, 16).replace('T', ' ') }}</td>
                        <td><Link :href="`/admin/articles/${article.id}/edit`">编辑</Link></td>
                    </tr>
                    <tr v-if="!articles.data?.length"><td colspan="5">还没有文章。</td></tr>
                </tbody>
            </table>
        </div>
        <nav v-if="articles.links?.length > 3" class="pagination" aria-label="文章分页">
            <template v-for="link in articles.links" :key="link.label">
                <Link v-if="link.url" :href="link.url" :class="{ 'button-primary': link.active }" class="button" v-html="link.label" />
                <span v-else class="button" v-html="link.label" />
            </template>
        </nav>
    </AdminLayout>
</template>
