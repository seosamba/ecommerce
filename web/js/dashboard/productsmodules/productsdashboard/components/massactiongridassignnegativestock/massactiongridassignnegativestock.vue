<template>
    <div v-show="loadedScreen" id="assign-product-negativestock-dialog" style="width:25%;">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionProductNegativeStock'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="assign-product-negativestock" id="assign-product-negativestock" action="">
          <p class="grid_12">
            <select v-model="selectedStock" name="negativestock" class="negativestock">
              <option value="" disabled>{{$t('message.select')}}</option>
              <option v-for="nStock in productNegativeStock" :value="nStock.value">{{$t('message.'+nStock.name)}}</option>
            </select>
          </p>
          <p class="grid_12">
            <span v-if="processedElBlock === true" id="mass-process-products-quantity-block" class="grid_12 alpha mt10px">
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
              <input v-model="matchingFilter" @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allNegativeStockFilterProducts" type="checkbox" name="allNegativeStockFilterProducts" value="0"/>
            </label>
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassignnegativestock.js"/>




