<template>
    <div v-show="loadedScreen" class="flex-row">
        <h2 class="mt0px">{{$t('message.productTabTitle')}}</h2>
        <div id="store-products" class="flex-row flex_12 f-alpha f-omega">
          <div class="search-line clearfix">
            <span class="search-block-element grid_3 alpha omega mb20px ">
              <input v-model="searchTerm" @change="applyFilter" type="text" autocomplete="off" class="pfilter search-input" id="product-search" name="productsearch" :placeholder="$t('message.searchByNameSkuMpn')" >
              <span @click="resetSearchBar()" :class="['ticon-cancel-search clear-input',searchTerm ? '': 'hidden']"></span>
            </span>
            <div class="grid_9 omega">
              <div class="grid_4 alpha t-grid_3 t-alpha mb20px">
                <VueMultiselect @select="applyFilter" @remove="applyFilter" class="filter" :showNoOptions="false" :multiple="true" id="product-brands" name="productBrands" open-direction="bottom" v-model="filterByBrands" :options="sortByColumn(gridInfo['brands'], 'label')" label="label" track-by="id" :searchable="true" :placeholder="$t('message.selectBrand')">
                  <template v-slot:noResult><strong>{{$t('message.noResultFoundSearch')}}</strong></template>
                </VueMultiselect>
              </div>
              <div class="grid_4 alpha omega t-grid_3 mb20px">
                <VueMultiselect @select="applyFilter" @remove="applyFilter" class="filter" :showNoOptions="false" :multiple="true" id="product-tags" name="productTags" open-direction="bottom" v-model="filterByTags" :options="sortByColumn(gridInfo['tags'], 'label')" label="label" track-by="id" :searchable="true" :placeholder="$t('message.selectTag')">
                  <template v-slot:noResult><strong>{{$t('message.noResultFoundSearch')}}</strong></template>
                </VueMultiselect>
              </div>
              <div class="grid_4 omega t-grid_3 t-omega mb20px">
                <VueMultiselect @select="applyFilter" @remove="applyFilter" class="filter" :showNoOptions="false" :multiple="true" id="product-stock" name="productStock" open-direction="bottom" v-model="filterByStock" :options="sortByColumn(gridInfo['inventory'], 'label', false, true)" label="label" track-by="id" :searchable="true" :placeholder="$t('message.selectInventory')">
                  <template v-slot:noResult><strong>{{$t('message.noResultFoundSearch')}}</strong></template>
                </VueMultiselect>
              </div>
            </div>
          </div>

          <productsgridtable></productsgridtable>
        </div>

        <router-view :key="$route.path"></router-view>
    </div>
</template>

<script src="./controller/grid.js"/>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>




