<template>
    <nav
        class="bg-gray-600 flex items-center h-14 fixed top-0 left-0 w-full z-20"
    >
        <div class="flex items-center w-13 sm:w-52">
            <Link :href="route('queue')" class="pl-5 py-3 sm:pr-3">
                <NavLogo />
            </Link>
        </div>
        <NavMobileMenu />
        <NavSearch />
        <div class="flex flex-1 justify-end">
            <NavDonate />
            <NavProfile />
        </div>
    </nav>
</template>

<script setup>
import { Link } from "@inertiajs/vue3";
import NavLogo from "./NavLogo.vue";
import NavSearch from "./NavSearch.vue";
import NavMobileMenu from "./NavMobileMenu.vue";
import NavProfile from "./NavProfile.vue";
import NavDonate from "./NavDonate.vue";
</script>
