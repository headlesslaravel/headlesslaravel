<template>
    <Layout>
        <template #header>
            <Heading :title="terms.plural"/>
        </template>
        <template #content>
            <Form
                :action="routes.update(values)"
                :fields="fields"
                :values="values"
                @cancel="visit('show')"
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
