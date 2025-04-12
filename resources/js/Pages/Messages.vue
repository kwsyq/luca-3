
<template>
    <div class="container">
        <div class="info-zone">
            <div v-if="selectedChat && messages" >
                <div v-for="item in messages" :key="item.id"
                    :class="[
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
        <div class="input-zone">
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

<style scoped>
.container {
    width: 100%; /* Occupy the full width reserved for the component */
    height: 100%; /* Occupy the available visible height */
    display: flex;
    flex-direction: column;
    border: 1px solid #ccc;
    background-color: white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.info-zone {
    height: 80%;
    overflow-y: auto;
    padding: 15px;
    box-sizing: border-box; /* Include padding in the height calculation */
    display: flex;
    flex-direction: column-reverse; /* Show bottom content by default */
}

.info-text {
    /* Styles for your text content */
    margin-bottom: 10px; /* Add spacing between paragraphs */
}

.input-zone {
    height: 20%;
    padding: 10px;
    box-sizing: border-box;
    display: flex;
}

.input-textarea {
    width: 100%;
    height: 100%;
    resize: none; /* Prevent manual resizing */
    border: 1px solid #ddd;
    padding: 8px;
    box-sizing: border-box;
    font-family: sans-serif; /* Example font */
    font-size: 14px;
}
</style>

<script setup lang="ts">
import {defineProps, ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';

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
    created_by: number; // O Date se vuoi convertirla
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



const sendMessage = async () => {
    if (props.selectedChat && newMessage.value.trim() !== '') {
        const tempId = Math.random().toString(36).substring(2, 15);
        //debugger;
        const newMessageObject: Message = {
            id: 0,
            chat_id: props.selectedChat.id,
            text: newMessage.value,
            created_by: usePage().props.auth.user.id,
        };

        try {
            const response = await axios.post(`/chats/${props.selectedChat.id}/messages`, {
                user_id: usePage().props.auth.user.id,
                chat_id: props.selectedChat.id,
                text: newMessageObject.text,
            });
            // Assuming the server responds with the newly created messages (including IDs and timestamps)
            if (Array.isArray(response.data) && response.data.length >= 1) {
                // Replace the temporary message with the actual messages from the server
            console.log(response.data[1].text);
                messages.value.push(response.data[0]);
                messages.value.push(response.data[1]);
            } else {
                console.error('Errore: Risposta del server inattesa durante l\'invio del messaggio.');
                // Optionally revert the local addition or show an error
            }
        } catch (error) {
            console.error('Errore nell\'invio del messaggio:', error);
            // Optionally revert the local addition or show an error
        }
    }
};
</script>


