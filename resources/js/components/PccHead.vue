<template>
    <div class="pcc-head">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <Breadcrumb :pages="breadcrumb" :home-route="pccRoute"></Breadcrumb>
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow mt-6 text-center relative">
            <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
            <div class="grid grid-cols-1 sm:grid-cols-1 lg:grid-cols-3 divide-y lg:divide-y-0 lg:divide-x divide-gray-200 dark:divide-gray-600">
                <div class="p-4">
                    <h3 class="mb-2 text-2xl">{{ pccName }}</h3>
                    <h4 class="text-lg">{{ pcc.description }}</h4>
                    <div>
                        <a target="_blank" :href="pcc.webInterfaceUrl" class="text-sm font-medium underline text-indigo-600 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-600">{{ pcc.webInterfaceUrl }}</a>
                    </div>
                </div>
                <div class="p-4">
                    <div v-if="!Object.keys(pcc).length" class="my-3"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading pcc from OVHcloud API...</div>
                    <div v-else class="py-3">
                        <i class="fas fa-map-marked-alt"></i> Datacenter: {{ pcc.location }}<br />
                        Commercial range: {{ pcc.commercialRange }}<br />
                        <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major + pcc.version.minor }}<br />
                        <div v-if="pcc.upgrades">
                            <small class="text-yellow-600" v-if="pcc.upgrades.length > 0">
                                <abbr :title="`Available upgrade(s): ${pcc.upgrades.join(', ')}`">Upgrade available</abbr>
                            </small>
                        </div>
                        <div v-if="vrack">
                            <i class="fas fa-network-wired"></i> vRack :
                            <template v-if="!vrack.pcc">
                                <template v-if="loading">
                                    <i class="fas fa-circle-notch fa-spin"></i>
                                </template>
                                <template v-else>
                                    <i class="text-gray-500">no</i>
                                </template>
                            </template>
                            <template v-else-if="vrack.pcc.name">
                                {{ vrack.pcc.name }} <span class="text-gray-500">({{ vrack.pcc.serviceName }})</span>
                            </template>
                            <template v-else>
                                {{ vrack.pcc.serviceName }}
                            </template>
                        </div>
                    </div>
                </div>
                <div class="p-4">
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
