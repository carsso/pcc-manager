<template>
    <div class="pcc-options-card card mt-3 text-center">
        <div class="card-body p-3">
            <div class="card-title">
                <span class="h5">{{title}}</span>
                <small class="badge rounded-pill bg-primary position-absolute top-0 start-0 m-3">{{options && Object.keys(options).length}}</small>
                <div class="position-absolute top-0 end-0 m-3">
                    <button class="btn btn-sm badge btn-info" @click="loadAll()">
                        <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i>
                    </button>
                </div>
            </div>
            <table class="table table-sm table-striped table-bordered mb-0">
                <tbody>
                    <tr v-if="!options">
                        <td colspan="2">
                            <i class="fas fa-circle-notch fa-spin me-1"></i> Loading from OVHcloud API...
                        </td>
                    </tr>
                    <tr v-else-if="!Object.keys(options).length">
                        <td colspan="2">
                            <i>No sector-specific compliance option enabled</i>
                        </td>
                    </tr>
                    <tr v-for="option in _.orderBy(_.values(options), ['compatible', 'state', 'name'], ['desc', 'desc', 'asc'])" :key="option.name">
                        <td>
                            <span class="text-muted" v-if="option.state == 'disabled' && option.compatible === false" :title="`Incompatibility reason: ${option.reason && option.reason.message}`">
                                <i class="fas fa-circle"></i>
                                incompatible
                            </span>
                            <span :class="getOptionStateClass(option)" v-else>
                                <i class="fas fa-circle"></i>
                                {{option.state}}
                            </span>                                    </td>
                        <td>
                            {{option.description}}
                            <small class="text-muted">
                                ({{option.name}})
                            </small>
                            <span class="text-muted" v-if="option.version">
                                - {{option.version}}
                                <template v-if="option.upgrades">
                                    <small class="text-warning" v-if="option.upgrades.length > 0"><abbr :title="`Available upgrade(s): ${option.upgrades.join(', ')}`">Upgrade available</abbr></small>
                                </template>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>

export default {
    name: 'PccOptionsCard',

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
            var resultClass = 'text-warning';
            if(option.state) {
                if(option.state == 'migrating') {
                    resultClass = 'text-warning';
                } else if(option.state == 'enabling') {
                    resultClass = 'text-warning';
                } else if(option.state == 'enabled') {
                    resultClass = 'text-success';
                } else if(option.state == 'error') {
                    resultClass = 'text-danger';
                } else if(option.state == 'disabling') {
                    resultClass = 'text-warning';
                } else if(option.state == 'disabled') {
                    resultClass = 'text-black';
                }
            }
            return resultClass;
        },
    },
}
</script>

<style lang="scss" scoped>
</style>
