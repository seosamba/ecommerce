<template>
    <div class="grid_12 alpha omega">
        <ul class="paginator-list list-unstyled grid_9">
            <li class="first-page" v-if="pagerState(sectionName).currentPage !== 1">
                <a href="#" @click.prevent="pageChanged(1)" >
                    <span aria-hidden="true">First</span>
                </a>
            </li>

            <li class="previous-page" v-if="pagerState(sectionName).currentPage !== 1">
                <a href="#" @click.prevent="pageChanged(pagerState(sectionName).currentPage - 1)" >
                    <span aria-hidden="true">Previous</span>
                </a>
            </li>

            <li v-if="paginationRange[0] > 1">
                ...
            </li>

            <li v-for="n in paginationRange" :class="activePage(n)">
                <a class="pagination__item" href="#" @click.prevent="pageChanged(n)">{{ n }}</a>
            </li>
            <li v-if="paginationRange[paginationRange.length - 1] < lastPage">
                ...
            </li>
            <li class="next-page" v-if="pagerState(sectionName).currentPage < lastPage">
                <a href="#" @click.prevent="pageChanged(pagerState(sectionName).currentPage +1)" >
                    <span aria-hidden="true">Next</span>
                </a>
            </li>
            <li class="last-page" v-if="pagerState(sectionName).currentPage < lastPage">
                <a href="#" @click.prevent="pageChanged(lastPage)" >
                    <span aria-hidden="true">Last</span>
                </a>
            </li>
        </ul>
        <p class="grid_3 mt15px text-right">Total <span class="text-bold"> {{$store.state.pagination[sectionName].totalItems}} records</span> found</p>
    </div>
</template>
<script src="./controller/index.js"/>