<template>
    <button class="loading-btn absolute top-0 right-0 inline-flex items-center m-2 px-1 py-1 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
        <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
    </button>
</template>

<script>

export default {
    name: "LoadingBtn",

    components: {
    },

    props: {
        loading: {
            type: Boolean,
            required: true,
        },
    },
};
</script>
