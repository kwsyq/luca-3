<template>
<Head title="Dashboard" />

<header class="bg-white px-6 py-4 shadow-none">
    <div class="container-fluid mx-auto flex items-center justify-between">
        <div class="mr-24 flex">
            <ApplicationLogo class="h-20" />
        </div>


        <div class="flex items-center space-x-4">
            <Link href="/logout" class="w-56 cursor-pointer rounded-lg border border-gray-800 px-4 py-4 text-center font-semibold text-gray-700 hover:bg-blue-700 hover:text-gray-100">Esci</Link>
        </div>
    </div>
</header>
<div class="flex h-full flex-col lg:flex-row">
    <!-- Sidebar (desktop only) -->
    <aside class="hidden w-[20%] overflow-y-auto border-r lg:block">
        <div v-for="chat in chats" :key="chat.id" class="border-b p-4" @click="selectChat(chat.id)"  
            :class="{ 'bg-gray-200': selectedChatId === chat.id }">
            <div class="cursor-pointer text-xs text-gray-500">
                {{ (chat as any).date }}
            </div>
            <div class="cursor-pointer font-semibold">
                {{ (chat as any).title }}
            </div>
            <div class="text-sm text-gray-600">
                {{ (chat as any).subtitle }}
            </div>
        </div>
    </aside>

    <!-- Main content -->
    <main class="flex-1 p-4 w-[80%]">

        <div class="w-32 cursor-pointer rounded-lg border border-gray-800 px-2 py-2 text-center text-sm font-semibold text-gray-700 hover:bg-blue-700 hover:text-gray-100" @click="addNewChat">Nuova chat</div>
        <div class="flex-1 overflow-y-auto">
                <div class="mt-10 text-center text-gray-400">
                <Messages :selectedChat="selectedChat" :isNewChat="isNewChat"/>
            </div>
        </div>
    </main>
</div>
</template>

<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';


import { defineProps, ref, reactive } from 'vue';
import axios from 'axios';

import Messages from './Messages.vue';

interface Chat {
    id: number;
    date: string;
    title: string;
    subtitle: string;
}

const props = defineProps<{
  chats: Chat[];
}>();

const isNewChat = ref(0);
const chatItems = ref([]);
const loading = ref(false);
let newMessage = ref('');
const sendMessage = () => {
    if (newMessage.value.trim() !== '') {
        // In a real application, you would send this message to the server
        console.log('Sending message:', newMessage.value);

        // For this example, let's just clear the input
        newMessage.value = '';

        // In a real application, you would likely update the `selectedChat.items`
        // either optimistically or after receiving a response from the server.
    }
};
const selectedChat = reactive<Chat>({
    id: 0,
    date: '',
    title: '',
    subtitle: '',
});

const addNewChat = async () => {
    const response = await axios.post(`/chats`);
    
    const localChat=response.data;
    props.chats.push(localChat);

    Object.assign(selectedChat, localChat);
    selectedChatId.value = localChat.id;
    isNewChat.value=1;
    console.log(response);
};
const selectedChatId = ref<number | null>(null);

const selectChat = async (chatId: number) => {
    console.log('Selected chat ID:', chatId);
    const chat = props.chats.find(chat => chat.id === chatId);

    if (chat) {
        Object.assign(selectedChat, chat);
        selectedChatId.value = chat.id;
        isNewChat.value = 0;
    } else {
        selectedChat.title = '';
        selectedChat.subtitle = '';
        selectedChat.date = '';
        selectedChat.id = 0;
    }

    console.log('Selected chat title:', selectedChat.title);
};
</script>
<style lang="css" scoped>

</style>