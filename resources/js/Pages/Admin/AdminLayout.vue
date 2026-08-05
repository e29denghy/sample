<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '内容后台',
    },
});

const page = usePage();
</script>

<template>
    <div class="admin-body">
        <header class="site-header">
            <div class="shell nav-shell">
                <Link class="brand" href="/admin">denghy <span>/ 内容后台</span></Link>
                <nav class="site-nav" aria-label="后台导航">
                    <Link href="/">查看网站</Link>
                    <Link href="/admin/articles">文章</Link>
                    <Link href="/admin/projects">项目</Link>
                    <Link href="/logout" method="delete" as="button" class="link-button">退出</Link>
                </nav>
            </div>
        </header>

        <main class="shell admin-main">
            <div v-if="page.props.flash?.success" class="flash-message alert-success">{{ page.props.flash.success }}</div>
            <div v-if="page.props.flash?.error" class="flash-message alert-danger">{{ page.props.flash.error }}</div>
            <slot />
        </main>
    </div>
</template>
