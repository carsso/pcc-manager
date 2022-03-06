<template>
    <div class="pcc-options-card bg-white dark:bg-gray-700 rounded-lg shadow text-center relative mt-6">
        <LoadingBtn @click="loadAll()" :loading="loading"></LoadingBtn>
        <small class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 absolute top-0 left-0 m-2">{{ (options && Object.keys(options).length) || 0 }}</small>
        <div class="p-4">
            <div class="mb-3">{{ title }}</div>

            <div class="shadow overflow-hidden border border-gray-200 dark:border-gray-800 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-xs font-medium text-gray-500 dark:text-gray-300 tracking-wider">
                        <tr>
                            <th scope="col" class="px-1 py-2">State</th>
                            <th scope="col" class="px-1 py-2">Name</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm bg-white dark:bg-gray-700">
                        <tr v-if="!options">
                            <td colspan="2" class="p-4"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading from OVHcloud API...</td>
                        </tr>
                        <tr v-else-if="!Object.keys(options).length">
                            <td colspan="2" class="p-4">
                                <i>No sector-specific compliance option enabled</i>
                            </td>
                        </tr>
                        <tr v-for="(option, optionIdx) in window._.orderBy(window._.values(options), ['compatible', 'state', 'name'], ['desc', 'desc', 'asc'])" :key="option.name" :class="optionIdx % 2 === 0 ? 'bg-white dark:bg-gray-700' : 'bg-gray-100 dark:bg-gray-800'">
                            <td>
                                <span class="text-gray-500" v-if="option.state == 'disabled' && option.compatible === false" :title="`Incompatibility reason: ${option.reason && option.reason.message}`">
                                    <i class="fas fa-circle"></i>
                                    incompatible
                                </span>
                                <span :class="getOptionStateClass(option)" v-else>
                                    <i class="fas fa-circle"></i>
                                    {{ option.state }}
                                </span>
                            </td>
                            <td>
                                {{ option.description }}
                                <small class="text-gray-500"> ({{ option.name }}) </small>
                                <span class="text-gray-500" v-if="option.version">
                                    - {{ option.version }}
                                    <template v-if="option.upgrades">
                                        <small class="text-yellow-600" v-if="option.upgrades.length > 0"><abbr :title="`Available upgrade(s): ${option.upgrades.join(', ')}`">Upgrade available</abbr></small>
                                    </template>
                                </span>
                                <table class="table table-sm table-striped table-bordered mb-0" v-if="option.suboptions">
                                    <tbody>
                                        <tr v-for="suboption in window._.orderBy(window._.values(option.suboptions), ['compatible', 'state', 'name'], ['desc', 'desc', 'asc'])" :key="suboption.id">
                                            <td>
                                                <span :class="getOptionStateClass(suboption)">
                                                    <i class="fas fa-circle"></i>
                                                    {{ suboption.state }}
                                                </span>
                                                <small class="text-gray-500"> #{{ suboption.id }} </small>
                                            </td>
                                            <td :title="getSuboptionTitle(suboption)">
                                                <template v-if="suboption.domainName">
                                                    {{ suboption.domainName }}
                                                    <small class="text-gray-500">
                                                        <template v-if="suboption.hostname">
                                                            <template v-if="suboption.port">
                                                                {{suboption.hostname}}:{{suboption.port}}
                                                            </template>
                                                            <template v-else>
                                                                {{suboption.hostname}}
                                                            </template>
                                                            ({{suboption.ip}})
                                                        </template>
                                                        <template v-else>
                                                            <template v-if="suboption.port">
                                                                {{suboption.ip}}:{{suboption.port}}
                                                            </template>
                                                            <template v-else>
                                                                {{suboption.ip}}
                                                            </template>
                                                        </template>
                                                        {{ suboption.noSsl ? '(no SSL/TLS)' : '' }}
                                                    </small>
                                                </template>
                                                <template v-else>
                                                    <template v-if="suboption.hostname">
                                                        <template v-if="suboption.port">
                                                            {{suboption.hostname}}:{{suboption.port}}
                                                        </template>
                                                        <template v-else>
                                                            {{suboption.hostname}}
                                                        </template>
                                                        ({{suboption.ip}})
                                                    </template>
                                                    <template v-else>
                                                        <template v-if="suboption.port">
                                                            {{suboption.ip}}:{{suboption.port}}
                                                        </template>
                                                        <template v-else>
                                                            {{suboption.ip}}
                                                        </template>
                                                    </template>
                                                    {{ suboption.noSsl ? '(no SSL/TLS)' : '' }}
                                                </template>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "PccOptionsCard",

    props: {
        options: {
            type: Object,
            required: false,
            default: null,
        },
        loading: {
            type: Boolean,
            required: true,
        },
        title: {
            type: String,
            required: true,
        },
        loadAll: {
            required: true,
        },
    },

    methods: {
        getOptionStateClass(option) {
            var resultClass = "text-yellow-600";
            if (option.state) {
                if (option.state == "enabled") {
                    resultClass = "text-green-700";
                } else if (option.state == "delivered") {
                    resultClass = "text-green-700";
                } else if (option.state == "error") {
                    resultClass = "text-red-700";
                } else if (option.state == "disabled") {
                    resultClass = "text-red-700";
                }
            }
            return resultClass;
        },
        getSuboptionTitle(suboption) {
            let titleParts = [];
            let keys = {
                description: "Description",
                domainName: "Domain name",
                domainAlias: "Domain alias",
                username: "Username",
                ip: "IP",
                kmsTcpPort: "KMS TCP port",
                ldapTcpPort: "LDAP TCP port",
                baseDnForUsers: "Base DN for users",
                baseDnForGroups: "Base DN for groups",
                sslThumbprint: "SSL thumbprint",
            };
            for (let key in keys) {
                if (suboption[key]) {
                    titleParts.push(`${keys[key]}: ${suboption[key]}`);
                }
            }
            return titleParts.join("\n");
        },
    },
};
</script>

<style lang="scss" scoped></style>
