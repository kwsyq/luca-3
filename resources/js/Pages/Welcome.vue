<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
}>();

function handleImageError() {
    document.getElementById('screenshot-container')?.classList.add('!hidden');
    document.getElementById('docs-card')?.classList.add('!row-span-1');
    document.getElementById('docs-card-content')?.classList.add('!flex-row');
    document.getElementById('background')?.classList.add('!hidden');
}
</script>

<template>
    <Head title="Welcome" />
    <header class="bg-white shadow-none py-4 px-6">
        <div class="container-fluid mx-auto flex justify-between items-center">

            <div class="flex mr-24">
                <ApplicationLogo class="h-20" />
            </div>

            <nav class="hidden md:flex space-x-4">
                <a href="#" class="text-gray-700 hover:text-gray-900">Funzionalità</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Contattaci</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Fonti</a>
                <a href="#" class="text-gray-700 hover:text-gray-900">Prezzi</a>
            </nav>

            <div class="flex items-center space-x-4">
                <a href="/login" class="text-center border border-gray-800 hover:bg-blue-700 hover:text-orange-100 cursor-pointer text-gray-700 font-semibold py-4 px-4 rounded-lg w-56">Accedi</a>
                <a href="/register" class="text-center bg-blue-700 hover:bg-gray-300 text-gray-100 hover:text-gray-800 font-semibold py-4 px-4 rounded-lg w-56">Attiva la Prova Gratuita</a>
            </div>

            <div class="md:hidden">
                <button id="mobile-menu-button" class="text-gray-700 focus:outline-none">
                    <svg class="h-6 w-6 fill-current" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M4 5h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2zm0 6h16a1 1 0 0 1 0 2H4a1 1 0 1 1 0-2z"/>
                    </svg>
                </button>
            </div>

        </div>



    </header>
    <div class="vertical-scroll-container">
        <section class="bg-gray-100 flex items-center justify-center rounded-lg min-h-[85svh] mx-10 my-5">
            <div class="max-w-[60%]">
                <p class="text-6xl font-bold text-gray-800 text-center leading-[90px] font-serif tracking-tight">
                    L'assistente di intelligenza artificiale per assicuratori e
                    <span class="inverted-text">professionisti</span>
                    in ambito assicurativo, legale e del l'energia.
                </p>
                <p class="text-center text-2xl font-base text-gray-800 mt-12">
                    Risposte rapide e precise ai tuoi quesiti, supporto nella redazione e revisione di documenti: più efficienza, meno tempo sprecato.
                </p>
                <div class="flex items-center space-x-4 w-full">
                <button class="mx-auto mt-11 bg-yellow-400 hover:bg-gray-300 text-gray-700 font-semibold py-4 px-4 rounded-lg w-56 ">Attiva la Prova Gratuita</button>
                    </div>
            </div>
        </section>
        <section class="pagetop">
            <div class="max-w-[60%]">

                <div class="border border-grey-2 rounded-full px-8 py-4 flex items-center gap-2 mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 29 24" class="h-6 w-6">
                        <path fill="#22253B" d="M22.5 12a1.48 1.48 0 0 1-.984 1.406l-5.948 2.163-2.162 5.947a1.5 1.5 0 0 1-2.812 0l-2.162-5.948-5.948-2.162a1.5 1.5 0 0 1 0-2.812l5.948-2.162 2.162-5.948a1.5 1.5 0 0 1 2.812 0l2.163 5.948 5.947 2.162A1.48 1.48 0 0 1 22.5 12"></path><g clip-path="url(#AIcon_svg__a)"><path fill="#22253B" d="M28.375 6a.62.62 0 0 1-.41.586l-2.479.9-.9 2.479a.626.626 0 0 1-1.172 0l-.9-2.478-2.479-.901a.625.625 0 0 1 0-1.172l2.478-.9.9-2.48a.625.625 0 0 1 1.173.001l.9 2.478 2.479.9a.62.62 0 0 1 .41.587"></path></g><defs><clipPath id="AIcon_svg__a"><path fill="#fff" d="M19 1h10v10H19z"></path></clipPath></defs>
                    </svg>
                    <h6 class="text-font-color-1 text-[0.75rem] leading-[1.5rem] font-[550] font-inter -tracking-[0.5px]">COME SI USA</h6>
                </div>
            </div>
            <div class="max-w-[60%]">
                    <p class="text-6xl font-bold text-gray-800  font-serif tracking-tight">Esempi di <span class="inverted-text">attività</span> che assicur.ai può svolgere.</p>
</div>
                </section>
    </div>



    <footer
        class="py-16 text-center text-sm text-black dark:text-white/70"
    >
        Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
    </footer>
</template>

<style scoped>
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: #fff;
    border-bottom: 1px solid #ddd;
    position: sticky;
    top: 0;
    z-index: 1000;
}
.inverted-text {

    color: white; /* Inverted text color */
    background-color: black; /* Inverted background color */
    padding: 0 5px; /* Optional padding for better readability */
    border-radius: 23px; /* Optional rounded corners */
    padding-left: 25px; /* Optional padding for better readability */
    padding-right: 25px; /* Optional padding for better readability */
    padding-bottom: 5px; /* Optional padding for better readability */
}
/* Vertical Pages Styles */
.vertical-scroll-container {
    display: flex;
    flex-direction: column;
    height: 100vh; /* Full viewport height */
    overflow-y: auto; /* Enable vertical scrolling */
}

.page {
    flex: 0 0 100vh; /* Each page takes full viewport height */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background-color: #f5f5f5;
    border-bottom: 1px solid #ddd;
    padding: 20px;
}
.pagetop {
    flex: 0 0 100vh; /* Each page takes full viewport height */
    display: flex;
    flex-direction: column;
    justify-content: top;
    align-items: center;
    background-color: #f5f5f5;
    border-bottom: 1px solid #ddd;
    padding: 20px;
}
.page:nth-child(odd) {
    background-color: #e0e0e0;
}

.page h1 {
    font-size: 2rem;
    margin-bottom: 1rem;
}

.page p {
    font-size: 1.2rem;
    text-align: center;
}
</style>
