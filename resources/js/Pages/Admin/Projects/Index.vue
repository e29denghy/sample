<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../AdminLayout.vue';

defineProps({
    projects: { type: Object, required: true },
    createUrl: { type: String, required: true },
});
</script>

<template>
    <Head title="项目" />
    <AdminLayout>
        <div class="admin-heading"><div><p class="eyebrow">PROJECTS / 项目管理</p><h1>项目档案</h1><p>维护项目的问题、决策、证据与公开状态。</p></div><Link class="button button-primary" :href="createUrl">新建项目</Link></div>
        <div class="admin-panel table-wrap">
            <table class="admin-table">
                <thead><tr><th>名称</th><th>状态</th><th>公开</th><th>更新时间</th><th></th></tr></thead>
                <tbody>
                    <tr v-for="project in projects.data" :key="project.id">
                        <td><Link :href="`/admin/projects/${project.id}/edit`">{{ project.name }}</Link><small>{{ project.slug }}</small></td>
                        <td>{{ project.status }}</td><td>{{ project.is_public ? '是' : '否' }}</td>
                        <td>{{ project.updated_at?.slice(0, 16).replace('T', ' ') }}</td>
                        <td><Link :href="`/admin/projects/${project.id}/edit`">编辑</Link></td>
                    </tr>
                    <tr v-if="!projects.data?.length"><td colspan="5">还没有项目。</td></tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
