<template>
    <div v-show="loadedScreen" id="assign-product-tag-dialog">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignTag'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <fieldset class="grid_12 mt15px">
        <ul class="list-unstyled grid_12 column_3 scroll" style="height:285px;">
          <li v-for="tag in productTags">
            <label>
              <input type="checkbox" @change="markTag($event, tag.id, tag.name)" :name="tag.name" id="" :value="tag.id" :checked="(this.usedTags[tag.name])">
              <span>{{tag.name}}</span>
            </label>
          </li>
        </ul>
      </fieldset>
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
        <label class="grid_5 ml10px">{{$t('message.dialogAllProductsMatchingFilter')}}
          <input v-model="matchingFilter" @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allTagsFilterProducts" type="checkbox" name="allTagsFilterProducts" value="0"/>
        </label>
      </p>
      <p class="grid_3 fl-right mt10px">
        <input @click.prevent="submitRegularForm" type="submit" class="btn grid_12" :value="$t('message.apply')">
      </p>
    </div>
</template>

<script src="./controller/massactiongridassigntag.js"/>




