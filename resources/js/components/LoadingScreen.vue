<template>
    <div class="loading-screen">
        <div class="loading-text"><i class="fas fa-circle-notch fa-spin mr-1"></i> Loading... {{ text }}</div>
    </div>
</template>

<script>
export default {
    name: "LoadingScreen",

    props: {
        text: {
            type: String,
            required: false,
        },
    },
};
</script>

<style lang="scss" scoped>
.loading-screen {
    position: fixed;
    bottom: 0;
    right: 0;
    width: 220px;
    padding: 5px;
    background-color: rgba(0, 0, 0, 0.7);
    font-size: 13px;
    line-height: 13px;
    color: white;
    z-index: 9999;
    border-top-left-radius: 5px;
}

.loading-screen-enter-active {
    transition: opacity 0.1s linear;
}
.loading-screen-leave-active {
    transition: opacity 0.5s ease;
}
.loading-screen-enter,
.loading-screen-leave-to {
    opacity: 0;
}
</style>
