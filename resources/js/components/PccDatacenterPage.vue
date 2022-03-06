<template>
    <div class="pcc-datacenter-page mx-4">
        <pcc-head :breadcrumb="breadcrumb" :pcc-name="pccName" :pcc-route="pccRoute" :pcc="pcc" :vrack="vrack" :loading="loading" :errors="errors" :load-all="loadAll">
            <template v-slot:third-column>
                <div v-if="!Object.keys(datacenter).length" class="py-6">
                    <div class="mb-2">
                        <span>Datacenter</span>
                        <span class="h4"> datacenter{{ datacenterId }} </span>
                        <span class="text-gray-500">#{{ datacenterId }}</span>
                    </div>
                    <div class="py-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading datacenter from OVHcloud API...</div>
                </div>
                <div v-else class="py-6">
                    <div class="mb-2">
                        <span>Datacenter</span>
                        <span class="h4">
                            {{ datacenter.description || datacenter.name }}
                        </span>
                        <span class="text-gray-500">{{ datacenter.description ? datacenter.name : "#" + datacenterId }}</span>
                    </div>
                    <div class="mb-2">
                        <span class="h5">{{ datacenter.commercialName }}</span>
                        <span class="text-gray-500">({{ datacenter.commercialRangeName }})</span>
                    </div>
                    <div>
                        <i class="fas fa-network-wired"></i> vRack :
                        <template v-if="!vrack.datacenters || !vrack.datacenters[datacenter.name]">
                            <template v-if="loading">
                                <i class="fas fa-circle-notch fa-spin"></i>
                            </template>
                            <template v-else>
                                <i class="text-gray-500">no</i>
                            </template>
                        </template>
                        <template v-else-if="vrack.datacenters[datacenter.name].name">
                            {{ vrack.datacenters[datacenter.name].name }} <span class="text-gray-500">({{ vrack.datacenters[datacenter.name].serviceName }})</span>
                        </template>
                        <template v-else>
                            {{ vrack.datacenters[datacenter.name].serviceName }}
                        </template>
                    </div>
                </div>
            </template>
        </pcc-head>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 mt-6">
            <div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <div class="p-4">
                        <div v-if="!Object.keys(backup).length" class="my-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading backup from OVHcloud API...</div>
                        <template v-else>
                            <div class="mb-3">Veeam Backup Managed</div>
                            <span :class="getOptionStateClass(backup)">
                                <i class="fas fa-circle"></i>
                                {{ backup.state }}
                            </span>
                            <span class="text-gray-400" v-if="backup.backupOffer">
                                - Offer: {{ backup.backupOffer }}
                                <span v-if="backup.replicationZone">
                                    -
                                    <i class="fas fa-map-marked-alt"></i>
                                    <abbr title="Replication datacenter">Replication</abbr> : {{ backup.replicationZone }}
                                </span>
                                <br />
                                Backup hour: {{ backup.scheduleHour }} - Encryption: <i class="fas" :class="backup.encryption ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <div class="p-4">
                        <div v-if="!Object.keys(disasterRecovery).length" class="my-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading disaster recovery from OVHcloud API...</div>
                        <template v-else>
                            <div class="mb-3">Zerto Disaster Recovery Plan</div>
                            <span :class="getOptionStateClass(disasterRecovery)">
                                <i class="fas fa-circle"></i>
                                {{ disasterRecovery.state }}
                            </span>
                            <span class="text-gray-400" v-if="disasterRecovery.drpType">
                                {{ disasterRecovery.systemVersion }} {{ disasterRecovery.localSiteInformation ? disasterRecovery.localSiteInformation.zertoVersion : "" }} - Type: {{ disasterRecovery.drpType }} - Role:
                                {{ disasterRecovery.localSiteInformation ? disasterRecovery.localSiteInformation.role : "unknown" }}
                                <br />
                                <template v-if="disasterRecovery.remoteSiteInformation">
                                    <span v-if="disasterRecovery.drpType == 'ovh'">
                                        Remote: {{ disasterRecovery.remoteSiteInformation.serviceName }}
                                        -
                                        {{ disasterRecovery.remoteSiteInformation.datacenterName }}
                                        <a :href="`${pccRoute}/${disasterRecovery.remoteSiteInformation.serviceName}/datacenter/${disasterRecovery.remoteSiteInformation.datacenterId}`" class="text-indigo-600 hover:text-indigo-800">
                                            <i class="far fa-arrow-alt-circle-right"></i>
                                        </a>
                                    </span>
                                    <span v-else> Remote public IP: {{ disasterRecovery.remoteSiteInformation.remoteEndpointPublicIp }} </span>
                                </template>
                            </span>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 mt-6">
            <div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (filers && Object.keys(filers).length) || 0 }}</small>
                    <div class="p-4">
                        <div class="mb-3">Datastores</div>
                        <div v-if="!filers" class="my-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading datastores from OVHcloud API...</div>
                        <template v-else>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 my-6">
                                <div class="mini-gauge mb-4">
                                    <vue-svg-gauge
                                        :start-angle="-110"
                                        :end-angle="110"
                                        :value="round(gaugesValues('datastores-usage'), 0)"
                                        :separator-step="0"
                                        :min="0"
                                        :max="100"
                                        :base-color="$root.$data.currentDarkmode ? '#1f2937' : '#e5e7eb'"
                                        :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                        :gauge-color="[
                                            { offset: 0, color: '#0b8c5a' },
                                            { offset: 50, color: '#f4c009' },
                                            { offset: 100, color: '#de3a21' },
                                        ]"
                                        :scale-interval="0"
                                    >
                                        <div class="inner-text">
                                            <span>
                                                <div>Space</div>
                                                <div>usage :</div>
                                                <div class="h4">{{ round(gaugesValues("datastores-usage"), 0) }} %</div>
                                            </span>
                                        </div>
                                    </vue-svg-gauge>
                                </div>
                                <div class="mini-gauge mb-4">
                                    <vue-svg-gauge
                                        :start-angle="-110"
                                        :end-angle="110"
                                        :value="round(gaugesValues('datastores-provisioned'), 0)"
                                        :separator-step="0"
                                        :min="0"
                                        :max="100"
                                        :base-color="$root.$data.currentDarkmode ? '#1f2937' : '#e5e7eb'"
                                        :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                        :gauge-color="[
                                            { offset: 0, color: '#0b8c5a' },
                                            { offset: 50, color: '#f4c009' },
                                            { offset: 100, color: '#de3a21' },
                                        ]"
                                        :scale-interval="0"
                                    >
                                        <div class="inner-text">
                                            <span>
                                                <div>Space</div>
                                                <div>provisioned :</div>
                                                <div class="h4">{{ round(gaugesValues("datastores-provisioned"), 0) }} %</div>
                                            </span>
                                        </div>
                                    </vue-svg-gauge>
                                </div>
                            </div>
                            <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                                        <tr>
                                            <th scope="col" colspan="2" class="px-1 py-2">Name</th>
                                            <th scope="col" class="px-1 py-2">Profile</th>
                                            <th scope="col" class="px-1 py-2">VM</th>
                                            <th scope="col" class="px-1 py-2">Space Usage</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm bg-white dark:bg-gray-700">
                                        <tr v-for="(filer, filerId, filerIdx) in filers" :key="filerId" :class="filerIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                            <td class="p-1" :title="`State: ${filer.state} - Profile: ${filer.profile}`">
                                                <i class="fas fa-circle" :class="getDatastoreStateClass(filer)"></i><br />
                                                <small class="text-gray-500">#{{ filerId }}</small>
                                            </td>
                                            <td class="p-1" :title="`State: ${filer.state} - Profile: ${filer.profile} - Node: ${filer.master.split(/\./)[0]} ${filer.activeNode}`">
                                                <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}/filer/${filerId}/graphs`" class="text-indigo-600 hover:text-indigo-800">
                                                    <i class="far fa-chart-bar"></i>
                                                </a>
                                                {{ filer.name || "pcc-00" + filerId }}
                                                <i v-if="filer.global" class="fas fa-globe text-cyan-500" title="Global"></i><br />
                                                <small class="text-gray-500">
                                                    <i class="fas" :title="filer.activeNode" :class="filer.activeNode == 'master' ? 'fa-check text-green-700' : 'fa-exclamation text-yellow-600'"></i>
                                                    {{ filer.master.split(/\./)[0] }}
                                                </small>
                                            </td>
                                            <td class="p-1">
                                                <small>{{ filer.profile.replace("pcc-datastore-", "") }}</small>
                                                <br />
                                                <small class="text-gray-500">
                                                    <i class="fas fa-coins text-yellow-600"></i>
                                                    {{ filer.billingType }}
                                                </small>
                                            </td>
                                            <td class="p-1">{{ filer.vmTotal }}</td>
                                            <td class="p-1">
                                                <span v-if="filer.size.unit" title="Space used">
                                                    <div class="micro-gauge align-middle">
                                                        <vue-svg-gauge
                                                            :start-angle="-270"
                                                            :end-angle="90"
                                                            :inner-radius="0"
                                                            :value="round(filer.spaceUsed, 0)"
                                                            :separator-step="0"
                                                            :min="0"
                                                            :max="round(filer.spaceUsed + filer.spaceFree, 0)"
                                                            :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                                            :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                                            gauge-color="#f4c009"
                                                            :scale-interval="0"
                                                        >
                                                        </vue-svg-gauge>
                                                    </div>
                                                    {{ round(filer.spaceUsed, 0) }} <small>of</small> {{ round(filer.spaceUsed + filer.spaceFree, 0) }} <small>{{ filer.size.unit }}</small>
                                                </span>
                                                <br />
                                                <span v-if="filer.size.unit" title="Space provisioned">
                                                    <div class="micro-gauge align-middle">
                                                        <vue-svg-gauge
                                                            :start-angle="-270"
                                                            :end-angle="90"
                                                            :inner-radius="0"
                                                            :value="round(filer.spaceProvisionned, 0)"
                                                            :separator-step="0"
                                                            :min="0"
                                                            :max="round(filer.spaceUsed + filer.spaceFree, 0)"
                                                            :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                                            :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                                            gauge-color="#f4c009"
                                                            :scale-interval="0"
                                                        >
                                                        </vue-svg-gauge>
                                                    </div>
                                                    {{ round(filer.spaceProvisionned, 0) }} <small>{{ filer.size.unit }} <small>provisioned</small></small>
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (hosts && Object.keys(hosts).length) || 0 }}</small>
                    <div class="p-4">
                        <div class="mb-3">Hosts</div>
                        <div v-if="!hosts" class="my-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading hosts from OVHcloud API...</div>
                        <template v-else>
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 my-6">
                                <div class="mini-gauge mb-4">
                                    <vue-svg-gauge
                                        :start-angle="-110"
                                        :end-angle="110"
                                        :value="round(gaugesValues('hosts-cpu'), 0)"
                                        :separator-step="0"
                                        :min="0"
                                        :max="100"
                                        :base-color="$root.$data.currentDarkmode ? '#1f2937' : '#e5e7eb'"
                                        :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                        :gauge-color="[
                                            { offset: 0, color: '#0b8c5a' },
                                            { offset: 50, color: '#f4c009' },
                                            { offset: 100, color: '#de3a21' },
                                        ]"
                                        :scale-interval="0"
                                    >
                                        <div class="inner-text">
                                            <span>
                                                <div>CPU</div>
                                                <div>usage :</div>
                                                <div class="h4">{{ round(gaugesValues("hosts-cpu"), 0) }} %</div>
                                            </span>
                                        </div>
                                    </vue-svg-gauge>
                                </div>
                                <div class="mini-gauge mb-4">
                                    <vue-svg-gauge
                                        :start-angle="-110"
                                        :end-angle="110"
                                        :value="round(gaugesValues('hosts-ram'), 0)"
                                        :separator-step="0"
                                        :min="0"
                                        :max="100"
                                        :base-color="$root.$data.currentDarkmode ? '#1f2937' : '#e5e7eb'"
                                        :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                        :gauge-color="[
                                            { offset: 0, color: '#0b8c5a' },
                                            { offset: 50, color: '#f4c009' },
                                            { offset: 100, color: '#de3a21' },
                                        ]"
                                        :scale-interval="0"
                                    >
                                        <div class="inner-text">
                                            <span>
                                                <div>RAM</div>
                                                <div>usage :</div>
                                                <div class="h4">{{ round(gaugesValues("hosts-ram"), 0) }} %</div>
                                            </span>
                                        </div>
                                    </vue-svg-gauge>
                                </div>
                            </div>

                            <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                                        <tr>
                                            <th scope="col" colspan="2" class="px-1 py-2">Name</th>
                                            <th scope="col" class="px-1 py-2">Profile</th>
                                            <th scope="col" class="px-1 py-2">Cores</th>
                                            <th scope="col" class="px-1 py-2">VM</th>
                                            <th scope="col" class="px-1 py-2">Usage</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm bg-white dark:bg-gray-700">
                                        <tr v-for="(host, hostId, hostIdx) in hosts" :key="hostId" :class="hostIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                            <td class="p-1" :title="`Rack: ${host.rack} - State: ${host.state} - Connexion state: ${host.connectionState} - In maintenance: ${host.inMaintenance ? 'yes' : 'no'}`">
                                                <i class="fas fa-circle" :class="getHostStateClass(host)"></i><br />
                                                <small class="text-gray-500">#{{ hostId }}</small>
                                            </td>
                                            <td class="p-1" :title="`Rack: ${host.rack} - State: ${host.state} - Connexion state: ${host.connectionState} - In maintenance: ${host.inMaintenance ? 'yes' : 'no'}`">
                                                <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}/host/${hostId}/graphs`" class="text-indigo-600 hover:text-indigo-800">
                                                    <i class="far fa-chart-bar"></i>
                                                </a>
                                                {{ host.name || "host" + hostId }}<br />
                                                <small class="text-gray-500">Rack: {{ host.rack }}</small>
                                            </td>
                                            <td class="p-1">
                                                <small>{{ host.profile }}</small>
                                                <br />
                                                <small class="text-gray-500">
                                                    <i class="fas fa-coins text-yellow-600"></i>
                                                    {{ host.billingType }}
                                                </small>
                                            </td>
                                            <td class="p-1">
                                                <small>{{ host.cpuNum }} cores</small><br />
                                                <small class="text-gray-500"> {{ host.vmVcpuTotal }} vCPU </small>
                                            </td>
                                            <td class="p-1">{{ host.vmTotal }}</td>
                                            <td class="p-1">
                                                <template v-if="host.cpuMax">
                                                    <div class="micro-gauge align-middle">
                                                        <vue-svg-gauge
                                                            :start-angle="-270"
                                                            :end-angle="90"
                                                            :inner-radius="0"
                                                            :value="host.cpuUsed / 1000"
                                                            :separator-step="0"
                                                            :min="0"
                                                            :max="host.cpuMax / 1000"
                                                            :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                                            :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                                            gauge-color="#f4c009"
                                                            :scale-interval="0"
                                                        >
                                                        </vue-svg-gauge>
                                                    </div>
                                                    <small>
                                                        CPU: {{ round(host.cpuUsed / 1000, 1) }} <small>of</small> {{ round(host.cpuMax / 1000,  0) }} <small>{{ host.cpu.unit }}</small>
                                                    </small>
                                                </template>
                                                <br />
                                                <template v-if="host.memoryUsed">
                                                    <div class="micro-gauge align-middle">
                                                        <vue-svg-gauge
                                                            :start-angle="-270"
                                                            :end-angle="90"
                                                            :inner-radius="0"
                                                            :value="host.memoryUsed / 1024"
                                                            :separator-step="0"
                                                            :min="0"
                                                            :max="host.ram.value"
                                                            :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                                            :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                                            gauge-color="#f4c009"
                                                            :scale-interval="0"
                                                        >
                                                        </vue-svg-gauge>
                                                    </div>
                                                    <small>
                                                        RAM: {{ round(host.memoryUsed / 1024, 0) }} <small>of</small> {{ round(host.ram.value, 0) }} <small>{{ host.ram.unit }}</small>
                                                    </small>
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
            <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
            <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (vms && Object.keys(vms).length) || 0 }}</small>
            <div class="p-4">
                <div class="mb-3">Virtual Machines</div>
                <div v-if="!vms" class="my-2"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading VMs from OVHcloud API...</div>
                <div v-else class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                            <tr>
                                <th scope="col" colspan="2" class="px-1 py-2">Name</th>
                                <th scope="col" class="px-1 py-2">
                                    Host &<br />
                                    storage
                                </th>
                                <th scope="col" class="px-1 py-2"><abbr title="Snapshots">Snap</abbr></th>
                                <th scope="col" class="px-1 py-2"><abbr title="VMWare Tools">Tools</abbr></th>
                                <th scope="col" class="px-1 py-2"><abbr title="Veeam Backup option">Backup</abbr></th>
                                <th scope="col" class="px-1 py-2"><abbr title="Fault tolerance">FT</abbr></th>
                                <th scope="col" class="px-1 py-2">
                                    RAM<br />
                                    vCPU
                                </th>
                                <th scope="col" class="px-1 py-2">CPU</th>
                                <th scope="col" class="px-1 py-2">
                                    <abbr title="Network TX/RX">Net TX/RX</abbr><br />
                                    <abbr title="Disk IOs R/W">Disk IO R/W</abbr>
                                </th>
                                <th scope="col" class="px-1 py-2">
                                    Disk R/W<br />
                                    <abbr title="Disk latency R/W">Latency R/W</abbr>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm bg-white dark:bg-gray-700">
                            <tr v-for="(vm, vmIdx) in window._.orderBy(window._.values(vms), ['isOvhVm', (vm) => vm.name.toLowerCase()], ['asc', 'asc'])" :key="vm.vmId" :class="vmIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                <td class="p-1" :title="`State: ${vm.powerState} - MoRef: ${vm.moRef}`">
                                    <i class="fas fa-circle" :class="getVirtualMachineStateClass(vm)"></i><br />
                                    <small class="text-gray-500">#{{ vm.vmId }}</small>
                                </td>
                                <td class="p-1" :title="`State: ${vm.powerState} - MoRef: ${vm.moRef}`">
                                    <a :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}/vm/${vm.vmId}/graphs`" class="text-indigo-600 hover:text-indigo-800">
                                        <i class="far fa-chart-bar"></i>
                                    </a>
                                    {{ vm.name }}
                                    <br />
                                    <small class="text-gray-500" v-if="vm.isOvhVm" title="This virtual machine is managed by OVHcloud">
                                        <i class="fas fa-user-cog"></i>
                                        OVHcloud
                                    </small>
                                </td>
                                <td class="p-1 text-center text-gray-500" colspan="3" v-if="vm.powerState == 'deleted'">
                                    <i>Virtual machine removed</i>
                                </td>
                                <td class="p-1" v-if="vm.powerState != 'deleted'">
                                    <small title="Host" class="text-gray-500">{{ vm.hostName }}</small>
                                    <br />
                                    <template v-if="vm.filers">
                                        <div v-for="filer in vm.filers" :key="filer.id" class="inline" :title="`Filer ${filer.name.substring(0, 13) == 'storageLocal_' ? 'local' : filer.name}: ${round(filer.committed / 1024, 1)} of ${round(filer.capacity / 1024, 0)} GB`">
                                            <template v-if="filer.capacity">
                                                <div class="micro-gauge align-middle">
                                                    <vue-svg-gauge
                                                        :start-angle="-270"
                                                        :end-angle="90"
                                                        :inner-radius="0"
                                                        :value="filer.committed / 1024"
                                                        :separator-step="0"
                                                        :min="0"
                                                        :max="filer.capacity / 1024"
                                                        :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                                        :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                                        gauge-color="#f4c009"
                                                        :scale-interval="0"
                                                    >
                                                    </vue-svg-gauge>
                                                </div>
                                                <small>
                                                    <template v-if="filer.name.substring(0, 13) == 'storageLocal_'">
                                                        <template v-if="vm.isOvhVm"> local </template>
                                                        <span v-else class="text-red-700"> local <i class="fas fa-exclamation-triangle"></i> </span>
                                                    </template>
                                                    <template v-else>
                                                        {{ filer.name }}
                                                    </template>
                                                    <small class="text-gray-500" v-if="filer.disks.length && filer.disks.length > 1">({{ filer.disks.length }} disks)</small>
                                                </small>
                                            </template>
                                        </div>
                                    </template>
                                </td>
                                <td class="p-1" v-if="vm.powerState != 'deleted'">
                                    <span class="px-2.5 py-0.5 rounded-full text-white text-xs font-medium" :class="getVirtualMachineSnapClass(vm)">{{ vm.snapshotNum }}</span>
                                </td>
                                <td class="p-1" :title="`VMWare Tools: ${vm.vmwareTools} - ${vm.vmwareToolsVersion}`" v-if="vm.powerState != 'deleted'">
                                    <span class="px-2.5 py-0.5 rounded-full text-white text-xs font-medium" :class="getVirtualMachineVmwareToolsData('class', vm)">
                                        <i class="fa" :class="getVirtualMachineVmwareToolsData('icon', vm)"></i>
                                    </span>
                                </td>
                                <td class="p-1" :title="`Backup: ${vm.backup && vm.backup.state ? vm.backup.state : 'removed'} - Restore points: ${vm.backup && vm.backup.restorePoints ? Object.keys(vm.backup.restorePoints).length : 0}`">
                                    <span class="px-2.5 py-0.5 rounded-full text-white text-xs font-medium" :class="getVirtualMachineBackupClass(vm)">
                                        <i class="fa" :class="getVirtualMachineBackupIcon(vm)"></i>
                                        <span v-if="vm.backup && vm.backup.state != 'removed'">
                                            <span v-if="vm.backup.restorePoints == null"> ... </span>
                                            <span v-if="vm.backup.restorePoints != null">
                                                {{ Object.keys(vm.backup.restorePoints).length }}
                                            </span>
                                        </span>
                                    </span>
                                </td>
                                <td class="p-1 text-center text-gray-500" colspan="6" v-if="vm.powerState == 'deleted'">
                                    <i>Virtual machine removed</i>
                                </td>
                                <td class="p-1" v-if="vm.powerState != 'deleted'" :title="`Fault Tolerance: ${vm.stateFt}`">
                                    <i class="fas fa-circle" :class="getVirtualMachineFtClass(vm)"></i>
                                </td>
                                <td class="p-1" :title="`RAM usage: ${round(vm.memoryUsed / 1024, 2)} of ${round(vm.memoryMax / 1024, 0)} GB - ${vm.cpuNum} vCPU`" v-if="vm.powerState != 'deleted'">
                                    <div class="micro-gauge align-middle">
                                        <vue-svg-gauge
                                            :start-angle="-270"
                                            :end-angle="90"
                                            :inner-radius="0"
                                            :value="vm.memoryUsed / 1024"
                                            :separator-step="0"
                                            :min="0"
                                            :max="vm.memoryMax / 1024"
                                            :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                            :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                            gauge-color="#f4c009"
                                            :scale-interval="0"
                                        >
                                        </vue-svg-gauge>
                                    </div>
                                    {{ round(vm.memoryMax / 1024, 0) }} <small>GB</small>
                                    <br />
                                    {{ vm.cpuNum }} <small>vCPU</small>
                                </td>
                                <td class="p-1" :title="`CPU usage: ${round(vm.cpuUsed / 1000, 2)} of ${round(vm.cpuMax / 1000, 1)} GHz - CPU Ready: ${vm.cpuReady} ms (${vm.cpuReadyPercent}} %)`" v-if="vm.powerState != 'deleted'">
                                    <div class="micro-gauge align-middle">
                                        <vue-svg-gauge
                                            :start-angle="-270"
                                            :end-angle="90"
                                            :inner-radius="0"
                                            :value="vm.cpuUsed / 1000"
                                            :separator-step="0"
                                            :min="0"
                                            :max="vm.cpuMax / 1000"
                                            :base-color="$root.$data.currentDarkmode ? '#6b7280' : '#e5e7eb'"
                                            :blur-color="$root.$data.currentDarkmode ? '#111111' : '#c7c6c6'"
                                            gauge-color="#f4c009"
                                            :scale-interval="0"
                                        >
                                        </vue-svg-gauge>
                                    </div>
                                    {{ round(vm.cpuUsed / 1000, 1) }} <small>GHz</small>
                                    <br />
                                    <small>
                                        Ready:
                                        <span :class="vm.cpuReadyPercent < 3 ? 'text-blue-500' : 'text-yellow-600'">{{ round(vm.cpuReady, 0) }} <small>ms</small></span>
                                    </small>
                                </td>
                                <td class="p-1" v-if="vm.powerState != 'deleted'" :title="`Network TX/RX: ${round(vm.netTx / 100, 1)} MBps / ${round(vm.netRx / 100, 1)} MBps - Disk IOs R/W: ${round(vm.readPerSecond, 0)} IOps / ${round(vm.writePerSecond, 0)} IOps`">
                                    <small><i class="fas fa-caret-up"></i> {{ round(vm.netTx / 100, 1) }} / {{ round(vm.netRx / 100, 1) }} <i class="fas fa-caret-down"></i> <small>MBps</small></small>
                                    <br />
                                    <small><i class="fas fa-caret-up"></i> {{ round(vm.readPerSecond, 0) }} / {{ round(vm.writePerSecond, 0) }} <i class="fas fa-caret-down"></i> <small>IOps</small></small>
                                </td>
                                <td class="p-1" v-if="vm.powerState != 'deleted'" :title="`Disk R/W: ${round(vm.readRate / 100, 1)} MBps / ${round(vm.writeRate / 100, 1)} MBps - Disk latency R/W: ${round(vm.readLatency, 0)} ms / ${round(vm.writeLatency, 0)} ms`">
                                    <small><i class="fas fa-caret-up"></i> {{ round(vm.readRate / 100, 1) }} / {{ round(vm.writeRate / 100, 1) }} <i class="fas fa-caret-down"></i> <small>MBps</small></small>
                                    <br />
                                    <small><i class="fas fa-caret-up"></i> {{ round(vm.readLatency, 0) }} / {{ round(vm.writeLatency, 0) }} <i class="fas fa-caret-down"></i> <small>ms</small></small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen";
import ErrorsZone from "./ErrorsZone";
import { httpRequester } from "./compositions/axios/httpRequester";
import VueSvgGauge from "./VueSvgGauge.vue";

export default {
    name: "PccDatacenterPage",

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
            vrack: {
                pcc: null,
                datacenters: null,
            },
            datacenter: {},
            backup: {},
            disasterRecovery: {},
            hosts: null,
            filers: null,
            vms: null,
            breadcrumb: [
                { name: this.pccName, href: this.pccRoute+'/'+this.pccName, current: false },
                { name: 'Datacenters', href: this.pccRoute+'/'+this.pccName, current: false },
                { name: 'Datacenter #'+this.datacenterId, href: this.pccRoute+'/'+this.pccName+'/datacenter/'+this.datacenterId, current: true },
            ],
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
                this.loadBackup();
                this.loadDisasterRecovery();
                this.loadHosts();
                this.loadFilers();
                this.loadVms();
                this.loadVracks();
            }
        },

        async loadPcc() {
            this.pcc = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}`);
            if (this.pcc) {
                this.loadPccUpgrades();
            }
        },

        async loadPccUpgrades() {
            const value = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/vcenterVersion`);
            let upgrades = [];
            if (value.currentVersion.build != value.lastMajor.build) {
                upgrades.push(value.lastMajor.major + value.lastMajor.minor);
            }
            if (value.currentVersion.build != value.lastMinor.build) {
                upgrades.push(value.lastMinor.major + value.lastMinor.minor);
            }
            this.pcc["upgrades"] = upgrades;
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
            if (!hostIds.length) {
                this.hosts = {};
            }
            let hostIdsChunks = this.chunkArray(hostIds, 40);
            for (let hostIdsChunk of hostIdsChunks) {
                const hosts = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/host/${hostIdsChunk.join(",")}?batch=,`);
                if (this.hosts === null) {
                    this.hosts = {};
                }
                for (const hostId in hosts) {
                    const host = hosts[hostId];
                    if (!host["error"]) {
                        this.hosts[host["key"]] = { ...host["value"] };
                    }
                }
            }
        },

        async loadFilers() {
            const filerIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/filer`);
            if (!filerIds.length) {
                this.filers = {};
            }
            let filerIdsChunks = this.chunkArray(filerIds, 40);
            this.loadGlobalFilers();
            for (let filerIdsChunk of filerIdsChunks) {
                const filers = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/filer/${filerIdsChunk.join(",")}?batch=,`);
                if (this.filers === null) {
                    this.filers = {};
                }
                for (const filerId in filers) {
                    const filer = filers[filerId];
                    if (!filer["error"]) {
                        filer["value"]["global"] = false;
                        this.filers[filer["key"]] = { ...filer["value"] };
                    }
                }
            }
        },

        async loadGlobalFilers() {
            const filerIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/filer`);
            let filerIdsChunks = this.chunkArray(filerIds, 40);
            for (let filerIdsChunk of filerIdsChunks) {
                const filers = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/filer/${filerIdsChunk.join(",")}?batch=,`);
                if (this.filers === null) {
                    this.filers = {};
                }
                for (const filerId in filers) {
                    const filer = filers[filerId];
                    if (!filer["error"]) {
                        filer["value"]["global"] = true;
                        this.filers[filer["key"]] = { ...filer["value"] };
                    }
                }
            }
        },

        async loadVms() {
            const vmIds = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/vm`);
            if (!vmIds.length) {
                this.vms = {};
            }
            let vmIdsChunks = this.chunkArray(vmIds, 40);
            for (let vmIdsChunk of vmIdsChunks) {
                const vms = await this.get(`${this.ovhapiRoute}/dedicatedCloud/${this.pccName}/datacenter/${this.datacenterId}/vm/${vmIdsChunk.join(",")}?batch=,`);
                if (this.vms === null) {
                    this.vms = {};
                }
                for (const vmId in vms) {
                    const vm = vms[vmId];
                    if (!vm["error"]) {
                        let value = vm["value"];
                        for (const i in value.filers) {
                            const filer = value.filers[i];
                            let capacity = 0;
                            for (const j in filer.disks) {
                                const disk = filer.disks[j];
                                if (disk.capacity) {
                                    capacity += disk.capacity;
                                }
                            }
                            value["filers"][i]["capacity"] = capacity;
                        }
                        value["isOvhVm"] = this.isOvhVm(value);
                        this.vms[vm["key"]] = { ...value };
                    }
                }
            }
        },

        async loadVracks() {
            const vrackNames = await this.get(`${this.ovhapiRoute}/vrack`);
            for (let vrackName of vrackNames) {
                this.loadVrack(vrackName);
            }
        },

        async loadVrack(vrackName) {
            let vrack = await this.get(`${this.ovhapiRoute}/vrack/${vrackName}`); // No batch mode on this call
            vrack["serviceName"] = vrackName;
            for (let serviceType of ["dedicatedCloud", "dedicatedCloudDatacenter"]) {
                const serviceNames = await this.get(`${this.ovhapiRoute}/vrack/${vrackName}/${serviceType}`);
                for (let serviceName of serviceNames) {
                    if (this.pccName == serviceName) {
                        this.vrack["pcc"] = vrack;
                    }
                    if (serviceName.includes(this.pccName + "_")) {
                        if (!this.vrack["datacenters"]) {
                            this.vrack["datacenters"] = {};
                        }
                        const datacenter = serviceName.replace(this.pccName + "_", "");
                        this.vrack["datacenters"][datacenter] = vrack;
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
                "hosts-cpu": {
                    max: 0,
                    used: 0,
                    value: 0,
                },
                "hosts-ram": {
                    max: 0,
                    used: 0,
                    value: 0,
                },
                "datastores-usage": {
                    max: 0,
                    used: 0,
                    value: 0,
                },
                "datastores-provisioned": {
                    max: 0,
                    used: 0,
                    value: 0,
                },
            };

            for (const hostId in this.hosts) {
                const host = this.hosts[hostId];
                if (host.billingType != "freeSpare") {
                    result["hosts-cpu"]["max"] = result["hosts-cpu"]["max"] + host.cpuMax / 1000;
                    result["hosts-cpu"]["used"] = result["hosts-cpu"]["used"] + host.cpuUsed / 1000;
                    result["hosts-cpu"]["value"] = result["hosts-cpu"]["used"] * (100 / result["hosts-cpu"]["max"]);

                    result["hosts-ram"]["max"] = result["hosts-ram"]["max"] + host.ram.value;
                    result["hosts-ram"]["used"] = result["hosts-ram"]["used"] + host.memoryUsed / 1024;
                    result["hosts-ram"]["value"] = result["hosts-ram"]["used"] * (100 / result["hosts-ram"]["max"]);
                }
            }
            for (const filerId in this.filers) {
                const datastore = this.filers[filerId];
                if (datastore.billingType != "freeSpare") {
                    result["datastores-usage"]["max"] = result["datastores-usage"]["max"] + datastore.spaceUsed + datastore.spaceFree;
                    result["datastores-usage"]["used"] = result["datastores-usage"]["used"] + datastore.spaceUsed;
                    result["datastores-usage"]["value"] = result["datastores-usage"]["used"] * (100 / result["datastores-usage"]["max"]);

                    result["datastores-provisioned"]["max"] = result["datastores-provisioned"]["max"] + datastore.spaceUsed + datastore.spaceFree;
                    result["datastores-provisioned"]["used"] = result["datastores-provisioned"]["used"] + datastore.spaceProvisionned;
                    result["datastores-provisioned"]["value"] = result["datastores-provisioned"]["used"] * (100 / result["datastores-provisioned"]["max"]);
                }
            }

            if (type == "hosts") {
                type = "hosts-cpu";
                if (result["hosts-cpu"]["value"] < result["hosts-ram"]["value"]) {
                    type = "hosts-ram";
                }
            }
            return result[type]["value"];
        },

        getDatastoreStateClass(datastore) {
            var resultClass = "text-yellow-600";
            if (datastore.state) {
                if (datastore.state == "delivered") {
                    resultClass = "text-green-700";
                } else if (datastore.state == "adding") {
                    resultClass = "text-yellow-600";
                } else if (datastore.state == "removing") {
                    resultClass = "text-yellow-600";
                } else if (datastore.state == "error") {
                    resultClass = "text-red-700";
                } else {
                    resultClass = "text-red-700";
                }
            }
            return resultClass;
        },

        getHostStateClass(host) {
            var resultClass = "text-yellow-600";
            if (host.connectionState) {
                if (host.connectionState == "connected") {
                    if (host.inMaintenance) {
                        resultClass = "text-yellow-600";
                    } else {
                        resultClass = "text-green-700";
                    }
                } else if (host.connectionState == "disconnected") {
                    resultClass = "text-red-700";
                } else if (host.connectionState == "notResponding") {
                    resultClass = "text-red-700";
                } else {
                    resultClass = "text-red-700";
                }
            } else {
                if (host.state) {
                    if (host.state == "delivered") {
                        resultClass = "text-green-700";
                    } else if (host.state == "adding") {
                        resultClass = "text-yellow-600";
                    } else if (host.state == "removing") {
                        resultClass = "text-yellow-600";
                    } else if (host.state == "error") {
                        resultClass = "text-red-700";
                    } else {
                        resultClass = "text-red-700";
                    }
                }
            }
            return resultClass;
        },

        getVirtualMachineFtClass(virtualmachine) {
            var resultClass = "text-yellow-600";
            if (virtualmachine.stateFt) {
                if (virtualmachine.stateFt == "running") {
                    resultClass = "text-green-700";
                } else if (virtualmachine.stateFt == "enabled") {
                    resultClass = "text-blue-500";
                } else if (virtualmachine.stateFt == "starting") {
                    resultClass = "text-cyan-500";
                } else if (virtualmachine.stateFt == "needSecondary") {
                    resultClass = "text-yellow-600";
                } else if (virtualmachine.stateFt == "disabled") {
                    resultClass = "text-gray-500";
                } else if (virtualmachine.stateFt == "notConfigured") {
                    resultClass = "text-gray-500";
                }
            }
            return resultClass;
        },

        getVirtualMachineStateClass(virtualmachine) {
            var resultClass = "text-yellow-600";
            if (virtualmachine.powerState) {
                if (virtualmachine.powerState == "poweredOn") {
                    resultClass = "text-green-700";
                } else if (virtualmachine.powerState == "suspended") {
                    resultClass = "text-yellow-600";
                } else if (virtualmachine.powerState == "deleted") {
                    resultClass = "text-gray-500";
                } else {
                    resultClass = "text-red-700";
                }
            }
            return resultClass;
        },

        getVirtualMachineVmwareToolsData(type, virtualmachine) {
            var result = {
                class: "",
                icon: "fa-question-circle",
            };
            if (virtualmachine.vmwareTools) {
                if (virtualmachine.vmwareTools == "guestToolsRunning") {
                    if (virtualmachine.vmwareToolsVersion == "guestToolsCurrent") {
                        result["class"] = "bg-green-700";
                        result["icon"] = "fa-check";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsSupportedNew") {
                        result["class"] = "bg-green-700";
                        result["icon"] = "fa-check";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsUnmanaged") {
                        result["class"] = "bg-cyan-500";
                        result["icon"] = "fa-check";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsNeedUpgrade") {
                        result["class"] = "bg-yellow-600";
                        result["icon"] = "fa-check";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsSupportedOld") {
                        result["class"] = "bg-yellow-600";
                        result["icon"] = "fa-check";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsTooNew") {
                        result["class"] = "bg-yellow-600";
                        result["icon"] = "fa-exclamation-triangle";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsTooOld") {
                        result["class"] = "bg-yellow-600";
                        result["icon"] = "fa-exclamation-triangle";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsNotInstalled") {
                        result["class"] = "bg-red-700";
                        result["icon"] = "fa-times";
                    } else if (virtualmachine.vmwareToolsVersion == "guestToolsBlacklisted") {
                        result["class"] = "bg-red-700";
                        result["icon"] = "fa-times";
                    }
                } else if (virtualmachine.vmwareTools == "guestToolsExecutingScripts") {
                    result["class"] = "bg-yellow-600";
                    result["icon"] = "fa-check";
                } else if (virtualmachine.vmwareTools == "guestToolsNotRunning") {
                    result["class"] = "bg-red-700";
                    result["icon"] = "fa-times";
                }
            }
            if (virtualmachine.isOvhVm) {
                result["class"] = "bg-gray-600";
            }
            return result[type];
        },

        isOvhVm(virtualmachine) {
            // Private gateway : private-gateway-1234.rbx2b.pcc.ovh.net
            if (virtualmachine.name.match(/^private-gateway-[0-9]+\.[a-z]{3}[0-9][a-z]\.pcc\.ovh\.[a-z]+$/)) {
                return true;
            }
            // Veeam backup server : backupServer1234
            if (virtualmachine.name.match(/^backupServer[0-9]+$/)) {
                return true;
            }
            // Veeam backup proxy : backupProxy1234
            if (virtualmachine.name.match(/^backupProxy[0-9]+$/)) {
                return true;
            }
            // Veeam backup proxy (new) : veeamproxy-1234-1.rbx2b.pcc.ovh.net
            if (virtualmachine.name.match(/^veeamproxy-[0-9]+-[0-9]+\.[a-z]{3}[0-9][a-z]\.pcc\.ovh\.[a-z]+$/)) {
                return true;
            }
            // Zerto l2L : l2lendpoint1234.rbx2b.pcc.ovh.net
            if (virtualmachine.name.match(/^l2lendpoint[0-9]+\.[a-z]{3}[0-9][a-z]\.pcc\.ovh\.[a-z]+$/)) {
                return true;
            }
            // Zerto VRA : Z-VRA-172.19.62.1
            if (virtualmachine.name.match(/^Z-VRA-172\.[0-9]+\.[0-9]+\.[0-9]+$/)) {
                return true;
            }
            // Zerto vRouter : rbx2b-1234-vr
            if (virtualmachine.name.match(/^[0-9]+\.[a-z]{3}[0-9][a-z]-[0-9]+-vr$/)) {
                return true;
            }
            return false;
        },

        getVirtualMachineSnapClass(virtualmachine) {
            var resultClass = "";
            if (virtualmachine.snapshotNum) {
                resultClass = "bg-red-700";
            } else {
                resultClass = "bg-green-700";
            }
            if (virtualmachine.isOvhVm) {
                resultClass = "bg-gray-600";
            }
            return resultClass;
        },

        getVirtualMachineBackupClass(virtualmachine) {
            var resultClass = "";
            if (virtualmachine.backup) {
                if (virtualmachine.backup.state) {
                    if (virtualmachine.backup.state == "delivered") {
                        resultClass = "bg-green-700";
                    } else if (virtualmachine.backup.state == "removed") {
                        resultClass = "bg-yellow-600";
                    } else if (virtualmachine.backup.state == "disabled") {
                        resultClass = "bg-red-700";
                    } else {
                        resultClass = "bg-red-700";
                    }
                }
            } else {
                resultClass = "bg-red-700";
            }
            if (virtualmachine.isOvhVm) {
                resultClass = "bg-gray-600";
            }
            return resultClass;
        },

        getVirtualMachineBackupIcon(virtualmachine) {
            var resultClass = "fa-question-circle";
            if (virtualmachine.backup) {
                if (virtualmachine.backup.state) {
                    if (virtualmachine.backup.state == "delivered") {
                        resultClass = "fa-check";
                    } else if (virtualmachine.backup.state == "removed") {
                        resultClass = "fa-exclamation-triangle";
                    } else if (virtualmachine.backup.state == "disabled") {
                        resultClass = "fa-times";
                    } else {
                        resultClass = "fa-times";
                    }
                }
            } else {
                resultClass = "fa-times";
            }
            return resultClass;
        },

        getOptionStateClass(option) {
            var resultClass = "text-yellow-600";
            if (option.state) {
                if (option.state == "delivered") {
                    resultClass = "text-green-700";
                } else if (option.state == "enabled") {
                    resultClass = "text-green-700";
                } else if (option.state == "error") {
                    resultClass = "text-red-700";
                } else if (option.state == "disabled") {
                    resultClass = "text-red-700";
                }
            }
            return resultClass;
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
