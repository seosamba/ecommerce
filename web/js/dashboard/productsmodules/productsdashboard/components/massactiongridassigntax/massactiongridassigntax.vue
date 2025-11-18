<template>
    <div v-show="loadedScreen" id="assign-product-tax-dialog" style="width:20%;height:200px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignTax'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="assign-product-tax" id="assign-product-tax" action="">
          <p class="grid_12">
            <label>{{$t('message.selectTaxCategory')}}:</label>
            <select v-model="selectedTax" name="taxs" class="taxs">
              <option v-for="tax in productTaxes" :value="tax.value">{{$t('message.'+tax.name)}}</option>
            </select>
          </p>
          <p class="grid_12">
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassigntax.js"/>




