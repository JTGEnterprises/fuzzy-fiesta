<template>
    <ForumLayout title="Questions">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <div class="flex space-x-4">
                                <button
                                    v-for="sort in sortOptions"
                                    :key="sort.value"
                                    @click="currentSort = sort.value"
                                    :class="[
                                        'px-4 py-2 rounded-md text-sm font-medium',
                                        currentSort === sort.value
                                            ? 'bg-vibrant-indigo bg-opacity-10 dark:bg-opacity-20 text-vibrant-indigo dark:text-vibrant-blue'
                                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'
                                    ]"
                                >
                                    {{ sort.label }}
                                </button>
                            </div>
                            <Link
                                v-if="$page.props.auth.user"
                                :href="route('questions.create')"
                                class="inline-flex items-center px-4 py-2 bg-vibrant-indigo border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-vibrant-purple transition-colors duration-200"
                            >
                                Ask Question
                            </Link>
                        </div>

                        <div class="space-y-4">
                            <div v-for="question in questions.data" :key="question.id" class="border-b border-gray-200 dark:border-gray-700 pb-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <div class="flex flex-col items-center">
                                            <button
                                                @click="vote(question, 1)"
                                                class="text-gray-400 dark:text-gray-500 hover:text-vibrant-blue dark:hover:text-vibrant-blue"
                                                :class="{ 'text-vibrant-blue': userVote(question) === 1 }"
                                            >
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            </button>
                                            <span class="text-xl font-semibold text-gray-900 dark:text-white">{{ question.score }}</span>
                                            <button
                                                @click="vote(question, -1)"
                                                class="text-gray-400 dark:text-gray-500 hover:text-vibrant-red dark:hover:text-vibrant-red"
                                                :class="{ 'text-vibrant-red': userVote(question) === -1 }"
                                            >
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="text-lg font-medium text-vibrant-blue dark:text-vibrant-blue">
                                            <Link :href="route('questions.show', question.id)">
                                                {{ question.title }}
                                            </Link>
                                        </div>
                                        <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center space-x-4">
                                                <span>{{ question.answers_count }} answers</span>
                                                <span>•</span>
                                                <span>{{ question.views }} views</span>
                                                <span>•</span>
                                                <span>Asked {{ formatDate(question.created_at) }}</span>
                                            </div>
                                        </div>
                                        <div class="mt-2 flex flex-wrap gap-2">
                                            <div
                                                v-for="tag in question.tags"
                                                :key="tag.id"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                            >
                                                {{ tag.name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <Pagination :links="questions.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ForumLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ForumLayout from '@/Layouts/ForumLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import axios from 'axios';

const props = defineProps({
    questions: {
        type: Object,
        required: true
    },
    search: {
        type: String,
        default: ''
    }
});

const currentSort = ref('latest');
const sortOptions = [
    { label: 'Latest', value: 'latest' },
    { label: 'Most Voted', value: 'voted' },
    { label: 'Most Viewed', value: 'viewed' },
    { label: 'Most Answered', value: 'answered' }
];

const vote = (question, voteType) => {
    // Check if user is authenticated
    if (!document.querySelector('body')?.dataset?.page) {
        window.location.href = route('login');
        return;
    }
    
    const pageData = JSON.parse(document.querySelector('body').dataset.page);
    if (!pageData.props.auth.user) {
        window.location.href = route('login');
        return;
    }

    axios.post(route('votes.store', ['question', question.id]), {
        vote_type: voteType
    }).then(response => {
        question.score = response.data.score;
    });
};

const userVote = (question) => {
    // Implement user vote check
    return null;
};

const formatDate = (date) => {
    const now = new Date();
    const questionDate = new Date(date);
    const diffTime = Math.abs(now - questionDate);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 1) {
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        if (diffHours < 1) {
            const diffMinutes = Math.floor(diffTime / (1000 * 60));
            return diffMinutes <= 1 ? 'just now' : `${diffMinutes} minutes ago`;
        }
        return diffHours === 1 ? '1 hour ago' : `${diffHours} hours ago`;
    } else if (diffDays === 1) {
        return 'yesterday';
    } else if (diffDays < 7) {
        return `${diffDays} days ago`;
    } else if (diffDays < 30) {
        const weeks = Math.floor(diffDays / 7);
        return weeks === 1 ? '1 week ago' : `${weeks} weeks ago`;
    } else if (diffDays < 365) {
        const months = Math.floor(diffDays / 30);
        return months === 1 ? '1 month ago' : `${months} months ago`;
    } else {
        const years = Math.floor(diffDays / 365);
        return years === 1 ? '1 year ago' : `${years} years ago`;
    }
};
</script> 