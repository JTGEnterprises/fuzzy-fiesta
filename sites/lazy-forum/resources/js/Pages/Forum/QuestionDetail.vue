<template>
    <ForumLayout :title="question.title">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                    <!-- Main Content -->
                    <div class="lg:col-span-3 space-y-8">
                        <!-- Question -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <div class="mb-4">
                                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ question.title }}</h1>
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Asked {{ formatDate(question.created_at) }}</span>
                                        <span class="mx-2">•</span>
                                        <span>Viewed {{ question.views }} times</span>
                                    </div>
                                </div>

                                <div class="border-t border-gray-200 pt-6">
                                    <div class="flex">
                                        <!-- Votes -->
                                        <div class="flex-shrink-0 pr-6">
                                            <div class="flex flex-col items-center">
                                                <button
                                                    @click="vote(question, 1)"
                                                    class="text-gray-400 dark:text-gray-500 hover:text-vibrant-blue dark:hover:text-vibrant-blue"
                                                    :class="{ 'text-vibrant-blue': userVote(question) === 1 }"
                                                >
                                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                    </svg>
                                                </button>
                                                <span class="text-2xl font-bold my-2 text-gray-900 dark:text-white">{{ question.score }}</span>
                                                <button
                                                    @click="vote(question, -1)"
                                                    class="text-gray-400 dark:text-gray-500 hover:text-vibrant-red dark:hover:text-vibrant-red"
                                                    :class="{ 'text-vibrant-red': userVote(question) === -1 }"
                                                >
                                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                    </svg>
                                                </button>
                                                <button
                                                    @click="bookmarkQuestion"
                                                    class="mt-4 text-gray-400 dark:text-gray-500 hover:text-vibrant-yellow dark:hover:text-vibrant-yellow"
                                                    :class="{ 'text-vibrant-yellow': isBookmarked }"
                                                >
                                                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" stroke="none">
                                                        <path d="M5 4a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 20V4z" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Question Content -->
                                        <div class="flex-1">
                                            <div class="prose dark:prose-invert max-w-none" v-html="question.body"></div>
                                            
                                            <div class="mt-6 flex flex-wrap gap-2">
                                                <div
                                                    v-for="tag in question.tags"
                                                    :key="tag.id"
                                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200"
                                                >
                                                    {{ tag.name }}
                                                </div>
                                            </div>
                                            
                                            <div class="mt-6 flex justify-between items-center">
                                                <div class="flex items-center space-x-4">
                                                    <button v-if="canEdit" @click="editQuestion" class="text-sm text-vibrant-blue hover:text-vibrant-indigo dark:text-vibrant-blue dark:hover:text-vibrant-indigo">
                                                        Edit
                                                    </button>
                                                    <button v-if="canDelete" @click="deleteQuestion" class="text-sm text-vibrant-red hover:text-red-700 dark:text-vibrant-red dark:hover:text-red-400">
                                                        Delete
                                                    </button>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    <img 
                                                        :src="question.user.profile_photo_url" 
                                                        alt="Profile" 
                                                        class="h-8 w-8 rounded-full"
                                                    >
                                                    <div class="text-sm">
                                                        <div class="font-medium text-gray-900">{{ question.user.name }}</div>
                                                        <div class="text-gray-500">Asked {{ formatDate(question.created_at) }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Answer Count -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                                    {{ question.answers.length }} {{ question.answers.length === 1 ? 'Answer' : 'Answers' }}
                                </h2>
                            </div>
                        </div>

                        <!-- Answers -->
                        <div v-for="answer in sortedAnswers" :key="answer.id" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg" :class="{ 'border-2 border-green-500 dark:border-green-600': answer.is_accepted }">
                            <div class="p-6">
                                <div class="flex">
                                    <!-- Votes -->
                                    <div class="flex-shrink-0 pr-6">
                                        <div class="flex flex-col items-center">
                                            <button
                                                @click="vote(answer, 1)"
                                                class="text-gray-400 dark:text-gray-500 hover:text-vibrant-blue dark:hover:text-vibrant-blue"
                                                :class="{ 'text-vibrant-blue': userVote(answer) === 1 }"
                                            >
                                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                                </svg>
                                            </button>
                                            <span class="text-2xl font-bold my-2 text-gray-900 dark:text-white">{{ answer.score || 0 }}</span>
                                            <button
                                                @click="vote(answer, -1)"
                                                class="text-gray-400 dark:text-gray-500 hover:text-vibrant-red dark:hover:text-vibrant-red"
                                                :class="{ 'text-vibrant-red': userVote(answer) === -1 }"
                                            >
                                                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            <button
                                                v-if="canAcceptAnswer(answer)"
                                                @click="acceptAnswer(answer)"
                                                class="mt-4 text-gray-400 dark:text-gray-500 hover:text-vibrant-green dark:hover:text-vibrant-green"
                                                :class="{ 'text-vibrant-green': answer.is_accepted }"
                                            >
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                </svg>
                                            </button>
                                            <div v-if="answer.is_accepted" class="mt-2 text-xs text-center text-vibrant-green dark:text-green-400">
                                                Accepted
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Answer Content -->
                                    <div class="flex-1">
                                        <div class="prose dark:prose-invert max-w-none" v-html="answer.body"></div>
                                        
                                        <div class="mt-6 flex justify-between items-center">
                                            <div class="flex items-center space-x-4">
                                                <button v-if="canEditAnswer(answer)" @click="editAnswer(answer)" class="text-sm text-vibrant-blue hover:text-vibrant-indigo dark:text-vibrant-blue dark:hover:text-vibrant-indigo">
                                                    Edit
                                                </button>
                                                <button v-if="canDeleteAnswer(answer)" @click="deleteAnswer(answer)" class="text-sm text-vibrant-red hover:text-red-700 dark:text-vibrant-red dark:hover:text-red-400">
                                                    Delete
                                                </button>
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                <img 
                                                    :src="answer.user.profile_photo_url" 
                                                    alt="Profile" 
                                                    class="h-8 w-8 rounded-full"
                                                >
                                                <div class="text-sm">
                                                    <div class="font-medium text-gray-900">{{ answer.user.name }}</div>
                                                    <div class="text-gray-500">Answered {{ formatDate(answer.created_at) }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Answer Form -->
                        <div v-if="$page.props.auth.user" class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Your Answer</h2>
                                <form @submit.prevent="submitAnswer">
                                    <div class="mb-4">
                                        <textarea
                                            v-model="answerForm.body"
                                            class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm dark:bg-gray-700 dark:text-white"
                                            rows="8"
                                            placeholder="Write your answer here..."
                                            required
                                        ></textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button
                                            type="submit"
                                            class="inline-flex items-center px-4 py-2 bg-vibrant-indigo border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-vibrant-purple focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vibrant-indigo disabled:opacity-25 transition-colors duration-200"
                                            :disabled="answerForm.processing"
                                        >
                                            Post Your Answer
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div v-else class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6 text-center">
                                <p class="text-gray-600 dark:text-gray-400 mb-4">You must be logged in to answer this question.</p>
                                <Link
                                    :href="route('login')"
                                    class="inline-flex items-center px-4 py-2 bg-vibrant-indigo border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-vibrant-purple transition-colors duration-200"
                                >
                                    Log in
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-8">
                        <!-- Related Questions -->
                        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Related Questions</h2>
                                <div class="space-y-4">
                                    <div v-for="relatedQuestion in relatedQuestions" :key="relatedQuestion.id" class="pb-4 border-b border-gray-200 dark:border-gray-700 last:border-0 last:pb-0">
                                        <Link
                                            :href="route('questions.show', relatedQuestion.id)"
                                            class="text-vibrant-blue hover:text-vibrant-indigo dark:text-vibrant-blue dark:hover:text-vibrant-indigo font-medium block"
                                        >
                                            {{ relatedQuestion.title }}
                                        </Link>
                                        <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-1 space-x-2">
                                            <span>{{ relatedQuestion.answers_count }} answers</span>
                                            <span>•</span>
                                            <span>{{ relatedQuestion.views }} views</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ask Question Card -->
                        <div class="bg-blue-50 dark:bg-blue-900 overflow-hidden shadow-xl sm:rounded-lg">
                            <div class="p-6">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Know the answer?</h2>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">Help others by sharing your knowledge</p>
                                <Link
                                    v-if="$page.props.auth.user"
                                    :href="route('questions.create')"
                                    class="inline-flex items-center px-4 py-2 bg-vibrant-indigo border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-vibrant-purple transition-colors duration-200"
                                >
                                    Ask a Question
                                </Link>
                                <Link
                                    v-else
                                    :href="route('login')"
                                    class="inline-flex items-center px-4 py-2 bg-vibrant-indigo border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-vibrant-purple transition-colors duration-200"
                                >
                                    Log in to Ask
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ForumLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import ForumLayout from '@/Layouts/ForumLayout.vue';

const page = usePage();

const props = defineProps({
    question: {
        type: Object,
        required: true
    },
    relatedQuestions: {
        type: Array,
        required: true
    },
    user: {
        type: Object,
        required: false,
        default: null
    }
});

const isBookmarked = ref(false);
const answerForm = useForm({
    body: ''
});

// Sort answers with accepted answer first, then by score
const sortedAnswers = computed(() => {
    return [...props.question.answers].sort((a, b) => {
        if (a.is_accepted && !b.is_accepted) return -1;
        if (!a.is_accepted && b.is_accepted) return 1;
        return (b.score || 0) - (a.score || 0);
    });
});

// Check if the current user can edit the question
const canEdit = computed(() => {
    return props.user && props.user.id === props.question.user_id;
});

// Check if the current user can delete the question
const canDelete = computed(() => {
    return props.user && props.user.id === props.question.user_id;
});

// Check if the current user can edit an answer
const canEditAnswer = (answer) => {
    return props.user && props.user.id === answer.user_id;
};

// Check if the current user can delete an answer
const canDeleteAnswer = (answer) => {
    return props.user && props.user.id === answer.user_id;
};

// Check if the current user can accept an answer
const canAcceptAnswer = (answer) => {
    return props.user && 
           props.user.id === props.question.user_id && 
           !props.question.is_answered;
};

// Vote on a question or answer
const vote = (item, voteType) => {
    if (!props.user) {
        window.location.href = route('login');
        return;
    }

    const itemType = item.answers ? 'question' : 'answer';
    
    axios.post(route('votes.store', [itemType, item.id]), {
        vote_type: voteType
    }).then(response => {
        item.score = response.data.score;
    });
};

// User's vote on a question or answer
const userVote = (item) => {
    // Implement user vote check
    return null;
};

// Bookmark a question
const bookmarkQuestion = () => {
    if (!props.user) {
        window.location.href = route('login');
        return;
    }
    
    axios.post(route('bookmarks.toggle', props.question.id))
        .then(response => {
            isBookmarked.value = response.data.bookmarked;
        });
};

// Submit an answer
const submitAnswer = () => {
    answerForm.post(route('question.answers.store', props.question.id), {
        onSuccess: () => {
            answerForm.reset();
        }
    });
};

// Format date
const formatDate = (date) => {
    const now = new Date();
    const targetDate = new Date(date);
    const diffTime = Math.abs(now - targetDate);
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

// Check if question is bookmarked by the user on component mount
if (props.user) {
    axios.get(route('bookmarks.index'))
        .then(response => {
            const bookmarked = response.data.some(bookmark => 
                bookmark.question_id === props.question.id
            );
            isBookmarked.value = bookmarked;
        });
}

const editQuestion = () => {
    window.location.href = route('questions.edit', props.question.id);
};

const deleteQuestion = () => {
    if (confirm('Are you sure you want to delete this question?')) {
        router.delete(route('questions.destroy', props.question.id));
    }
};

const editAnswer = (answer) => {
    // Implement edit answer functionality
    alert('Edit answer functionality not implemented yet');
};

const deleteAnswer = (answer) => {
    if (confirm('Are you sure you want to delete this answer?')) {
        // Implement delete answer functionality
        alert('Delete answer functionality not implemented yet');
    }
};
</script>
