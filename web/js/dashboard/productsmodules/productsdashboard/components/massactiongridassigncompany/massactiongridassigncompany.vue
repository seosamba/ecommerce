<template>
    <div v-show="loadedScreen" id="assign-product-company-dialog" style="width:350px;height:305px;">
      <div class="system-popup-header-block">
        <span class="system-popup-title">{{ucFirstAllText($t('message.massActionAssignCompany'))}}</span>
        <span class="ticon-close" @click="closeMassAction"></span>
      </div>
      <div class="system-popup-content-block clearfix" style="max-height: 200px">
        <template v-for="company in allCompanies">
          <label class="fl-left mr30px pointer">
            {{company.companyName}}
            <input type="checkbox" @change="markCompany($event, company.id)" :name="company.companyName" id="" :value="company.id" :checked="(this.usedCompanyIds[company.id])">
          </label>
        </template>
      </div>
      <p class="grid_3 fl-right mt10px">
        <input @click.prevent="submitRegularForm" type="submit" class="btn grid_12" :value="$t('message.apply')">
      </p>
    </div>
</template>

<script src="./controller/massactiongridassigncompany.js"/>




