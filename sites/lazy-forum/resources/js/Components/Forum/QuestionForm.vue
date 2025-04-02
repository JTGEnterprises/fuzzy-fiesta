<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <InputLabel for="title" value="Title" />
            <TextInput
                id="title"
                type="text"
                class="mt-1 block w-full"
                v-model="form.title"
                required
                autofocus
                autocomplete="title"
            />
            <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <div>
            <InputLabel for="body" value="Body" />
            <textarea
                id="body"
                class="mt-1 block w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                v-model="form.body"
                rows="10"
                required
            ></textarea>
            <InputError class="mt-2" :message="form.errors.body" />
        </div>

        <div>
            <InputLabel for="tags" value="Tags" />
            <div class="mt-1">
                <div class="flex flex-wrap gap-2">
                    <div
                        v-for="tag in availableTags"
                        :key="tag.id"
                        class="inline-flex items-center"
                    >
                        <input
                            type="checkbox"
                            :id="'tag-' + tag.id"
                            :value="tag.id"
                            v-model="form.tags"
                            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                        />
                        <label
                            :for="'tag-' + tag.id"
                            class="ml-2 text-sm text-gray-700"
                        >
                            {{ tag.name }}
                        </label>
                    </div>
                </div>
            </div>
            <InputError class="mt-2" :message="form.errors.tags" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                {{ submitButtonText }}
            </PrimaryButton>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    question: {
        type: Object,
        default: () => ({
            title: '',
            body: '',
            tags: []
        })
    },
    availableTags: {
        type: Array,
        required: true
    },
    submitButtonText: {
        type: String,
        default: 'Submit'
    }
});

const form = useForm({
    title: props.question.title,
    body: props.question.body,
    tags: props.question.tags
});

const submit = () => {
    form.post(route('questions.store'));
};
</script> 