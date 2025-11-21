<template>
    <div v-show="loadedScreen" id="product-quantity-dialog" style="width:355px;height:170px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionProductQuantity'))}}</span>
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
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.apply'))">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridproductquantity.js"/>




