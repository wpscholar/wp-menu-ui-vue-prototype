<template>
    <div class="customlinkdiv">
        <input type="hidden" value="custom" name="menu-item-type"/>
        <p id="menu-item-url-wrap" class="wp-clearfix">
            <label class="howto">URL</label>
            <input type="text" class="code menu-item-textbox" placeholder="http://" v-model="menuItem.url"/>
        </p>

        <p id="menu-item-name-wrap" class="wp-clearfix">
            <label class="howto">Link Text</label>
            <input type="text" class="regular-text menu-item-textbox" v-model="menuItem.title"/>
        </p>

        <p class="button-controls wp-clearfix">
            <span class="add-to-menu">
                <button type="button" class="button submit-add-to-menu right" :disabled="isDisabled" @click="onAddLink">Add to Menu</button>
            </span>
        </p>
    </div>
</template>

<script>
    export default {
        props: {
            isDisabled: Boolean,
            onAdd: Function
        },
        data() {
            return {
                menuItem: {
                    object: 'custom',
                    post_status: 'draft',
                    title: '',
                    type: 'custom',
                    url: '',
                }
            }
        },
        methods: {
            onAddLink() {
                this.onAdd(this.menuItem);
            }
        }
    }
</script>