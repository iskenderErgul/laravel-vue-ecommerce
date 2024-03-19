<template>

    <!--    navbar-->
    <Header/>

    <!--    main content-->
    <slot></slot>


    <!--    //footer-->
    <Footer/>

</template>

<script setup>


import Header from '@/Pages/User/Layouts/Header.vue'
import Footer from '@/Pages/User/Layouts/Footer.vue'
import Hero from "@/Pages/User/Layouts/Hero.vue";

</script>

