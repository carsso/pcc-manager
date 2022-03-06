<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol role="list" class="bg-white dark:bg-gray-700 rounded-md shadow px-6 py-2 lg:py-0 grid grid-cols-1 lg:flex space-4">
            <li class="flex my-1">
                <div class="flex items-center">
                    <a :href="homeRoute" class="text-gray-400 hover:text-gray-500 dark:text-white dark:hover:text-gray-400">
                        <i class="fas fa-home mr-1"></i>
                        <span class="lg:hidden">Home</span>
                    </a>
                </div>
            </li>
            <li v-for="page in pages" :key="page.name" class="flex my-1">
                <div class="flex items-center">
                    <i class="mx-1 lg:hidden fas fa-chevron-right flex-shrink-0 h-5 w-5 text-gray-400" aria-hidden="true" />
                    <svg class="hidden lg:block flex-shrink-0 w-6 h-full text-gray-200 dark:text-gray-500" viewBox="0 0 24 44" preserveAspectRatio="none" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
                    </svg>
                    <a :href="page.href" class="lg:ml-4 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-200 dark:hover:text-gray-400" :aria-current="page.current ? 'page' : undefined">{{ page.name }}</a>
                </div>
            </li>
        </ol>
    </nav>
</template>

<script>
export default {
    name: "Breadcrumb",

    props: {
        homeRoute: {
            type: String,
            required: true,
        },
        pages: {
            type: Object,
            required: true,
        },
    },
};
</script>
