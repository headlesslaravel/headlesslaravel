<template>
    <div>
        <div class="min-h-screen bg-gray-50 flex items-start">
            <!-- This example requires Tailwind CSS v2.0+ -->
            <div class="flex flex-col min-h-0 bg-gray-800 w-64 flex-shrink-0 sticky top-0 h-screen">
                <div class="flex-1 flex flex-col overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 p-5 bg-gray-900">
                        <Logo class="text-white w-10 fill-current" />
                    </div>
                    <nav class="mt-5 flex-1 px-2 space-y-1 flex flex-col text-white" aria-label="Sidebar">
                        <MainMenu />
                    </nav>
                </div>
                <div class="flex border-t border-indigo-800 p-4">
                    <a href="#" class="flex-shrink-0 w-full group block">
                        <div class="flex items-center">
                            <div>
                                <img class="inline-block h-9 w-9 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-white">
<!--                                    {{  auth.user.name  }}-->
                                </p>
                                <p class="text-xs font-medium text-indigo-200 group-hover:text-white">
                                    View profile
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="bg-white border-b py-3" v-if="route().has('seekers.main.index')">
                    <Seeker :url="route('seekers.main.index')" />
                </div>

                <!-- Page Heading -->
                <header class="bg-white shadow sticky top-0" v-if="$slots.header">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main v-if="$slots.content" class="py-12">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="bg-white shadow-sm sm:rounded-lg">
                            <slot name="content" />
                        </div>
                    </div>
                </main>
                <main v-else>
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>

<script>
import Logo from '@/Components/ApplicationLogo.vue'
import MainMenu from "@/Menus/Main";
import Dropdown from '@/Components/Dropdown.vue'
import DropdownLink from '@/Components/DropdownLink.vue'
import NavLink from '@/Components/NavLink.vue'
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import { Link } from '@inertiajs/inertia-vue3';
import Seeker from "@craniums/vue/src/Elements/Seeker";

export default {
    components: {
        Seeker,
        Logo,
        MainMenu,
        Dropdown,
        DropdownLink,
        NavLink,
        ResponsiveNavLink,
        Link,
    },
    props: ['auth'],
    data() {
        return {
            showingNavigationDropdown: false,
        }
    },
}
</script>
