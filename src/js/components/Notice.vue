<template>
    <div :class="{ notice: true, [`notice-${type}`]: true }">
        <p>{{message}}</p>
    </div>
</template>

<script>
    export default {
        props: {
            message: String,
            type: String,
        },
    }
</script>