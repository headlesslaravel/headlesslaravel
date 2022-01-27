<template>
    <Link
        :href="route('dashboard')"
        class="py-3 px-5 hover:bg-gray-700 transition-all"
        :class="{'font-bold bg-gray-900': route().current('dashboard') }">
        Dashboard
    </Link>
    <Link
        :href="route('users.index')"
        class="py-3 px-5 hover:bg-gray-700 transition-all"
        :class="{'font-bold bg-gray-900': route().current('users.*') }">
        Users
    </Link>
</template>

<script>
import { Link } from '@inertiajs/inertia-vue3';

export default {
    components: {
        Link,
    },

}
</script>
