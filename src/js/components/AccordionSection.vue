<template>
    <li class="control-section accordion-section" :class="{open: this.expanded}">
        <h3 class="accordion-section-title hndle" v-on:click="toggle">{{title}}</h3>
        <div class="accordion-section-content" v-show="expanded">
            <div class="inside">
                <slot></slot>
            </div>
        </div>
    </li>
</template>

<script>
    export default {
        props: {
            open: Boolean,
            title: String,
        },
        data() {
            return {
                expanded: this.open || false,
            };
        },
        methods: {
            toggle: function () {
                this.expanded = !this.expanded;
            }
        }
    }
</script>
