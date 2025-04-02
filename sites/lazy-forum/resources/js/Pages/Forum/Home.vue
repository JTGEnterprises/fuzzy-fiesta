<template>
    <ForumLayout title="Forum Home">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Hero Section -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-8">
                    <div class="p-8 text-center">
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">Welcome to the Forum</h1>
                        <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">Ask questions, share knowledge, and connect with others</p>
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('questions.create')"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-vibrant-indigo hover:bg-vibrant-purple transition-colors duration-200"
                            >
                                Ask a Question
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-vibrant-indigo hover:bg-vibrant-purple transition-colors duration-200 mr-4"
                            >
                                Log In
                            </Link>
                            <Link
                                :href="route('register')"
                                class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200"
                            >
                                Register
                            </Link>
                        </template>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-2 space-y-8">
                        <!-- Featured Questions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Featured Questions</h2>
                                <div v-if="featuredQuestions && featuredQuestions.length > 0" class="space-y-6">
                                    <div v-for="question in featuredQuestions" :key="question.id" class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-0">
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
                                                <h3 class="text-lg font-medium text-vibrant-blue dark:text-vibrant-blue">
                                                    <Link :href="route('questions.show', question.id)">
                                                        {{ question.title }}
                                                    </Link>
                                                </h3>
                                                <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
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
                                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    No featured questions yet
                                </div>
                            </div>
                        </div>

                        <!-- All Questions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <div class="flex justify-between items-center mb-6">
                                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">All Questions</h2>
                                    <div class="flex items-center space-x-2">
                                        <label for="sort" class="text-sm text-gray-600 dark:text-gray-400">Sort by:</label>
                                        <select 
                                            id="sort"
                                            v-model="sortOption"
                                            @change="sortQuestions"
                                            class="border-gray-300 dark:border-gray-600 rounded-md text-sm focus:ring-vibrant-indigo focus:border-vibrant-indigo bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                        >
                                            <option value="newest">Newest</option>
                                            <option value="active">Active</option>
                                            <option value="votes">Votes</option>
                                            <option value="answers">Answers</option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="questions && questions.data && questions.data.length > 0" class="space-y-6">
                                    <div v-for="question in questions.data" :key="question.id" class="border-b border-gray-200 dark:border-gray-700 pb-6 last:border-0">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <div class="flex flex-col items-center mr-4">
                                                    <div class="text-center mb-2">
                                                        <span class="block text-xl font-semibold text-gray-900 dark:text-white">{{ question.score }}</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">votes</span>
                                                    </div>
                                                    <div class="text-center mb-2">
                                                        <span class="block text-xl font-semibold" :class="question.answers_count > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400'">
                                                            {{ question.answers_count }}
                                                        </span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">answers</span>
                                                    </div>
                                                    <div class="text-center">
                                                        <span class="block text-xl font-semibold text-gray-500 dark:text-gray-400">{{ question.views }}</span>
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">views</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-lg font-medium text-vibrant-blue dark:text-vibrant-blue">
                                                    <Link :href="route('questions.show', question.id)">
                                                        {{ question.title }}
                                                    </Link>
                                                </h3>
                                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                                    {{ stripHtml(question.body) }}
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
                                                <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                    Asked {{ formatDate(question.created_at) }} by {{ question.user.name }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    No questions found
                                </div>

                                <!-- Pagination -->
                                <div v-if="questions && questions.data && questions.data.length > 0" class="mt-8">
                                    <nav class="flex items-center justify-between">
                                        <div class="flex-1 flex justify-between sm:hidden">
                                            <Link
                                                v-if="questions.prev_page_url"
                                                :href="questions.prev_page_url"
                                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                Previous
                                            </Link>
                                            <Link
                                                v-if="questions.next_page_url"
                                                :href="questions.next_page_url"
                                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                            >
                                                Next
                                            </Link>
                                        </div>
                                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                            <div>
                                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                                    Showing
                                                    <span class="font-medium">{{ questions.from }}</span>
                                                    to
                                                    <span class="font-medium">{{ questions.to }}</span>
                                                    of
                                                    <span class="font-medium">{{ questions.total }}</span>
                                                    results
                                                </p>
                                            </div>
                                            <div>
                                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                                    <Link
                                                        v-if="questions.prev_page_url"
                                                        :href="questions.prev_page_url"
                                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                    >
                                                        <span class="sr-only">Previous</span>
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </Link>
                                                    <div v-for="(link, i) in questions.links.slice(1, -1)" :key="i">
                                                        <Link
                                                            v-if="link.url"
                                                            :href="link.url"
                                                            :class="[
                                                                link.active ? 'bg-blue-50 dark:bg-blue-900 border-vibrant-blue dark:border-vibrant-blue text-vibrant-blue dark:text-vibrant-blue' : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700',
                                                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                                            ]"
                                                        >
                                                            {{ link.label }}
                                                        </Link>
                                                    </div>
                                                    <Link
                                                        v-if="questions.next_page_url"
                                                        :href="questions.next_page_url"
                                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                    >
                                                        <span class="sr-only">Next</span>
                                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                                        </svg>
                                                    </Link>
                                                </nav>
                                            </div>
                                        </div>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Top Tags -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Top Tags</h2>
                                <div v-if="topTags && topTags.length > 0" class="space-y-4">
                                    <div v-for="tag in topTags" :key="tag.id" class="flex items-center justify-between">
                                        <Link :href="route('tags.show', tag.id)" class="text-vibrant-blue hover:text-vibrant-indigo dark:text-vibrant-blue dark:hover:text-vibrant-indigo">
                                            {{ tag.name }}
                                        </Link>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ tag.questions_count }} questions</span>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    No tags found
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Recent Activity</h2>
                                <div v-if="recentActivity && recentActivity.length > 0" class="space-y-4">
                                    <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                                    {{ activity.user?.name?.charAt(0) || '?' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-gray-900 dark:text-white">
                                                {{ activity.user?.name || 'Anonymous' }} {{ activity.action }}
                                                <Link :href="activity.link" class="text-vibrant-blue hover:text-vibrant-indigo dark:text-vibrant-blue dark:hover:text-vibrant-indigo">
                                                    {{ activity.title }}
                                                </Link>
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ formatDate(activity.created_at) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4 text-gray-500 dark:text-gray-400">
                                    No recent activity
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ForumLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import axios from 'axios';
import ForumLayout from '@/Layouts/ForumLayout.vue';
import { ref } from 'vue';

const page = usePage();

const props = defineProps({
    questions: {
        type: Object,
        required: true
    },
    featuredQuestions: {
        type: Array,
        required: true
    },
    topTags: {
        type: Array,
        required: true
    },
    recentActivity: {
        type: Array,
        required: true
    }
});

const sortOption = ref('newest');

const sortQuestions = () => {
    window.location.href = route('forum.home', { sort: sortOption.value });
};

const vote = (question, voteType) => {
    if (!page.props.auth.user) {
        // Handle not authenticated
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
    return new Date(date).toLocaleDateString();
};

const stripHtml = (html) => {
    if (!html) return '';
    const doc = new DOMParser().parseFromString(html, 'text/html');
    return doc.body.textContent || '';
};
</script> 