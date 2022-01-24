<template>
    <Layout>
        <template #header>
            <Heading :title="terms.plural"/>
        </template>
        <template #content>
            <Form
                :action="routes.store"
                :fields="fields"
                @cancel="visit('index')"
            />
        </template>
    </Layout>
</template>

<script>
import Layout from "@/Layouts/Sidebar";
import { Heading, Finder, Form, Headless } from "@craniums/vue";

export default {
    mixins: [Headless],
    components: {
        Layout,
        Heading,
        Finder,
        Form,
    }
}
</script>
