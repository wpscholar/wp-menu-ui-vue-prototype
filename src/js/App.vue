<template>
	<div class="vue-menus">

		<Notice type="success" :message="successMessage" v-if="successMessage"/>
		<Notice type="error" :message="errorMessage" v-if="errorMessage"/>

		<div class="vue-menus__header">
			Select a template to edit:
			<select v-model="activeMenuId">
				<option value="0">Select</option>
				<option v-for="menu in menus" :value="menu.term_id">{{menu.name}}</option>
			</select>
			<span> or <a href="#" @click.prevent="onCreateMenu">create a new menu</a>.</span>
		</div>
		<div id="nav-menus-frame" class="wp-clearfix">
			<div id="menu-settings-column" class="metabox-holder"
				 :class="{'metabox-holder-disabled': isDisabled}">
				<Accordion>
					<AccordionSection title="Custom Links" open="open">
						<AddCustomLink :isDisabled="isDisabled" :onAdd="onAddMenuItem"/>
					</AccordionSection>
					<AccordionSection v-for="postType in postTypes" :title="postType.name" :key="postType.name">
						<AddPostLink :fetch="fetch" :isDisabled="isDisabled" :onAdd="onAddMenuItem"
									 :postType="postType"/>
					</AccordionSection>
					<AccordionSection v-for="taxonomy in taxonomies" :title="taxonomy.name" :key="taxonomy.name">
						<AddTaxonomyLink :fetch="fetch" :isDisabled="isDisabled" :onAdd="onAddMenuItem"
										 :taxonomy="taxonomy"/>
					</AccordionSection>
				</Accordion>
			</div>
			<div id="menu-management-liquid" style="border: 1px solid #e5e5e5;">
				<div class="vue-menus__main-header">
                    <span><em>Menu Name</em>
                        <input id="menu-name" type="text" class="menu-name regular-text menu-item-textbox"
							   v-model="activeMenuName"/>
                    </span>
					<button type="button" class="button button-primary" @click="onSaveMenu">{{saveMenuText}}</button>
				</div>
				<div class="vue-menus__main-content">
					<p v-if="activeMenuId === 0">Give your menu a name, then click Create Menu.</p>
					<div v-else>
						<h3>Menu Structure</h3>
						<div>
							<p>
								Drag each item into the order you prefer. Click the arrow on the right of the item to
								reveal additional configuration options.
							</p>
						</div>
					</div>
					<Draggable class="vue-menus__menu-items nav-menus-php" v-model="menuItems" @change="onReorder">
						<MenuItem
							v-for="(menuItem, index) in menuItems"
							:key="index"
							:menuItem="menuItem"
							:onRemove="onRemoveMenuItem"
						/>
					</Draggable>
					<div class="menu-settings">
						<h3>Menu Settings</h3>
						<fieldset class="menu-settings-group auto-add-pages">
							<legend class="menu-settings-group-name howto">Auto add pages</legend>
							<div class="menu-settings-input checkbox-input">
								<input type="checkbox" name="auto-add-pages" id="auto-add-pages"
									   v-model="menuSettings['auto-add-pages']">&nbsp;
								<label for="auto-add-pages">Automatically add new top-level pages to this menu</label>
							</div>
						</fieldset>
						<fieldset v-if="Object.keys(menuLocations).length > 0"
								  class="menu-settings-group menu-theme-locations">
							<legend class="menu-settings-group-name howto">Display location</legend>
							<div class="menu-settings-input checkbox-input" v-for="menuLocation in menuLocations">
								<input type="checkbox" :id="`locations-${menuLocation.slug}`" :name="menuLocation.slug"
									   :value="menuLocation.id" :checked="menuLocation.id === activeMenuId && activeMenuId !== 0"/>
								<label :for="`locations-${menuLocation.slug}`">{{menuLocation.label}}</label>
								<span v-if="menuLocation.id && menuLocation.id !== activeMenuId"
									  class="theme-location-set">(Currently set to: {{getNavMenuNameById(menuLocation.id)}})
								</span>
							</div>
						</fieldset>
					</div>
				</div>
				<div class="vue-menus__main-footer">
					<a v-if="activeMenuId" href="#" class="delete" @click.prevent="onDeleteMenu">Delete Menu</a>
					<span v-else></span>
					<button type="button" class="button button-primary" @click="onSaveMenu">{{saveMenuText}}</button>
				</div>
			</div>
		</div>

	</div>
</template>

<script>

	import Draggable from 'vuedraggable';
	import { find, findIndex, first, map, reject, without } from 'lodash';

	import Accordion from './components/Accordion.vue';
	import AccordionSection from './components/AccordionSection.vue';
	import AddCustomLink from './components/AddCustomLink.vue';
	import AddPostLink from './components/AddPostLink.vue';
	import AddTaxonomyLink from './components/AddTaxonomyLink.vue';
	import MenuItem from './components/MenuItem.vue';
	import Notice from './components/Notice.vue';

	/**
	 * Sort by a specific property name.
	 *
	 * @param property
	 * @returns {function(*, *): number}
	 */
	function sortBy(property) {

		/**
		 * Comparison function for sorting an array of items by a specific property.
		 * @param a
		 * @param b
		 * @returns {number}
		 */
		return function sortBy(a, b) {
			return (a[property] < b[property]) ? -1 : ((a[property] > b[property]) ? 1 : 0);
		}
	}


	export default {
		props: {
			fetch: Function
		},
		created() {
			this.loadMenus();
			this.loadPostTypes();
			this.loadTaxonomies();
			this.loadMenuLocations();
		},
		components: {
			Accordion,
			AccordionSection,
			AddCustomLink,
			AddPostLink,
			AddTaxonomyLink,
			Draggable,
			MenuItem,
			Notice
		},
		data() {
			return {
				activeMenuId: 0,
				activeMenuName: '',
				errorMessage: '',
				menus: [],
				menuItems: [],
				menuSettings: {},
				menuLocations: [],
				postTypes: [],
				successMessage: '',
				taxonomies: [],
			}
		},
		computed: {
			isDisabled() {
				return this.activeMenuId === 0;
			},
			saveMenuText() {
				return this.activeMenuId ? 'Save Menu' : 'Create Menu';
			}
		},
		methods: {
			loadMenus() {
				this.fetch( {path: '/wp/v2/menus'} )
					.then(
						(data) => {
							this.menus = data.sort( sortBy( 'name' ) );

							// If URL param is set, load correct menu
							const params = {};
							location.search.substr( 1 ).split( '&' ).forEach( param => {
								let key, value;
								[key, value] = param.split( '=' );
								params[key] = value;
							} );
							if (params.hasOwnProperty( 'menu_id' )) {
								const menuId = parseInt( params.menu_id, 10 );
								const menuIndex = findIndex( this.menus, {term_id: menuId} );
								if (menuIndex >= 0) {
									this.activeMenuId = menuId;
									return;
								}
							}
							this.setDefaultMenu();
						},
						() => this.errorMessage = 'Something went wrong. Please reload the page. If the issue persists, contact a site administrator.',
					);
			},
			loadMenuItems() {
				this.fetch( {path: `/wp/v2/menus/${this.activeMenuId}/items`} )
					.then(
						(data) => this.menuItems = data.sort( sortBy( 'menu_order' ) ),
						() => this.errorMessage = 'Something went wrong. Please reload the page. If the issue persists, contact a site administrator.'
					);
			},
			loadMenuSettings() {
				this.fetch( {path: `/wp/v2/menus/${this.activeMenuId}/settings`} )
					.then(
						(data) => this.menuSettings = data,
						() => this.errorMessage = 'Something went wrong. Please reload the page. If the issue persists, contact a site administrator.'
					);
			},
			loadMenuLocations() {
				this.fetch( {path: '/wp/v2/menus/locations'} )
					.then(
						(data) => this.menuLocations = data,
						() => this.errorMessage = 'Something went wrong. Please reload the page. If the issue persists, contact a site administrator.'
					);
			},
			loadPostTypes() {
				this.fetch( {path: '/wp/v2/types'} )
					.then(
						(data) => {
							// TODO: Clean this up on the API side?
							this.postTypes = reject( data, ({slug}) => {
								return slug === 'post' || slug === 'attachment';
							} );
						},
						() => this.errorMessage = 'Something went wrong. Please reload the page. If the issue persists, contact a site administrator.',
					);
			},
			loadTaxonomies() {
				this.fetch( {path: '/wp/v2/taxonomies'} )
					.then(
						(data) => this.taxonomies = data,
						() => this.errorMessage = 'Something went wrong. Please reload the page. If the issue persists, contact a site administrator.',
					);
			},
			createMenu() {
				this.fetch( {
					method: 'POST',
					path: `/wp/v2/menus/`,
					data: {
						'menu-name': this.activeMenuName,
						'menu-items': this.menuItems.map( this.transformMenuItem ),
					}
				} )
					.then(
						(response) => {
							this.menus.push( response );
							this.menus.sort( sortBy( 'name' ) );
							this.updateMenuLocations( response.term_id );
							this.saveMenuSettings( response.term_id );
							this.activeMenuId = response.term_id;
							this.setSuccessMessage( `Menu "${response.name}" created!` );
						},
						(response) => {
							console.log( response );
							this.setErrorMessage( 'Sorry, we were unable to create your menu.' );
						}
					);
			},
			createMenuItem(data) {
				this.fetch( {
					method: 'POST',
					path: `/wp/v2/menus/${this.activeMenuId}/items`,
					data: this.transformMenuItem( data ),
				} )
					.then(
						(response) => {
							this.menuItems.push( response );
							this.menuItems.sort( sortBy( 'menu_order' ) );
						},
						(response) => {
							console.log( response );
							this.setErrorMessage( 'Sorry, we were unable to create your menu item.' );
						}
					);
			},
			saveMenu() {
				this.fetch( {
					method: 'POST',
					path: `/wp/v2/menus/${this.activeMenuId}`,
					data: {
						'menu-name': this.activeMenuName,
						'menu-items': this.menuItems.map( this.transformMenuItem ),
					}
				} )
					.then(
						(response) => {
							const index = findIndex( this.menus, {term_id: response.term_id} );
							this.menus[index] = response;
							this.menus.sort( sortBy( 'name' ) );
							this.updateMenuLocations( response.term_id );
							this.saveMenuSettings( response.term_id );
							this.activeMenuId = response.term_id;
							this.setSuccessMessage( `Menu "${response.name}" saved!` );
						},
						(response) => {
							console.log( response );
							this.setErrorMessage( 'Sorry, we were unable to save your menu.' );
						}
					);
			},
			saveMenuSettings(menuId) {
				this.fetch( {
					method: 'POST',
					path: `/wp/v2/menus/${menuId}/settings`,
					data: this.menuSettings
				} )
					.then(
						(response) => {
						},
						(response) => {
							console.log( response );
							this.setErrorMessage( 'Sorry, we were unable to save your menu.' );
						}
					);
			},
			deleteMenu() {
				this.fetch( {
					method: 'DELETE',
					path: `/wp/v2/menus/${this.activeMenuId}`
				} )
					.then(
						() => {
						},
						(response) => {
							if (response.code) {
								this.setErrorMessage( 'Sorry, we were unable to delete your menu.' );
							} else {
								this.menus = without( this.menus, find( this.menus, {term_id: response.term_id} ) );
								this.setDefaultMenu();
								this.setSuccessMessage( `Menu "${response.name}" deleted!` );
							}
						}
					);
			},
			deleteMenuItem(id) {
				this.fetch( {
					method: 'DELETE',
					path: `/wp/v2/menus/${this.activeMenuId}/items/${id}`
				} )
					.then(
						() => {
						},
						(response) => {
							if (response.code) {
								this.setErrorMessage( 'Sorry, we were unable to delete your menu item.' );
							} else {
								this.menuItems = without( this.menuItems, find( this.menuItems, {ID: response.ID} ) );
							}
						}
					);
			},
			onAddMenuItem(menuItem) {
				this.createMenuItem( menuItem );
			},
			onCreateMenu() {
				this.activeMenuId = 0;
				this.clearMessages();
			},
			onDeleteMenu() {
				this.deleteMenu();
			},
			onSaveMenu() {
				this.activeMenuId ? this.saveMenu() : this.createMenu();
			},
			onRemoveMenuItem(id) {
				const menuItem = find( this.menuItems, {ID: id} );
				if (menuItem.post_status === 'draft') {
					// Cleanup draft menu items as we go
					this.deleteMenuItem( id );
				} else {
					this.menuItems = without( this.menuItems, menuItem );
				}
			},
			onReorder() {
				this.menuItems.map( (item, index) => this.menuItems[index].menu_order = index + 1 );
			},
			clearMessages() {
				this.errorMessage = '';
				this.successMessage = '';
			},
			getNavMenuById(id) {
				return find( this.menus, {'term_id': id} );
			},
			getNavMenuNameById(id) {
				const menu = this.getNavMenuById( id );
				return menu && menu.hasOwnProperty( 'name' ) ? menu.name : '';
			},
			setErrorMessage(msg) {
				this.clearMessages();
				this.errorMessage = msg;
			},
			setSuccessMessage(msg) {
				this.clearMessages();
				this.successMessage = msg;
			},
			setDefaultMenu() {
				const menu = first( this.menus );
				this.activeMenuId = menu ? menu.term_id : 0;
			},
			setMenuLocation(slug, id) {
				this.fetch( {
					method: 'POST',
					path: `/wp/v2/menus/locations/${slug}`,
					data: {id}
				} )
					.then(
						(response) => {
							if (response.hasOwnProperty( 'slug' )) {
								this.menuLocations[response.slug] = response;
							}
						},
						(response) => {
							console.log( response );
							this.setErrorMessage( 'Sorry, we were unable to save your menu.' );
						}
					);
			},
			transformMenuItem(menuItem) {
				// Prepare menu item object for sending via REST API
				const item = {};
				const keyMap = {
					ID: 'id',
					attr_title: 'menu-item-attr-title',
					classes: 'menu-item-classes',
					db_id: 'menu-item-db-id',
					description: 'menu-item-description',
					object: 'menu-item-object',
					object_id: 'menu-item-object-id',
					post_parent: 'menu-item-parent-id',
					menu_order: 'menu-item-position',
					post_status: 'menu-item-status',
					target: 'menu-item-target',
					title: 'menu-item-title',
					type: 'menu-item-type',
					url: 'menu-item-url',
					xfn: 'menu-item-xfn',
				};
				const valueMap = {
					classes(value) {
						return value.join( ' ' );
					}
				};
				Object.entries( menuItem ).forEach( ([key, value]) => {
					if (keyMap.hasOwnProperty( key )) {
						item[keyMap[key]] = valueMap.hasOwnProperty( key ) ? valueMap[key]( value ) : value;
					}
				} );
				return item;
			},
			updateMenuLocations(menuId) {
				Array.from( this.$el.querySelectorAll( '.menu-settings .menu-theme-locations input[type="checkbox"]' ) ).forEach( (input) => {
					const slug = input.getAttribute( 'name' );
					const id = parseInt( input.getAttribute( 'value' ), 10 );
					const checked = input.checked;
					if (!checked && id === menuId) {
						this.setMenuLocation( slug, 0 );
					} else if (checked) {
						this.setMenuLocation( slug, menuId );
					}
				} );
			}
		},
		watch: {
			activeMenuId(id) {
				if (id > 0) {
					const menu = find( this.menus, {term_id: id} );
					this.activeMenuName = menu.name;
					this.loadMenuItems();
					this.loadMenuSettings();
				} else {
					this.activeMenuName = '';
					this.menuItems = [];
				}
			}
		}
	}

</script>
