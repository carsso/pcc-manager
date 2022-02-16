<template>
    <div class="pcc-graphs-page mx-4">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <div class="row text-center">
            <div class="col-8">
                <div class="card">
                    <div class="card-body p-3">
                        <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                            <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                        </button>
                        <div class="row text-center">
                            <div class="col-6">
                                <h3 class="mb-1">
                                    <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}`">
                                        <i class="far fa-arrow-alt-circle-left"></i>
                                    </a>
                                    {{ pccName }}
                                </h3>
                                <h4 class="mb-1">{{ pcc.description }}</h4>
                                <div>
                                    <a target="_blank" :href="pcc.webInterfaceUrl">{{ pcc.webInterfaceUrl }}</a>
                                </div>
                            </div>
                            <div v-if="!Object.keys(pcc).length" class="col-6 py-4"><i class="fas fa-circle-notch fa-spin me-1"></i> Loading pcc from OVHcloud API...</div>
                            <div v-else class="col-6">
                                <i class="fas fa-map-marked-alt"></i> Datacenter: {{ pcc.location }}<br />
                                Commercial range: {{ pcc.commercialRange }}<br />
                                <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major + pcc.version.minor }}<br />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body p-3">
                        <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                            <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                        </button>
                        <div v-if="!Object.keys(datacenter).length" class="py-1">
                            <div class="mb-1">
                                <span class="text-capitalize">{{ entityType }}</span>
                                <span class="h4">
                                    {{ entity.name }}
                                </span>
                                <span class="text-muted">{{ "#" + entityId }}</span>
                            </div>
                            <div class="py-2"><i class="fas fa-circle-notch fa-spin me-1"></i> Loading datacenter from OVHcloud API...</div>
                        </div>
                        <div v-else class="py-1">
                            <div class="mb-1">
                                <span class="text-capitalize">{{ entityType }}</span>
                                <span class="h4">
                                    {{ entity.name }}
                                </span>
                                <span class="text-muted">{{ "#" + entityId }}</span>
                            </div>
                            <div class="mb-1">
                                <span class="h5">Datacenter {{ datacenter.description || datacenter.name }}</span>
                                <span class="text-muted">{{ datacenter.description ? datacenter.name : "#" + datacenterId }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body p-3 text-center">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <template v-for="(text, btnDaysFrom) in daysButtons" :key="text">
                        <button type="button" class="btn btn-primary" :class="daysFrom == btnDaysFrom ? 'active' : ''" @click="changeDaysFrom(btnDaysFrom)">
                            {{ text }}
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div v-if="!graphs" class="card mt-3">
            <div class="card-body p-4 text-center"><i class="fas fa-circle-notch fa-spin me-1"></i> Loading graphs from OVHcloud API & Metrics API...</div>
        </div>
        <div class="row text-center" v-else>
            <div class="col-12 col-lg-6" v-for="(graph, graphName) in window._(graphs).toPairs().sortBy(0).fromPairs().value()" :key="graphName">
                <div class="card mt-3">
                    <div class="card-body p-4">
                        <div class="position-absolute top-0 end-0 m-3">
                            <button class="btn btn-sm badge btn-info" @click="loadAll()">
                                <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                            </button>
                        </div>
                        {{ graphName }}
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

Chart.register(...registerables);

export default {
    name: "PccGraphsPage",

    components: {
        LoadingScreen,
        ErrorsZone,
        LineChart,
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
            datacenter: {},
            user: null,
            metricsToken: null,
            daysFrom: 1,
            daysButtons: {
                1: "today",
                2: "yesterday",
                7: "last week",
                30: "last month",
                182: "last 6 months",
                365: "last year",
            },
        };
    },

    mounted() {
        this.loadAll();
    },

    methods: {
        async loadAll(force = false) {
            if (force || !this.loading) {
                this.loadPcc();
                this.loadDatacenter();
                this.loadUser();
            }
        },

        changeDaysFrom(days) {
            if (this.daysFrom == days) return;
            this.daysFrom = days;
            this.loadGraphsData();
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
