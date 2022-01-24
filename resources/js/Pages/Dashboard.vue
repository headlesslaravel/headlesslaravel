<template>
    <Layout>
        <template #header>
            <Heading>
                <template #title>
                    Dashboard
                </template>
                <template #aside>
                    <RelativeDates />
                </template>
            </Heading>
        </template>
        <template #content>
            <Cards key="dashboard" class="p-5" />
        </template>
    </Layout>
</template>

<script>
import Layout from '@/Layouts/Sidebar'
import { Heading, Cards, RelativeDates } from "@craniums/vue";

export default {
    components: {
        Layout,
        Heading,
        RelativeDates,
        Cards,
    },
}
</script>
