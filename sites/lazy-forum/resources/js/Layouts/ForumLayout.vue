<template>
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors duration-200">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center">
                            <Link :href="route('forum.home')" class="flex items-center">
                                <ApplicationLogo class="block h-9 w-auto fill-current text-vibrant-indigo dark:text-vibrant-purple" />
                                <span class="ml-2 text-lg font-bold text-gray-900 dark:text-white">Lazy Forum</span>
                            </Link>
                        </div>

                        <!-- Navigation Links -->
                        <div class="hidden space-x-6 sm:-my-px sm:ml-8 sm:flex">
                            <NavLink :href="route('forum.home')" :active="route().current('forum.home')" class="text-gray-700 dark:text-gray-300 hover:text-vibrant-indigo dark:hover:text-vibrant-purple">
                                Home
                            </NavLink>
                            <NavLink :href="route('questions.index')" :active="route().current('questions.index')" class="text-gray-700 dark:text-gray-300 hover:text-vibrant-indigo dark:hover:text-vibrant-purple">
                                Questions
                            </NavLink>
                            <NavLink :href="route('tags.index')" :active="route().current('tags.index')" class="text-gray-700 dark:text-gray-300 hover:text-vibrant-indigo dark:hover:text-vibrant-purple">
                                Tags
                            </NavLink>
                        </div>
                    </div>

                    <!-- Search Bar -->
                    <div class="flex-1 max-w-2xl mx-4 flex items-center">
                        <div class="w-full">
                            <form @submit.prevent="search" class="relative">
                                <input
                                    type="text"
                                    v-model="searchQuery"
                                    placeholder="Search questions..."
                                    class="w-full pl-10 pr-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-vibrant-indigo dark:focus:ring-vibrant-purple focus:border-vibrant-indigo dark:focus:border-vibrant-purple bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                                >
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Navigation -->
                    <div class="hidden sm:flex sm:items-center">
                        <!-- Dark Mode Toggle -->
                        <button @click="toggleDarkMode" class="p-2 rounded-full text-gray-600 dark:text-gray-300 hover:text-vibrant-indigo dark:hover:text-vibrant-purple">
                            <svg v-if="darkMode" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            <svg v-else class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                            </svg>
                        </button>

                        <!-- Ask Question Button -->
                        <Link 
                            v-if="$page.props.auth.user"
                            :href="route('questions.create')" 
                            class="ml-4 px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-vibrant-indigo hover:bg-vibrant-purple focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-vibrant-indigo transition-colors duration-200"
                        >
                            Ask Question
                        </Link>

                        <!-- Bookmarks Dropdown -->
                        <Dropdown v-if="$page.props.auth.user" align="right" width="48" class="ml-4">
                            <template #trigger>
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-vibrant-indigo dark:hover:text-vibrant-purple focus:outline-none transition ease-in-out duration-150">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                    </svg>
                                    <span class="ml-2">{{ bookmarksCount }}</span>
                                </button>
                            </template>

                            <template #content>
                                <div class="w-80 bg-white dark:bg-gray-800 rounded-md shadow-lg">
                                    <div v-if="bookmarks.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                        No bookmarks yet
                                    </div>
                                    <div v-else class="divide-y divide-gray-100 dark:divide-gray-700">
                                        <div v-for="bookmark in bookmarks" :key="bookmark.id" class="p-4 hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <Link :href="route('questions.show', bookmark.question_id)" class="block">
                                                <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ bookmark.question?.title || 'Unknown question' }}</h4>
                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Bookmarked {{ formatDate(bookmark.created_at) }}</p>
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </Dropdown>

                        <!-- User Dropdown -->
                        <Dropdown align="right" width="48" class="ml-4">
                            <template #trigger>
                                <button v-if="$page.props.auth.user" class="inline-flex items-center px-3 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-vibrant-indigo hover:bg-vibrant-purple transition-colors duration-200">
                                    {{ $page.props.auth.user.name }}
                                </button>
                                <Link v-else :href="route('login')" class="inline-flex items-center px-4 py-2 bg-vibrant-indigo border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-vibrant-purple transition-colors duration-200">
                                    Log in
                                </Link>
                            </template>

                            <template #content>
                                <div class="bg-white dark:bg-gray-800 rounded-md shadow-lg">
                                    <DropdownLink :href="route('dashboard')" class="text-gray-700 dark:text-gray-300">
                                        Profile
                                    </DropdownLink>
                                    <DropdownLink :href="route('my-questions')" class="text-gray-700 dark:text-gray-300">
                                        My Questions
                                    </DropdownLink>
                                    <DropdownLink :href="route('my-answers')" class="text-gray-700 dark:text-gray-300">
                                        My Answers
                                    </DropdownLink>
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button" class="text-gray-700 dark:text-gray-300">
                                            Log Out
                                        </DropdownLink>
                                    </form>
                                </div>
                            </template>
                        </Dropdown>
                    </div>

                    <!-- Hamburger -->
                    <div class="flex items-center sm:hidden ml-4">
                        <button @click="showingNavigationDropdown = !showingNavigationDropdown" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': showingNavigationDropdown, 'inline-flex': !showingNavigationDropdown }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': !showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Responsive Navigation Menu -->
            <div :class="{'block': showingNavigationDropdown, 'hidden': !showingNavigationDropdown}" class="hidden sm:hidden">
                <div class="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink :href="route('forum.home')" :active="route().current('forum.home')" class="text-gray-700 dark:text-gray-300">
                        Home
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('questions.index')" :active="route().current('questions.index')" class="text-gray-700 dark:text-gray-300">
                        Questions
                    </ResponsiveNavLink>
                    <ResponsiveNavLink :href="route('tags.index')" :active="route().current('tags.index')" class="text-gray-700 dark:text-gray-300">
                        Tags
                    </ResponsiveNavLink>
                    <ResponsiveNavLink v-if="$page.props.auth.user" :href="route('questions.create')" :active="route().current('questions.create')" class="text-gray-700 dark:text-gray-300">
                        Ask Question
                    </ResponsiveNavLink>
                </div>

                <!-- Responsive Settings Options -->
                <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700" v-if="$page.props.auth.user">
                    <div class="px-4">
                        <div class="font-medium text-base text-gray-800 dark:text-white">{{ $page.props.auth.user.name }}</div>
                        <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ $page.props.auth.user.email }}</div>
                    </div>

                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" class="text-gray-700 dark:text-gray-300">
                            Profile
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('my-questions')" class="text-gray-700 dark:text-gray-300">
                            My Questions
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('my-answers')" class="text-gray-700 dark:text-gray-300">
                            My Answers
                        </ResponsiveNavLink>
                        <div @click="toggleDarkMode" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-700 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out cursor-pointer">
                            <div class="flex items-center">
                                <div class="mr-3">
                                    <svg v-if="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                </div>
                                <div>{{ darkMode ? 'Light Mode' : 'Dark Mode' }}</div>
                            </div>
                        </div>
                        <ResponsiveNavLink as="button" @click="logout" class="text-gray-700 dark:text-gray-300">
                            Log Out
                        </ResponsiveNavLink>
                    </div>
                </div>
                <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-700" v-else>
                    <div class="mt-3 space-y-1">
                        <ResponsiveNavLink :href="route('login')" class="text-gray-700 dark:text-gray-300">
                            Log In
                        </ResponsiveNavLink>
                        <ResponsiveNavLink :href="route('register')" class="text-gray-700 dark:text-gray-300">
                            Register
                        </ResponsiveNavLink>
                        <div @click="toggleDarkMode" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-700 dark:text-gray-300 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition duration-150 ease-in-out cursor-pointer">
                            <div class="flex items-center">
                                <div class="mr-3">
                                    <svg v-if="darkMode" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                </div>
                                <div>{{ darkMode ? 'Light Mode' : 'Dark Mode' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main>
            <slot></slot>
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const props = defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const searchQuery = ref('');
const bookmarks = ref([]);
const bookmarksCount = ref(0);
const darkMode = ref(localStorage.getItem('darkMode') === 'true');

// Apply dark mode on page load
onMounted(() => {
    applyDarkMode();
    fetchBookmarks();
});

// Toggle dark mode
const toggleDarkMode = () => {
    darkMode.value = !darkMode.value;
    localStorage.setItem('darkMode', darkMode.value);
    applyDarkMode();
};

// Apply dark mode to HTML element
const applyDarkMode = () => {
    if (darkMode.value) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

// Search function
const search = () => {
    if (searchQuery.value.trim()) {
        window.location.href = route('search', { search: searchQuery.value });
    }
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

// Fetch bookmarks
const fetchBookmarks = () => {
    if (document.querySelector('body').dataset.page && 
        JSON.parse(document.querySelector('body').dataset.page).props.auth.user) {
        axios.get(route('bookmarks.index'))
            .then(response => {
                bookmarks.value = response.data;
                bookmarksCount.value = response.data.length;
            })
            .catch(error => {
                console.error('Error fetching bookmarks:', error);
            });
    }
};

// Logout function
const logout = () => {
    router.post(route('logout'));
};
</script> 