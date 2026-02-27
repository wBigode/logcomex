<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PokemonForm from './Partials/PokemonForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    nome: '',
    tipo: '',
    altura: '',
    peso: '',
    sprite: '',
    ativo: true,
});

const submit = () => {
    form.post(route('pokemon.store'));
};
</script>

<template>
    <Head title="Novo Pokémon" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Novo Pokémon
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                    <PokemonForm
                        :form="form"
                        submit-label="Criar"
                        @submit="submit"
                        class="max-w-xl"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
