<template>
    <div class="add-menu-item-pagelinks" v-if="totalPages > 1">
        <a v-if="previousPage" class="prev page-numbers" href="#" @click.prevent="onClick(previousPage)">
            <span aria-label="Previous page">«</span>
        </a>
        <template v-for="page in totalPages">
            <template v-if="page < currentPage && page >= (currentPage - padding)">
                <a class="page-numbers" href="#" @click.prevent="onClick(page)">
                    <span class="screen-reader-text">Page</span> {{page}}
                </a>
            </template>
            <template v-if="page === currentPage">
                <span class="screen-reader-text">Page</span> {{page}}
            </template>
            <template v-if="page > currentPage && page <= (currentPage + padding)">
                <a class="page-numbers" href="#" @click.prevent="onClick(page)">
                    <span class="screen-reader-text">Page</span> {{page}}
                </a>
            </template>
        </template>
        <a v-if="nextPage" class="next page-numbers" href="#" @click.prevent="onClick(nextPage)">
            <span aria-label="Next page">»</span>
        </a>
    </div>
</template>

<script>
    export default {
        props: {
            onChange: Function,
            totalPages: Number,
        },
        data() {
            return {
                currentPage: 1,
                padding: 3,
            };
        },
        computed: {
            nextPage() {
                let next = this.currentPage + 1;
                if (next > this.totalPages) {
                    next = null;
                }
                return next;
            },
            previousPage() {
                let previous = this.currentPage - 1;
                if (previous < 1) {
                    previous = null;
                }
                return previous;
            }
        },
        methods: {
            isCurrentPage(page) {
                return page === this.currentPage;
            },
            onClick(page) {
                this.currentPage = parseInt(page, 10);
            }
        },
        watch: {
            currentPage(page) {
                this.onChange(page);
            }
        }
    };
</script>