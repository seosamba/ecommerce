<template>
    <div v-show="loadedScreen" id="assign-product-brand-dialog" style="width:20%;height:230px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignBrand'))}}</span>
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
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassignbrand.js"/>




