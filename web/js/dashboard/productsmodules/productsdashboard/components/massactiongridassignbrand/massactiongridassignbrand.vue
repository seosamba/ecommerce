<template>
    <div v-show="loadedScreen" id="assign-product-brand-dialog" style="width:25%;">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignBrand'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="assign-product-brand" id="assign-product-brand" action="">
          <p class="grid_12">
            <select v-model="selectedBrand" name="brands" class="brands">
              <option value="0" disabled>{{$t('message.selectProductBrand')}}</option>
              <option v-for="brand in productBrands" :value="brand.name">{{brand.name}}</option>
            </select>
          </p>
          <p class="grid_12">
            <input v-model="newBrand" id="newbrand" name="newbrand" type="text" :placeholder="$t('message.orCreateNew')" />
          </p>
          <p>
            <span v-if="processedElBlock === true" id="mass-process-products-quantity-block" class="grid_12 alpha mt20px">
                <span v-if="origProcessed === true" class="orig-processed text-center grid_12"> <span class="mass-process-products-quantity"> {{itemsProcessed+' '}}</span>
                   <span v-if="itemsQuantity === 1" class="process-products-orig-processed">{{$t('message.productProcessedSoFar')}}</span>
                   <span v-else class="process-products-orig-processed">{{$t('message.productsProcessedSoFar')}}</span>
                </span>
                <span v-if="endProcessed === true" class="end-processed text-center grid_12"> {{$t('message.completed')}}. <span class="mass-process-products-quantity"> {{itemsProcessed+' '}} </span>
                   <span v-if="itemsQuantity === 1" class="process-products-end-processed">{{$t('message.productHasBeenProcessed')}}</span>
                   <span v-else class="process-products-end-processed">{{$t('message.productsHaveBeenProcessed')}}</span>
                </span>
            </span>
            <label class="fl-left mt10px pointer matching-filter">{{$t('message.dialogAllProductsMatchingFilter')}}
              <input v-model="matchingFilter" @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allBrandFilterProducts" type="checkbox" name="allBrandFilterProducts" value="0"/>
            </label>
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassignbrand.js"/>




