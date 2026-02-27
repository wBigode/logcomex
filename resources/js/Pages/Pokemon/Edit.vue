<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PokemonForm from './Partials/PokemonForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({
    pokemon: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    nome: props.pokemon.nome,
    tipo: props.pokemon.tipo,
    altura: props.pokemon.altura,
    peso: props.pokemon.peso,
    sprite: props.pokemon.sprite,
    ativo: Boolean(props.pokemon.ativo),
});

const submit = () => {
    form.put(route('pokemon.update', props.pokemon.id));
};
</script>

<template>
    <Head :title="`Editar ${pokemon.nome}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Editar {{ pokemon.nome }}
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <PokemonForm
                        :form="form"
                        submit-label="Atualizar"
                        @submit="submit"
                        class="max-w-xl"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
