<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Questions</h1>
                <Link
                    :href="route('questions.create')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                >
                    Ask Question
                </Link>
            </div>

            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul role="list" class="divide-y divide-gray-200">
                    <li v-for="question in questions" :key="question.id">
                        <div class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="text-center">
                                            <div class="text-lg font-semibold text-gray-900">{{ question.score }}</div>
                                            <div class="text-sm text-gray-500">votes</div>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-blue-600">
                                            <Link :href="route('questions.show', question.id)">
                                                {{ question.title }}
                                            </Link>
                                        </div>
                                        <div class="mt-1 flex items-center text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <span class="text-xs">{{ question.answers_count }} answers</span>
                                                <span class="mx-2">•</span>
                                                <span class="text-xs">{{ question.views }} views</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <div class="flex items-center space-x-2">
                                        <div v-for="tag in question.tags" :key="tag.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ tag.name }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    questions: {
        type: Array,
        required: true
    }
});
</script> 