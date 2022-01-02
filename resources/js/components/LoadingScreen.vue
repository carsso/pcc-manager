<template>
    <div class="loading-screen">
        <div class="loading-text">
            <i class="fas fa-circle-notch fa-spin me-2"></i> Loading... {{ text }}
        </div>
    </div>
</template>

<script>
    export default {
        name: 'LoadingScreen',

        props: {
            text: {
                type: String,
                required: false,
            },
        },
    }
</script>
