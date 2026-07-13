<template>
    <div v-show="loadedScreen" id="product-price-dialog" style="width:730px;">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignProductOptionPrice'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="process-product" id="process-product" action="">
          <div class="grid_8 alpha omega product-tab-productoptions-choose btn-group mb10px prefix_4">
            <input v-model="productOptionSwitcher" class="hidden product-options-switch" id="product-tab-product" type="radio" checked="checked" name="product-tab-switch" value="product"/>
            <label class="btn" for="product-tab-product">{{$t('message.products')}}</label>
            <input v-model="productOptionSwitcher" class="hidden product-options-switch" id="product-tab-option" type="radio" name="product-tab-switch" value="option"/>
            <label class="btn" for="product-tab-option">{{$t('message.options')}}</label>
          </div>
          <div class="grid_12 alpha omega">
            <div>
              <p class="message info mt10px text-center larger">{{$t('message.attention')}}</p>
              <p :class="[productOptionSwitcher === 'option' ? 'grid_3' : 'grid_4', 'alpha']">
                <label>{{$t('message.priceWillChangeTo')}}</label>
                <input v-model="priceToChange" autocomplete="off" type="text" id="price-to-change" name="price-to-change" :placeholder="0"/>
              </p>
              <p :class="[productOptionSwitcher === 'option' ? 'grid_2' : 'grid_4', 'alpha']">
                <label>{{$t('message.priceSignModifier')}}</label>
                <select v-model="selectedPriceSign" name="price-sign" class="price-sign">
                  <option v-for="priceSign in priceModifierSign" :value="priceSign.value">{{priceSign.name}}</option>
                </select>
              </p>
              <p :class="[productOptionSwitcher === 'option' ? 'grid_2' : 'grid_4', 'alpha']">
                <label>{{$t('message.priceTypeModifier')}}</label>
                <select v-model="selectedPriceType" name="price-type" class="price-type">
                  <option v-for="priceType in priceModifierType" :value="priceType.value">{{priceType.name}}</option>
                </select>
              </p>
              <p v-if="productOptionSwitcher === 'option'" class="grid_5 alpha omega mt35px">
                <label for="minus-option-process">
                  {{$t('message.minusOptionProcess')}}
                  <i class="tooltip info ticon-question-sign icon16 mb5px ml3" :title="$t('message.minusOptionSign')"></i>
                <input v-model="minusOptionProcess" id="minus-option-process" type="checkbox" name="minus-option-process" value="0">
                </label>
              </p>
            </div>
          </div>
          <p class="grid_12">
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
              <input v-model="matchingFilter" @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allOwnerFilterProducts" type="checkbox" name="allOwnerFilterProducts" value="0"/>
            </label>
            <input :disabled="formProcessing === true" @click.prevent="submitRegularForm" type="submit" class="btn alpha grid_4 mt10px green" :value="$t('message.massActionAssignPrice')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridproductoptionprice.js"/>




