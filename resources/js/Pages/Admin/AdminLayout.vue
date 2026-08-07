<script setup>
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: '内容后台',
    },
});

const page = usePage();
const isActive = (path) => page.url === path || page.url.startsWith(`${path}/`);
</script>

<template>
    <div class="admin-body">
        <header class="site-header">
            <div class="shell nav-shell">
                <Link class="brand" href="/admin" aria-label="内容后台首页">
                    <span class="brand-mark" aria-hidden="true">修</span>
                    <span class="brand-copy">程序员的个人修养 <small>内容后台</small></span>
                </Link>
                <nav class="site-nav" aria-label="后台导航">
                    <Link href="/">查看网站</Link>
                    <Link href="/admin" :class="{ 'is-active': page.url === '/admin' }">概览</Link>
                    <Link href="/admin/articles" :class="{ 'is-active': isActive('/admin/articles') }">文章</Link>
                    <Link href="/admin/projects" :class="{ 'is-active': isActive('/admin/projects') }">项目</Link>
                    <Link href="/logout" method="delete" as="button" class="link-button">退出</Link>
                </nav>
            </div>
        </header>

        <main class="shell admin-main">
            <div class="admin-context"><span><i /> SECURE WORKSPACE</span><span>{{ page.props.auth?.user?.email }}</span></div>
            <div v-if="page.props.flash?.success" class="flash-message alert-success">{{ page.props.flash.success }}</div>
            <div v-if="page.props.flash?.error" class="flash-message alert-danger">{{ page.props.flash.error }}</div>
            <slot />
        </main>
    </div>
</template>
