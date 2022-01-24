<template>
    <Layout>
        <template #header>
            <Heading>
                <template #title>
                    {{ terms.singular }}
                </template>
                <template #aside>
                    <ItemDots
                        :routes="routes"
                        :values="values"
                    />
                </template>
            </Heading>
        </template>
        <template #content>
            <Detail
                :fields="fields"
                :values="values"
            />
        </template>
    </Layout>
</template>

<script>
import Layout from "@/Layouts/Sidebar";
import {Link} from "@inertiajs/inertia-vue3";
import { Heading, Detail, ItemDots, Headless } from "@craniums/vue";

export default {
    mixins: [Headless],
    components: {
        Layout,
        Heading,
        Link,
        Detail,
        ItemDots
    }
}
</script>
