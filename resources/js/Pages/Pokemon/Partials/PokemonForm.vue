<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    form: {
        type: Object,
        required: true,
    },
    submitLabel: {
        type: String,
        default: 'Salvar',
    },
});

const emit = defineEmits(['submit']);
</script>

<template>
    <form @submit.prevent="emit('submit')" class="space-y-6">
        <div>
            <InputLabel for="nome" value="Nome" />
            <TextInput
                id="nome"
                type="text"
                class="mt-1 block w-full"
                v-model="form.nome"
                required
                autofocus
            />
            <InputError class="mt-2" :message="form.errors.nome" />
        </div>

        <div>
            <InputLabel for="tipo" value="Tipo" />
            <TextInput
                id="tipo"
                type="text"
                class="mt-1 block w-full"
                v-model="form.tipo"
                required
            />
            <InputError class="mt-2" :message="form.errors.tipo" />
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <InputLabel for="altura" value="Altura (cm)" />
                <TextInput
                    id="altura"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.altura"
                    min="1"
                    required
                />
                <InputError class="mt-2" :message="form.errors.altura" />
            </div>

            <div>
                <InputLabel for="peso" value="Peso (kg)" />
                <TextInput
                    id="peso"
                    type="number"
                    class="mt-1 block w-full"
                    v-model="form.peso"
                    min="0.01"
                    step="0.01"
                    required
                />
                <InputError class="mt-2" :message="form.errors.peso" />
            </div>
        </div>

        <div>
            <InputLabel for="sprite" value="Sprite URL" />
            <TextInput
                id="sprite"
                type="text"
                class="mt-1 block w-full"
                v-model="form.sprite"
                required
            />
            <InputError class="mt-2" :message="form.errors.sprite" />
        </div>

        <div v-if="form.sprite" class="flex justify-center">
            <img
                :src="form.sprite"
                alt="Sprite preview"
                class="h-24 w-24 object-contain"
            />
        </div>

        <div>
            <label class="flex items-center">
                <Checkbox v-model:checked="form.ativo" />
                <span class="ms-2 text-sm text-gray-600">Ativo</span>
            </label>
            <InputError class="mt-2" :message="form.errors.ativo" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                {{ submitLabel }}
            </PrimaryButton>

            <Link
                :href="route('pokemon.index')"
                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Cancelar
            </Link>
        </div>
    </form>
</template>
