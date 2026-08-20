<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '../AdminLayout.vue';

const props = defineProps({
    project: { type: Object, default: null },
    formAction: { type: String, required: true },
    formMethod: { type: String, required: true },
});

const form = useForm({
    name: props.project?.name || '',
    slug: props.project?.slug || '',
    status: props.project?.status || 'active',
    summary: props.project?.summary || '',
    problem: props.project?.problem || '',
    decisions: props.project?.decisions || '',
    evidence: props.project?.evidence || '',
    outcome: props.project?.outcome || '',
    source_url: props.project?.source_url || '',
    sort_order: props.project?.sort_order || 0,
    is_featured: Boolean(props.project?.is_featured),
    is_public: props.project ? Boolean(props.project.is_public) : true,
});

const submit = () => form[props.formMethod](props.formAction);
const error = (key) => form.errors[key] || '';
</script>

<template>
    <Head :title="project ? '编辑项目' : '新建项目'" />
    <AdminLayout>
        <div class="admin-heading"><div><p class="eyebrow">PROJECT FILE / 项目编辑器</p><h1>{{ project ? '编辑项目' : '新建项目' }}</h1><p>用统一结构记录项目现场，并控制首页精选与公开状态。</p></div></div>
        <form class="editor-form" @submit.prevent="submit">
            <div class="editor-main admin-panel">
                <div class="editor-section-title"><span>01</span><div><strong>项目内容</strong><small>问题、决策、证据和结果</small></div></div>
                <label>名称<input v-model="form.name" required><small class="alert-danger">{{ error('name') }}</small></label>
                <label>Slug<input v-model="form.slug" required><small class="alert-danger">{{ error('slug') }}</small></label>
                <label>摘要<textarea v-model="form.summary" rows="4" /></label>
                <label>问题与约束<textarea v-model="form.problem" rows="7" /></label>
                <label>方案与决策<textarea v-model="form.decisions" rows="7" /></label>
                <label>证据<textarea v-model="form.evidence" rows="7" /></label>
                <label>结果<textarea v-model="form.outcome" rows="7" /></label>
                <label>开源地址<input v-model="form.source_url" type="url" placeholder="https://github.com/owner/repository"><small class="alert-danger">{{ error('source_url') }}</small></label>
            </div>
            <aside class="editor-side admin-panel">
                <div class="editor-section-title"><span>02</span><div><strong>展示设置</strong><small>状态、排序与公开范围</small></div></div>
                <label>状态<input v-model="form.status" required></label>
                <label>排序<input v-model="form.sort_order" type="number" min="0"></label>
                <label class="check-label"><input v-model="form.is_featured" type="checkbox"> 首页精选</label>
                <label class="check-label"><input v-model="form.is_public" type="checkbox"> 公开显示</label>
                <button class="button button-primary" type="submit" :disabled="form.processing">{{ form.processing ? '保存中…' : '保存项目' }}</button>
            </aside>
        </form>
    </AdminLayout>
</template>
