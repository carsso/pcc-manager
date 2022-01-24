<template>
    <div class="errors-zone col-10 col-sm-6 col-lg-4">
        <transition-group name="errors-zone">
            <template v-for="(error, errorKey) in errors">
                <div class="alert alert-danger alert-dismissible fade show" role="alert" v-if="!dismissed.includes(errorKey)" :key="errorKey">
                    <strong v-if="error.config && error.config.url && error.config.method"> {{ error.config.method.toUpperCase() }} {{ error.config.url }}<br /> </strong>
                    <template v-if="error.message">
                        {{ error.message }}
                    </template>
                    <template v-if="error.errors">
                        <ul>
                            <li v-for="(errorsError, index) in error.errors" :key="index">{{ errorsError[0] }}</li>
                        </ul>
                    </template>
                    <button type="button" class="btn-close" @click="dismiss(errorKey)" aria-label="Close"></button>
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
