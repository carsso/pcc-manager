<template>
    <div class="pcc-graphs-page mx-4">
        <pcc-head :breadcrumb="breadcrumb" :pcc-name="pccName" :pcc-route="pccRoute" :pcc="pcc" :vrack="vrack" :loading="loading" :errors="errors" :load-all="loadAll">
            <template v-slot:third-column>
                <div v-if="!Object.keys(datacenter).length" class="py-4">
                    <div class="mb-2">
                        <span class="text-capitalize">{{ capitalize(entityType) }}</span>
                        <span class="h4">
                            {{ entity.name }}
                        </span>
                        <span class="text-gray-500">{{ "#" + entityId }}</span>
                    </div>
                    <div class="py-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading datacenter from OVHcloud API...</div>
                </div>
                <div v-else class="py-4">
                    <div class="mb-2">
                        <span class="text-capitalize">{{ capitalize(entityType) }}</span>
                        <span class="h4">
                            {{ entity.name }}
                        </span>
                        <span class="text-gray-500">{{ "#" + entityId }}</span>
                    </div>
                    <div>
                        <span class="h5">Datacenter {{ datacenter.description || datacenter.name }}</span>
                        <span class="text-gray-500">{{ datacenter.description ? datacenter.name : "#" + datacenterId }}</span>
                    </div>
                </div>
            </template>
        </pcc-head>

        <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center mt-6">
            <div class="px-8 py-4 flex justify-center">
                <RadioGroup v-model="daysFrom">
                    <RadioGroupLabel class="sr-only"> Display graphs since : </RadioGroupLabel>
                    <div class="grid grid-cols-3 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                        <RadioGroupOption as="template" v-for="(text, btnDaysFrom) in daysButtons" :key="text" :value="btnDaysFrom" v-slot="{ active, checked }">
                        <div :class="[active ? 'ring-2 ring-offset-2 ring-indigo-500' : '', checked ? 'bg-indigo-600 border-transparent text-white hover:bg-indigo-700' : 'bg-white dark:bg-gray-600 border-gray-200 dark:border-gray-500 text-gray-900 dark:text-gray-200 hover:bg-gray-50', 'border rounded-md py-2 px-2 flex items-center justify-center text-sm font-medium uppercase cursor-pointer sm:flex-1']">
                            <RadioGroupLabel as="p">
                                {{ text }}
                            </RadioGroupLabel>
                        </div>
                        </RadioGroupOption>
                    </div>
                </RadioGroup>
            </div>
        </div>

        <div v-if="!graphs" class="bg-white dark:bg-gray-700 rounded-lg shadow mt-6 text-center py-6">
            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading graphs from OVHcloud API & Metrics API...
        </div>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 mt-6">
            <div v-for="(graph, graphName) in window._(graphs).toPairs().sortBy(0).fromPairs().value()" :key="graphName">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <div class="p-4">
                        <div class="mb-3">{{ graphName }}</div>
                        <line-chart :chart-data="graph.data" :options="graph.options" :height="300"></line-chart>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import { Chart, registerables } from 'chart.js';
import 'chartjs-adapter-moment';
import { LineChart, useLineChart } from 'vue-chart-3'
import { httpRequester } from "./compositions/axios/httpRequester";
import moment from "moment";
import { RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'

Chart.register(...registerables);

export default {
    name: "PccGraphsPage",

    components: {
        LoadingScreen,
        ErrorsZone,
        LineChart,
        RadioGroup,
        RadioGroupLabel,
        RadioGroupOption,
    },

    props: {
        pccName: {
            type: String,
            required: true,
        },
        datacenterId: {
            type: String,
            required: true,
        },
        entityType: {
            type: String,
            required: true,
        },
        entityId: {
            type: String,
            required: true,
        },
        entity: {
            type: Object,
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
        let key = this.entityType;
        let value = this.entity.name;
        let graphs = {};
        if (this.entityType == "filer") {
            key = "datastore";
            graphs = {
                "Disk space used": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.filer.datastore.diskspace.used",
                                label: "Disk space used",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Usage (GB)",
                                },
                            },
                        },
                    },
                },
                "Disk space used %": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.filer.datastore.diskspace.used.perc",
                                label: "Disk space used %",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: "Usage percent (%)",
                                },
                            },
                        },
                    },
                },
            };
        } else if (this.entityType == "host") {
            graphs = {
                "CPU usage %": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.host.cpu.usage.perc",
                                label: "CPU usage %",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: "Usage percent (%)",
                                },
                            },
                        },
                    },
                },
                /*
                "Memory TPS": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.host.mem.tps",
                                label: "Memory TPS",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true
                                title: {
                                    display: true,
                                    text: 'TPS'
                                },
                            },
                        },
                    },
                },
                */
                "Memory usage %": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.host.mem.usage.perc",
                                label: "Memory usage %",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: "Usage percent (%)",
                                },
                            },
                        },
                    },
                },
            };
            for (const nic of ["vmnic0", "vmnic3", "vmnic2", "vmnic1"]) {
                graphs["Network bandwidth " + nic] = {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.host.net.packetsrx",
                                _filter: {
                                    nicname: nic,
                                },
                                label: "Packets received",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d5ffd3",
                                borderColor: "#7af879",
                                xAxisID: "xAxis",
                                yAxisID: "pps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.host.net.packetstx",
                                _filter: {
                                    nicname: nic,
                                },
                                label: "Packets sent",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#ffd3d3",
                                borderColor: "#f87979",
                                xAxisID: "xAxis",
                                yAxisID: "pps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.host.net.rx",
                                _filter: {
                                    nicname: nic,
                                },
                                label: "Bytes received",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "kbps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.host.net.tx",
                                _filter: {
                                    nicname: nic,
                                },
                                label: "Bytes sent",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#fffad3",
                                borderColor: "#f8d779",
                                xAxisID: "xAxis",
                                yAxisID: "kbps",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            kbps: {
                                id: "kbps",
                                type: "linear",
                                position: "left",
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "KBytes/s",
                                },
                            },
                            pps: {
                                id: "pps",
                                type: "linear",
                                position: "right",
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Packets/s",
                                },
                            },
                        },
                    },
                };
            }
        } else if (this.entityType == "vm") {
            value = this.entity.moRef;
            graphs = {
                "CPU usage %": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.cpu.usage.perc",
                                label: "CPU usage %",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: "Usage percent (%)",
                                },
                            },
                        },
                    },
                },
                "Memory usage %": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.mem.usage.perc",
                                label: "Memory usage %",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                suggestedMax: 100,
                                title: {
                                    display: true,
                                    text: "Usage percent (%)",
                                },
                            },
                        },
                    },
                },
                "CPU ready": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.cpu.ready",
                                label: "CPU ready",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Miliseconds (CPU Ready)",
                                },
                            },
                        },
                    },
                },
                /*
                "Memory TPS": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.mem.tps",
                                label: "Memory TPS",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'TPS'
                                },
                            },
                        },
                    },
                },
                */
                "Disk throughput": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.disk.bandwidth.read",
                                label: "Disk read throughput",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d5ffd3",
                                borderColor: "#7af879",
                                xAxisID: "xAxis",
                                yAxisID: "kbps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.disk.bandwidth.write",
                                label: "Disk write throughput",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#ffd3d3",
                                borderColor: "#f87979",
                                xAxisID: "xAxis",
                                yAxisID: "kbps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.disk.io.read",
                                label: "Disk read IOPS",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "iops",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.disk.io.write",
                                label: "Disk write IOPS",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#fffad3",
                                borderColor: "#f8d779",
                                xAxisID: "xAxis",
                                yAxisID: "iops",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            kbps: {
                                type: "linear",
                                position: "left",
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "KBytes/s",
                                },
                            },
                            iops: {
                                type: "linear",
                                position: "right",
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Operations/s",
                                },
                            },
                        },
                    },
                },
                "Disk latency": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.disk.latency.read",
                                label: "Disk read latency",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.disk.latency.write",
                                label: "Disk write latency",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#fffad3",
                                borderColor: "#f8d779",
                                xAxisID: "xAxis",
                                yAxisID: "yAxis",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            yAxis: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Miliseconds",
                                },
                            },
                        },
                    },
                },
                "Network bandwidth": {
                    data: {
                        datasets: [
                            {
                                data: [],
                                _metricName: "vscope.vm.net.packetsrx",
                                label: "Packets received",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d5ffd3",
                                borderColor: "#7af879",
                                xAxisID: "xAxis",
                                yAxisID: "pps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.net.packetstx",
                                label: "Packets sent",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#ffd3d3",
                                borderColor: "#f87979",
                                xAxisID: "xAxis",
                                yAxisID: "pps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.net.rx",
                                label: "Bytes received",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#d3d8ff",
                                borderColor: "#7994f8",
                                xAxisID: "xAxis",
                                yAxisID: "kbps",
                            },
                            {
                                data: [],
                                _metricName: "vscope.vm.net.tx",
                                label: "Bytes sent",
                                fill: false,
                                cubicInterpolationMode: 'monotone',
                                tension: 0.4,
                                pointRadius: 2,
                                backgroundColor: "#fffad3",
                                borderColor: "#f8d779",
                                xAxisID: "xAxis",
                                yAxisID: "kbps",
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxis: {
                                type: "time",
                                time: {
                                    unit: "day",
                                },
                            },
                            kbps: {
                                type: "linear",
                                position: "left",
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "KBytes/s",
                                },
                            },
                            pps: {
                                type: "linear",
                                position: "right",
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: "Packets/s",
                                },
                            },
                        },
                    },
                },
            };
        }

        return {
            entityMetrics: {
                key: key,
                value: value,
            },
            graphs: graphs,
            pcc: {},
            vrack: null,
            datacenter: {},
            user: null,
            metricsToken: null,
            daysFrom: "1",
            daysButtons: {
                "1": "today",
                "2": "yesterday",
                "7": "last week",
                "30": "last month",
                "182": "last 6 months",
                "365": "last year",
            },
            breadcrumb: [
                { name: this.pccName, href: this.pccRoute+'/'+this.pccName, current: false },
                { name: 'Datacenters', href: this.pccRoute+'/'+this.pccName, current: false },
                { name: 'Datacenter #'+this.datacenterId, href: this.pccRoute+'/'+this.pccName+'/datacenter/'+this.datacenterId, current: false },
                { name: this.capitalize(this.entityType+'s'), href: this.pccRoute+'/'+this.pccName+'/datacenter/'+this.datacenterId, current: false },
                { name: this.capitalize(this.entityType)+' #'+this.entityId, href: this.pccRoute+'/'+this.pccName+'/datacenter/'+this.datacenterId+'/'+this.entityType+'/'+this.entityId, current: true },
            ],
        };
    },

    mounted() {
        this.loadAll();
    },

    watch: {
        daysFrom(newValue, oldValue) {
            this.loadGraphsData();
        },
    },

    methods: {
        async loadAll(force = false) {
            if (force || !this.loading) {
                this.loadPcc();
                this.loadDatacenter();
                this.loadUser();
            }
        },

        capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        },

        async loadPcc() {
            this.pcc = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}`);
        },

        async loadDatacenter() {
            this.datacenter = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}`);
            this.loadGraphsData();
        },

        async loadUser() {
            const userIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user?name=admin`);
            let userIdsChunks = this.chunkArray(userIds, 40);
            for (let userIdsChunk of userIdsChunks) {
                const users = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${userIdsChunk.join(",")}?batch=,`);
                for (const i in users) {
                    const user = users[i];
                    if (!user["error"]) {
                        let value = user["value"];
                        if (value.name == "admin") {
                            this.metricsToken = await this.request({
                                method: "post",
                                url: `${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${value.userId}/metricsToken`,
                            });
                            this.user = value;
                            this.loadGraphsData();
                            return; // We found the admin user and its token, we can stop here
                        }
                    }
                }
            }
        },

        async loadGraphsData() {
            if (!this.datacenter || !this.entity || !this.metricsToken) {
                return;
            }

            for (const graphName in this.graphs) {
                for (const dataset of this.graphs[graphName].data.datasets) {
                    this.loadGraphData(graphName, dataset._metricName, dataset._filter);
                }
            }
        },

        async loadGraphData(graphName, metricName, filter) {
            if (!this.datacenter || !this.entity || !this.metricsToken) {
                return;
            }

            let dateFrom = moment().subtract(this.daysFrom, "days").toISOString();
            let dateTo = moment().toISOString();

            let wantedPoints = 50;
            let minutes = this.daysFrom * 24 * 60;
            let pointMinutesInterval = Math.ceil(minutes / wantedPoints);
            let granularity = pointMinutesInterval + " m 0";

            const params = `
                [
                    "${this.metricsToken.token}"
                    "${metricName}" { "${this.entityMetrics.key}" "${this.entityMetrics.value}" }
                    "${dateFrom}" "${dateTo}"
                ] FETCH 'gts' STORE
                [ $gts bucketizer.max "${dateTo}" TOTIMESTAMP ${granularity} ] BUCKETIZE
            `;
            const warp10Data = await this.request({
                method: "post",
                url: `${this.metricsToken.warpEndpoint}/api/v0/exec`,
                data: params,
                transformRequest: [
                    (data, headers) => {
                        delete headers.common["X-Requested-With"];
                        return data;
                    },
                ],
            });

            let datas = {};
            for (const i of warp10Data) {
                for (const j of i) {
                    let metricName = j.c;
                    for (const v of j.v) {
                        let exclude = false;
                        if (filter) {
                            for (const filterName in filter) {
                                if (j.l[filterName] && j.l[filterName] != filter[filterName]) {
                                    exclude = true;
                                }
                            }
                        }
                        if (!exclude) {
                            if (!datas[metricName]) {
                                datas[metricName] = [];
                            }
                            datas[metricName].push({
                                x: new Date(v[0] / 1000),
                                y: v[1],
                            });
                        }
                    }
                }
            }
            if (this.graphs[graphName] && this.graphs[graphName].data.datasets && datas[metricName]) {
                let data = this.graphs[graphName].data;
                for (const i in data.datasets) {
                    if (data.datasets[i]._metricName == metricName) {
                        data.datasets[i].data = datas[metricName];
                        this.graphs[graphName]["data"] = { ...data };
                    }
                }
            }
        },

        chunkArray(myArray, chunk_size) {
            var results = [];
            while (myArray.length) {
                results.push(myArray.splice(0, chunk_size));
            }
            return results;
        },

        round(value, decimals) {
            if (!value) {
                value = 0;
            }

            if (!decimals) {
                decimals = 0;
            }

            value = Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals);
            return value;
        },
    },
};
</script>

<style lang="scss" scoped></style>
