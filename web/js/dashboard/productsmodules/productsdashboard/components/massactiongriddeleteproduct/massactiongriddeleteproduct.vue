<template>
    <div v-show="loadedScreen" id="product-delete-dialog" style="width:350px;height:180px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionDelProduct'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="delete-product" id="delete-product" action="">
          <div class="textcentered">
            <h2 class="ui-state-error-text">{{$t('message.areYouSure')}}</h2>
            <span class="ui-state-error-text">{{$t('message.thisOperationCannotBeUndone')}}</span>
          </div>
          <p class="grid_12">
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.yes'))">
            <input @click.prevent="closeMassAction" type="submit" class="btn grid_6" :value="ucFirstAllText($t('message.no'))">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongriddeleteproduct.js"/>




