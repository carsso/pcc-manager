<template>
    <div class="vracks-page">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <Breadcrumb :pages="breadcrumb" :home-route="homeRoute"></Breadcrumb>
        <ul role="list" class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 text-center mt-6">
            <li class="col-span-1" v-for="(vrack, vrackIdx) in window._.orderBy(window._.values(vracks), [(vrack) => vrack.connectedTo ? Object.keys(vrack.connectedTo).length : 0, 'serviceName'], ['desc', 'asc'])" :key="vrackIdx">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <div class="px-4 py-4">
                        <h3 class="mb-1 text-2xl">vRack {{ vrack.serviceName }}</h3>
                        <div v-if="!Object.keys(vrack).length" class="py-6">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading data from OVHcloud API...
                        </div>
                        <div v-else>
                            <h4>{{ vrack.name || vrack.serviceName }}</h4>
                            <p class="text-gray-500">{{ vrack.description }}</p>
                            <span class="text-gray-500" v-if="!Object.keys(vrack.connectedTo).length">
                                <i>This vRack is empty</i>
                            </span>
                            <div v-else class="pt-2">
                                <h4>Connected to : </h4>
                                <div class="flex items-center justify-between my-1 px-2 py-1 border border-gray-200 dark:border-gray-600 rounded-lg" v-for="(vrackConnection, serviceName) in window._(vrack.connectedTo).toPairs().sortBy(0).fromPairs().value()" :key="serviceName">
                                    <div class="grow">
                                        <div>
                                            <span><i class="fas fa-network-wired"></i> {{ serviceName }}</span>
                                        </div>
                                        <div class="text-sm">
                                            <span class="text-gray-500">Type : {{ vrackConnection.serviceType }}</span>
                                        </div>
                                    </div>
                                    <div class="pl-4">
                                        <a v-if="vrackConnection.serviceType == 'dedicatedCloud' || vrackConnection.serviceType == 'dedicatedCloudDatacenter'" class="inline-flex items-center px-2 py-1 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :href="`${pccRoute}/${vrackConnection.dedicatedCloud}`">
                                            <i class="fas fa-magnifying-glass mr-2"></i>
                                            PCC
                                        </a>
                                    </div>
                                </div>
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
    name: "VracksPage",

    components: {
        LoadingScreen,
        ErrorsZone,
    },

    props: {
        vrackNames: {
            type: Array,
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
            vracks: {},
            breadcrumb: [
                { name: 'vRacks', href: this.homeRoute+'/vrack', current: true },
            ],
            vrackServicesTypes: [],
        };
    },

    mounted() {
        for (const vrackName of this.vrackNames) {
            this.vracks[vrackName] = {};
        }
        this.loadAll(true);
    },

    methods: {
        async loadAll(force = false) {
            if (force || !this.loading) {
                this.loadVracks();
            }
        },

        async loadVracks() {
            const vrackSchema = await this.get(`${this.ovhapiRoute}/v1/vrack.json`);
            this.vrackServicesTypes = vrackSchema.models['vrack.AllowedServiceEnum'].enum;
            const vrackNames = await this.get(`${this.ovhapiRoute}/v1/vrack`);
            for (let vrackName of vrackNames) {
                this.loadVrack(vrackName);
            }
        },

        async loadVrack(vrackName) {
            let vrack = await this.get(`${this.ovhapiRoute}/v1/vrack/${vrackName}`); // No batch mode on this call
            vrack['serviceName'] = vrackName;
            vrack['connectedTo'] = {};
            for (let serviceType of this.vrackServicesTypes) {
                const serviceNames = await this.get(`${this.ovhapiRoute}/v1/vrack/${vrackName}/${serviceType}`);
                for (let serviceName of serviceNames) {
                    let serviceInfo = {};
                    if(serviceType == 'dedicatedCloud' || serviceType == 'dedicatedCloudDatacenter') {
                        // Load data only for PCC serviceTypes for now, optimizing the number of API calls
                        const serviceNameEncoded = encodeURIComponent(encodeURIComponent(serviceName)); // Need to double encode slashes because of laravel routing bug with %2F
                        serviceInfo = await this.get(`${this.ovhapiRoute}/v1/vrack/${vrackName}/${serviceType}/${serviceNameEncoded}`);
                    }
                    serviceInfo.serviceType = serviceType;
                    vrack['connectedTo'][serviceName] = serviceInfo;
                }
            }
            this.vracks[vrackName] = { ...vrack };
        },
    },
};
</script>

<style lang="scss" scoped></style>
