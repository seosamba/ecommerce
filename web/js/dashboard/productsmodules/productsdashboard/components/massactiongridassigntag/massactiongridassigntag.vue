<template>
    <div v-show="loadedScreen" id="assign-product-tag-dialog">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignTag'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block clearfix" style="max-height: 397px">
        <div style="float:left; margin: 0 5px;" v-for="tag in productTags">
          <label>
            <input type="checkbox" @change="markTag($event, tag.id, tag.name)" :name="tag.name" id="" :value="tag.id" :checked="(this.usedTags[tag.name])">
            {{tag.name}}
          </label>
        </div>
      </div>
      <p class="grid_3 fl-right mt10px">
        <input @click.prevent="submitRegularForm" type="submit" class="btn grid_12" :value="$t('message.apply')">
      </p>
    </div>
</template>

<script src="./controller/massactiongridassigntag.js"/>




