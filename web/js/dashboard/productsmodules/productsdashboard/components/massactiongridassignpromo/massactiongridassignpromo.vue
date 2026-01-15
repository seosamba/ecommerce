<template>
    <div v-show="loadedScreen" id="assign-product-promo-dialog" style="width:540px;height:380px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignPromo'))}} ({{$t('message.dialogTitleTotal')}}
          {{itemsQuantity}} <span v-if="itemsQuantity === 1">{{$t('message.titleRecord')}}</span>
          <span v-if="itemsQuantity > 1">{{$t('message.titleRecords')}}</span>)
        </span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12 mt10px" method="POST" name="assign-product-promo" id="assign-product-promo" action="" style="height: 520px">
          <fieldset class="background">
            <legend class="large background">{{$t('message.setSelectedProductsOnSale')}}:</legend>
            <p>
              <label class="mt5px grid_5 alpha" for="promo-price">{{$t('message.salePrice')}} (<span id="promo-price-unit">{{currencyInfo.currency}}</span>):</label>
              <input v-model="promoPrice" id="promo-price" autocomplete="off" class="grid_4 omega"  name="promo-price" type="text" >
              <label class="mt5px labeled grid_3 text-center omega">
                <span>{{$t('message.time')}}</span><i class="tooltip info ticon-question-sign icon16 mb5px ml3" :title="$t('message.salesTimeInfo')"></i>
              </label>
            </p>
            <p>
              <label class="mt5px grid_5 alpha">{{$t('message.startingDate')}}:</label>
              <label class="labeled icon right grid_4 omega">
                <div class="labeled icon right">
                  <VueDatePicker class="promo-from" id="promo-from" @update:model-value="selectedDatepickerPromoDate($event, 'promo-from')" :locale="locale" :key="componentKey" :clearable="false" auto-apply v-model="promoFrom" :enable-time-picker="false" format="dd-MMM-yyyy" :max-date="this.maxDateCreationDate" ></VueDatePicker>
                </div>
                <i class="ticon-calendar icon16"></i>
              </label>
              <label class="mt5px labeled text-center grid_3">
                <span>00.00</span>
              </label>
            </p>
            <p>
              <label class="mt5px grid_5 alpha">{{$t('message.expiryDate')}}:</label>
              <label class="labeled icon right grid_4 omega">
                <VueDatePicker class="promo-due" id="promo-due" @update:model-value="selectedDatepickerPromoDate($event, 'promo-due')" :locale="locale" :key="componentKey" :clearable="false" auto-apply v-model="promoDue" :enable-time-picker="false" format="dd-MMM-yyyy" :min-date="this.minDateCreationDate" ></VueDatePicker>
                <i class="ticon-calendar icon16"></i>
              </label>
              <label class="mt5px labeled text-center grid_3">
                <span>23.59</span>
              </label>
            </p>
          </fieldset>
          <p class="grid_12">
            <label class="fl-left mt10px pointer matching-filter">{{$t('message.dialogAllProductsMatchingFilter')}}
              <input @change.prevent="countProducts($event)" id="assign-price-all-products-matching-filter" class="allPromoFilterProducts" type="checkbox" name="allPromoFilterProducts" value="0"/>
            </label>
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6 green" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassignpromo.js"/>




