<template>
    <div v-show="loadedScreen" id="product-delete-dialog" style="width:350px;height:150px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionEnableDisableProducts'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="delete-product" id="delete-product" action="">
          <p class="grid_12 mt15px">
            <input @click.prevent="submitRegularForm('enable')" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.enable'))">
            <input @click.prevent="submitRegularForm('disable')" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.disable'))">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridtoggleproduct.js"/>




