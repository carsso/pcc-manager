<template>
    <div class="pcc-page mx-4">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading"/>
        </transition>
        <div class="card my-2">
            <div class="card-body p-3">
                <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                </button>
                <div class="row text-center">
                    <div class="col-4">
                        <h3 class="mb-1">{{ pccName }}</h3>
                        <h4 class="mb-1">{{ pcc.description }}</h4>
                        <div>
                            <a target="_blank" :href="pcc.webInterfaceUrl">{{ pcc.webInterfaceUrl }}</a>
                        </div>
                    </div>
                    <div class="col-4 py-2">
                        <div v-if="!Object.keys(pcc).length" class="my-3">
                            <i class="fas fa-circle-notch fa-spin me-2"></i> Loading pcc from OVHcloud API...
                        </div>
                        <div v-else>
                            <i class="fas fa-map-marked-alt"></i> Datacenter : {{ pcc.location }}<br />
                            Commercial range : {{ pcc.commercialRange }}<br />
                            <i class="fas fa-laptop-code"></i> {{ pcc.managementInterface.toUpperCase() }} {{ pcc.version.major }} {{ pcc.version.minor }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!datacenters" class="card my-2">
            <div class="card-body p-4">
                <i class="fas fa-circle-notch fa-spin me-2"></i> Loading datacenters from OVHcloud API...
            </div>
        </div>
        <template v-else>
            <div class="row text-center">
                <div class="col-12 col-lg-6">
                    <div class="card my-2" v-for="(datacenter, datacenterId) in datacenters" :key="datacenterId">
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
                                <a class="btn btn-primary btn-sm" :href="`/pcc/${pccName}/datacenter/${datacenterId}`">
                                    <i class="fas fa-building"></i> View<br />
                                    Datacenter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="card my-2 text-center">
            <div class="card-body p-3">
                <div class="card-title">
                    <span class="h5">Active tasks</span>
                    <small class="badge rounded-pill bg-danger position-absolute top-0 start-0 m-3">{{tasks && Object.keys(tasks).length}}</small>
                    <div class="position-absolute top-0 end-0 m-3">
                        <button class="btn btn-sm badge" :class="autoRefresh ? 'btn-danger' : 'btn-primary'" @click="autoRefresh = !autoRefresh">
                            <i class="fas" :class="autoRefresh ? 'fa-times' : 'fa-check'"></i>
                            {{ autoRefresh ? 'Disable' : 'Enable' }} auto refresh (30s)
                        </button>
                        <button class="btn btn-sm badge btn-info" @click="loadAll()">
                            <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                        </button>
                    </div>
                </div>
                <div v-if="!tasks" class="my-2">
                    <i class="fas fa-circle-notch fa-spin me-2"></i> Loading Tasks from OVHcloud API...
                </div>
                <template v-else>
                    <table class="table table-sm table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">State</th>
                                <th scope="col">Name</th>
                                <th scope="col">Created by</th>
                                <th scope="col">Date</th>
                                <th scope="col">Related to</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="task in _.orderBy(_.values(tasks), ['lastModificationDate'], ['desc'])" :key="task.taskId">
                                <td>
                                    <span :class="getTaskStateClass(task)">
                                        <i class="fas fa-circle"></i>
                                        {{task.state}}
                                    </span>
                                    <small v-if="task.state == 'todo'" class="text-muted"> - {{dateFormat(task.executionDate)}}</small>
                                    <br />
                                    <small class="text-muted">#{{task.taskId}}</small>
                                </td>
                                <td>
                                    <small>{{task.name}}</small><br />
                                    <div class="micro-gauge">
                                        <vue-svg-gauge
                                            :start-angle="-270"
                                            :end-angle="90"
                                            :inner-radius="0"
                                            :value="task.progress"
                                            :separator-step="0"
                                            :min="0"
                                            :max="100"
                                            gauge-color="#f4c009"
                                            :scale-interval="0">
                                        </vue-svg-gauge>
                                    </div>
                                    <small>{{task.progress | round(0)}}%</small>
                                    -
                                    <small class="text-muted" v-if="task.description">
                                        {{task.description}}
                                    </small>
                                    <small class="text-muted" v-else>
                                        <i>No description</i>
                                    </small>
                                </td>
                                <td>
                                    <small>{{task.createdFrom}}</small><br />
                                    <small class="text-muted" v-if="task.createdBy">({{task.createdBy}})</small>
                                </td>
                                <td>
                                    <small v-if="task.endDate && task.state == 'done'" class="text-success">
                                        Finished on<br />
                                        {{dateFormat(task.endDate)}}
                                    </small>
                                    <small v-else>
                                        Last change : <br />
                                        {{dateFormat(task.lastModificationDate)}}
                                    </small>
                                </td>
                                <td>
                                    <small v-if="task.parentTaskId" class="text-muted">
                                        <abbr title="Parent task">Task.</abbr> : #{{task.parentTaskId}}<br />
                                    </small>
                                    <small v-if="task.datacenterId" class="text-muted">
                                        <abbr title="Datacenter">Datac.</abbr> : #{{task.datacenterId}}<br />
                                    </small>
                                    <small v-if="task.userId" class="text-muted">
                                        User : #{{task.userId}}<br />
                                    </small>
                                    <small v-if="task.orderId" class="text-muted">
                                        Order : #{{task.orderId}}<br />
                                    </small>
                                    <small v-if="task.vlanId" class="text-muted">
                                        Vlan : #{{task.vlanId}}<br />
                                    </small>
                                    <small v-if="task.filerId" class="text-muted">
                                        Filer : #{{task.filerId}}<br />
                                    </small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import {useGetLoader} from "./compositions/axios/loadingRequest";
import {VueSvgGauge} from 'vue-svg-gauge'
import moment from "moment";


export default {
    name: 'PccDatacenterPage',

    components: {
        LoadingScreen,
        VueSvgGauge,
    },

    props: {
        pccName: {
            type: String,
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
            pcc: {},
            datacenters: null,
            tasks: null,
            taskIds: {},
            timer: null,
            autoRefresh: false,
        };
    },

    mounted() {
        this.loadAll();
        this.timer = setInterval(this.refreshData, 30*1000);
    },

    beforeDestroy () {
        this.cancelAutoUpdate();
    },

    methods: {
        refreshData() {
            if(this.autoRefresh) {
                this.loadAll();
            }
        },

        cancelAutoUpdate () {
            clearInterval(this.timer);
        },

        dateFormat(date) {
            if(!date) {
                return null;
            }
            return moment(date).format('MMM Do YYYY HH:mm');
        },

        async loadAll(force = false) {
            if(force || !this.loading) {
                this.loadPcc();
                this.loadDatacenters();
                this.loadTasks();
            }
        },

        async loadPcc() {
            this.pcc = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}`);
        },

        async loadDatacenters() {
            const datacenterIds = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter`);
            if(!datacenterIds.length) {
                this.datacenters = {};
            }
            let datacenterIdsChunks = this.chunkArray(datacenterIds, 40);
            for(let datacenterIdsChunk of datacenterIdsChunks) {
                const datacenters = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${datacenterIdsChunk.join(',')}?batch=,`);
                if(this.datacenters === null) {
                    this.datacenters = {};
                }
                for (const datacenterId in datacenters) {
                    const datacenter = datacenters[datacenterId];
                    if(!datacenter['error']) {
                        this.$set(this.datacenters, datacenter['key'], {...datacenter['value']});
                    }
                }
            }
        },

        async loadTasks() {
            for (const state of ['todo', 'doing', 'error', 'fixing', 'toCancel', 'toCreate', 'unknown', 'waitingForChilds', 'waitingTodo']) {
                const stateTaskIds = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/task?state=${state}`);
                for(const taskId of stateTaskIds) {
                    if(this.taskIds.hasOwnProperty(taskId)) {
                        continue;
                    }
                    this.$set(this.taskIds, taskId, taskId);
                }
            }
            if(!Object.values(this.taskIds).length) {
                this.tasks = {};
            }
            for (const taskId in this.tasks) {
                const task = this.tasks[taskId];
                if(task.state == 'done' || task.state == 'canceled') {
                    this.$delete(this.tasks, taskId);
                    this.$delete(this.taskIds, taskId);
                }
            }
            let taskIdsChunks = this.chunkArray(Object.values(this.taskIds), 40);
            for(let taskIdsChunk of taskIdsChunks) {
                const tasks = await this.load(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/task/${taskIdsChunk.join(',')}?batch=,`);
                if(this.tasks === null) {
                    this.tasks = {};
                }
                for (const taskId in tasks) {
                    const task = tasks[taskId];
                    if(!task['error']) {
                        let value = task['value'];
                        this.$set(this.tasks, task['key'], {...value});
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


        getTaskStateClass(task) {
            var resultClass = 'text-warning';
            if(task.state) {
                if(task.state == 'todo') {
                    resultClass = 'text-secondary';
                } else if(task.state == 'waitingTodo') {
                    resultClass = 'text-secondary';
                } else if(task.state == 'toCancel') {
                    resultClass = 'text-secondary';
                } else if(task.state == 'toCreate') {
                    resultClass = 'text-secondary';
                } else if(task.state == 'fixing') {
                    resultClass = 'text-warning';
                } else if(task.state == 'doing') {
                    resultClass = 'text-warning';
                } else if(task.state == 'waitingForChilds') {
                    resultClass = 'text-warning';
                } else if(task.state == 'done') {
                    resultClass = 'text-success';
                } else if(task.state == 'canceled') {
                    resultClass = 'text-success';
                } else if(task.state == 'error') {
                    resultClass = 'text-danger';
                } else if(task.state == 'unknown') {
                    resultClass = 'text-black';
                } else {
                    resultClass = 'text-danger';
                }
            }
            return resultClass;
        },

        round(value, decimals) {
            if (!value) {
                value = 0
            }

            if (!decimals) {
                decimals = 0
            }

            value = Math.round(value * Math.pow(10, decimals)) / Math.pow(10, decimals)
            return value
        },
    },
}
</script>

<style lang="scss" scoped>
    .inner-text {
        height: 100%;
        width: 100%;
        display: flex;
        align-items: flex-end;
        justify-content: center;

        span {
            max-width: 100px;
            color: grey;
            font-size: 14px;
            font-weight: 700;
        }
    }

    .mini-gauge {
        max-width: 180px;
        margin: auto;
    }

    .micro-gauge {
        max-width: 20px;
        margin: auto;
        display: inline-block;
    }
</style>
