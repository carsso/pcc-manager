<template>
    <div class="darkmode-toggler">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <a class="btn btn-outline-secondary" @click="switchMode()" :disabled="loading">
            <i class="fas" :class="$currentDarkmode ? 'fa-sun' : 'fa-moon'"></i>
        </a>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import { httpRequester } from "./compositions/axios/httpRequester";

export default {
    name: "DarkmodeToggler",

    components: {
        LoadingScreen,
        ErrorsZone,
    },

    props: {
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
            let response = await this.get(`${this.route}/darkmode/${this.$currentDarkmode ? "0" : "1"}`);
            if (response && !this.errors) {
                if(this.$currentDarkmode != response.$currentDarkmode) {
                    this.window.location.reload();
                }
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
