<template>
    <nav class="bg-gray-600 flex items-center h-14">
        <div class="flex items-center w-13 sm:w-52">
            <a :href="route('home')" class="pl-5 py-3 sm:pr-3">
                <NavLogo />
            </a>
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
import NavLogo from "./NavLogo.vue";
import NavSearch from "./NavSearch.vue";
import NavMobileMenu from "./NavMobileMenu.vue";
import NavProfile from "./NavProfile.vue";
import NavDonate from "./NavDonate.vue";
</script>
