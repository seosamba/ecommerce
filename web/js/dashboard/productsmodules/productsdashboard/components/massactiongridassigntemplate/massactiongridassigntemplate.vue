<template>
    <div v-show="loadedScreen" id="assign-product-template-dialog" style="width:20%;height:180px">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignTemplate'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block">
        <form class="grid_12" method="POST" name="assign-product-template" id="assign-product-template" action="">
          <p class="grid_12">
            <select v-model="selectedTemplate" name="templates" class="templates">
              <option value="0" disabled>{{$t('message.selectProductTemplate')}}</option>
              <option v-for="template in productTemplates" :value="template.name">{{template.name}}</option>
            </select>
          </p>
          <p class="grid_12">
            <input @click.prevent="submitRegularForm" type="submit" class="btn grid_6" :value="$t('message.apply')">
          </p>
        </form>
      </div>
    </div>
</template>

<script src="./controller/massactiongridassigntemplate.js"/>




