<template>
    <div class="errors-zone w-2/3 sm:w-2/3 lg:w-1/3">
        <transition-group name="errors-zone">
            <template v-for="(error, errorKey) in errors">
                <div class="rounded-md bg-red-50 dark:bg-red-800 p-4 mb-4" v-if="!dismissed.includes(errorKey)" :key="errorKey">
                    <div class="text-sm font-medium text-red-800 dark:text-red-50">
                        <div class="float-right pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" @click="dismiss(errorKey)" class="inline-flex rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600">
                                    <span class="sr-only">Dismiss</span>
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <strong v-if="error.config && error.config.url && error.config.method">
                            <i class="fas fa-circle-exclamation text-red-400 mr-1"></i>
                            {{ error.config.method.toUpperCase() }} {{ error.config.url }}
                        </strong><br />
                        <template v-if="error.message">
                            {{ error.message }}
                        </template>
                        <template v-if="error.errors">
                            <ul>
                                <li v-for="(errorsError, index) in error.errors" :key="index">{{ errorsError[0] }}</li>
                            </ul>
                        </template>
                    </div>
                </div>
            </template>
        </transition-group>
    </div>
</template>

<script>
export default {
    name: "ErrorsZone",

    props: ["errors"],

    data() {
        return {
            dismissed: [],
        };
    },

    methods: {
        dismiss(errorKey) {
            this.dismissed.push(errorKey);
        },
    },

    watch: {
        errors(newValue, oldValue) {
            this.dismissed = [];
        },
    },
};
</script>

<style lang="scss" scoped>
.errors-zone {
    position: fixed;
    top: 100px;
    right: -3px;
    color: white;
    z-index: 9999;
    border-top-left-radius: 5px;
    opacity: 1;
}

.errors-zone-enter-active {
    transition: right 0.1s linear;
}
.errors-zone-leave-active {
    transition: opacity right 0.5s ease;
}
.errors-zone-enter,
.errors-zone-leave-to {
    opacity: 0;
    right: -220px;
}
</style>
