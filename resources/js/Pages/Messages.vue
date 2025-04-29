<template>
    <div class="container">
      <div class="flex gap-6">
        <div
          v-for="(prompt, index) in prompts"
          :key="index"
          class="box"
          @click="fillTextArea(prompt)"
        >
          {{ prompt }}
        </div>
      </div>
      <div class="info-zone">
        <div v-if="selectedChat && messages">
          <div
            v-for="item in messages"
            :key="item.id"
            :class="[
              'mb-2 rounded border p-2 w-3/5 ',
              item.created_by === $page.props.auth.user.id
                ? 'ml-auto bg-blue-100 text-right'
                : 'mr-auto bg-gray-100 text-left',
            ]"
          >
          <div class="message-text text-sm text-gray-900" v-html="item.text"></div>
          </div>
        </div>
        <div v-else class="mt-10 text-center text-gray-400">
          {{ selectedChat?.title }}
        </div>
      </div>
      <div class="input-zone">
  <textarea
    v-model="newMessage"
    class="flex-1 rounded border text-gray-900 p-2 focus:border-blue-300 focus:outline-none focus:ring input-textarea"
    placeholder="Write a new message..."
  ></textarea>

    <!-- New: File Upload -->
<input
    type="file"
    @change="handleFileUpload"
    ref="fileInput"
    class="ml-2"
  />

  <button
    class="send-button"
    @click="sendMessage"
    :disabled="false"
  >
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6">
      <path fill-rule="evenodd" d="M3.422 2.706a.75.75 0 01.58.172l18.667 9.333a.75.75 0 010 1.588L3.998 21.122a.75.75 0 01-.58-.172A.75.75 0 013 20.36V3.44a.75.75 0 01.422-.734zM6.75 6a.75.75 0 00-.75.75v10.5a.75.75 0 001.085.67l7.5-5.25a.75.75 0 000-1.34l-7.5-5.25A.75.75 0 006.75 6z" clip-rule="evenodd" />
    </svg>
  </button>
</div>
    </div>
  </template>

  <script setup lang="ts">
  import { defineProps, ref, onMounted, watch } from 'vue';
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
    chat_id: number;
    text: string;
    created_by: number;
  }

  interface User {
    id: number;
    [key: string]: any;
  }

  const messages = ref<Message[]>([]);
  const loadingMessages = ref(false);

  let props = defineProps<{
    selectedChat: Chat | null;
    isNewChat: number;
  }>();

  let newChat: number = props.isNewChat;
  const newMessage = ref('');

  const prompts = ref([
    'Carico una preventivo di una polizza, puoi scrivermi l\'email per il cliente?',
    'Carico una polizza, puoi estrarre tutte le informazioni chiave?',
    'Puoi riscrivere questa email in modo più chiaro e professionale?',
    'Carico un documento, puoi farmi un riassunto chiaro e dettagliato?',
  ]);

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
    } finally {
      loadingMessages.value = false;
    }
  };

  onMounted(() => {
    if (props.selectedChat?.id) {
      fetchMessages(props.selectedChat.id);
    }
  });

  const formatText = (text: string): string => {
   let formatted = text.replace(/\n/g, '<br>');
   formatted = formatted.replace(/\*([^*]+)\*/g, '<b>$1</b>');
   return formatted;
 };

  watch(() => props.selectedChat?.id, (newChatId) => {
    if (newChatId) {
      fetchMessages(newChatId);
    } else {
      messages.value = [];
    }
  });

  const fileToUpload = ref<File | null>(null); // <- NEW

const fileInput = ref<HTMLInputElement | null>(null); // <- NEW

const handleFileUpload = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files && target.files.length > 0) {
    fileToUpload.value = target.files[0];
  }
};

const sendMessage = async () => {
  if (!props.selectedChat) return;

  if (!newMessage.value.trim() && !fileToUpload.value) {
    alert('Devi scrivere un messaggio o caricare un file.');
    return;
  }

  try {
    let response;

    if (fileToUpload.value) {
      // Send FILE + TEXT to API
      const formData = new FormData();
      formData.append('file', fileToUpload.value);
      formData.append('text', newMessage.value);
      formData.append('chat_id', props.selectedChat.id.toString());
      formData.append('user_id', usePage().props.auth.user.id.toString());

      response = await axios.post(`/chats/${props.selectedChat.id}/upload-and-process`, formData, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      });
    } else {
      // No file: normal text message
      response = await axios.post(`/chats/${props.selectedChat.id}/messages`, {
        user_id: usePage().props.auth.user.id,
        chat_id: props.selectedChat.id,
        text: newMessage.value,
      });
    }

    if (Array.isArray(response.data) && response.data.length >= 1) {
      messages.value.push(response.data[0]);
      messages.value.push(response.data[1]);
      newChat = 0;
    } else {
      console.error('Errore: Risposta del server inattesa durante l\'invio del messaggio.');
    }

    // Reset input after sending
    newMessage.value = '';
    if (fileInput.value) fileInput.value.value = ''; // Clear file input
    fileToUpload.value = null;

  } catch (error) {
    console.error('Errore nell\'invio del messaggio o del file:', error);
  }
};
  const fillTextArea = (prompt: string) => {
    newMessage.value = prompt;
  };
  </script>

  <style scoped>
  .box {
    padding: 5px 5px;
    width: 18rem;
    height: 12rem;
    background-color: #edf2f7;
    border-radius: 0.5rem;
    font-weight: 400;
    font-size: 0.9em;
    color: black;
    cursor: pointer;
    margin: auto;
    transition: background-color 0.2s ease-in-out;

    display: flex;        /* Enable Flexbox */
    align-items: center;  /* Vertical centering */
    justify-content: center; /* Horizontal centering (again, for Flexbox) */
  }

  .box:hover {
    background-color: #d3e0e9;
  }

  .container {
    width: 100%;
    height: 100%;
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
    box-sizing: border-box;
    display: flex;
    flex-direction: column-reverse;
  }

  .input-zone {
    height: 20%;
    padding: 10px;
    box-sizing: border-box;
    display: flex;
  }
  .message-text {
   word-wrap: break-word; /* Keep this for long words */
   white-space: pre-wrap;  /* Preserve newlines */
 }
  .input-textarea {
    max-width: 100%;
    height: 15vh;
    resize: none;
    border: 1px solid #ddd;
    padding: 8px;
    box-sizing: border-box;
    font-family: sans-serif;
    font-size: 16px;
  }

.input-zone {
  position: relative; /* Make this the positioning context */
  display: flex;
  /* other styles */
}

.input-textarea {
  flex: 1;
  /* other styles */
}

.send-button {
  position: absolute; /* Position it absolutely */
  bottom: 1em;    /* Adjust as needed */
  right: 1em;     /* Adjust as needed */
  background: none;  /* Remove default button background */
  border: none;      /* Remove default button border */
  cursor: pointer;
  /* other styles */
}

.send-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
  </style>
