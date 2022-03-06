<template>
    <div class="pccs-page">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <Breadcrumb :pages="breadcrumb" :home-route="pccRoute"></Breadcrumb>
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 text-center mt-6">
            <li class="col-span-1" v-for="(pcc, pccName) in window._(pccs).toPairs().sortBy(0).fromPairs().value()" :key="pccName">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <div class="divide-y divide-gray-200 dark:divide-gray-600 py-2">
                        <div class="px-4 py-2">
                            <h3 class="mb-1 text-2xl">{{ pccName }}</h3>
                            <h4>{{ pcc.description }}</h4>
                            <div v-if="!Object.keys(pcc).length" class="py-6">
                                <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading data from OVHcloud API...
                            </div>
                            <p v-else>
                                <a target="_blank" :href="pcc.webInterfaceUrl" class="text-sm font-medium underline text-indigo-600 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-600">{{ pcc.webInterfaceUrl }}</a>
                            </p>
                            <div v-if="Object.keys(pcc).length" class="flex items-center justify-between pt-2">
                                <div class="grow">
                                    <i class="fas fa-map-marked-alt"></i> Location: {{ pcc.location }}<br />
                                    Commercial range: {{ pcc.commercialRange }}<br />
                                    <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major + pcc.version.minor }}
                                </div>
                                <div class="pl-4">
                                    <a class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :href="`${pccRoute}/${pccName}`">
                                        <i class="fas fa-tasks mr-2"></i>
                                        Pcc / Tasks
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div v-if="!pcc.hasOwnProperty('datacenters')" class="p-4">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading datacenters from OVHcloud API...
                        </div>
                        <div v-else class="flex items-center justify-between px-4 py-2" v-for="(datacenter, datacenterId) in window._(pcc.datacenters).toPairs().sortBy(0).fromPairs().value()" :key="datacenterId">
                            <div class="grow">
                                <div>
                                    <span>
                                        {{ datacenter.description || datacenter.name }}
                                    </span>
                                    <span class="text-gray-500">{{ datacenter.description ? datacenter.name : "#" + datacenterId }}</span>
                                </div>
                                <div class="text-sm">
                                    <span>{{ datacenter.commercialName }}</span>
                                    <span class="text-gray-500">({{ datacenter.commercialRangeName }})</span>
                                </div>
                            </div>
                            <div class="pl-4">
                                <a class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}`">
                                    <i class="fas fa-building mr-2"></i>
                                    Datacenter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import { httpRequester } from "./compositions/axios/httpRequester";

export default {
    name: "PccsPage",

    components: {
        LoadingScreen,
        ErrorsZone,
    },

    props: {
        pccNames: {
            type: Array,
            required: true,
        },
        pccRoute: {
            type: String,
            required: true,
        },
        ovhapiRoute: {
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
            pccs: {},
            breadcrumb: [],
        };
    },

    mounted() {
        for (const pccName of this.pccNames) {
            this.pccs[pccName] = {};
        }
        this.loadAll(true);
    },

    methods: {
        async loadAll(force = false) {
            if (force || !this.loading) {
                this.loadPccs();
            }
        },

        async loadPccs() {
            for (const pccName of this.pccNames) {
                this.loadPcc(pccName);
            }
        },
        async loadPcc(pccName) {
            let pcc = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${pccName}`);
            let pccDatacenters = {};
            if (this.pccs && this.pccs[pccName] && this.pccs[pccName]["datacenters"]) {
                pccDatacenters = this.pccs[pccName]["datacenters"];
                pcc["datacenters"] = pccDatacenters;
            }
            this.pccs[pccName] = { ...pcc };
            if (pcc) {
                const datacenterIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${pccName}/datacenter`);
                if (datacenterIds) {
                    const datacenters = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${pccName}/datacenter/${datacenterIds.join(",")}?batch=,`);
                    pcc["datacenters"] = pccDatacenters;
                    for (const datacenterId in datacenters) {
                        const datacenter = datacenters[datacenterId];
                        if (!datacenter["error"]) {
                            pcc["datacenters"][datacenter["key"]] = datacenter["value"];
                        }
                    }
                    this.pccs[pccName] = { ...pcc };
                }
            }
        },
    },
};
</script>

<style lang="scss" scoped></style>
