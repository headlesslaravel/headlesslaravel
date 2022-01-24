<template>
    <Layout>
        <template #header>
            Dashboard
        </template>
        <template #content>
            <Cards key="dashboard" class="p-5" />
        </template>
    </Layout>
</template>

<script>
import Layout from '@/Layouts/Sidebar'
import { Head } from '@inertiajs/inertia-vue3';
import { Cards } from "@craniums/vue";
import RelativeDates from "@craniums/vue/src/Elements/RelativeDates";

export default {
    components: {
        RelativeDates,
        Layout,
        Head,
        Cards,
    },
}
</script>
