<template>
    <div :id="`menu-item-${menuItem.ID}`"
         :class="{'menu-item': true, 'menu-item-depth-0': true, [`menu-item-${menuItem.object}`]: true, 'menu-item-edit-active': this.active, 'menu-item-edit-inactive': ! this.active}"
         style="display: table; margin: 1em 0;">

        <div class="menu-item-bar" style="margin: 0;">
            <div class="menu-item-handle">
                <span class="item-title">
                    <span class="menu-item-title">{{menuItem.title}}</span>
                </span>
                <span class="item-controls">
                    <span class="item-type">{{menuItem.type_label}}</span>
                    <a class="item-edit" href="#" @click="toggle"><span class="screen-reader-text">Edit</span></a>
                </span>
            </div>
        </div>

        <div class="menu-item-settings wp-clearfix" v-if="active">
            <p class="field-url description description-wide" v-if="menuItem.object === 'custom'">
                <label>
                    URL<br>
                    <input type="text" class="widefat code edit-menu-item-url" v-model="menuItem.url">
                </label>
            </p>
            <p class="description description-wide">
                <label>
                    Navigation Label<br>
                    <input type="text" class="widefat edit-menu-item-title" v-model="menuItem.title">
                </label>
            </p>
            <p class="field-title-attribute field-attr-title description description-wide">
                <label>
                    Title Attribute<br>
                    <input type="text" class="widefat edit-menu-item-attr-title" v-model="menuItem.attr_title">
                </label>
            </p>
            <p class="field-link-target description">
                <label>
                    <input type="checkbox" v-model="menuItem.target" true-value="_blank" false-value=""/>
                    Open link in a new tab
                </label>
            </p>
            <p class="field-css-classes description description-thin">
                <label>
                    CSS Classes (optional)<br>
                    <input
                            type="text"
                            class="widefat code edit-menu-item-classes"
                            v-bind:value="menuItem.classes.join(' ')"
                            v-on:input="menuItem.classes = $event.target.value.split(' ')"
                    />
                </label>
            </p>
            <p class="field-xfn description description-thin">
                <label>
                    Link Relationship (XFN)<br>
                    <input type="text" class="widefat code edit-menu-item-xfn" v-model="menuItem.xfn">
                </label>
            </p>
            <p class="field-description description description-wide">
                <label>
                    Description<br>
                    <textarea class="widefat edit-menu-item-description" rows="3" cols="20"
                              v-model="menuItem.description"></textarea>
                    <span class="description">The description will be displayed in the menu if the current theme supports it.</span>
                </label>
            </p>

            <div class="menu-item-actions description-wide submitbox">
                <p class="link-to-original" v-if="menuItem.type !== 'custom'">
                    Original: <a :href="menuItem.url" target="_blank">{{menuItem.post_title}}</a>
                </p>
                <a class="item-delete submitdelete deletion" href="#" @click="onRemoveMenuItem">Remove</a>
                <span class="meta-sep"> | </span>
                <a class="item-cancel submitcancel" href="#" @click="toggle">Cancel</a>
            </div>
        </div><!-- .menu-item-settings-->
    </div>
</template>

<script>
    export default {
        props: {
            menuItem: Object,
            onRemove: Function
        },
        data() {
            return {
                active: false,
            }
        },
        methods: {
            onRemoveMenuItem(e) {
                e.preventDefault();
                this.onRemove(this.menuItem.ID);
            },
            toggle(e) {
                e ? e.preventDefault() : null;
                this.active = !this.active;
            }
        }
    }
</script>