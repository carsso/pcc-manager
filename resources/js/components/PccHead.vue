<template>
    <div class="pcc-head">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <Breadcrumb :pages="breadcrumb" :home-route="homeRoute" :pcc-route="pccRoute" ></Breadcrumb>
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow mt-6 text-center relative">
            <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
            <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-200 dark:divide-gray-600">
                <div class="py-4 px-2">
                    <h3 class="mb-1 text-2xl">{{ pccName }}</h3>
                    <h4>{{ pcc.description }}</h4>
                    <div>
                        <a target="_blank" :href="pcc.webInterfaceUrl" class="text-sm font-medium underline text-indigo-600 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-600">{{ pcc.webInterfaceUrl }}</a>
                    </div>
                </div>
                <div class="py-4 px-2">
                    <div v-if="!Object.keys(pcc).length" class="my-3"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading pcc from OVHcloud API...</div>
                    <div v-else>
                        <i class="fas fa-map-marked-alt"></i> Location: {{ pcc.location }}<br />
                        Commercial range: {{ pcc.commercialRange }}<br />
                        <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major + pcc.version.minor }}
                        <template v-if="pcc.upgrades">
                            <small class="text-yellow-600" v-if="pcc.upgrades.length > 0">
                                <abbr :title="`Available upgrade(s): ${pcc.upgrades.join(', ')}`">Upgrade available</abbr>
                            </small>
                        </template>
                        <br />
                        <div v-if="vrack">
                            <i class="fas fa-network-wired"></i> VMNetwork vRack :
                            <template v-if="!vrack.pcc">
                                <template v-if="loading">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </template>
                                <template v-else>
                                    <i class="text-gray-500">no</i>
                                </template>
                            </template>
                            <template v-else-if="vrack.pcc.name">
                                {{ vrack.pcc.name }}
                                <a class="text-indigo-600 hover:text-indigo-800" :href="`${homeRoute}/vrack`">
                                    ({{ vrack.pcc.serviceName }})
                                </a>
                            </template>
                            <template v-else>
                                <a class="text-indigo-600 hover:text-indigo-800" :href="`${homeRoute}/vrack`">
                                    {{ vrack.pcc.serviceName }}
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
                <div class="py-4 px-2">
                    <slot name="third-column"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    name: "PccHead",

    components: {
    },

    props: {
        loading: {
            type: Boolean,
            required: true,
        },
        loading: {
            type: Boolean,
            required: true,
        },
        loadAll: {
            required: true,
        },
        errors: {
            required: true,
        },
        breadcrumb: {
            type: Array,
            required: true,
        },
        pccName: {
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
        pcc: {
            type: Object,
            required: true,
        },
        vrack: {
            type: Object,
            required: false,
        },
    },
};
</script>
