<template>
    <div class="pcc-datacenter-page mx-4">
        <transition name="loading-screen">
            <LoadingScreen v-if="loading"/>
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
                            <a :href="`${pccRoute}/${pccName}`">
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
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading datacenter from OVHcloud API...
                        </div>
                        <template v-else>
                            <div class="mb-1">
                                <span>Datacenter</span>
                                <span class="h4">
                                    {{ datacenter.description || datacenter.name }}
                                </span>
                                <span class="text-muted">{{ datacenter.description ? datacenter.name : '#'+datacenterId }}</span>
                            </div>
                            <div class="mb-1">
                                <span class="h5">{{ datacenter.commercialName }}</span>
                                <span class="text-muted">({{ datacenter.commercialRangeName }})</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="card my-2">
            <div class="card-body p-3">
                <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                    <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                </button>
                <div class="row text-center">
                    <div class="col-6">
                        <div v-if="!Object.keys(backup).length" class="my-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading backup from OVHcloud API...
                        </div>
                        <template v-else>
                            Veeam Backup Managed
                            <br />
                            <span :class="getOptionStateClass(backup)">
                                <i class="fas fa-circle"></i>
                                {{backup.state}}
                            </span>
                            <span class="text-muted" v-if="backup.backupOffer">
                                -
                                Offer: {{backup.backupOffer}}
                                <span v-if="backup.replicationZone">
                                    -
                                    <i class="fas fa-map-marked-alt"></i>
                                    <abbr title="Replication datacenter">Replication</abbr> : {{backup.replicationZone}}
                                </span>
                                <br />
                                Backup hour: {{backup.scheduleHour}}
                                -
                                Encryption: <i class="fas" :class="backup.encryption ? 'fa-check text-success' : 'fa-times text-danger'"></i>
                            </span>
                        </template>
                    </div>
                    <div class="col-6">
                        <div v-if="!Object.keys(disasterRecovery).length" class="my-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading disaster recovery from OVHcloud API...
                        </div>
                        <template v-else>
                            Zerto Disaster Recovery Plan
                            <br />
                            <span :class="getOptionStateClass(disasterRecovery)">
                                <i class="fas fa-circle"></i>
                                {{disasterRecovery.state}}
                            </span>
                            <span class="text-muted" v-if="disasterRecovery.drpType">
                                {{disasterRecovery.systemVersion}}
                                -
                                Type: {{disasterRecovery.drpType}}
                                -
                                Role: {{disasterRecovery.localSiteInformation ? disasterRecovery.localSiteInformation.role : 'unknown'}}
                                <br />
                                <template v-if="disasterRecovery.remoteSiteInformation">
                                    <span v-if="disasterRecovery.drpType == 'ovh'">
                                        Remote PCC: {{disasterRecovery.remoteSiteInformation.serviceName}}
                                        -
                                        <abbr title="Remote datacenter">Remote DC</abbr> {{disasterRecovery.remoteSiteInformation.datacenterName}} (#{{disasterRecovery.remoteSiteInformation.datacenterId}})
                                    </span>
                                    <span v-else>
                                        Remote Public IP: {{disasterRecovery.remoteSiteInformation.remoteEndpointPublicIp}}
                                        -
                                        Remote Internal IP: {{disasterRecovery.remoteSiteInformation.remoteEndpointInternalIp}}
                                    </span>
                                </template>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="row text-center">
            <div class="col-12 col-lg-6">
                <div class="card my-2">
                    <div class="card-body p-3">
                        <div class="card-title">
                            <span class="h5">Datastores</span>
                            <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{filers && Object.keys(filers).length}}</small>
                            <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                                <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                            </button>
                        </div>
                        <div v-if="!filers" class="my-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading datastores from OVHcloud API...
                        </div>
                        <template v-else>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mini-gauge mb-4">
                                        <vue-svg-gauge
                                            :start-angle="-110"
                                            :end-angle="110"
                                            :value="gaugesValues('datastores-usage') | round(0)"
                                            :separator-step="0"
                                            :min="0"
                                            :max="100"
                                            :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                            :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                            :gauge-color="[{offset:0,color:'#0b8c5a'},{offset:50,color:'#f4c009'},{offset:100,color:'#de3a21'}]"
                                            :scale-interval="0">
                                            <div class="inner-text">
                                                <span>
                                                    <div>Space</div><div>usage :</div>
                                                    <div class="h4">{{ gaugesValues('datastores-usage') | round(0) }} %</div>
                                                </span>
                                            </div>
                                        </vue-svg-gauge>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mini-gauge mb-4">
                                        <vue-svg-gauge
                                            :start-angle="-110"
                                            :end-angle="110"
                                            :value="gaugesValues('datastores-provisioned') | round(0)"
                                            :separator-step="0"
                                            :min="0"
                                            :max="100"
                                            :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                            :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                            :gauge-color="[{offset:0,color:'#0b8c5a'},{offset:50,color:'#f4c009'},{offset:100,color:'#de3a21'}]"
                                            :scale-interval="0">
                                            <div class="inner-text">
                                                <span>
                                                    <div>Space</div><div>provisioned :</div>
                                                    <div class="h4">{{ gaugesValues('datastores-provisioned') | round(0) }} %</div>
                                                </span>
                                            </div>
                                        </vue-svg-gauge>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-sm table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" colspan="2">Name</th>
                                        <th scope="col">Profile</th>
                                        <th scope="col">VM</th>
                                        <th scope="col">Space Usage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(filer, filerId) in filers" :key="filerId">
                                        <td :title="`State: ${filer.state} - Profile: ${filer.profile}`">
                                            <i class="fas fa-circle" :class="getDatastoreStateClass(filer)"></i><br />
                                            <small class="text-muted">#{{filerId}}</small>
                                        </td>
                                        <td :title="`State: ${filer.state} - Profile: ${filer.profile} - Node: ${filer.master.split(/\./)[0]} ${filer.activeNode}`">
                                            <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}/filer/${filerId}/graphs`">
                                                <i class="far fa-chart-bar"></i>
                                            </a>
                                            {{filer.name || 'pcc-00'+filerId }}
                                            <i v-if="filer.global" class="fas fa-globe text-info" title="Global"></i><br />
                                            <small class="text-muted">
                                                <i class="fas" :title="filer.activeNode" :class="filer.activeNode == 'master' ? 'fa-check text-success' : 'fa-exclamation text-warning'"></i>
                                                {{filer.master.split(/\./)[0]}}
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{filer.profile.replace('pcc-datastore-', '')}}</small><br />
                                            <small class="text-muted">
                                                <i class="fas fa-coins text-warning"></i>
                                                {{filer.billingType}}
                                            </small>
                                        </td>
                                        <td>{{filer.vmTotal}}</td>
                                        <td>
                                            <span v-if="filer.size.unit" title="Space used">
                                                <div class="micro-gauge">
                                                    <vue-svg-gauge
                                                        :start-angle="-270"
                                                        :end-angle="90"
                                                        :inner-radius="0"
                                                        :value="filer.spaceUsed | round(0)"
                                                        :separator-step="0"
                                                        :min="0"
                                                        :max="(filer.spaceUsed + filer.spaceFree) | round(0)"
                                                        :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                                        :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                                        gauge-color="#f4c009"
                                                        :scale-interval="0">
                                                    </vue-svg-gauge>
                                                </div>
                                                {{filer.spaceUsed | round(0)}} <small>of</small> {{(filer.spaceUsed + filer.spaceFree) | round(0)}} <small>{{filer.size.unit}}</small>
                                            </span>
                                            <br />
                                            <span v-if="filer.size.unit" title="Space provisioned">
                                                <div class="micro-gauge">
                                                    <vue-svg-gauge
                                                        :start-angle="-270"
                                                        :end-angle="90"
                                                        :inner-radius="0"
                                                        :value="filer.spaceProvisionned | round(0)"
                                                        :separator-step="0"
                                                        :min="0"
                                                        :max="(filer.spaceUsed + filer.spaceFree) | round(0)"
                                                        :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                                        :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                                        gauge-color="#f4c009"
                                                        :scale-interval="0">
                                                    </vue-svg-gauge>
                                                </div>
                                                {{filer.spaceProvisionned | round(0)}} <small>{{filer.size.unit}} <small>provisioned</small></small>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card my-2">
                    <div class="card-body p-3">
                        <div class="card-title">
                            <span class="h5">Hosts</span>
                            <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{hosts && Object.keys(hosts).length}}</small>
                            <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                                <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                            </button>
                        </div>
                        <div v-if="!hosts" class="my-2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading hosts from OVHcloud API...
                        </div>
                        <template v-else>
                            <div class="row">
                                <div class="col-6">
                                    <div class="mini-gauge mb-4">
                                        <vue-svg-gauge
                                            :start-angle="-110"
                                            :end-angle="110"
                                            :value="gaugesValues('hosts-cpu') | round(0)"
                                            :separator-step="0"
                                            :min="0"
                                            :max="100"
                                            :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                            :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                            :gauge-color="[{offset:0,color:'#0b8c5a'},{offset:50,color:'#f4c009'},{offset:100,color:'#de3a21'}]"
                                            :scale-interval="0">
                                            <div class="inner-text">
                                                <span>
                                                    <div>CPU</div><div>usage :</div>
                                                    <div class="h4">{{ gaugesValues('hosts-cpu') | round(0) }} %</div>
                                                </span>
                                            </div>
                                        </vue-svg-gauge>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="mini-gauge mb-4">
                                        <vue-svg-gauge
                                            :start-angle="-110"
                                            :end-angle="110"
                                            :value="gaugesValues('hosts-ram') | round(0)"
                                            :separator-step="0"
                                            :min="0"
                                            :max="100"
                                            :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                            :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                            :gauge-color="[{offset:0,color:'#0b8c5a'},{offset:50,color:'#f4c009'},{offset:100,color:'#de3a21'}]"
                                            :scale-interval="0">
                                            <div class="inner-text">
                                                <span>
                                                    <div>RAM</div><div>usage :</div>
                                                    <div class="h4">{{ gaugesValues('hosts-ram') | round(0) }} %</div>
                                                </span>
                                            </div>
                                        </vue-svg-gauge>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-sm table-striped table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" colspan="2">Name</th>
                                        <th scope="col">Profile</th>
                                        <th scope="col">Cores</th>
                                        <th scope="col">VM</th>
                                        <th scope="col">Usage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(host, hostId) in hosts" :key="hostId">
                                        <td :title="`Rack: ${host.rack} - State: ${host.state} - Connexion state: ${host.connectionState} - In maintenance: ${(host.inMaintenance)?'yes':'no'}`">
                                            <i class="fas fa-circle" :class="getHostStateClass(host)"></i><br />
                                            <small class="text-muted">#{{hostId}}</small>
                                        </td>
                                        <td :title="`Rack: ${host.rack} - State: ${host.state} - Connexion state: ${host.connectionState} - In maintenance: ${(host.inMaintenance)?'yes':'no'}`">
                                            <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}/host/${hostId}/graphs`">
                                                <i class="far fa-chart-bar"></i>
                                            </a>
                                            {{host.name || 'host'+hostId }}<br />
                                            <small class="text-muted">Rack: {{host.rack}}</small>
                                        </td>
                                        <td>
                                            <small>{{host.profile}}</small><br />
                                            <small class="text-muted">
                                                <i class="fas fa-coins text-warning"></i>
                                                {{host.billingType}}
                                            </small>
                                        </td>
                                        <td>
                                            <small>{{host.cpuNum}} cores</small><br />
                                            <small class="text-muted">
                                                {{host.vmVcpuTotal}} vCPU
                                            </small>
                                        </td>
                                        <td>{{host.vmTotal}}</td>
                                        <td>
                                            <template v-if="host.cpuMax">
                                                <div class="micro-gauge">
                                                    <vue-svg-gauge
                                                        :start-angle="-270"
                                                        :end-angle="90"
                                                        :inner-radius="0"
                                                        :value="host.cpuUsed/1000"
                                                        :separator-step="0"
                                                        :min="0"
                                                        :max="host.cpuMax/1000"
                                                        :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                                        :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                                        gauge-color="#f4c009"
                                                        :scale-interval="0">
                                                    </vue-svg-gauge>
                                                </div>
                                                <small>CPU: {{host.cpuUsed/1000 | round(1)}} <small>of</small> {{host.cpuMax/1000 | round(0)}} <small>{{host.cpu.unit}}</small></small>
                                            </template>
                                            <br />
                                            <template v-if="host.memoryUsed">
                                                <div class="micro-gauge">
                                                    <vue-svg-gauge
                                                        :start-angle="-270"
                                                        :end-angle="90"
                                                        :inner-radius="0"
                                                        :value="host.memoryUsed/1024"
                                                        :separator-step="0"
                                                        :min="0"
                                                        :max="host.ram.value"
                                                        :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                                        :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                                        gauge-color="#f4c009"
                                                        :scale-interval="0">
                                                    </vue-svg-gauge>
                                                </div>
                                                <small>RAM: {{host.memoryUsed/1024 | round(0)}} <small>of</small> {{host.ram.value | round(0)}} <small>{{host.ram.unit}}</small></small>
                                            </template>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="card my-2 text-center">
            <div class="card-body p-3">
                <div class="card-title">
                    <span class="h5">Virtual Machines</span>
                    <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{vms && Object.keys(vms).length}}</small>
                    <button class="btn btn-sm badge btn-info position-absolute top-0 end-0 m-3" @click="loadAll()">
                        <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                    </button>
                </div>
                <div v-if="!vms" class="my-2">
                    <i class="fas fa-circle-notch fa-spin me-1"></i> Loading VMs from OVHcloud API...
                </div>
                <template v-else>
                    <table class="table table-sm table-striped table-bordered mb-0">
                        <thead>
                            <tr>
                                <th scope="col" colspan="2">Name</th>
                                <th scope="col">
                                    Host &<br />
                                    storage
                                </th>
                                <th scope="col"><abbr title="Snapshots">Snap</abbr></th>
                                <th scope="col"><abbr title="VMWare Tools">Tools</abbr></th>
                                <th scope="col"><abbr title="Veeam Backup option">Backup</abbr></th>
                                <th scope="col"><abbr title="Fault tolerance">FT</abbr></th>
                                <th scope="col">
                                    RAM<br />
                                    vCPU
                                </th>
                                <th scope="col">
                                    CPU
                                </th>
                                <th scope="col">
                                    <abbr title="Network TX/RX">Net TX/RX</abbr><br />
                                    <abbr title="Disk IOs R/W">Disk IO R/W</abbr>
                                </th>
                                <th scope="col">
                                    Disk R/W<br />
                                    <abbr title="Disk latency R/W">Latency R/W</abbr>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="vm in _.orderBy(_.values(vms), ['name'])" :key="vm.vmId">
                                <td :title="`State: ${vm.powerState} - MoRef: ${vm.moRef}`">
                                    <i class="fas fa-circle" :class="getVirtualMachineStateClass(vm)"></i><br />
                                    <small class="text-muted">#{{vm.vmId}}</small>
                                </td>
                                <td :title="`State: ${vm.powerState} - MoRef: ${vm.moRef}`">
                                    <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}/vm/${vm.vmId}/graphs`">
                                        <i class="far fa-chart-bar"></i>
                                    </a>
                                    {{vm.name}}
                                    <small class="badge bg-secondary" v-if="isOvhVm(vm)" title="This virtual machine is managed by OVHcloud">
                                        <i class="fas fa-user-cog"></i>
                                        OVHcloud
                                    </small>
                                </td>
                                <td colspan="3" v-if="vm.powerState == 'deleted'" class="text-center text-secondary">
                                    <i>Virtual machine removed</i>
                                </td>
                                <td v-if="vm.powerState != 'deleted'">
                                    <small title="Host" class="text-muted">{{vm.hostName}}</small><br />
                                    <template v-if="vm.filers">
                                        <div v-for="filer in vm.filers" :key="filer.id" class="inline" :title="`Filer ${(filer.name.substring(0, 13) == 'storageLocal_')?'local':filer.name}: ${round(filer.committed/1024, 1)} of ${round(filer.capacity/1024, 0)} GB`">
                                            <template v-if="filer.capacity">
                                                <div class="micro-gauge">
                                                    <vue-svg-gauge
                                                        :start-angle="-270"
                                                        :end-angle="90"
                                                        :inner-radius="0"
                                                        :value="(filer.committed/1024)"
                                                        :separator-step="0"
                                                        :min="0"
                                                        :max="(filer.capacity/1024)"
                                                        :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                                        :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                                        gauge-color="#f4c009"
                                                        :scale-interval="0">
                                                    </vue-svg-gauge>
                                                </div>
                                                <small>{{(filer.name.substring(0, 13) == 'storageLocal_')?'local':filer.name}} <small class="text-muted" v-if="filer.disks.length && filer.disks.length > 1">({{filer.disks.length}} disks)</small></small>
                                            </template>
                                        </div>
                                    </template>
                                </td>
                                <td v-if="vm.powerState != 'deleted'">
                                    <span class="badge" :class="(vm.snapshotNum)?'bg-danger':'bg-success'">{{vm.snapshotNum}}</span>
                                </td>
                                <td :title="`VMWare Tools: ${vm.vmwareTools} - ${vm.vmwareToolsVersion}`" v-if="vm.powerState != 'deleted'">
                                    <span class="badge" :class="getVirtualMachineVmwareToolsData('class', vm)">
                                        <i class="fa" :class="getVirtualMachineVmwareToolsData('icon', vm)"></i>
                                    </span>
                                </td>
                                <td :title="`Backup: ${vm.backup && vm.backup.state ? vm.backup.state : 'removed'} - Restore points: ${ vm.backup && vm.backup.restorePoints ? Object.keys(vm.backup.restorePoints).length : 0}`">
                                    <span class="badge" :class="getVirtualMachineBackupClass(vm)">
                                        <i class="fa" :class="getVirtualMachineBackupIcon(vm)"></i> 
                                        <span v-if="vm.backup && vm.backup.state != 'removed'">
                                            <span v-if="vm.backup.restorePoints == null">
                                                ...
                                            </span> 
                                            <span v-if="vm.backup.restorePoints != null">
                                                {{Object.keys(vm.backup.restorePoints).length}}
                                            </span>
                                        </span> 
                                    </span>
                                </td>
                                <td colspan="6" v-if="vm.powerState == 'deleted'" class="text-center text-secondary">
                                    <i>Virtual machine removed</i>
                                </td>
                                <td v-if="vm.powerState != 'deleted'" :title="`Fault Tolerance: ${vm.stateFt}`">
                                    <i class="fas fa-circle" :class="getVirtualMachineFtClass(vm)"></i>
                                </td>
                                <td :title="`RAM usage: ${round(vm.memoryUsed/1024, 2)} of ${round(vm.memoryMax/1024, 0)} GB - ${vm.cpuNum} vCPU`" v-if="vm.powerState != 'deleted'">
                                    <div class="micro-gauge">
                                        <vue-svg-gauge
                                            :start-angle="-270"
                                            :end-angle="90"
                                            :inner-radius="0"
                                            :value="(vm.memoryUsed/1024)"
                                            :separator-step="0"
                                            :min="0"
                                            :max="(vm.memoryMax/1024)"
                                            :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                            :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                            gauge-color="#f4c009"
                                            :scale-interval="0">
                                        </vue-svg-gauge>
                                    </div>
                                    {{vm.memoryMax/1024 | round(0)}} <small>GB</small>
                                    <br />
                                    {{vm.cpuNum}} <small>vCPU</small>
                                </td>
                                <td :title="`CPU usage: ${round(vm.cpuUsed/1000, 2)} of ${round(vm.cpuMax/1000, 1)} GHz - CPU Ready: ${vm.cpuReady} ms (${vm.cpuReadyPercent}} %)`" v-if="vm.powerState != 'deleted'">
                                    <div class="micro-gauge">
                                        <vue-svg-gauge
                                            :start-angle="-270"
                                            :end-angle="90"
                                            :inner-radius="0"
                                            :value="(vm.cpuUsed/1000)"
                                            :separator-step="0"
                                            :min="0"
                                            :max="(vm.cpuMax/1000)"
                                            :base-color="$currentDarkmode ? '#555555' : '#dddddd'"
                                            :blur-color="$currentDarkmode ? '#111111' : '#c7c6c6'"
                                            gauge-color="#f4c009"
                                            :scale-interval="0">
                                        </vue-svg-gauge>
                                    </div>
                                    {{vm.cpuUsed/1000 | round(1)}} <small>GHz</small>
                                    <br />
                                    <small>
                                        Ready: 
                                        <span :class="(vm.cpuReadyPercent<3)?'text-primary':'text-warning'">{{vm.cpuReady | round(0)}} <small>ms</small></span>
                                    </small>
                                </td>
                                <td v-if="vm.powerState != 'deleted'" :title="`Network TX/RX: ${round(vm.netTx/100, 1)} MBps / ${round(vm.netRx/100, 1)} MBps - Disk IOs R/W: ${round(vm.readPerSecond, 0)} IOps / ${round(vm.writePerSecond, 0)} IOps`">
                                    <small><i class="fas fa-caret-up"></i> {{vm.netTx/100 | round(1)}} / {{vm.netRx/100 | round(1)}} <i class="fas fa-caret-down"></i> <small>MBps</small></small><br />
                                    <small><i class="fas fa-caret-up"></i> {{vm.readPerSecond | round(0)}} / {{vm.writePerSecond | round(0)}} <i class="fas fa-caret-down"></i> <small>IOps</small></small>
                                </td>
                                <td v-if="vm.powerState != 'deleted'" :title="`Disk R/W: ${round(vm.readRate/100, 1)} MBps / ${round(vm.writeRate/100, 1)} MBps - Disk latency R/W: ${round(vm.readLatency, 0)} ms / ${round(vm.writeLatency, 0)} ms`">
                                    <small><i class="fas fa-caret-up"></i> {{vm.readRate/100 | round(1)}} / {{vm.writeRate/100 | round(1)}} <i class="fas fa-caret-down"></i> <small>MBps</small></small><br />
                                    <small><i class="fas fa-caret-up"></i> {{vm.readLatency | round(0)}} / {{vm.writeLatency | round(0)}} <i class="fas fa-caret-down"></i> <small>ms</small></small>
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
import ErrorsZone from "./ErrorsZone";
import {httpRequester} from "./compositions/axios/httpRequester";
import {VueSvgGauge} from 'vue-svg-gauge'

export default {
    name: 'PccDatacenterPage',

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
        datacenterId: {
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
            datacenter: {},
            backup: {},
            disasterRecovery: {},
            hosts: null,
            filers: null,
            vms: null,
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
                this.loadBackup();
                this.loadDisasterRecovery();
                this.loadHosts();
                this.loadFilers();
                this.loadVms();
            }
        },

        async loadPcc() {
            this.pcc = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}`);
        },

        async loadDatacenter() {
            this.datacenter = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}`);
        },

        async loadBackup() {
            this.backup = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/backup`);
        },

        async loadDisasterRecovery() {
            this.disasterRecovery = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/disasterRecovery/zerto/status`);
        },

        async loadHosts() {
            const hostIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/host`);
            if(!hostIds.length) {
                this.hosts = {};
            }
            let hostIdsChunks = this.chunkArray(hostIds, 40);
            for(let hostIdsChunk of hostIdsChunks) {
                const hosts = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/host/${hostIdsChunk.join(',')}?batch=,`);
                if(this.hosts === null) {
                    this.hosts = {};
                }
                for (const hostId in hosts) {
                    const host = hosts[hostId];
                    if(!host['error']) {
                        this.$set(this.hosts, host['key'], {...host['value']});
                    }
                }
            }
        },

        async loadFilers() {
            const filerIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/filer`);
            if(!filerIds.length) {
                this.filers = {};
            }
            let filerIdsChunks = this.chunkArray(filerIds, 40);
            this.loadGlobalFilers();
            for(let filerIdsChunk of filerIdsChunks) {
                const filers = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/filer/${filerIdsChunk.join(',')}?batch=,`);
                if(this.filers === null) {
                    this.filers = {};
                }
                for (const filerId in filers) {
                    const filer = filers[filerId];
                    if(!filer['error']) {
                        filer['value']['global'] = false;
                        this.$set(this.filers, filer['key'], {...filer['value']});
                    }
                }
            }
        },

        async loadGlobalFilers() {
            const filerIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/filer`);
            let filerIdsChunks = this.chunkArray(filerIds, 40);
            for(let filerIdsChunk of filerIdsChunks) {
                const filers = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/filer/${filerIdsChunk.join(',')}?batch=,`);
                if(this.filers === null) {
                    this.filers = {};
                }
                for (const filerId in filers) {
                    const filer = filers[filerId];
                    if(!filer['error']) {
                        filer['value']['global'] = true;
                        this.$set(this.filers, filer['key'], {...filer['value']});
                    }
                }
            }
        },

        async loadVms() {
            const vmIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/vm`);
            if(!vmIds.length) {
                this.vms = {};
            }
            let vmIdsChunks = this.chunkArray(vmIds, 40);
            for(let vmIdsChunk of vmIdsChunks) {
                const vms = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/vm/${vmIdsChunk.join(',')}?batch=,`);
                if(this.vms === null) {
                    this.vms = {};
                }
                for (const vmId in vms) {
                    const vm = vms[vmId];
                    if(!vm['error']) {
                        let value = vm['value'];
                        for (const i in value.filers) {
                            const filer = value.filers[i];
                            let capacity = 0;
                            for (const j in filer.disks) {
                                const disk = filer.disks[j];
                                if(disk.capacity) {
                                    capacity += disk.capacity;
                                }
                            }
                            value['filers'][i]['capacity'] = capacity;
                        }
                        this.$set(this.vms, vm['key'], {...value});
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

        gaugesValues(type) {
            var result = {
                'hosts-cpu': {
                    'max': 0,
                    'used': 0,
                    'value': 0
                },
                'hosts-ram': {
                    'max': 0,
                    'used': 0,
                    'value': 0
                },
                    'datastores-usage': {
                        'max': 0,
                        'used': 0,
                        'value': 0
                    },
                    'datastores-provisioned': {
                        'max': 0,
                        'used': 0,
                        'value': 0
                    }
            };

            for (const hostId in this.hosts) {
                const host = this.hosts[hostId];
                if(host.billingType != 'freeSpare') {
                    result['hosts-cpu']['max'] = result['hosts-cpu']['max'] + (host.cpuMax/1000);
                    result['hosts-cpu']['used'] = result['hosts-cpu']['used'] + (host.cpuUsed/1000);
                    result['hosts-cpu']['value'] = result['hosts-cpu']['used'] * (100 / result['hosts-cpu']['max']);

                    result['hosts-ram']['max'] = result['hosts-ram']['max'] + host.ram.value;
                    result['hosts-ram']['used'] = result['hosts-ram']['used'] + (host.memoryUsed/1024);
                    result['hosts-ram']['value'] = result['hosts-ram']['used'] * (100 / result['hosts-ram']['max']);
                }
            }
            for (const filerId in this.filers) {
                const datastore = this.filers[filerId];
                if(datastore.billingType != 'freeSpare') {
                    result['datastores-usage']['max'] = result['datastores-usage']['max'] + datastore.spaceUsed + datastore.spaceFree;
                    result['datastores-usage']['used'] = result['datastores-usage']['used'] + datastore.spaceUsed;
                    result['datastores-usage']['value'] = result['datastores-usage']['used'] * (100 / result['datastores-usage']['max']);

                    result['datastores-provisioned']['max'] = result['datastores-provisioned']['max'] + datastore.spaceUsed + datastore.spaceFree;
                    result['datastores-provisioned']['used'] = result['datastores-provisioned']['used'] + datastore.spaceProvisionned;
                    result['datastores-provisioned']['value'] = result['datastores-provisioned']['used'] * (100 / result['datastores-provisioned']['max']);
                }
            }

            if(type == 'hosts') {
                type = 'hosts-cpu';
                if(result['hosts-cpu']['value'] < result['hosts-ram']['value']) {
                    type = 'hosts-ram';
                }
            }
            return result[type]['value'];
        },

        getDatastoreStateClass(datastore) {
            var resultClass = 'text-warning';
            if(datastore.state) {
                if(datastore.state == 'delivered') {
                    resultClass = 'text-success';
                } else if(datastore.state == 'adding') {
                    resultClass = 'text-warning';
                } else if(datastore.state == 'removing') {
                    resultClass = 'text-warning';
                } else if(datastore.state == 'error') {
                    resultClass = 'text-danger';
                } else {
                    resultClass = 'text-danger';
                }
            }
            return resultClass;
        },

        getHostStateClass(host) {
            var resultClass = 'text-warning';
            if(host.connectionState) {
                if(host.connectionState == 'connected') {
                    if(host.inMaintenance) {
                        resultClass = 'text-warning';
                    } else {
                        resultClass = 'text-success';
                    }
                } else if(host.connectionState == 'disconnected') {
                    resultClass = 'text-danger';
                } else if(host.connectionState == 'notResponding') {
                    resultClass = 'text-danger';
                } else {
                    resultClass = 'text-danger';
                }
            } else {
                if(host.state) {
                    if(host.state == 'delivered') {
                        resultClass = 'text-success';
                    } else if(host.state == 'adding') {
                        resultClass = 'text-warning';
                    } else if(host.state == 'removing') {
                        resultClass = 'text-warning';
                    } else if(host.state == 'error') {
                        resultClass = 'text-danger';
                    } else {
                        resultClass = 'text-danger';
                    }
                }
            }
            return resultClass;
        },

        getVirtualMachineFtClass(virtualmachine) {
            var resultClass = 'text-warning';
            if(virtualmachine.stateFt) {
                if(virtualmachine.stateFt == 'running') {
                    resultClass = 'text-success';
                } else if(virtualmachine.stateFt == 'enabled') {
                    resultClass = 'text-primary';
                } else if(virtualmachine.stateFt == 'starting') {
                    resultClass = 'text-info';
                } else if(virtualmachine.stateFt == 'needSecondary') {
                    resultClass = 'text-warning';
                } else if(virtualmachine.stateFt == 'disabled') {
                    resultClass = 'text-secondary';
                } else if(virtualmachine.stateFt == 'notConfigured') {
                    resultClass = 'text-secondary';
                }
            }
            return resultClass;
        },

        getVirtualMachineStateClass(virtualmachine) {
            var resultClass = 'text-warning';
            if(virtualmachine.powerState) {
                if(virtualmachine.powerState == 'poweredOn') {
                    resultClass = 'text-success';
                } else if(virtualmachine.powerState == 'suspended') {
                    resultClass = 'text-warning';
                } else if(virtualmachine.powerState == 'deleted') {
                    resultClass = 'text-secondary';
                } else {
                    resultClass = 'text-danger';
                }
            }
            return resultClass;
        },

        getVirtualMachineVmwareToolsData(type, virtualmachine) {
            var result = {
                'class':'',
                'icon':'fa-question-circle'
            };
            if(virtualmachine.vmwareTools) {
                if(virtualmachine.vmwareTools == 'guestToolsRunning') {
                    if(virtualmachine.vmwareToolsVersion == 'guestToolsCurrent') {
                        result['class'] = 'bg-success';
                        result['icon'] = 'fa-check';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsSupportedNew') {
                        result['class'] = 'bg-success';
                        result['icon'] = 'fa-check';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsUnmanaged') {
                        result['class'] = 'bg-info';
                        result['icon'] = 'fa-check';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsNeedUpgrade') {
                        result['class'] = 'bg-warning';
                        result['icon'] = 'fa-check';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsSupportedOld') {
                        result['class'] = 'bg-warning';
                        result['icon'] = 'fa-check';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsTooNew') {
                        result['class'] = 'bg-warning';
                        result['icon'] = 'fa-exclamation-triangle';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsTooOld') {
                        result['class'] = 'bg-warning';
                        result['icon'] = 'fa-exclamation-triangle';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsNotInstalled') {
                        result['class'] = 'bg-danger';
                        result['icon'] = 'fa-times';
                    } else if(virtualmachine.vmwareToolsVersion == 'guestToolsBlacklisted') {
                        result['class'] = 'bg-danger';
                        result['icon'] = 'fa-times';
                    }
                } else if(virtualmachine.vmwareTools == 'guestToolsExecutingScripts') {
                    result['class'] = 'bg-warning';
                    result['icon'] = 'fa-check';
                } else if(virtualmachine.vmwareTools == 'guestToolsNotRunning') {
                    result['class'] = 'bg-danger';
                    result['icon'] = 'fa-times';
                }
            }
            if(this.isOvhVm(virtualmachine)) {
                result['class'] = 'bg-secondary';
            }
            return result[type];
        },

        isOvhVm(virtualmachine) {
            if(virtualmachine.name.match(/^backupServer[0-9]+$/)) {
                return true;
            }
            return false;
        },

        getVirtualMachineBackupClass(virtualmachine) {
            var resultClass = '';
            if(virtualmachine.backup) {
                if(virtualmachine.backup.state) {
                    if(virtualmachine.backup.state == 'delivered') {
                        resultClass = 'bg-success';
                    } else if(virtualmachine.backup.state == 'removed') {
                        resultClass = 'bg-warning';
                    } else if(virtualmachine.backup.state == 'disabled') {
                        resultClass = 'bg-danger';
                    } else {
                        resultClass = 'bg-danger';
                    }
                }
            } else {
                resultClass = 'bg-danger';
            }
            if(this.isOvhVm(virtualmachine)) {
                resultClass = 'bg-secondary';
            }
            return resultClass;
        },

        getVirtualMachineBackupIcon(virtualmachine) {
            var resultClass = 'fa-question-circle';
            if(virtualmachine.backup) {
                if(virtualmachine.backup.state) {
                    if(virtualmachine.backup.state == 'delivered') {
                        resultClass = 'fa-check';
                    } else if(virtualmachine.backup.state == 'removed') {
                        resultClass = 'fa-exclamation-triangle';
                    } else if(virtualmachine.backup.state == 'disabled') {
                        resultClass = 'fa-times';
                    } else {
                        resultClass = 'fa-times';
                    }
                }
            } else {
                resultClass = 'fa-times';
            }
            return resultClass;
        },

        getOptionStateClass(option) {
            var resultClass = 'text-warning';
            if(option.state) {
                if(option.state == 'delivered') {
                    resultClass = 'text-success';
                } else if(option.state == 'enabled') {
                    resultClass = 'text-success';
                } else if(option.state == 'error') {
                    resultClass = 'text-danger';
                } else if(option.state == 'disabled') {
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
