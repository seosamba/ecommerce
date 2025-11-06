<template>
    <div v-if="loadedScreen === true" id="product-block-detailed-view">
        <div id="product-detailed-view-general-info" class="wrap">
          <div class="flex-container flex-wrap f-stretch grid_12 alpha omega" id="product-profile-details-block">
            <div class="profile-details-block white-box grid_7">
              <div class="grid-back-link" id="product-grid-back-link">
                <a @click="backToGrid" href="javascript:;" id="produuct-grid-back"><span class="icon-arrow-left">{{$t('message.backLink')}}</span></a>
              </div>
            </div>
          </div>
        </div>
        <router-view :key="$route.path"></router-view>
    </div>
</template>

<script src="./controller/productinfo.js"/>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>




