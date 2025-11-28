<template>
    <div v-show="loadedScreen" id="product-delete-dialog" style="width:30%;height:260px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionDelProduct'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="delete-product" id="delete-product" action="">
          <p class="message info text-center larger">
            <span class="ui-state-error-text">{{$t('message.areYouSure')}} {{$t('message.thisOperationCannotBeUndone')}}</span>
          </p>
          <p class="grid_12">
            <span v-if="processedElBlock === true" id="mass-process-products-quantity-block" class="grid_12 alpha mt20px">
                <span v-if="origProcessed === true" class="orig-processed text-center grid_12"> <span class="mass-process-products-quantity"> {{itemsProcessed+' '}}</span>
                   <span v-if="itemsQuantity === 1" class="process-products-orig-processed">{{$t('message.productDeletedSoFar')}}</span>
                   <span v-else class="process-products-orig-processed">{{$t('message.productsDeletedSoFar')}}</span>
                </span>
                <span v-if="endProcessed === true" class="end-processed text-center grid_12"> {{$t('message.completed')}}. <span class="mass-process-products-quantity"> {{itemsProcessed+' '}} </span>
                   <span v-if="itemsQuantity === 1" class="process-products-end-processed">{{$t('message.productHasBeenDeletedLabel')}}</span>
                   <span v-else class="process-products-end-processed">{{$t('message.productsHaveBeenDeleted')}}</span>
                </span>
            </span>
            <label class="grid_12 alpha mt10px mb5px pointer matching-filter">{{$t('message.dialogAllProductsMatchingFilter')}}
              <input v-model="matchingFilter" @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allDeleteFilterProducts" type="checkbox" name="allDeleteFilterProducts" value="0"/>
            </label>
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.yes'))">
            <input @click.prevent="closeMassAction" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.no'))">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongriddeleteproduct.js"/>




