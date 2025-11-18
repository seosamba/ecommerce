<template>
    <div v-show="loadedScreen" id="assign-product-shipping-dialog" style="width:20%;height:180px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignShipping'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="assign-product-shipping" id="assign-product-shipping" action="">
          <p class="grid_12">
            <select v-model="selectedShipping" name="shippings" class="shippings">
              <option value="" disabled>{{$t('message.selectProductShipping')}}</option>
              <option v-for="shipping in productShipping" :value="shipping.value">{{$t('message.'+shipping.name)}}</option>
            </select>
          </p>
          <p class="grid_12">
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassignshipping.js"/>




