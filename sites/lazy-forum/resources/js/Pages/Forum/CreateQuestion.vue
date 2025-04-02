<template>
    <ForumLayout title="Ask a Question">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Ask a Question</h1>
                        <form @submit.prevent="submit">
                            <div class="space-y-6">
                                <div>
                                    <InputLabel for="title" value="Title" class="text-gray-700 dark:text-gray-300" />
                                    <TextInput
                                        id="title"
                                        type="text"
                                        class="mt-1 block w-full bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-gray-300 dark:border-gray-600"
                                        v-model="form.title"
                                        required
                                        autofocus
                                        autocomplete="title"
                                        placeholder="Be specific and imagine you're asking a question to another person"
                                    />
                                    <InputError class="mt-2" :message="form.errors.title" />
                                </div>

                                <div>
                                    <InputLabel for="body" value="Body" class="text-gray-700 dark:text-gray-300" />
                                    <textarea
                                        id="body"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-600 focus:border-vibrant-indigo focus:ring-vibrant-indigo dark:focus:border-vibrant-purple dark:focus:ring-vibrant-purple rounded-md shadow-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        v-model="form.body"
                                        rows="10"
                                        required
                                        placeholder="Include all the information someone would need to answer your question"
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.body" />
                                </div>

                                <div>
                                    <InputLabel for="tags" value="Tags" class="text-gray-700 dark:text-gray-300" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Add up to 5 tags to describe what your question is about</p>
                                    <div class="mt-1">
                                        <div class="flex flex-wrap gap-2">
                                            <div
                                                v-for="tag in tags"
                                                :key="tag.id"
                                                class="inline-flex items-center p-2 border border-gray-200 dark:border-gray-700 rounded-md"
                                                :class="{'bg-blue-50 dark:bg-blue-900 border-blue-200 dark:border-blue-800': form.tags.includes(tag.id)}"
                                            >
                                                <input
                                                    type="checkbox"
                                                    :id="'tag-' + tag.id"
                                                    :value="tag.id"
                                                    v-model="form.tags"
                                                    class="rounded border-gray-300 dark:border-gray-600 text-vibrant-indigo dark:text-vibrant-purple shadow-sm focus:ring-vibrant-indigo dark:focus:ring-vibrant-purple"
                                                />
                                                <label
                                                    :for="'tag-' + tag.id"
                                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer"
                                                >
                                                    {{ tag.name }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <InputError class="mt-2" :message="form.errors.tags" />
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <Link
                                        :href="route('questions.index')"
                                        class="mr-4 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-vibrant-indigo hover:bg-vibrant-purple border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vibrant-indigo transition-colors duration-200"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                    >
                                        Post Question
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </ForumLayout>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import ForumLayout from '@/Layouts/ForumLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    tags: {
        type: Array,
        required: true
    }
});

const form = useForm({
    title: '',
    body: '',
    tags: []
});

const submit = () => {
    form.post(route('questions.store'), {
        onSuccess: () => {
            // Form will automatically redirect to the question show page on success
        }
    });
};
</script> 