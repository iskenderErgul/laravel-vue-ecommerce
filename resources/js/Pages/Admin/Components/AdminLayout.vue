<template>
    <div class="antialiased bg-gray-50 dark:bg-gray-900">
        <!-- nav bar start-->
            <Navbar/>
        <!-- nav bar end-->

        <!-- Sidebar start  -->
            <Sidebar/>
        <!-- Sidebar end  -->

        <main class="p-4 md:ml-64 h-auto pt-20">
            <slot></slot>
        </main>


    </div>
</template>

<script setup>
import { initFlowbite } from 'flowbite'
import {onMounted} from "vue";
import Navbar from "@/Pages/Admin/Components/Navbar.vue";
import Sidebar from "@/Pages/Admin/Components/Sidebar.vue";

onMounted(() => {
    initFlowbite();
})

</script>
