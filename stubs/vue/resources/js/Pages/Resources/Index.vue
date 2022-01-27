<template>
    <Layout>
        <template #header>
            <Topper/>
        </template>
        <template #content>
            <Finder
                :filters="filters"
            />
            <Table
                :click="routes.show"
                :fields="fields"
                :sort="sort"
                :values="values"
            />
        </template>
    </Layout>
</template>

<script>
import Layout from "@/Layouts/Sidebar";
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
