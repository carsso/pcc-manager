<template>
    <div class="pccs-page">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading"/>
        </transition>
        <div class="row text-center">
            <div class="col-6" v-for="(pcc, pccName) in pccs" :key="pccName">
                <div class="card">
                    <div class="card-body p-4">
                        <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                            <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                        </button>
                        <h3 class="mb-2">{{ pccName }}</h3>
                        <h4>{{ pcc.description }}</h4>
                        <div v-if="!Object.keys(pcc).length" class="mt-5 mb-3">
                            <i class="fas fa-circle-notch fa-spin me-2"></i> Loading data from OVHcloud API...
                        </div>
                        <template v-else>
                            <p>
                                <a target="_blank" :href="pcc.webInterfaceUrl">{{ pcc.webInterfaceUrl }}</a>
                            </p>
                            <div class="row">
                                <div class="col-12 col-lg-8">
                                    <i class="fas fa-map-marked-alt"></i> Datacenter : {{ pcc.location }}<br />
                                    Commercial range : {{ pcc.commercialRange }}<br />
                                    <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major }} {{ pcc.version.minor }}
                                </div>
                                <div class="col-12 col-lg-4 py-2">
                                    <a class="btn btn-outline-primary btn-sm" :href="`/pcc/${pccName}`">
                                        <i class="fas fa-tasks fa-2x"></i><br />
                                        Pcc / Tasks
                                    </a>
                                </div>
                            </div>
                            <div v-if="!pcc.hasOwnProperty('datacenters')" class="card mt-4">
                                <div class="card-body p-4">
                                    <i class="fas fa-circle-notch fa-spin me-2"></i> Loading datacenters from OVHcloud API...
                                </div>
                            </div>
                            <template v-else>
                                <div class="card mt-4" v-for="(datacenter, datacenterId) in pcc.datacenters" :key="datacenterId">
                                    <div class="card-body p-3 row">
                                        <div class="col-12 col-lg-8">
                                            <div class="mb-1">
                                                <span class="h4">
                                                    {{ datacenter.description }}
                                                </span>
                                                <span class="text-muted">#{{ datacenterId }}</span>
                                            </div>
                                            <div>
                                                <span>{{ datacenter.commercialName }}</span>
                                                <span class="text-muted">({{ datacenter.commercialRangeName }})</span>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-4">
                                            <a class="btn btn-outline-primary btn-sm" :href="`/pcc/${pccName}/datacenter/${datacenterId}`">
                                                <i class="fas fa-building fa-2x"></i><br />
                                                Datacenter
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import {useGetLoader} from "./compositions/axios/loadingRequest";

export default {
    name: 'PccsPage',

    components: {
        LoadingScreen,
    },

    props: {
        pccNames: {
            type: Array,
            required: true,
        },
        ovhapiRoute: {
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
            pccs: {},
        };
    },


    mounted() {
        for (const pccName of this.pccNames) {
            this.$set(this.pccs, pccName, {});
        }
        this.loadAll(true);
    },

    methods: {
        async loadAll(force = false) {
            if(force || !this.loading) {
                this.loadPccs();
            }
        },

        async loadPccs() {
            for (const pccName of this.pccNames) {
                this.loadPcc(pccName);
            }
        },
        async loadPcc(pccName) {
            let pcc = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${pccName}`);
            let pccDatacenters = {};
            if(this.pccs && this.pccs[pccName] && this.pccs[pccName]['datacenters']) {
                pccDatacenters = this.pccs[pccName]['datacenters'];
                pcc['datacenters'] = pccDatacenters;
            }
            this.$set(this.pccs, pccName, {...pcc});
            if (pcc) {
                const datacenterIds = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${pccName}/datacenter`);
                if (datacenterIds) {
                    const datacenters = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${pccName}/datacenter/${datacenterIds.join(',')}?batch=,`);
                    pcc['datacenters'] = pccDatacenters;
                    for (const datacenterId in datacenters) {
                        const datacenter = datacenters[datacenterId];
                        if(!datacenter['error']) {
                            pcc['datacenters'][datacenter['key']] = datacenter['value'];
                        }
                    }
                    this.$set(this.pccs, pccName, {...pcc});
                }
            }
        },
    },
}
</script>

<style lang="scss" scoped>
</style>
