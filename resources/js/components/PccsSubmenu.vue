<template>
    <ul class="pccs-submenu dropdown-menu" aria-labelledby="navbarPccsDropdown">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <li v-if="!Object.keys(pccs).length" class="my-3">
            <a class="dropdown-item disabled"> <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading PCCs from OVHcloud API... </a>
        </li>
        <template v-for="(pcc, pccName) in window._(pccs).toPairs().sortBy(0).fromPairs().value()" :key="pccName">
            <li>
                <a class="dropdown-item" :href="`${pccRoute}/${pccName}`">
                    {{ pccName }}
                    <small class="text-gray-500">
                        <template v-if="pcc.serviceName"> ({{ pcc.description || pcc.serviceName }}) </template>
                        <template v-else> (<i class="fas fa-circle-notch fa-spin mr-1"></i> Loading from OVHcloud API...) </template>
                    </small>
                </a>
            </li>
        </template>
    </ul>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import { httpRequester } from "./compositions/axios/httpRequester";

export default {
    name: "PccsSubmenu",

    components: {
        LoadingScreen,
        ErrorsZone,
    },

    props: {
        ovhapiRoute: {
            type: String,
            required: true,
        },
        homeRoute: {
            type: String,
            required: true,
        },
        pccRoute: {
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

    data() {
        return {
            pccNames: null,
            pccs: {},
        };
    },

    mounted() {
        this.loadAll(true);
    },

    methods: {
        async loadAll(force = false) {
            if (force || !this.loading) {
                this.loadPccs();
            }
        },

        async loadPccs() {
            this.pccNames = await this.get(`${this.ovhapiRoute}/v1/dedicatedCloud`);
            for (const pccName of this.pccNames) {
                this.pccs[pccName] = {};
            }
            for (const pccName of this.pccNames) {
                this.loadPcc(pccName);
            }
        },
        async loadPcc(pccName) {
            let pcc = await this.get(`${this.ovhapiRoute}/v1/dedicatedCloud/${pccName}`);
            this.pccs[pccName] = { ...pcc };
        },
    },
};
</script>

<style lang="scss" scoped></style>
