<template>
    <div class="errors-zone col-10 col-sm-6 col-lg-4">
        <transition name="errors-zone">
            <div v-if="visible" class="alert alert-danger alert-dismissible fade show" role="alert">
                <template v-if="errors.message">
                    {{ errors.message }}
                </template>
                <template v-if="errors.errors">
                    <ul>
                        <template v-for="error in errors.errors">
                            <li>{{ error[0] }}</li>
                        </template>
                    </ul>
                </template>
                <button type="button" class="btn-close" @click="dismiss()" aria-label="Close"></button>
            </div>
        </transition>
    </div>
</template>

<script>
    export default {
        name: 'ErrorsZone',

        props: ['errors'],

        data() {
            return {
                dismissed: false,
            }
        },

        methods: {
            dismiss() {
                this.dismissed = true;
            },
        },

        computed: {
            hasErrors() {
                return this.errors && (this.errors.length > 0 || Object.keys(this.errors).length > 0);
            },

            visible() {
                return this.hasErrors && !this.dismissed;
            },
        },

        watch: {
            errors(newValue, oldValue) {
                this.dismissed = false;
            },
        },
    }
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
        transition: right .1s linear;
    }
    .errors-zone-leave-active {
        transition: opacity right .5s ease;
    }
    .errors-zone-enter, .errors-zone-leave-to {
        opacity: 0;
        right: -220px;
    }
</style>

