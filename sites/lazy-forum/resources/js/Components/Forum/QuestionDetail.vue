<template>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <!-- Question Section -->
                <div class="px-4 py-5 sm:px-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex flex-col items-center">
                                <button
                                    @click="vote(question, 1)"
                                    class="text-gray-400 hover:text-blue-500"
                                    :class="{ 'text-blue-500': userVote(question) === 1 }"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                    </svg>
                                </button>
                                <span class="text-xl font-semibold text-gray-900">{{ question.score }}</span>
                                <button
                                    @click="vote(question, -1)"
                                    class="text-gray-400 hover:text-red-500"
                                    :class="{ 'text-red-500': userVote(question) === -1 }"
                                >
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h1 class="text-2xl font-bold text-gray-900">{{ question.title }}</h1>
                            <div class="mt-2 prose max-w-none" v-html="question.body"></div>
                            <div class="mt-4 flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div v-for="tag in question.tags" :key="tag.id" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ tag.name }}
                                    </div>
                                </div>
                                <div class="flex items-center text-sm text-gray-500">
                                    <span>Asked {{ formatDate(question.created_at) }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ question.views }} views</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Answers Section -->
                <div class="border-t border-gray-200">
                    <div class="px-4 py-5 sm:px-6">
                        <h2 class="text-xl font-semibold text-gray-900">{{ question.answers.length }} Answers</h2>
                    </div>
                    <div class="px-4 py-5 sm:px-6">
                        <div v-for="answer in question.answers" :key="answer.id" class="mb-8">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="flex flex-col items-center">
                                        <button
                                            @click="vote(answer, 1)"
                                            class="text-gray-400 hover:text-blue-500"
                                            :class="{ 'text-blue-500': userVote(answer) === 1 }"
                                        >
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                            </svg>
                                        </button>
                                        <span class="text-xl font-semibold text-gray-900">{{ answer.score }}</span>
                                        <button
                                            @click="vote(answer, -1)"
                                            class="text-gray-400 hover:text-red-500"
                                            :class="{ 'text-red-500': userVote(answer) === -1 }"
                                        >
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <div class="prose max-w-none" v-html="answer.body"></div>
                                    <div class="mt-4 flex items-center justify-between">
                                        <div class="flex items-center text-sm text-gray-500">
                                            <span>Answered {{ formatDate(answer.created_at) }}</span>
                                        </div>
                                        <div v-if="canAcceptAnswer(answer)" class="flex items-center">
                                            <button
                                                @click="acceptAnswer(answer)"
                                                class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                            >
                                                Accept Answer
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Answer Form -->
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <h3 class="text-lg font-medium text-gray-900">Your Answer</h3>
                    <form @submit.prevent="submitAnswer" class="mt-4">
                        <div>
                            <textarea
                                v-model="answerForm.body"
                                rows="6"
                                class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md"
                                required
                            ></textarea>
                        </div>
                        <div class="mt-4">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                Post Answer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    question: {
        type: Object,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
});

const answerForm = useForm({
    body: ''
});

const vote = (item, voteType) => {
    // Implement voting logic
};

const userVote = (item) => {
    // Implement user vote check
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const canAcceptAnswer = (answer) => {
    return props.question.user_id === props.user.id && !props.question.is_answered;
};

const acceptAnswer = (answer) => {
    // Implement accept answer logic
};

const submitAnswer = () => {
    answerForm.post(route('answers.store', props.question.id));
};
</script> 