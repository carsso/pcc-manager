<template>
    <button type="button" @click="switchMode()" :disabled="loading" :class="btnClass" class="darkmode-toggler p-1 rounded-full text-gray-400 hover:text-gray-500 dark:hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-white">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <span class="sr-only">Toggle dark mode</span>
        <i class="fas fa-sun w-6" v-if="$root.$data.currentDarkmode" aria-hidden="true"></i>
        <i class="fas fa-moon w-6" v-else aria-hidden="true"></i>
    </button>
</template>

<script>
import LoadingScreen from "./LoadingScreen.vue";
import ErrorsZone from "./ErrorsZone.vue";
import { httpRequester } from "./compositions/axios/httpRequester.js";
import { MoonIcon, SunIcon } from "@heroicons/vue/24/outline";

export default {
    name: "NavbarDarkmodeToggler",

    components: {
        LoadingScreen,
        ErrorsZone,
        MoonIcon,
        SunIcon,
    },

    props: {
        btnClass: {
            type: String,
            default: "",
        },
        route: {
            type: String,
            required: true,
        },
    },

    setup() {
        const { loaded, loading, errors, request, get } = httpRequester();

        return {
            loaded,
            loading,
            errors,
            request,
            get,
        };
    },

    methods: {
        async switchMode() {
            let response = await this.get(`${this.route}/darkmode/${this.$root.$data.currentDarkmode ? "0" : "1"}`);
            if (response && !this.errors) {
                this.$root.$data.currentDarkmode = response.darkmode;
            }
        },
    },
};
</script>

<style lang="scss" scoped>
.darkmode-toggler {
    display: inline;
}
</style>
