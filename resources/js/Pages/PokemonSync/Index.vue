<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    activeJob: Object,
});

const syncing = ref(false);
const syncStatus = ref(null);
const totalImported = ref(0);
const errorMessage = ref('');
let pollingInterval = null;

onMounted(() => {
    if (props.activeJob) {
        syncing.value = true;
        syncStatus.value = props.activeJob.status;
        totalImported.value = props.activeJob.total_pokemon_imported;
        pollStatus(props.activeJob.id);
    }
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

const startSync = async () => {
    syncing.value = true;
    syncStatus.value = 'pending';
    totalImported.value = 0;
    errorMessage.value = '';

    try {
        const { data } = await axios.post(route('pokemon-sync.store'));
        pollStatus(data.id);
    } catch (e) {
        if (e.response?.status === 409) {
            pollStatus(e.response.data.id);
            return;
        }

        syncing.value = false;
        syncStatus.value = 'failed';
        errorMessage.value = 'Erro ao iniciar a sincronização.';
    }
};

const pollStatus = (jobId) => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }

    pollingInterval = setInterval(async () => {
        try {
            const { data } = await axios.get(route('pokemon-sync.show', jobId));

            syncStatus.value = data.status;
            totalImported.value = data.total_pokemon_imported;

            if (data.status === 'completed' || data.status === 'failed') {
                clearInterval(pollingInterval);
                pollingInterval = null;
                syncing.value = false;

                if (data.status === 'failed') {
                    errorMessage.value = 'A sincronização falhou. Verifique os logs.';
                }
            }
        } catch (e) {
            clearInterval(pollingInterval);
            pollingInterval = null;
            syncing.value = false;
            syncStatus.value = 'failed';
            errorMessage.value = 'Erro ao consultar o status da sincronização.';
        }
    }, 2000);
};
</script>

<template>
    <Head title="Pokémon Sync" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pokémon Sync
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="max-w-xl">
                            <h3 class="text-lg font-medium text-gray-900">
                                Sincronizar com PokeAPI
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Importe os dados dos Pokémon diretamente da PokeAPI. A sincronização irá buscar nome, tipo, altura, peso e sprite de cada Pokémon.
                            </p>

                            <div class="mt-6">
                                <PrimaryButton
                                    :class="{ 'opacity-25': syncing }"
                                    :disabled="syncing"
                                    @click="startSync"
                                >
                                    {{ syncing ? 'Sincronizando...' : 'Iniciar Sincronização' }}
                                </PrimaryButton>
                            </div>

                            <!-- Alert de progresso -->
                            <div
                                v-if="syncStatus === 'pending' || syncStatus === 'processing'"
                                class="mt-6 rounded-md border border-blue-200 bg-blue-50 p-4"
                            >
                                <div class="flex items-center">
                                    <svg class="mr-3 h-5 w-5 animate-spin text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-blue-800">
                                            Sincronização em andamento...
                                        </p>
                                        <p class="mt-1 text-sm text-blue-600">
                                            {{ totalImported }} pokémon importados até agora.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert de sucesso -->
                            <div
                                v-if="syncStatus === 'completed'"
                                class="mt-6 rounded-md border border-green-200 bg-green-50 p-4"
                            >
                                <div class="flex items-center">
                                    <svg class="mr-3 h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-green-800">
                                            Sincronização concluída!
                                        </p>
                                        <p class="mt-1 text-sm text-green-600">
                                            {{ totalImported }} pokémon foram importados com sucesso.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert de erro -->
                            <div
                                v-if="syncStatus === 'failed'"
                                class="mt-6 rounded-md border border-red-200 bg-red-50 p-4"
                            >
                                <div class="flex items-center">
                                    <svg class="mr-3 h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                    </svg>
                                    <div>
                                        <p class="text-sm font-medium text-red-800">
                                            Falha na sincronização
                                        </p>
                                        <p class="mt-1 text-sm text-red-600">
                                            {{ errorMessage }}
                                            <span v-if="totalImported > 0">
                                                {{ totalImported }} pokémon foram importados antes da falha.
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
