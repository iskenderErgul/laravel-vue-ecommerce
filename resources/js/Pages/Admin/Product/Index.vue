
<template>
<AdminLayout>
    <product-list :products="products"></product-list>
</AdminLayout>

</template>

<script setup>
defineProps({
    products:Array
})
import AdminLayout from "@/Pages/Admin/Components/AdminLayout.vue";
import ProductList from "@/Pages/Admin/Product/ProductList.vue";

</script>
