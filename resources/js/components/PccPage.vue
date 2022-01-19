<template>
    <div class="pcc-page mx-4">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading"/>
        </transition>
        <transition name="errors-zone">
            <errors-zone :errors="errors" v-if="errors" />
        </transition>
        <div class="card">
            <div class="card-body p-3">
                <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                </button>
                <div class="row text-center">
                    <div class="col-4">
                        <h3 class="mb-1">
                            <a :href="`${pccRoute}`">
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
                            <template v-if="pcc.upgrades">
                                <small class="text-warning" v-if="pcc.upgrades.length > 0"><abbr :title="`Available upgrade(s): ${pcc.upgrades.join(', ')}`">Upgrade available</abbr></small>
                            </template>
                        </div>
                    </div>
                    <div class="col-4 py-2">
                        <div v-if="Object.keys(pcc).length">
                            <i class="fas fa-tachometer-alt"></i> Bandwidth: {{ pcc.bandwidth }}<br />
                            <i class="fas fa-user-lock"></i> Concurrent sessions: {{ pcc.userLimitConcurrentSession }}<br />
                            <i class="far fa-clock"></i> Session timeout: {{ pcc.userSessionTimeout ? pcc.userSessionTimeout : 'never' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="!datacenters" class="card mt-3">
            <div class="card-body p-4">
                <i class="fas fa-circle-notch fa-spin me-1"></i> Loading datacenters from OVHcloud API...
            </div>
        </div>
        <template v-else>
            <div class="row text-center">
                <div class="col-12 col-lg-6 mt-3" v-for="(datacenter, datacenterId) in _(datacenters).toPairs().sortBy(0).fromPairs().value()" :key="datacenterId">
                    <div class="card">
                        <div class="card-body p-3 row">
                            <div class="col-12 col-lg-8">
                                <div class="mb-1">
                                    <span class="h4">
                                        {{ datacenter.description || datacenter.name }}
                                    </span>
                                    <span class="text-muted">{{ datacenter.description ? datacenter.name : '#'+datacenterId }}</span>
                                </div>
                                <div>
                                    <span>{{ datacenter.commercialName }}</span>
                                    <span class="text-muted">({{ datacenter.commercialRangeName }})</span>
                                </div>
                            </div>
                            <div class="col-12 col-lg-4">
                                <a class="btn btn-outline-primary btn-sm" :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}`">
                                    <i class="fas fa-building"></i> View<br />
                                    Datacenter
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div class="row text-center">
            <div class="col-12 col-lg-6">
                <pcc-options-card
                    title="Product options"
                    :loading="loading"
                    :options="options"
                    :load-all="loadAll"
                >
                </pcc-options-card>
            </div>

            <div class="col-12 col-lg-6">
                <pcc-options-card
                    title="Sector-specific compliance options"
                    :loading="loading"
                    :options="complianceOptions"
                    :load-all="loadAll"
                >
                </pcc-options-card>
                <div class="card mt-3 text-center">
                    <div class="card-body p-3">
                        <div class="card-title">
                            <span class="h5">IP blocks</span>
                            <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{ips && Object.keys(ips).length}}</small>
                            <div class="position-absolute top-0 end-0 m-3">
                                <button class="btn btn-sm badge btn-info" @click="loadAll()">
                                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                                </button>
                            </div>
                        </div>
                        <table class="table table-sm table-striped table-bordered mb-0">
                            <tbody>
                                <tr v-if="!ips">
                                    <td colspan="2">
                                        <i class="fas fa-circle-notch fa-spin me-1"></i> Loading IP blocks from OVHcloud API...
                                    </td>
                                </tr>
                                <tr v-else-if="!Object.keys(ips).length">
                                    <td colspan="2">
                                        <i>No IP block found</i>
                                    </td>
                                </tr>
                                <tr v-for="ip in _.orderBy(_.values(ips), ['network'])" :key="ip.network" :title="`${ip.country} - ${ip.network} : Netmask: ${ip.netmask} - Gateway: ${ip.gateway}`">
                                    <td>
                                        <span class="fi me-1" :class="'fi-'+flagFromCountry(ip.country).toLowerCase()"></span>
                                        {{ip.network}}
                                    </td>
                                    <td>
                                        <small class="text-muted">Gateway: {{ip.gateway}}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-3 text-center">
            <div class="card-body p-3">
                <div class="card-title">
                    <span class="h5">{{ recentTasks ? 'Active & recent' : 'Active' }} tasks</span>
                    <div class="position-absolute top-0 start-0 m-3">
                        <small class="badge rounded-pill bg-primary">{{tasks && Object.keys(tasks).length}}</small>
                        <button class="btn btn-sm badge" :class="recentTasks ? 'btn-outline-danger text-danger' : 'btn-outline-primary text-primary'" @click="toggleRecentTasks">
                            <i class="fas" :class="recentTasks ? 'fa-eye-slash' : 'fa-eye'"></i>
                            {{ recentTasks ? 'Hide' : 'Display' }} recent
                        </button>
                    </div>
                    <div class="position-absolute top-0 end-0 m-3">
                        <button class="btn btn-sm badge" :class="autoRefreshTasks ? 'btn-danger' : 'btn-primary'" @click="toggleAutoRefreshTasks">
                            <i class="fas" :class="autoRefreshTasks ? 'fa-times' : 'fa-check'"></i>
                            {{ autoRefreshTasks ? 'Disable' : 'Enable' }} tasks auto refresh (30s)
                        </button>
                        <button class="btn btn-sm badge btn-info" @click="loadAll()">
                            <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                        </button>
                    </div>
                </div>
                <table class="table table-sm table-striped table-bordered mb-0">
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
                        <tr v-if="!tasks">
                            <td colspan="5">
                                <i class="fas fa-circle-notch fa-spin me-1"></i> Loading tasks from OVHcloud API...
                            </td>
                        </tr>
                        <tr v-else-if="!Object.keys(tasks).length">
                            <td colspan="5">
                                <i>No task found</i>
                            </td>
                        </tr>
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
                                <span :title="robots && robots[task.name] && robots[task.name].description">
                                    {{task.name}}
                                </span>
                                <br />
                                <div class="micro-gauge">
                                    <vue-svg-gauge
                                        :start-angle="-270"
                                        :end-angle="90"
                                        :inner-radius="0"
                                        :value="task.progress"
                                        :separator-step="0"
                                        :min="0"
                                        :max="100"
                                        :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                        :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                        gauge-color="#f4c009"
                                        :scale-interval="0">
                                    </vue-svg-gauge>
                                </div>
                                <small>{{task.progress | round(0)}}%</small>
                                -
                                <small class="text-muted" v-if="task.description">
                                    {{task.description}}
                                </small>
                                <small class="text-muted" v-else-if="!robots">
                                    <i class="fas fa-circle-notch fa-spin me-1"></i> Loading...
                                </small>
                                <small class="text-muted" v-else-if="robots && robots[task.name]">
                                    <template v-if="robots[task.name].description">
                                        {{ robots[task.name].description }}
                                    </template>
                                    <i v-else>No description</i>
                                </small>
                                <small class="text-muted" v-else-if="loading">
                                    <i class="fas fa-circle-notch fa-spin me-1"></i> Loading...
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
                                    Last change: <br />
                                    {{dateFormat(task.lastModificationDate)}}
                                </small>
                            </td>
                            <td>
                                <small v-if="task.parentTaskId" class="text-muted">
                                    <abbr title="Parent task">Task.</abbr>: #{{task.parentTaskId}}<br />
                                </small>
                                <small v-if="task.datacenterId" class="text-muted">
                                    <abbr title="Datacenter">Datac.</abbr>: #{{task.datacenterId}}<br />
                                </small>
                                <small v-if="task.userId" class="text-muted">
                                    User: #{{task.userId}}<br />
                                </small>
                                <small v-if="task.orderId" class="text-muted">
                                    Order: #{{task.orderId}}<br />
                                </small>
                                <small v-if="task.vlanId" class="text-muted">
                                    Vlan: #{{task.vlanId}}<br />
                                </small>
                                <small v-if="task.filerId" class="text-muted">
                                    Filer: #{{task.filerId}}<br />
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3 text-center">
            <div class="card-body p-3">
                <div class="card-title">
                    <span class="h5">Users</span>
                    <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{users && Object.keys(users).length}}</small>
                    <div class="position-absolute top-0 end-0 m-3">
                        <button class="btn btn-sm badge btn-info" @click="loadAll()">
                            <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                        </button>
                    </div>
                </div>
                <table class="table table-sm table-striped table-bordered mb-0">
                    <thead>
                        <tr>
                            <th scope="col">State</th>
                            <th scope="col">Name</th>
                            <th scope="col">Contact</th>
                            <th scope="col">Global<br />accesses</th>
                            <th scope="col" v-for="(datacenter, datacenterId) in datacenters" :key="datacenterId">
                                {{ datacenter.description || datacenter.name }}
                                <span class="text-muted">{{ datacenter.description ? datacenter.name : '#'+datacenterId }}</span>
                                <br />
                                accesses
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!users">
                            <td colspan="4">
                                <i class="fas fa-circle-notch fa-spin me-1"></i> Loading users from OVHcloud API...
                            </td>
                        </tr>
                        <tr v-else-if="!Object.keys(users).length">
                            <td colspan="4">
                                <i>No user found</i>
                            </td>
                        </tr>
                        <tr v-for="user in _.orderBy(_.values(users), ['name'])" :key="user.userId">
                            <td>
                                <span :class="getUserStateClass(user)">
                                    <i class="fas fa-circle"></i>
                                    {{user.state}}
                                </span>
                                <br />
                                <small class="text-muted">#{{user.userId}}</small>
                            </td>
                            <td>
                                <span v-if="user.login != user.name && user.login.includes('@')" title="This is an Active Directory federation user">
                                    <i class="far fa-address-book text-info"></i>
                                </span>
                                {{user.login}}
                                <br />
                                <small class="text-muted">{{user.firstName}} {{user.lastName}}</small>
                            </td>
                            <td>
                                <small v-if="user.email">
                                    {{user.email}}
                                    (<abbr title="Technical email alerts">Alerts</abbr>:
                                    <i class="fas" :class="user.receiveAlerts ? 'fa-check text-success' : 'fa-times text-danger'"></i>)
                                </small>
                                <br />
                                <small v-if="user.phoneNumber">
                                    {{user.phoneNumber}}
                                    (<abbr title="Security token validation">Token</abbr>:
                                    <i class="fas" :class="user.isTokenValidator ? 'fa-check text-success' : 'fa-times text-danger'"></i>)
                                </small>
                            </td>
                            <td>
                                <small>
                                    <abbr title="NSX access">NSX</abbr>:
                                    <i class="fas" :class="user.nsxRight ? 'fa-check text-success' : 'fa-times text-danger'"></i>
                                    -
                                    <span v-if="user.objectRights" :title="`Objects rights: ${Object.keys(user.objectRights).length}`">
                                        <abbr :title="`Objects rights: ${Object.keys(user.objectRights).length}`">Obj.</abbr>:
                                        <span v-if="Object.keys(user.objectRights).length">
                                            {{ Object.keys(user.objectRights).length }}
                                        </span>
                                        <i class="fas fa-times text-danger" v-else></i>
                                    </span>
                                    <span v-else-if="loading">
                                        <abbr title="Objects rights">Obj.</abbr>:
                                        <i class="fas fa-circle-notch fa-spin me-1"></i> Loading user object rights<br />from OVHcloud API...
                                    </span>
                                </small>
                                <br />
                                <small>
                                    <abbr title="Manage IPs (OVHcloud plugin)">IPs</abbr>:
                                    <i class="fas" :class="user.canManageNetwork ? 'fa-check text-success' : 'fa-times text-danger'"></i>
                                    -
                                    <abbr title="Manage IP failovers (OVHcloud plugin)">IPFO</abbr>:
                                    <i class="fas" :class="user.canManageIpFailOvers ? 'fa-check text-success' : 'fa-times text-danger'"></i>
                                    <!--
                                    -
                                    <abbr title="Manage rights (OVHcloud plugin)">Rights</abbr>:
                                    <i class="fas" :class="user.canManageRights ? 'fa-check text-success' : 'fa-times text-danger'"></i>
                                    -->
                                </small>
                            </td>
                            <td v-for="(datacenter, datacenterId) in datacenters" :key="datacenterId">
                                <span v-if="user.rights && user.rights[datacenterId]">
                                    <abbr title="Datacenter">DC.</abbr>:
                                    <span :class="getUserAccessStateClass(user.rights[datacenterId].right)">{{ user.rights[datacenterId].right }}</span>
                                    -
                                    <small>
                                        <abbr title="Add ressources (OVHcloud plugin)">Add res.</abbr>:
                                        <i class="fas" :class="user.rights[datacenterId].canAddRessource ? 'fa-check text-success' : 'fa-times text-danger'"></i>
                                    </small>
                                    <br />
                                    <small>
                                        <abbr title="VM Network access">VM Net.</abbr>:
                                        <span :class="getUserAccessStateClass(user.rights[datacenterId].vmNetworkRole)">{{ user.rights[datacenterId].vmNetworkRole }}</span>
                                        -
                                        <abbr title="v(x)Lans access">vLans</abbr>:
                                        <span :class="getUserAccessStateClass(user.rights[datacenterId].networkRole)">{{ user.rights[datacenterId].networkRole }}</span>
                                    </small>
                                </span>
                                <small v-else-if="loading">
                                    <i class="fas fa-circle-notch fa-spin me-1"></i> Loading user datacenter rights<br />from OVHcloud API...
                                </small>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-12 col-lg-6">
                <div class="card mt-3 text-center">
                    <div class="card-body p-3">
                        <div class="card-title">
                            <span class="h5" title="IPs allowed to access the PCC">
                                Allowed networks
                            </span>
                            <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{allowedNetworks && Object.keys(allowedNetworks).length}}</small>
                            <div class="position-absolute top-0 end-0 m-3">
                                <button class="btn btn-sm badge btn-info" @click="loadAll()">
                                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                                </button>
                            </div>
                        </div>
                        <table class="table table-sm table-striped table-bordered mb-0">
                            <tbody>
                                <tr v-if="!allowedNetworks">
                                    <td colspan="2">
                                        <i class="fas fa-circle-notch fa-spin me-1"></i> Loading allowed networks from OVHcloud API...
                                    </td>
                                </tr>
                                <tr v-else-if="!Object.keys(allowedNetworks).length">
                                    <td colspan="2">
                                        <i>No allowed network found</i>
                                    </td>
                                </tr>
                                <tr v-for="allowedNetwork in _.orderBy(_.values(allowedNetworks), ['network'])" :key="allowedNetwork.networkAccessId" :title="`${allowedNetwork.network}: ${allowedNetwork.state} #${allowedNetwork.networkAccessId}`">
                                    <td>
                                        <i class="fas me-1" :class="allowedNetwork.state == 'allowed' ? 'fa-check text-success': 'fa-clock text-warning'"></i>
                                        {{allowedNetwork.network}}
                                    </td>
                                    <td>
                                        <small class="text-muted">{{allowedNetwork.description}}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card mt-3 text-center">
                    <div class="card-body p-3">
                        <div class="card-title">
                            <span class="h5" title="IPs allowed to bypass the two factor authentication (if enabled)">
                                Two factor authentication trusted IPs
                            </span>
                            <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{twoFAWhitelists && Object.keys(twoFAWhitelists).length}}</small>
                            <div class="position-absolute top-0 end-0 m-3">
                                <button class="btn btn-sm badge btn-info" @click="loadAll()">
                                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                                </button>
                            </div>
                        </div>
                        <table class="table table-sm table-striped table-bordered mb-0">
                            <tbody>
                                <tr v-if="!twoFAWhitelists">
                                    <td colspan="2">
                                        <i class="fas fa-circle-notch fa-spin me-1"></i> Loading two factor authentication trusted IPs from OVHcloud API...
                                    </td>
                                </tr>
                                <tr v-else-if="!Object.keys(twoFAWhitelists).length">
                                    <td colspan="2">
                                        <i>No two factor authentication trusted IP found</i>
                                    </td>
                                </tr>
                                <tr v-for="twoFAWhitelist in _.orderBy(_.values(twoFAWhitelists), ['network'])" :key="twoFAWhitelist.id" :title="`${twoFAWhitelist.cidrNetmask}: ${twoFAWhitelist.state} #${twoFAWhitelist.id}`">
                                    <td>
                                        <i class="fas me-1" :class="twoFAWhitelist.state == 'enabled' ? 'fa-check text-success': 'fa-clock text-warning'"></i>
                                        {{twoFAWhitelist.cidrNetmask}}
                                    </td>
                                    <td>
                                        <small class="text-muted">{{twoFAWhitelist.description}}</small>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <pcc-options-card
                    title="Security options"
                    :loading="loading"
                    :options="securityOptions"
                    :load-all="loadAll"
                >
                </pcc-options-card>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import {httpRequester} from "./compositions/axios/httpRequester";
import {VueSvgGauge} from 'vue-svg-gauge';
import moment from "moment";


export default {
    name: 'PccPage',

    components: {
        LoadingScreen,
        ErrorsZone,
        VueSvgGauge,
    },

    props: {
        pccName: {
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
        const {
            loaded,
            loading,
            errors,
            request,
            get,
        } = httpRequester();

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
            pccVersion: null,
            options: null,
            complianceOptions: null,
            securityOptions: null,
            datacenters: null,
            users: null,
            ips: null,
            allowedNetworks: null,
            twoFAWhitelists: null,
            tasks: null,
            taskIds: {},
            robots: null,
            timer: null,
            autoRefreshTasks: false,
            recentTasks: false,
        };
    },

    mounted() {
        this.loadAll();
        this.timer = setInterval(this.refreshTasks, 30*1000);
    },

    beforeDestroy () {
        this.cancelAutoUpdate();
    },

    methods: {
        flagFromCountry(country) {
            if(country == "UK") {
                return "GB";
            }
            return country;
        },

        toggleAutoRefreshTasks() {
            this.autoRefreshTasks = !this.autoRefreshTasks;
            this.loadTasks();
        },

        toggleRecentTasks() {
            this.recentTasks = !this.recentTasks;
            this.loadTasks();
        },

        refreshTasks() {
            if(this.autoRefreshTasks) {
                this.loadTasks();
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
                this.loadUsers();
                this.loadIps();
                this.loadPccSecurityOptionsMatrix();
                this.loadPccOption('pcidss');
                this.loadPccOption('hds');
                this.loadPccOption('hipaa');
                this.loadPccOption('nsx');
                this.loadPccOption('vrops');
                this.loadPccOption('hcx');
                this.loadPccOption('federation', 'activeDirectory');
                this.loadPccOption('vmEncryption', 'kms');
                this.loadAllowedNetworks();
                this.loadTwoFAWhitelists();
            }
        },

        async loadPcc() {
            this.pcc = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}`);
            if(this.pcc) {
                this.loadPccUpgrades();
            }
        },

        async loadPccUpgrades() {
            const value = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/vcenterVersion`);
            let upgrades = [];
            if(value.currentVersion.build != value.lastMajor.build) {
                upgrades.push(value.lastMajor.major + value.lastMajor.minor);
            }
            if(value.currentVersion.build != value.lastMinor.build) {
                upgrades.push(value.lastMinor.major + value.lastMinor.minor);
            }
            this.$set(this.pcc, 'upgrades', upgrades);
        },

        async loadPccSecurityOptionsMatrix() {
            let compatibilityMatrix = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/securityOptions/compatibilityMatrix`);
            if(compatibilityMatrix) {
                for(let option of compatibilityMatrix) {
                    this.fillOption(option.name, option);
                }
            }
        },

        fillOption(optionName, optionFromApi) {
            let value = {};
            if(this.options && this.options[optionName]) {
                value = this.options[optionName];
            }
            if(this.complianceOptions && this.complianceOptions[optionName]) {
                value = this.complianceOptions[optionName];
            }
            if(this.securityOptions && this.securityOptions[optionName]) {
                value = this.securityOptions[optionName];
            }

            for(let key in optionFromApi) {
                let val = optionFromApi[key];
                value[key] = val;
            }
            if(value['state'] === 'delivered') {
                value['state'] = 'enabled';
            }
            if(!('compatible' in value)) {
                value['compatible'] = true;
            }

            value['name'] = optionName;
            value['description'] = value['description'] || optionName;
            value['optionType'] = 'option';
            if(optionName == 'accessNetworkFiltered') {
                value['optionType'] = 'security';
            } else if(optionName == 'advancedSecurity') {
                value['optionType'] = 'security';
            } else if(optionName == 'base') {
                value['optionType'] = 'security';
            } else if(optionName == 'federation') {
                value['name'] = 'Federation';
                value['description'] = 'Active Directory user federation';
                value['optionType'] = 'option';
            } else if(optionName == 'grsecKernel') {
                value['optionType'] = 'security';
            } else if(optionName == 'hcx') {
                value['name'] = 'HCX';
                value['description'] = 'VMware Hybrid Cloud Extension';
                value['optionType'] = 'option';
            } else if(optionName == 'hds') {
                value['name'] = 'HDS';
                value['description'] = 'French Healthcare Data Hosting';
                value['optionType'] = 'compliance';
            } else if(optionName == 'hids') {
                value['optionType'] = 'security';
            } else if(optionName == 'hipaa') {
                value['name'] = 'HIPAA';
                value['description'] = 'American Health Insurance Portability and Accountability Act';
                value['optionType'] = 'compliance';
            } else if(optionName == 'nids') {
                value['optionType'] = 'security';
            } else if(optionName == 'nsx') {
                value['name'] = 'NSX';
                value['description'] = 'VMware Network Virtualization';
                value['optionType'] = 'option';
            } else if(optionName == 'pcidss') {
                value['name'] = 'PCI DSS';
                value['description'] = 'Payment Card Industry Data Security Standard';
                value['optionType'] = 'compliance';
            } else if(optionName == 'privateCustomerVlan') {
                value['optionType'] = 'security';
            } else if(optionName == 'privateGw') {
                value['optionType'] = 'security';
            } else if(optionName == 'sendLogToCustomer') {
                value['optionType'] = 'security';
            } else if(optionName == 'sessionTimeout') {
                value['optionType'] = 'security';
            } else if(optionName == 'sftp') {
                value['optionType'] = 'security';
            } else if(optionName == 'snc') {
                value['name'] = 'SecNumCloud';
                value['description'] = 'French SecNumCloud ANSSI Security Visa';
                value['optionType'] = 'compliance';
            } else if(optionName == 'spla') {
                value['name'] = 'SPLA';
                value['description'] = 'Windows licensing management';
                value['optionType'] = 'option';
            } else if(optionName == 'sslV3') {
                value['optionType'] = 'security';
            } else if(optionName == 'tls1.2') {
                value['optionType'] = 'security';
            } else if(optionName == 'tokenValidation') {
                value['optionType'] = 'security';
            } else if(optionName == 'twoFa') {
                value['optionType'] = 'security';
            } else if(optionName == 'twoFaFail2ban') {
                value['optionType'] = 'security';
            } else if(optionName == 'vmEncryption') {
                value['name'] = 'VM encryption';
                value['description'] = 'Virtual machine encryption';
                value['optionType'] = 'option';
            } else if(optionName == 'vrliForwarder') {
                value['optionType'] = 'security';
            } else if(optionName == 'vrops') {
                value['name'] = 'vROps';
                value['description'] = 'VMware vRealize Operations';
                value['optionType'] = 'option';
            } else if(optionName == 'waf') {
                value['optionType'] = 'security';
            }

            if(value['optionType'] == 'compliance') {
                if(!this.complianceOptions) {
                    this.complianceOptions = {};
                }
                if(value['state'] != 'disabled' || 1) {
                    this.$set(this.complianceOptions, optionName, {...value});
                }
            } else if(value['optionType'] == 'security') {
                if(!this.securityOptions) {
                    this.securityOptions = {};
                }
                this.$set(this.securityOptions, optionName, {...value});
            } else {
                if(!this.options) {
                    this.options = {};
                }
                this.$set(this.options, optionName, {...value});
            }
        },

        async loadPccOption(optionName, suboptionName) {
            let value = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/${optionName}`);
            if(suboptionName) {
                value['suboptions'] = {};
                const suboptionIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/${optionName}/${suboptionName}`);
                if(!suboptionIds.length) {
                    this.suboptions = {};
                }
                let suboptionIdsChunks = this.chunkArray(suboptionIds, 40);
                for(let suboptionIdsChunk of suboptionIdsChunks) {
                    const suboptions = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/${optionName}/${suboptionName}/${suboptionIdsChunk.join(',')}?batch=,`);
                    if(this.suboptions === null) {
                        this.suboptions = {};
                    }
                    for (const i in suboptions) {
                        const suboption = suboptions[i];
                        if(!suboption['error']) {
                            let suboptionValue = suboption['value'];
                            suboptionValue['type'] = suboptionName;
                            suboptionValue['id'] = suboptionValue[suboptionName+'Id'];
                            for(const k in suboptionValue) {
                                if(k.includes('Port')) {
                                    suboptionValue['port'] = suboptionValue[k];
                                }
                            }
                            value['suboptions'][suboption['key']] = suboptionValue;
                        }
                    }
                }
            }
            this.fillOption(optionName, value);
        },

        async loadIps() {
            const ipNets = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/ip`);
            if(!ipNets.length) {
                this.ips = {};
            }
            for(let ipNet of ipNets) {
                const ipNetEncoded = encodeURIComponent(encodeURIComponent(ipNet)); // Need to double encode slashes because of laravel routing bug with %2F
                const ip = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/ip/${ipNetEncoded}`); // No batch mode on this call
                if(!this.ips) {
                    this.ips = {};
                }
                this.$set(this.ips, ipNet, {...ip});
            }
        },

        async loadAllowedNetworks() {
            const allowedNetworkIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/allowedNetwork`);
            if(!allowedNetworkIds.length) {
                this.allowedNetworks = {};
            }
            let allowedNetworkIdsChunks = this.chunkArray(allowedNetworkIds, 40);
            for(let allowedNetworkIdsChunk of allowedNetworkIdsChunks) {
                const allowedNetworks = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/allowedNetwork/${allowedNetworkIdsChunk.join(',')}?batch=,`);
                if(this.allowedNetworks === null) {
                    this.allowedNetworks = {};
                }
                for (const i in allowedNetworks) {
                    const allowedNetwork = allowedNetworks[i];
                    if(!allowedNetwork['error']) {
                        this.$set(this.allowedNetworks, allowedNetwork['key'], {...allowedNetwork['value']});
                    }
                }
            }
        },

        async loadTwoFAWhitelists() {
            const twoFAWhitelistIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/twoFAWhitelist`);
            if(!twoFAWhitelistIds.length) {
                this.twoFAWhitelists = {};
            }
            let twoFAWhitelistIdsChunks = this.chunkArray(twoFAWhitelistIds, 40);
            for(let twoFAWhitelistIdsChunk of twoFAWhitelistIdsChunks) {
                const twoFAWhitelists = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/twoFAWhitelist/${twoFAWhitelistIdsChunk.join(',')}?batch=,`);
                if(this.twoFAWhitelists === null) {
                    this.twoFAWhitelists = {};
                }
                for (const i in twoFAWhitelists) {
                    const twoFAWhitelist = twoFAWhitelists[i];
                    if(!twoFAWhitelist['error']) {
                        this.$set(this.twoFAWhitelists, twoFAWhitelist['key'], {...twoFAWhitelist['value']});
                    }
                }
            }
        },

        async loadUsers() {
            const userIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user`);
            if(!userIds.length) {
                this.users = {};
            }
            let userIdsChunks = this.chunkArray(userIds, 40);
            for(let userIdsChunk of userIdsChunks) {
                const users = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${userIdsChunk.join(',')}?batch=,`);
                if(this.users === null) {
                    this.users = {};
                }
                for (const i in users) {
                    const user = users[i];
                    if(!user['error']) {
                        let value = user['value'];
                        if(this.users[value.userId]) {
                            value.rights = this.users[value.userId].rights;
                            value.objectRights = this.users[value.userId].objectRights;
                        }
                        this.$set(this.users, user['key'], {...value});
                        this.loadUser(user['key']);
                    }
                }
            }
        },

        async loadUser(userId) {
            this.loadUserRights(userId);
            this.loadUserObjectRights(userId);
        },

        async loadUserRights(userId) {
            const userRightIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${userId}/right`);
            let userRightIdsChunks = this.chunkArray(userRightIds, 40);
            let rights = {};
            for(let userRightIdsChunk of userRightIdsChunks) {
                const userRights = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${userId}/right/${userRightIdsChunk.join(',')}?batch=,`);
                for (const i in userRights) {
                    const userRight = userRights[i]['value'];
                    rights[userRight.datacenterId] = userRight;
                }
            }
            this.$set(this.users[userId], 'rights', {...rights});
        },

        async loadUserObjectRights(userId) {
            const userObjectRightIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${userId}/objectRight`);
            let userObjectRightIdsChunks = this.chunkArray(userObjectRightIds, 40);
            let objectRights = {};
            for(let userObjectRightIdsChunk of userObjectRightIdsChunks) {
                const userRights = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/user/${userId}/objectRight/${userObjectRightIdsChunk.join(',')}?batch=,`);
                for (const i in userRights) {
                    const userRight = userRights[i]['value'];
                    objectRights[userRight.datacenterId] = userRight;
                }
            }
            this.$set(this.users[userId], 'objectRights', {...objectRights});
        },

        async loadDatacenters() {
            const datacenterIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter`);
            if(!datacenterIds.length) {
                this.datacenters = {};
            }
            let datacenterIdsChunks = this.chunkArray(datacenterIds, 40);
            for(let datacenterIdsChunk of datacenterIdsChunks) {
                const datacenters = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${datacenterIdsChunk.join(',')}?batch=,`);
                if(this.datacenters === null) {
                    this.datacenters = {};
                }
                for (const i in datacenters) {
                    const datacenter = datacenters[i];
                    if(!datacenter['error']) {
                        this.$set(this.datacenters, datacenter['key'], {...datacenter['value']});
                    }
                }
            }
        },

        async loadTasks() {
            let taskIds = {};
            let recentTaskIds = {};
            const stateTaskIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/task`);
            for(const taskId of stateTaskIds) {
                if(!taskIds.hasOwnProperty(taskId)) {
                    taskIds[taskId] = taskId;
                }
            }
            for(const taskId of stateTaskIds.slice(-10)) {
                if(!recentTaskIds.hasOwnProperty(taskId)) {
                    recentTaskIds[taskId] = taskId;
                }
            }
            for (const state of ['done', 'canceled']) {
                const stateTaskIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/task?state=${state}`);
                for(const taskId of stateTaskIds) {
                    if(taskIds.hasOwnProperty(taskId)) {
                        if(this.recentTasks && recentTaskIds.hasOwnProperty(taskId)) {
                            continue;
                        }
                        delete taskIds[taskId];
                    }
                }
            }
            for(const taskId in taskIds) {
                if(this.taskIds.hasOwnProperty(taskId)) {
                    continue;
                }
                this.$set(this.taskIds, taskId, taskId);
            }

            if(!Object.values(this.taskIds).length) {
                this.tasks = {};
            }
            for (const taskId in this.tasks) {
                const task = this.tasks[taskId];
                if(this.recentTasks && recentTaskIds.hasOwnProperty(taskId)) {
                    continue;
                }
                if(task.state == 'done' || task.state == 'canceled') {
                    this.$delete(this.tasks, taskId);
                    this.$delete(this.taskIds, taskId);
                }
            }
            let robotsNames = {};
            let taskIdsChunks = this.chunkArray(Object.values(this.taskIds), 40);
            for(let taskIdsChunk of taskIdsChunks) {
                const tasks = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/task/${taskIdsChunk.join(',')}?batch=,`);
                if(this.tasks === null) {
                    this.tasks = {};
                }
                for (const i in tasks) {
                    const task = tasks[i];
                    if(!task['error']) {
                        let value = task['value'];
                        robotsNames[value.name] = value.name;
                        this.$set(this.tasks, task['key'], {...value});
                    }
                }
            }
            this.loadRobots(_.values(robotsNames));
        },

        async loadRobots(robotsNames) {
            const availableRobotsNames = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/robot`);
            let robotNamesChunks = this.chunkArray(Object.values(robotsNames), 40);
            for(let robotNamesChunk of robotNamesChunks) {
                for(let robotName of robotNamesChunk) {
                    if(!availableRobotsNames.includes(robotName)) {
                        delete robotNamesChunk[robotName];
                    }
                }
                const robots = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/robot/${robotNamesChunk.join(',')}?batch=,`);
                if(!this.robots) {
                    this.robots = {};
                }
                for (const i in robots) {
                    const robot = robots[i];
                    if(!robot['error']) {
                        let value = robot['value'];
                        this.$set(this.robots, robot['key'], {...value});
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
                    resultClass = 'text-danger';
                } else {
                    resultClass = 'text-danger';
                }
            }
            return resultClass;
        },

        getUserStateClass(user) {
            var resultClass = 'text-warning';
            if(user.state) {
                if(user.state == 'creating') {
                    resultClass = 'text-warning';
                } else if(user.state == 'deleting') {
                    resultClass = 'text-warning';
                } else if(user.state == 'delivered') {
                    resultClass = 'text-success';
                } else if(user.state == 'error') {
                    resultClass = 'text-danger';
                }
            }
            return resultClass;
        },

        getUserAccessStateClass(access) {
            var resultClass = 'text-warning';
            if(access) {
                if(access == 'admin') {
                    resultClass = 'text-success';
                } else if(access == 'readwrite') {
                    resultClass = 'text-success';
                } else if(access == 'manager') {
                    resultClass = 'text-success';
                } else if(access == 'readonly') {
                    resultClass = 'text-warning';
                } else if(access == 'noAccess') {
                    resultClass = 'text-danger';
                } else if(access == 'disabled') {
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
