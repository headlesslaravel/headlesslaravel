<template>
    <Layout>
        <template #content>
            <Topper/>
            <Finder
                :filters="filters"
                :sort="sort"
            />
            <Table
                :fields="fields"
                :values="values"
            //>
        </template>
    </Layout>
</template>

<script>
import Layout from "@/Layouts/Authenticated";
import { Topper, Finder, Table, Headless } from "@craniums/vue";

export default {
    mixins: [Headless],
    components: {
        Layout,
        Topper,
        Finder,
        Table,
    }
}
</script>
