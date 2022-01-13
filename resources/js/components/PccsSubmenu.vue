<template>
    <ul class="pccs-submenu dropdown-menu" aria-labelledby="navbarPccsDropdown">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading"/>
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <li v-if="!Object.keys(pccs).length" class="my-3">
            <a class="dropdown-item disabled">
                <i class="fas fa-circle-notch fa-spin me-1"></i> Loading PCCs from OVHcloud API...
            </a>
        </li>
        <template v-for="(pcc, pccName, index) in pccs">
            <li :key="pccName+'-divider'" v-if="index != 0"><hr class="dropdown-divider"></li>
            <li :key="pccName">
                <a class="dropdown-item" :href="`${pccRoute}/${pccName}`">
                    {{ pccName }}
                    <small class="text-muted">
                        <template v-if="pcc.serviceName">
                            ({{ pcc.description || pcc.serviceName }})
                        </template>
                        <template v-else>
                            (<i class="fas fa-circle-notch fa-spin me-1"></i> Loading from OVHcloud API...)
                        </template>
                    </small>
                    
                </a>
            </li>
        </template>
    </ul>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import {useGetLoader} from "./compositions/axios/loadingRequest";

export default {
    name: 'PccsSubmenu',

    components: {
        LoadingScreen,
        ErrorsZone,
    },

    props: {
        ovhapiRoute: {
            type: String,
            required: true,
        },
        pccRoute: {
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
            if(force || !this.loading) {
                this.loadPccs();
            }
        },

        async loadPccs() {
            this.pccNames = await this.load(`${this.ovhapiRoute}/dedicatedCloud`);
            for (const pccName of this.pccNames) {
                this.$set(this.pccs, pccName, {});
            }
            for (const pccName of this.pccNames) {
                this.loadPcc(pccName);
            }
        },
        async loadPcc(pccName) {
            let pcc = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${pccName}`);
            this.$set(this.pccs, pccName, {...pcc});
        },
    },
}
</script>

<style lang="scss" scoped>
</style>
