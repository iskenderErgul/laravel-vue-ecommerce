<template>
  <AdminLayout>

  </AdminLayout>
</template>

<script setup>
import { initFlowbite } from 'flowbite'
import {onMounted} from "vue";
import AdminLayout from "@/Pages/Admin/Components/AdminLayout.vue";


onMounted(() => {
    initFlowbite();
})
</script>
