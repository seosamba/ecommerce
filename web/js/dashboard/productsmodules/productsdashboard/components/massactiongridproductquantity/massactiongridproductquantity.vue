<template>
    <div v-show="loadedScreen" id="product-quantity-dialog" style="width:25%;">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionProductQuantity'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="quantity-product" id="quantity-product" action="">
          <label class="partial grid_3 alpha mt10px">
            <input @change="useInfinite($event)" class="infinite-param partial" type="checkbox" name="infinite" id="" value="">
            {{$t('message.infinite')}}
          </label>
          <div v-show="inventoryEl" class="custom-quantity-block grid_9">
            <span class="grid_2 omega mt10px">{{$t('message.or')}}</span>
            <input v-model="inventory" class="custom-quantity grid_10 alpha omega" name="custom-quantity" type="text" :title="$t('message.specifyInventoryStockLevel')" :placeholder="$t('message.specifyInventoryStockLevel')" />
          </div>
          <p class="grid_12 mt15px">
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
              <input v-model="matchingFilter" @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allInventoryFilterProducts" type="checkbox" name="allInventoryFilterProducts" value="0"/>
            </label>
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6 green" :value="ucFirstAllText($t('message.apply'))">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridproductquantity.js"/>




