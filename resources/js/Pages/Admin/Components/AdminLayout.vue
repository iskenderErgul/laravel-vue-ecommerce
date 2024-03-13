<template>
    <div class="antialiased bg-gray-50 dark:bg-gray-900">
        <!-- nav bar start-->
            <Navbar/>
        <!-- nav bar end-->

        <!-- Sidebar start  -->
            <Sidebar/>
        <!-- Sidebar end  -->

        <slot></slot>

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
