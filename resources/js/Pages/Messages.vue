<template>
    <div class="flex flex-col h-full">
        <div class="flex-1 overflow-y-auto p-4" style="max-height: calc(100% - 200px);">
            <div v-if="selectedChat && messages">
                <div v-for="item in messages" :key="item.id" :class="[
                    'mb-2 rounded border p-2 w-3/5',
                    item.created_by === $page.props.auth.user.id ? 'ml-auto bg-blue-100 text-right' : 'mr-auto bg-gray-100 text-left',
                ]">
                    <textarea
                        class="w-full h-auto resize-none border-none bg-transparent focus:outline-none text-sm"
                        :value="item.text"
                        readonly
                    ></textarea>
                </div>
            </div>
            <div v-else class="mt-10 text-center text-gray-400">
                {{ selectedChat?.title }}
            </div>
        </div>

        <div class="h-[200px] border-t p-4 flex items-center">
            <textarea
                v-model="newMessage"
                class="flex-1 rounded border p-2 focus:border-blue-300 focus:outline-none focus:ring"
                placeholder="Write a new message..."
            ></textarea>
            <button class="ml-4 rounded bg-blue-700 px-4 py-2 text-white font-semibold hover:bg-blue-800 focus:outline-none focus:ring focus:border-blue-300" @click="sendMessage" :disabled="!newMessage">
                Send
            </button>
        </div>
    </div>
</template>


<script setup lang="ts">
import {defineProps, ref, onMounted, watch } from 'vue';
import axios from 'axios';
interface Chat {
    id: number;
    date: string;
    title: string;
    subtitle: string;
}

interface Message {
    id: number;
    chat_id: number; // Assicurati che la tua tabella ChatItems abbia questa colonna
    text: string;
    created_at: string; // O Date se vuoi convertirla
    updated_at: string; // O Date se vuoi convertirla
}

interface User {
    id: number;
    // Add other properties of your user object as needed (e.g., name, email)
    [key: string]: any; // To allow other properties
}

const messages = ref<Message[]>([]);
const loadingMessages = ref(false);

const props = defineProps <{
    selectedChat: Chat | null;
}> ();

const newMessage = ref('');

const fetchMessages = async (chatId: number) => {
    if (!chatId) {
        messages.value = [];
        return;
    }

    loadingMessages.value = true;
    try {
        const response = await axios.get(`/chats/${chatId}/messages`);
        messages.value = response.data;
    } catch (error) {
        console.error('Errore nel caricamento dei messaggi:', error);
        // Gestisci l'errore come preferisci (es. mostrando un messaggio all'utente)
    } finally {
        loadingMessages.value = false;
    }
};
onMounted(() => {
    if (props.selectedChat?.id) {
        fetchMessages(props.selectedChat.id);
    }
});

// Chiamata quando la prop selectedChat cambia
watch(() => props.selectedChat?.id, (newChatId) => {
    if (newChatId) {
        fetchMessages(newChatId);
    } else {
        messages.value = []; // Pulisci i messaggi se non c'è una chat selezionata
    }
});



const sendMessage = () => {
    if (props.selectedChat && newMessage.value.trim() !== '') {
        // In a real application, you would send this message to the server
        console.log('Sending message:', newMessage.value, 'to chat ID:', props.selectedChat.id);

        // For this example, let's just clear the input
        newMessage.value = '';

        // In a real application, you would likely update the `selectedChat.items`
        // either optimistically or after receiving a response from the server.
    }
};
</script>


<style scoped>
/* You can add specific styles for the Messages component here if needed */
</style>
