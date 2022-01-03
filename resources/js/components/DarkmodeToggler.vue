<template>
    <div class="darkmode-toggler">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading"/>
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
import {useGetLoader} from "./compositions/axios/loadingRequest";

export default {
    name: 'DarkmodeToggler',

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
        const {
            loaded,
            loading,
            errors,
            load,
        } = useGetLoader();

        return {
            loaded,
            loading,
            errors,
            load,
        };
    },

    methods: {
        async switchMode() {
            let response = await this.load(`${this.route}/darkmode/${this.$currentDarkmode ? '0' : '1'}`);
            if(response && !this.errors) {
                this.$currentDarkmode = response.darkmode;
            }
        },
    },
}
</script>

<style lang="scss" scoped>
    .darkmode-toggler {
        display: inline;
    }
</style>
