<template>
    <div v-show="loadedScreen" id="assign-product-negativestock-dialog" style="width:20%;height:180px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionProductNegativeStock'))}}</span>
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
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassignnegativestock.js"/>




