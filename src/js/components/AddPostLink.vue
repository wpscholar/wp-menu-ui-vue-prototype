<template>
    <div :id="`posttype-${postType.slug}`" class="posttypediv" v-if="menuItems.length">

        <ul :id="`posttype-${postType.slug}-tabs`" class="posttype-tabs add-menu-item-tabs">
            <li :class="{tabs: currentTab === 'all'}">
                <a class="nav-tab-link" data-type="all" href="#" @click.prevent="onChangeTab('all')">View All </a>
            </li>
            <li :class="{tabs: currentTab === 'search'}">
                <a class="nav-tab-link" data-type="search" href="#" @click.prevent="onChangeTab('search')">Search</a>
            </li>
        </ul>

        <div :class="{['tabs-panel-active']: currentTab === 'all', ['tabs-panel-inactive']: currentTab !== 'all'}"
             class="tabs-panel tabs-panel-view-all tabs-panel-all">
            <ItemPageLinks :onChange="onChangePage" :totalPages="totalPages"/>
            <ul :id="`${postType.slug}checklist`" class="categorychecklist form-no-clear">
                <li v-for="(menuItem, index) in menuItems" :key="index">
                    <label class="menu-item-title">
                        <input type="checkbox" class="menu-item-checkbox" :data-index="index"> {{menuItem.title}}
                    </label>
                </li>
            </ul>
            <ItemPageLinks :onChange="onChangePage" :totalPages="totalPages"/>
        </div>

        <div :class="{['tabs-panel-active']: currentTab === 'search', ['tabs-panel-inactive']: currentTab !== 'search'}"
             class="tabs-panel tabs-panel-search">
            <form v-on:submit.prevent="loadItems">
                <p class="quick-search-wrap">
                    <label :for="`quick-search-posttype-${postType.slug}`" class="screen-reader-text">Search</label>
                    <input :id="`quick-search-posttype-${postType.slug}`" type="search" class="quick-search"
                           v-model="search" @keyup="loadItems"/>
                    <span class="spinner" v-if="showSpinner"></span>
                </p>
            </form>

            <ul :id="`${postType.slug}-search-checklist`" class="categorychecklist form-no-clear">
                <li v-for="(menuItem, index) in searchItems" :key="index">
                    <label class="menu-item-title">
                        <input type="checkbox" class="menu-item-checkbox" :data-index="index"> {{menuItem.title}}
                    </label>
                </li>
            </ul>
        </div>

        <p class="button-controls wp-clearfix">
			<span class="list-controls">
				<a :href="!isDisabled && '#'" class="select-all" role="button" @click.prevent="selectAll">Select All</a>
			</span>

            <span class="add-to-menu">
                <button type="button" class="button submit-add-to-menu right"
                        :disabled="isDisabled" @click="onAddToMenu">Add to Menu</button>
			</span>
        </p>

    </div>
    <div v-else>
        <p>No items.</p>
    </div>
</template>

<script>

    import ItemPageLinks from './ItemPageLinks.vue'

    export default {
        props: {
            fetch: Function,
            isDisabled: Boolean,
            onAdd: Function,
            postType: Object,
        },
        created() {
            this.loadItems();
        },
        components: {
            ItemPageLinks,
        },
        data() {
            return {
                currentPage: 1,
                currentTab: 'all',
                itemsPerPage: 15,
                menuItems: [],
                searchItems: [],
                search: '',
                showSpinner: false,
                totalPages: 1,
            }
        },
        methods: {
            loadItems() {
                this.showSpinner = true;
                let queryString = `?per_page=${this.itemsPerPage}`;
                switch (this.currentTab) {
                    case 'search':
                        queryString += `&search=${this.search}`;
                        break;
                    default:
                        queryString += `&page=${this.currentPage}`;
                }
                this.fetch({
                    path: `/wp/v2/${this.postType.rest_base}${queryString}`,
                    parse: false,
                })
                    .then((response) => {
                        this.totalPages = parseInt(response.headers.get('X-WP-TotalPages'), 10);
                        return response.json();
                    })
                    .then((items) => {
                        if (items.length) {
                            switch (this.currentTab) {
                                case 'search':
                                    this.searchItems = items.map(this.transformItem);
                                    break;
                                default:
                                    this.menuItems = items.map(this.transformItem);
                            }
                        }
                        this.showSpinner = false;
                    })
                    .catch((error) => {
                        console.log(error);
                    })
            },
            onAddToMenu(index) {
                Array.from(
                    this.$el.querySelectorAll(`.tabs-panel-${this.currentTab} input[type=checkbox]:checked`)
                ).forEach(
                    (el) => {
                        el.checked = false;
                        const index = parseInt(el.getAttribute('data-index'), 10);
                        const menuItem = this.currentTab === 'all' ? this.menuItems[index] : this.searchItems[index];
                        this.onAdd(menuItem);
                    }
                );
            },
            onChangePage(page) {
                this.currentPage = page;
            },
            onChangeTab(tab) {
                this.currentTab = tab;
            },
            selectAll() {
                Array.from(
                    this.$el.querySelectorAll('input[type="checkbox"]')
                ).forEach((el) => {
                    el.checked = true;
                });
            },
            transformItem(post) {
                return {
                    object: post.type,
                    object_id: post.id,
                    post_status: 'draft',
                    post_title: post.title.rendered,
                    title: post.title.rendered,
                    type: 'post_type',
                    type_title: this.postType.name,
                };
            }
        },
        watch: {
            currentPage() {
                this.loadItems();
            },
            currentTab(tab) {
                if (tab === 'search') {
                    this.searchItems = [];
                }
            }
        }
    }
</script>