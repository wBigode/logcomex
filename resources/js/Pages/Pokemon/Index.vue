<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DangerButton from '@/Components/DangerButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    pokemon: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({ search: '' }),
    },
});

const search = ref(props.filters.search);
let searchTimeout = null;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('pokemon.index'), { search: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
});

const confirmingDeactivation = ref(false);
const pokemonToDeactivate = ref(null);
const deactivateForm = useForm({});

const confirmDeactivate = (poke) => {
    pokemonToDeactivate.value = poke;
    confirmingDeactivation.value = true;
};

const deactivatePokemon = () => {
    deactivateForm.delete(route('pokemon.destroy', pokemonToDeactivate.value.id), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingDeactivation.value = false;
    pokemonToDeactivate.value = null;
};
</script>

<template>
    <Head title="Pokémon" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pokémon
                </h2>
                <Link
                    :href="route('pokemon.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
                >
                    Novo Pokémon
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <div class="mb-4">
                            <TextInput
                                type="text"
                                class="block w-full sm:w-80"
                                placeholder="Pesquisar por nome ou tipo..."
                                v-model="search"
                            />
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full table-fixed divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="w-16 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Sprite
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Nome
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Tipo
                                        </th>
                                        <th class="w-24 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Altura (cm)
                                        </th>
                                        <th class="w-24 px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Peso (kg)
                                        </th>
                                        <th class="w-16 px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Ativo
                                        </th>
                                        <th class="w-36 px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">
                                            Ações
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white">
                                    <tr v-for="poke in pokemon.data" :key="poke.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <img
                                                :src="poke.sprite"
                                                :alt="poke.nome"
                                                class="h-10 w-10 object-contain"
                                            />
                                        </td>
                                        <td class="truncate px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ poke.nome }}
                                        </td>
                                        <td class="truncate px-4 py-3 text-sm text-gray-500">
                                            {{ poke.tipo }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ poke.altura }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-500">
                                            {{ poke.peso }}
                                        </td>
                                        <td class="px-4 py-3 text-center text-sm text-gray-500">
                                            <span
                                                :class="poke.ativo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                                class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            >
                                                {{ poke.ativo ? 'Sim' : 'Não' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right text-sm font-medium">
                                            <Link
                                                :href="route('pokemon.edit', poke.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                            >
                                                Editar
                                            </Link>
                                            <button
                                                @click="confirmDeactivate(poke)"
                                                class="ms-4 text-red-600 hover:text-red-900"
                                            >
                                                Desativar
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="pokemon.data.length === 0">
                                        <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                                            Nenhum Pokémon cadastrado.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="pokemon.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="flex items-center gap-1">
                                <template v-for="link in pokemon.links" :key="link.label">
                                    <Link
                                        v-if="link.url"
                                        :href="link.url"
                                        v-html="link.label"
                                        class="rounded-md px-3 py-2 text-sm"
                                        :class="link.active
                                            ? 'bg-gray-800 text-white'
                                            : 'text-gray-500 hover:bg-gray-100'"
                                        preserve-scroll
                                    />
                                    <span
                                        v-else
                                        v-html="link.label"
                                        class="rounded-md px-3 py-2 text-sm text-gray-300"
                                    />
                                </template>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Deactivate Confirmation Modal -->
        <Modal :show="confirmingDeactivation" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">
                    Tem certeza que deseja desativar este Pokémon?
                </h2>

                <p class="mt-1 text-sm text-gray-600">
                    O Pokémon <strong>{{ pokemonToDeactivate?.nome }}</strong> será marcado como inativo.
                </p>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeModal">
                        Cancelar
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': deactivateForm.processing }"
                        :disabled="deactivateForm.processing"
                        @click="deactivatePokemon"
                    >
                        Desativar
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
