<template>
    <div class="pcc-page mx-4">
        <pcc-head :breadcrumb="breadcrumb" :pcc-name="pccName" :home-route="homeRoute" :pcc-route="pccRoute" :pcc="pcc" :vrack="vrack" :loading="loading" :errors="errors" :load-all="loadAll">
            <template v-slot:third-column>
                <div v-if="Object.keys(pcc).length" class="py-3">
                    <i class="fas fa-tachometer-alt"></i> Bandwidth: {{ pcc.bandwidth }}<br />
                    <i class="fas fa-user-lock"></i> Concurrent sessions: {{ pcc.userLimitConcurrentSession }}<br />
                    <i class="far fa-clock"></i> Session timeout: {{ pcc.userSessionTimeout ? pcc.userSessionTimeout : "never" }}
                </div>
            </template>
        </pcc-head>

        <div v-if="!datacenters" class="bg-white dark:bg-gray-700 rounded-lg shadow mt-6 text-center py-4">
            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading datacenters from OVHcloud API...
        </div>
        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2 mt-6">
            <div v-for="(datacenter, datacenterId) in window._(datacenters).toPairs().sortBy(0).fromPairs().value()" :key="datacenterId">
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center">
                    <div class="flex items-center justify-between p-4">
                        <div class="grow">
                            <div>
                                <span>
                                    {{ datacenter.description || datacenter.name }}
                                </span>
                                <span class="text-gray-500">{{ datacenter.description ? datacenter.name : "#" + datacenterId }}</span>
                            </div>
                            <div class="text-sm">
                                <span>{{ datacenter.commercialName }}</span>
                                <span class="text-gray-500">({{ datacenter.commercialRangeName }})</span>
                            </div>
                            <div class="text-sm">
                                <i class="fas fa-network-wired"></i> Datacenter vRack :
                                <template v-if="!vrack.datacenters || !vrack.datacenters[datacenter.name]">
                                    <template v-if="loading">
                                        <i class="fas fa-circle-notch fa-spin"></i>
                                    </template>
                                    <template v-else>
                                        <i class="text-gray-500">no</i>
                                    </template>
                                </template>
                                <template v-else-if="vrack.datacenters[datacenter.name].name">
                                    {{ vrack.datacenters[datacenter.name].name }}
                                    <a class="text-indigo-600 hover:text-indigo-800" :href="`${homeRoute}/vrack`">
                                        ({{ vrack.datacenters[datacenter.name].serviceName }})
                                    </a>
                                </template>
                                <template v-else>
                                    <a class="text-indigo-600 hover:text-indigo-800" :href="`${homeRoute}/vrack`">
                                        {{ vrack.datacenters[datacenter.name].serviceName }}
                                    </a>
                                </template>
                            </div>
                        </div>
                        <div class="pl-4">
                            <a class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :href="`${pccRoute}/${pccName}/datacenter/${datacenterId}`">
                                <i class="fas fa-building mr-2"></i>
                                Datacenter
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2">
            <div>
                <pcc-options-card title="Product options" :loading="loading" :options="options" :load-all="loadAll"> </pcc-options-card>
            </div>

            <div>
                <pcc-options-card title="Sector-specific compliance options" :loading="loading" :options="complianceOptions" :load-all="loadAll"> </pcc-options-card>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (ips && Object.keys(ips).length) || 0 }}</small>
                    <div class="p-4">
                        <div class="mb-3">IP blocks</div>
                        <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                                    <tr>
                                        <th scope="col" class="px-1 py-2">IP block</th>
                                        <th scope="col" class="px-1 py-2">Gateway</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm bg-white dark:bg-gray-700">
                                    <tr v-if="!ips">
                                        <td colspan="2" class="p-4">
                                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading IP blocks from OVHcloud API...
                                        </td>
                                    </tr>
                                    <tr v-else-if="!Object.keys(ips).length">
                                        <td colspan="2" class="p-4">
                                            <i>No IP block found</i>
                                        </td>
                                    </tr>
                                    <tr v-for="(ip, ipIdx) in window._.orderBy(window._.values(ips), ['network'])" :key="ip.network" :title="`${ip.country} - ${ip.network} : Netmask: ${ip.netmask} - Gateway: ${ip.gateway}`" :class="ipIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                        <td class="p-1">
                                            <span class="fi mr-1" :class="'fi-' + flagFromCountry(ip.country).toLowerCase()"></span>
                                            {{ ip.network }}
                                        </td>
                                        <td class="p-1">
                                            <small class="text-gray-500">{{ ip.gateway }}</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
            <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
            <button class="m-2 absolute top-0 right-10 px-2.5 py-0.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="toggleAutoRefreshTasks">
                <i class="fas mr-1" :class="autoRefreshTasks ? 'fa-times' : 'fa-check'"></i>
                {{ autoRefreshTasks ? "Disable" : "Enable" }} tasks auto refresh (30s)
            </button>
            <div class="absolute top-0 left-0 m-2">
                <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ (tasks && Object.keys(tasks).length) || 0 }}</small>
                <button class="ml-2 px-2.5 py-0.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" @click="toggleRecentTasks">
                    <i class="fas" :class="recentTasks ? 'fa-eye-slash' : 'fa-eye'"></i>
                    {{ recentTasks ? "Hide" : "Display" }} recent
                </button>
            </div>
            <div class="p-4">
                <div class="mb-3">{{ recentTasks ? "Active & recent" : "Active" }} tasks</div>
                <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                            <tr>
                                <th scope="col" class="px-1 py-2">State</th>
                                <th scope="col" class="px-1 py-2">Name</th>
                                <th scope="col" class="px-1 py-2">Created by</th>
                                <th scope="col" class="px-1 py-2">Date</th>
                                <th scope="col" class="px-1 py-2">Related to</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm bg-white dark:bg-gray-700">
                            <tr v-if="!tasks">
                                <td colspan="5" class="p-4">
                                    <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading tasks from OVHcloud API...
                                </td>
                            </tr>
                            <tr v-else-if="!Object.keys(tasks).length">
                                <td colspan="5" class="p-4">
                                    <i>No task found</i>
                                </td>
                            </tr>
                            <tr v-for="(task, taskIdx) in window._.orderBy(window._.values(tasks), ['lastModificationDate'], ['desc'])" :key="task.taskId" :class="taskIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                <td class="p-1">
                                    <span :class="getTaskStateTextClass(task)">
                                        <i class="fas fa-circle"></i>
                                        {{ task.state }}
                                    </span>
                                    <small v-if="task.state == 'todo'" class="text-gray-500"> - {{ dateFormat(task.executionDate) }}</small>
                                    <br />
                                    <small class="text-gray-500">#{{ task.taskId }}</small>
                                </td>
                                <td class="p-1 relative">
                                    <span :title="robots && robots[task.name] && robots[task.name].description">
                                        {{ task.name }}
                                    </span>
                                    <br />
                                    <small>{{ round(task.progress, 0) }}%</small>
                                    -
                                    <small class="text-gray-500" v-if="task.description">
                                        {{ task.description }}
                                    </small>
                                    <small class="text-gray-500" v-else-if="!robots"> <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading... </small>
                                    <small class="text-gray-500" v-else-if="robots && robots[task.name]">
                                        <template v-if="robots[task.name].description">
                                            {{ robots[task.name].description }}
                                        </template>
                                        <i v-else>No description</i>
                                    </small>
                                    <small class="text-gray-500" v-else-if="loading"> <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading... </small>
                                    <small class="text-gray-500" v-else>
                                        <i>No description</i>
                                    </small>
                                    <div class="progress task-progress">
                                        <div class="progress-bar" :class="getTaskStateBgClass(task)" role="progressbar" :style="`width:${round(task.progress, 0)}%`" :aria-valuenow="round(task.progress, 0)" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td class="p-1">
                                    <small>{{ task.createdBy }}</small>
                                    <br />
                                    <small class="text-gray-500" v-if="task.createdFrom">({{ task.createdFrom }})</small>
                                </td>
                                <td class="p-1">
                                    <small v-if="task.endDate && task.state == 'done'" class="text-green-700">
                                        Finished on<br />
                                        {{ dateFormat(task.endDate) }}
                                    </small>
                                    <small v-else>
                                        Last change: <br />
                                        {{ dateFormat(task.lastModificationDate) }}
                                    </small>
                                </td>
                                <td class="p-1">
                                    <div><small v-if="task.parentTaskId" class="text-gray-500"> <abbr title="Parent task">Parent</abbr>: #{{ task.parentTaskId }}</small></div>
                                    <div><small v-if="task.datacenterId" class="text-gray-500"> <abbr title="Datacenter">Datac.</abbr>: #{{ task.datacenterId }}</small></div>
                                    <div><small v-if="task.hostId" class="text-gray-500"> Host: #{{ task.hostId }}</small></div>
                                    <div><small v-if="task.userId" class="text-gray-500"> User: #{{ task.userId }}</small></div>
                                    <div><small v-if="task.orderId" class="text-gray-500"> Order: #{{ task.orderId }}</small></div>
                                    <div><small v-if="task.vlanId" class="text-gray-500"> Vlan: #{{ task.vlanId }}</small></div>
                                    <div><small v-if="task.filerId" class="text-gray-500"> Filer: #{{ task.filerId }}</small></div>
                                    <div><small v-if="task.networkAccessId" class="text-gray-500"> <abbr title="Allowed networks">Net.</abbr>: #{{ task.networkAccessId }}</small></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
            <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
            <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (users && Object.keys(users).length) || 0 }}</small>
            <div class="p-4">
                <div class="mb-3">Users</div>
                <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                            <tr>
                                <th scope="col" class="px-1 py-2">State</th>
                                <th scope="col" class="px-1 py-2">Name</th>
                                <th scope="col" class="px-1 py-2">Contact</th>
                                <th scope="col" class="px-1 py-2">Global<br />accesses</th>
                                <th scope="col" v-for="(datacenter, datacenterId) in datacenters" :key="datacenterId" class="px-1 py-2">
                                    {{ datacenter.description || datacenter.name }}
                                    <span class="text-gray-500">{{ datacenter.description ? datacenter.name : "#" + datacenterId }}</span>
                                    <br />
                                    accesses
                                </th>
                            </tr>
                        </thead>
                        <tbody class="text-sm bg-white dark:bg-gray-700">
                            <tr v-if="!users">
                                <td :colspan="4 + window._.values(datacenters).length" class="p-4">
                                    <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading users from OVHcloud API...
                                </td>
                            </tr>
                            <tr v-else-if="!Object.keys(users).length">
                                <td :colspan="4 + window._.values(datacenters).length" class="p-4">
                                    <i>No user found</i>
                                </td>
                            </tr>
                            <tr v-for="(user, userIdx) in window._.orderBy(window._.values(users), ['name'])" :key="user.userId" :class="userIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                <td class="p-1">
                                    <span :class="getUserStateClass(user)">
                                        <i class="fas fa-circle"></i>
                                        {{ user.state }}
                                    </span>
                                    <br />
                                    <small class="text-gray-500">#{{ user.userId }}</small>
                                </td>
                                <td class="p-1">
                                    <template v-if="user.identityProviderType == 'federation'">
                                        <span v-if="user.type == 'group'" :title="`This is an Active Directory federation group from Active Directory #${user.identityProviderId}`">
                                            <i class="far fa-address-book text-cyan-500"></i>
                                            <i class="fas fa-users"></i>
                                            {{ user.name }} <span class="text-gray-500">({{ user.login.split('@')[1] }})</span>
                                        </span>
                                        <span v-else :title="`This is an Active Directory federation user from Active Directory #${user.identityProviderId}`">
                                            <i class="far fa-address-book text-cyan-500"></i>
                                            {{ user.name }}<span class="text-gray-500">@{{ user.login.split('@')[1] }}</span>
                                        </span>
                                    </template>
                                    <template v-else-if="user.identityProviderType == 'iam'">
                                        <span v-if="user.type == 'group'" :title="`This is an OVHcloud IAM role`">
                                            <i class="far fa-id-card text-yellow-600"></i>
                                            {{ user.name }} <span class="text-gray-500">(OVHcloud IAM)</span>
                                        </span>
                                    </template>
                                    <span v-else>
                                        {{ user.login }}
                                    </span>
                                    <br />
                                    <small class="text-gray-500">{{ user.firstName }} {{ user.lastName }}</small>
                                </td>
                                <td class="p-1">
                                    <small v-if="user.email">
                                        {{ user.email }}
                                        (<abbr title="Technical email alerts">Alerts</abbr>: <i class="fas" :class="user.receiveAlerts ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>)
                                    </small>
                                    <br />
                                    <small v-if="user.phoneNumber">
                                        {{ user.phoneNumber }}
                                        (<abbr title="Security token validation">Token</abbr>: <i class="fas" :class="user.isTokenValidator ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>)
                                    </small>
                                </td>
                                <td class="p-1">
                                    <small>
                                        <abbr title="NSX access">NSX</abbr>:
                                        <i class="fas" :class="user.nsxRight ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
                                        -
                                        <abbr title="Manage encryption">Enc.</abbr>:
                                        <i class="fas" :class="user.encryptionRight ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
                                        -
                                        <span v-if="user.objectRights" :title="`Objects rights: ${Object.keys(user.objectRights).length}`">
                                            <abbr :title="`Objects rights: ${Object.keys(user.objectRights).length}`">Obj.</abbr>:
                                            <span v-if="Object.keys(user.objectRights).length">
                                                {{ Object.keys(user.objectRights).length }}
                                            </span>
                                            <i class="fas fa-times text-red-700" v-else></i>
                                        </span>
                                        <span v-else-if="loading" title="Loading user object rights from OVHcloud API...">
                                            <abbr title="Objects rights">Obj.</abbr>:
                                            <i class="fas fa-circle-notch fa-spin mr-1"></i>
                                        </span>
                                    </small>
                                    <br />
                                    <small>
                                        <abbr title="Manage IPs (OVHcloud plugin)">IPs</abbr>:
                                        <i class="fas" :class="user.canManageNetwork ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
                                        -
                                        <abbr title="Manage IP failovers (OVHcloud plugin)">IPFO</abbr>:
                                        <i class="fas" :class="user.canManageIpFailOvers ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
                                        <!--
                                        -
                                        <abbr title="Manage rights (OVHcloud plugin)">Rights</abbr>:
                                        <i class="fas" :class="user.canManageRights ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
                                        -->
                                    </small>
                                </td>
                                <td class="p-1" v-for="(datacenter, datacenterId) in datacenters" :key="datacenterId">
                                    <span v-if="user.rights && user.rights[datacenterId]">
                                        <abbr title="Datacenter">DC.</abbr>:
                                        <span :class="getUserAccessStateClass(user.rights[datacenterId].right)">{{ user.rights[datacenterId].right }}</span>
                                        -
                                        <small>
                                            <abbr title="Add ressources (OVHcloud plugin)">Add res.</abbr>:
                                            <i class="fas" :class="user.rights[datacenterId].canAddRessource ? 'fa-check text-green-700' : 'fa-times text-red-700'"></i>
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
                                    <small v-else-if="loading"> <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading user datacenter rights<br />from OVHcloud API... </small>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-1 lg:grid-cols-2">
            <div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (allowedNetworks && Object.keys(allowedNetworks).length) || 0 }}</small>
                    <div class="p-4">
                        <div class="mb-3">Allowed networks</div>
                        <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                                    <tr>
                                        <th scope="col" class="px-1 py-2">IP block</th>
                                        <th scope="col" class="px-1 py-2">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm bg-white dark:bg-gray-700">
                                    <tr v-if="!allowedNetworks">
                                        <td colspan="2" class="p-4">
                                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading allowed networks from OVHcloud API...
                                        </td>
                                    </tr>
                                    <tr v-else-if="!Object.keys(allowedNetworks).length">
                                        <td colspan="2" class="p-4">
                                            <i>No allowed network found</i>
                                        </td>
                                    </tr>
                                    <tr v-for="(allowedNetwork, allowedNetworkIdx) in window._.orderBy(window._.values(allowedNetworks), ['network'])" :key="allowedNetwork.networkAccessId" :title="`${allowedNetwork.network}: ${allowedNetwork.state} #${allowedNetwork.networkAccessId}`" :class="allowedNetworkIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                        <td class="p-1">
                                            <i class="fas mr-1" :class="allowedNetwork.state == 'allowed' ? 'fa-check text-green-700' : 'fa-clock text-yellow-600'"></i>
                                            {{ allowedNetwork.network }}
                                        </td>
                                        <td class="p-1">
                                            <small class="text-gray-500">{{ allowedNetwork.description }}</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
                    <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
                    <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (twoFAWhitelists && Object.keys(twoFAWhitelists).length) || 0 }}</small>
                    <div class="p-4">
                        <div class="mb-3">Two factor authentication trusted IPs</div>
                        <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                                    <tr>
                                        <th scope="col" class="px-1 py-2">IP block</th>
                                        <th scope="col" class="px-1 py-2">Description</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm bg-white dark:bg-gray-700">
                                    <tr v-if="!twoFAWhitelists">
                                        <td colspan="2" class="p-4">
                                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Loading two factor authentication trusted IPs from OVHcloud API...
                                        </td>
                                    </tr>
                                    <tr v-else-if="!Object.keys(twoFAWhitelists).length">
                                        <td colspan="2" class="p-4">
                                            <i>No two factor authentication trusted IP found</i>
                                        </td>
                                    </tr>
                                    <tr v-for="(twoFAWhitelist, twoFAWhitelistIdx) in window._.orderBy(window._.values(twoFAWhitelists), ['network'])" :key="twoFAWhitelist.id" :title="`${twoFAWhitelist.cidrNetmask}: ${twoFAWhitelist.state} #${twoFAWhitelist.id}`" :class="twoFAWhitelistIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                                        <td class="p-1">
                                            <i class="fas mr-1" :class="twoFAWhitelist.state == 'enabled' ? 'fa-check text-green-700' : 'fa-clock text-yellow-600'"></i>
                                            {{ twoFAWhitelist.cidrNetmask }}
                                        </td>
                                        <td class="p-1">
                                            <small class="text-gray-500">{{ twoFAWhitelist.description }}</small>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div>
                <pcc-options-card title="Security options" :loading="loading" :options="securityOptions" :load-all="loadAll"> </pcc-options-card>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingScreen from "./LoadingScreen.vue";
import ErrorsZone from "./ErrorsZone.vue";
import { httpRequester } from "./compositions/axios/httpRequester.js";
import VueSvgGauge from "./VueSvgGauge.vue";
import moment from "moment";

export default {
    name: "PccPage",

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
        const { loaded, loading, errors, request, get, getList } = httpRequester();

        return {
            loaded,
            loading,
            errors,
            request,
            get,
            getList,
        };
    },

    data() {
        return {
            pcc: {},
            vrack: {
                pcc: null,
                datacenters: null,
            },
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
            robots: null,
            timer: null,
            autoRefreshTasks: false,
            recentTasks: false,
            breadcrumb: [
                { name: this.pccName, href: this.pccRoute+'/'+this.pccName, current: true },
            ],
        };
    },

    mounted() {
        this.loadAll();
        this.timer = setInterval(this.refreshTasks, 30 * 1000);
    },

    beforeDestroy() {
        this.cancelAutoUpdate();
    },

    methods: {
        flagFromCountry(country) {
            if (country == "UK") {
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
            if (this.autoRefreshTasks) {
                this.loadTasks();
            }
        },

        cancelAutoUpdate() {
            clearInterval(this.timer);
        },

        dateFormat(date) {
            if (!date) {
                return null;
            }
            return moment(date).format("MMM Do YYYY HH:mm");
        },

        async loadAll(force = false) {
            if (force || !this.loading) {
                this.loadPcc();
                this.loadDatacenters();
                this.loadPccOption("federation", "activeDirectory");
                this.loadPccOption("iam");
                this.loadPccOption("vmEncryption", "kms");
                this.loadPccOption("nsx");
                this.loadPccOption("nsxt");
                this.loadPccOption("vrops");
                this.loadPccOption("hcx");
                this.loadPccOption("pcidss");
                this.loadPccOption("hds");
                this.loadPccOption("hipaa");
                this.loadIps();
                this.loadTasks();
                this.loadRobots();
                this.loadUsers();
                this.loadAllowedNetworks();
                this.loadTwoFAWhitelists();
                this.loadPccSecurityOptionsMatrix();
                this.loadVracks();
            }
        },

        async loadPcc() {
            this.pcc = await this.get(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}`);
            if (!this.pcc) return;
            this.loadPccUpgrades();
        },

        async loadPccUpgrades() {
            const value = await this.get(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/vcenterVersion`);
            let upgrades = [];
            if (value.currentVersion.build != value.lastMajor.build) {
                upgrades.push(value.lastMajor.major + value.lastMajor.minor);
            }
            if (value.currentVersion.build != value.lastMinor.build) {
                upgrades.push(value.lastMinor.major + value.lastMinor.minor);
            }
            this.pcc["upgrades"] = upgrades;
        },

        async loadPccSecurityOptionsMatrix() {
            let compatibilityMatrix = await this.get(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/securityOptions/compatibilityMatrix`);
            if (compatibilityMatrix) {
                for (let option of compatibilityMatrix) {
                    this.fillOption(option.name, option);
                }
            }
        },

        fillOption(optionName, optionFromApi) {
            let value = {};
            if (this.options && this.options[optionName]) {
                value = this.options[optionName];
            }
            if (this.complianceOptions && this.complianceOptions[optionName]) {
                value = this.complianceOptions[optionName];
            }
            if (this.securityOptions && this.securityOptions[optionName]) {
                value = this.securityOptions[optionName];
            }

            for (let key in optionFromApi) {
                let val = optionFromApi[key];
                value[key] = val;
            }
            if (value["state"] === "delivered") {
                value["state"] = "enabled";
            }
            if (!("compatible" in value)) {
                value["compatible"] = true;
            }

            value["name"] = optionName;
            value["description"] = value["description"] || optionName;
            value["optionType"] = "security";
            if (optionName == "accessNetworkFiltered") {
                value["optionType"] = "security";
            } else if (optionName == "advancedSecurity") {
                value["optionType"] = "security";
            } else if (optionName == "base") {
                value["optionType"] = "security";
            } else if (optionName == "federation") {
                value["name"] = "Federation";
                value["description"] = "Active Directory user federation";
                value["optionType"] = "option";
            } else if (optionName == "iam") {
                value["name"] = "IAM";
                value["description"] = "OVHcloud Identity and Access Management";
                value["optionType"] = "option";
            } else if (optionName == "grsecKernel") {
                value["optionType"] = "security";
            } else if (optionName == "hcx") {
                value["name"] = "HCX";
                value["description"] = "VMware Hybrid Cloud Extension";
                value["optionType"] = "option";
            } else if (optionName == "hds") {
                value["name"] = "HDS";
                value["description"] = "French Healthcare Data Hosting";
                value["optionType"] = "compliance";
            } else if (optionName == "hids") {
                value["optionType"] = "security";
            } else if (optionName == "hipaa") {
                value["name"] = "HIPAA";
                value["description"] = "American Health Insurance Portability and Accountability Act";
                value["optionType"] = "compliance";
            } else if (optionName == "nids") {
                value["optionType"] = "security";
            } else if (optionName == "nsx") {
                value["name"] = "NSX-V";
                value["description"] = "NSX-V VMware Network Virtualization";
                value["optionType"] = "option";
            } else if (optionName == "nsxt") {
                value["name"] = "NSX-T";
                value["description"] = "NSX-T Data Center for vSphere";
                value["optionType"] = "option";
                value["version"] = value["version"] ? value["version"].split("-")[0] : '';
            } else if (optionName == "pcidss") {
                value["name"] = "PCI DSS";
                value["description"] = "Payment Card Industry Data Security Standard";
                value["optionType"] = "compliance";
            } else if (optionName == "privateCustomerVlan") {
                value["optionType"] = "security";
            } else if (optionName == "privateGw") {
                value["optionType"] = "security";
            } else if (optionName == "sendLogToCustomer") {
                value["optionType"] = "security";
            } else if (optionName == "sessionTimeout") {
                value["optionType"] = "security";
            } else if (optionName == "sftp") {
                value["optionType"] = "security";
            } else if (optionName == "snc") {
                value["name"] = "SecNumCloud";
                value["description"] = "French SecNumCloud ANSSI Security Visa";
                value["optionType"] = "compliance";
            } else if (optionName == "spla") {
                value["name"] = "SPLA";
                value["description"] = "Windows licensing management";
                value["optionType"] = "option";
            } else if (optionName == "sslV3") {
                value["optionType"] = "security";
            } else if (optionName == "tls1.2") {
                value["optionType"] = "security";
            } else if (optionName == "tokenValidation") {
                value["optionType"] = "security";
            } else if (optionName == "twoFa") {
                value["optionType"] = "security";
            } else if (optionName == "twoFaFail2ban") {
                value["optionType"] = "security";
            } else if (optionName == "vmEncryption") {
                value["name"] = "VM encryption";
                value["description"] = "Virtual machine encryption";
                value["optionType"] = "option";
            } else if (optionName == "vrliForwarder") {
                value["optionType"] = "security";
            } else if (optionName == "vrops") {
                value["name"] = "vROps";
                value["description"] = "VMware vRealize Operations";
                value["optionType"] = "option";
            } else if (optionName == "waf") {
                value["optionType"] = "security";
            }

            if (value["optionType"] == "compliance") {
                if (!this.complianceOptions) {
                    this.complianceOptions = {};
                }
                if (value["state"] != "disabled" || 1) {
                    this.complianceOptions[optionName] = { ...value };
                }
            } else if (value["optionType"] == "security") {
                if (!this.securityOptions) {
                    this.securityOptions = {};
                }
                this.securityOptions[optionName] = { ...value };
            } else {
                if (!this.options) {
                    this.options = {};
                }
                this.options[optionName] = { ...value };
            }
        },

        async loadPccOption(optionName, suboptionName) {
            let value = await this.get(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/${optionName}`);
            if (suboptionName) {
                value["suboptions"] = {};
                const suboptions = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/${optionName}/${suboptionName}`);
                for (const suboption of suboptions || []) {
                    suboption["type"] = suboptionName;
                    suboption["id"] = suboption[suboptionName + "Id"];
                    for (const k in suboption) {
                        if (k.includes("Port")) {
                            suboption["port"] = suboption[k];
                        }
                        if (k.includes("Hostname")) {
                            suboption["hostname"] = suboption[k];
                        }
                    }
                    value["suboptions"][suboption["id"]] = suboption;
                }
            }
            this.fillOption(optionName, value);
        },

        async loadIps() {
            const ips = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/ip`);
            if (!ips) return;
            let loadedIps = {};
            for (const ip of ips) {
                loadedIps[ip.network] = { ...ip };
            }
            this.ips = loadedIps;
        },

        async loadAllowedNetworks() {
            const allowedNetworks = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/allowedNetwork`);
            if (!allowedNetworks) return;
            let loadedAllowedNetworks = {};
            for (const allowedNetwork of allowedNetworks) {
                loadedAllowedNetworks[allowedNetwork.networkAccessId] = { ...allowedNetwork };
            }
            this.allowedNetworks = loadedAllowedNetworks;
        },

        async loadTwoFAWhitelists() {
            const twoFAWhitelists = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/twoFAWhitelist`);
            if (!twoFAWhitelists) return;
            let loadedTwoFAWhitelists = {};
            for (const twoFAWhitelist of twoFAWhitelists) {
                loadedTwoFAWhitelists[twoFAWhitelist.id] = { ...twoFAWhitelist };
            }
            this.twoFAWhitelists = loadedTwoFAWhitelists;
        },

        async loadUsers() {
            const users = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/user`);
            if (!users) return;
            let loadedUsers = {};
            for (const user of users) {
                const previousUser = this.users ? this.users[user.userId] : null;
                if (previousUser) {
                    // Rights are loaded separately, keep the ones we already have while they refresh
                    user.rights = previousUser.rights;
                    user.objectRights = previousUser.objectRights;
                }
                loadedUsers[user.userId] = { ...user };
            }
            this.users = loadedUsers;
            for (const userId in loadedUsers) {
                this.loadUser(userId);
            }
        },

        async loadUser(userId) {
            this.loadUserRights(userId);
            this.loadUserObjectRights(userId);
        },

        async loadUserRights(userId) {
            const userRights = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/user/${userId}/right`);
            if (!userRights || !this.users[userId]) return;
            let rights = {};
            for (const userRight of userRights) {
                rights[userRight.datacenterId] = userRight;
            }
            this.users[userId]["rights"] = { ...rights };
        },

        async loadUserObjectRights(userId) {
            const userObjectRights = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/user/${userId}/objectRight`);
            if (!userObjectRights || !this.users[userId]) return;
            let objectRights = {};
            for (const userObjectRight of userObjectRights) {
                objectRights[userObjectRight.objectRightId] = userObjectRight;
            }
            this.users[userId]["objectRights"] = { ...objectRights };
        },

        async loadDatacenters() {
            const datacenters = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/datacenter`);
            if (!datacenters) return;
            let loadedDatacenters = {};
            for (const datacenter of datacenters) {
                loadedDatacenters[datacenter.datacenterId] = { ...datacenter };
            }
            this.datacenters = loadedDatacenters;
        },

        async loadTasks() {
            const tasks = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/task`);
            if (!tasks) return;
            // Only active tasks are displayed, plus the 10 most recent ones when asked for
            const recentTaskIds = window._.map(window._.orderBy(tasks, ["taskId"], ["desc"]).slice(0, 10), "taskId");
            let loadedTasks = {};
            for (const task of tasks) {
                const isActive = task.state != "done" && task.state != "canceled";
                if (!isActive && !(this.recentTasks && recentTaskIds.includes(task.taskId))) {
                    continue;
                }
                loadedTasks[task.taskId] = { ...task };
            }
            this.tasks = loadedTasks;
        },

        async loadRobots() {
            const robots = await this.getList(`${this.ovhapiRoute}/v1/dedicatedCloud/${this.pccName}/robot`);
            if (!robots) return;
            let loadedRobots = {};
            for (const robot of robots) {
                loadedRobots[robot.name] = { ...robot };
            }
            this.robots = loadedRobots;
        },

        async loadVracks() {
            const vrackNames = await this.get(`${this.ovhapiRoute}/v1/vrack`);
            for (let vrackName of vrackNames) {
                this.loadVrack(vrackName);
            }
        },

        async loadVrack(vrackName) {
            let vrack = await this.get(`${this.ovhapiRoute}/v1/vrack/${vrackName}`);
            if (!vrack) return;
            vrack["serviceName"] = vrackName;
            for (let serviceType of ["dedicatedCloud", "dedicatedCloudDatacenter"]) {
                const serviceNames = await this.get(`${this.ovhapiRoute}/v1/vrack/${vrackName}/${serviceType}`);
                if (serviceNames) {
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
            }
        },

        getTaskStateTextClass(task) {
            return "text-"+this.getTaskStateColor(task);
        },

        getTaskStateBgClass(task) {
            return "bg-"+this.getTaskStateColor(task);
        },

        getTaskStateColor(task) {
            var resultClass = "yellow-600";
            if (task.state) {
                if (task.state == "todo") {
                    resultClass = "gray-500";
                } else if (task.state == "waitingTodo") {
                    resultClass = "gray-500";
                } else if (task.state == "toCancel") {
                    resultClass = "gray-500";
                } else if (task.state == "toCreate") {
                    resultClass = "gray-500";
                } else if (task.state == "fixing") {
                    resultClass = "yellow-600";
                } else if (task.state == "doing") {
                    resultClass = "yellow-600";
                } else if (task.state == "waitingForChilds") {
                    resultClass = "yellow-600";
                } else if (task.state == "done") {
                    resultClass = "green-700";
                } else if (task.state == "canceled") {
                    resultClass = "cyan-500";
                } else if (task.state == "error") {
                    resultClass = "red-700";
                } else if (task.state == "unknown") {
                    resultClass = "red-700";
                } else {
                    resultClass = "red-700";
                }
            }
            return resultClass;
        },

        getUserStateClass(user) {
            var resultClass = "text-yellow-600";
            if (user.state) {
                if (user.state == "creating") {
                    resultClass = "text-yellow-600";
                } else if (user.state == "deleting") {
                    resultClass = "text-yellow-600";
                } else if (user.state == "delivered") {
                    resultClass = "text-green-700";
                } else if (user.state == "error") {
                    resultClass = "text-red-700";
                }
            }
            return resultClass;
        },

        getUserAccessStateClass(access) {
            var resultClass = "text-yellow-600";
            if (access) {
                if (access == "admin") {
                    resultClass = "text-green-700";
                } else if (access == "readwrite") {
                    resultClass = "text-green-700";
                } else if (access == "manager") {
                    resultClass = "text-green-700";
                } else if (access == "readonly") {
                    resultClass = "text-yellow-600";
                } else if (access == "noAccess") {
                    resultClass = "text-red-700";
                } else if (access == "disabled") {
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

.task-progress {
    position: absolute;
    bottom: 0;
    height: 2px;
    width: 100%;
    margin: 0 -0.25rem;
}

.task-progress .progress-bar {
    height: 2px;
}
</style>
