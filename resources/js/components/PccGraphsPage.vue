<template>
    <div class="pcc-graphs-page mx-4">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading" />
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <div class="card my-2">
            <div class="card-body p-3">
                <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                </button>
                <div class="row text-center">
                    <div class="col-4">
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
                    <div class="col-4 py-2">
                        <div v-if="!Object.keys(pcc).length" class="my-3">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading pcc from OVHcloud API...
                        </div>
                        <div v-else>
                            <i class="fas fa-map-marked-alt"></i> Datacenter: {{ pcc.location }}<br />
                            Commercial range: {{ pcc.commercialRange }}<br />
                            <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major + pcc.version.minor }}
                        </div>
                    </div>
                    <div class="col-4 py-3">
                        <div v-if="!Object.keys(datacenter).length" class="my-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading from OVHcloud API...
                        </div>
                        <template v-else>
                            <div class="mb-1">
                                <span class="text-capitalize">{{ entityType }}</span>
                                <span class="h4">
                                    {{ entity.name }}
                                </span>
                                <span class="text-muted">{{ '#'+entityId }}</span>
                            </div>
                            <div class="mb-1">
                                <span class="h5">Datacenter {{ datacenter.description || datacenter.name }}</span>
                                <span class="text-muted">{{ datacenter.description ? datacenter.name : '#'+datacenterId }}</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-body p-3 text-center">
                <div class="btn-group" role="group" aria-label="Basic example">
                    <template v-for="(text, btnDaysFrom) in daysButtons">
                        <button type="button" class="btn btn-primary" :class="daysFrom == btnDaysFrom ? 'active' : ''" @click="changeDaysFrom(btnDaysFrom)" :key="text">
                            {{ text }}
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div v-if="!graphsData" class="card mt-3">
            <div class="card-body p-4 text-center">
                <i class="fas fa-circle-notch fa-spin me-1"></i> Loading graphs from OVHcloud API & Metrics API...
            </div>
        </div>
        <div class="row text-center" v-else>
            <div
                class="col-12 col-lg-6"
                v-for="(graphData, metric) in _(graphsData).toPairs().sortBy(0).fromPairs().value()"
                :key="metric"
            >
                <div class="card mt-3">
                    <div class="card-body p-4">
                        <div class="position-absolute top-0 end-0 m-3">
                            <button class="btn btn-sm badge btn-info" @click="loadAll()">
                                <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                            </button>
                        </div>
                        <line-chart
                            :chart-data="graphData"
                            :options="options"
                            :height="300"
                        ></line-chart>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import LineChart from "./compositions/LineChart";
import { httpRequester } from "./compositions/axios/httpRequester";
import moment from "moment";

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
        return {
            pcc: {},
            datacenter: {},
            user: null,
            metricsToken: null,
            daysFrom: 1,
            daysButtons: {
                1: 'today',
                2: 'yesterday',
                7: 'last week',
                30: 'last month',
                182: 'last 6 months',
                365: 'last year'
            },
            graphsData: null,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    xAxes: [{
                        type: 'time',
                        time: {
                            unit: 'day',
                        }
                    }]
                }
            },
        };
    },

    mounted() {
        this.loadAll();
    },

    methods: {
        async loadAll(force = false) {
            if(force || !this.loading) {
                this.loadPcc();
                this.loadDatacenter();
                this.loadUser();
            }
        },

        changeDaysFrom(days) {
            if(this.loading) return;
            if(this.daysFrom == days) return;
            this.daysFrom = days;
            this.loadGraphsData();
        },

        async loadPcc() {
            this.pcc = await this.get(
                `${this.ovhapiRoute}/dedicatedCloud/${this.pccName}`
            );
        },

        async loadDatacenter() {
            this.datacenter = await this.get(
                `${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}`
            );
            this.loadGraphsData();
        },

        async loadUser() {
            const userIds = await this.get(
                `${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user?name=admin`
            );
            let userIdsChunks = this.chunkArray(userIds, 40);
            for (let userIdsChunk of userIdsChunks) {
                const users = await this.get(
                    `${this.ovhapiRoute}/dedicatedCloud/${
                        this.pccName
                    }/user/${userIdsChunk.join(",")}?batch=,`
                );
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

            let metrics = {};
            let title = 'Unknown';
            let key = this.entityType;
            let value = this.entity.name;
            if (this.entityType == "filer") {
                key = "datastore";
                metrics = {
                    "vscope.filer.datastore.diskspace.used" : "Diskspace used",
                    "vscope.filer.datastore.diskspace.used.perc" : "Diskspace used %",
                };
            } else if (this.entityType == "host") {
                metrics = {
                    "vscope.host.cpu.usage.perc" : "CPU usage %",
                    "vscope.host.mem.tps" : "Memory throughput",
                    "vscope.host.mem.usage.perc" : "Memory usage %",
                    "vscope.host.net.packetsrx" : "Packets received",
                    "vscope.host.net.packetstx" : "Packets sent",
                    "vscope.host.net.rx" : "Bytes received",
                    "vscope.host.net.tx" : "Bytes sent",
                };
            } else if (this.entityType == "vm") {
                value = this.entity.moRef;
                metrics = {
                    "vscope.vm.cpu.ready" : "CPU ready",
                    "vscope.vm.cpu.usage.perc" : "CPU usage %",
                    "vscope.vm.disk.bandwidth.read" : "Disk read throughput",
                    "vscope.vm.disk.bandwidth.write" : "Disk write throughput",
                    "vscope.vm.disk.io.read" : "Disk read IOPS",
                    "vscope.vm.disk.io.write" : "Disk write IOPS",
                    "vscope.vm.disk.latency.read" : "Disk read latency",
                    "vscope.vm.disk.latency.write" : "Disk write latency",
                    "vscope.vm.mem.tps" : "Memory throughput",
                    "vscope.vm.mem.usage.perc" : "Memory usage %",
                    "vscope.vm.net.packetsrx" : "Packets received",
                    "vscope.vm.net.packetstx" : "Packets sent",
                    "vscope.vm.net.rx" : "Bytes received",
                    "vscope.vm.net.tx" : "Bytes sent",
                };
            }

            for (const metric in metrics) {
                let title = metrics[metric];
                this.loadGraphData(metric, key, value, title);
            }
        },

        async loadGraphData(metric, key, value, title) {
            let dateFrom = moment()
                .subtract(this.daysFrom, "days")
                .toISOString();
            let dateTo = moment().toISOString();
            let granularity = '2 h 0';
            if(this.daysFrom > 15) {
                granularity = '1 d 0';
            }
            if(this.daysFrom > 90) {
                granularity = '1 w 0';
            }
            if(this.daysFrom > 300) {
                granularity = '2 w 0';
            }
            if(this.daysFrom > 600) {
                granularity = '30 d 0';
            }
            const params = `
                [
                    "${this.metricsToken.token}"
                    "${metric}" { "${key}" "${value}" }
                    "${dateFrom}" "${dateTo}"
                ] FETCH 'gts' STORE
                [ $gts bucketizer.max "${dateTo}" TOTIMESTAMP ${granularity} ] BUCKETIZE
            `;
            const data = await this.request({
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
            const graphData = this.getGraphData(data, title);
            if(!this.graphsData) {
                this.graphsData = {};
            }
            this.$set(this.graphsData, metric, graphData);
        },

        getGraphData(warp10Data, title) {
            let data = [];
            let label = "";
            if(warp10Data && warp10Data[0] && warp10Data[0][0]) {
                label = title;
                for (const v of warp10Data[0][0].v) {
                    data.push({
                        x: new Date(v[0]/1000),
                        y: v[1],
                    });
                }
            }
            let dataset = {
                label: label,
                data: data,
                fill: false,
                backgroundColor: "#ffd3d3",
                borderColor: "#f87979",
            };
            return { datasets: [dataset] };
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

            value =
                Math.round(value * Math.pow(10, decimals)) /
                Math.pow(10, decimals);
            return value;
        },
    },
};
</script>

<style lang="scss" scoped>
</style>
