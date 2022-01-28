<template>
    <Layout>
        <template #header>
            <Heading>
                <template #title>
                    Dashboard
                </template>
                <template #aside>
                    <DateInterval />
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
import { Heading, Cards, DateInterval } from "@craniums/vue";

export default {
    components: {
        Layout,
        Heading,
        DateInterval,
        Cards,
    },
}
</script>
