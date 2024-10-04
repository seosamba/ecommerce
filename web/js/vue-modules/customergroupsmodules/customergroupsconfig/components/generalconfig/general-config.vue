<template>
    <div>
        <div id="customer-groups-grid" v-if="loadedGrid === true">
          <fieldset class="background mb10px grid_12 alpha omega">
            <legend class="background p5px">{{$t('message.defaultUserGroup')}}</legend>
            <select v-model="defaultGroupId" @change="changeDefaultGroup($event)" name="groups-list" id="groups-list" class="grid_4 alpha">
              <option value="0">{{$t('message.selectGroup')}}</option>
              <option v-for="(value, key) in additionalInfo.groupsList" v-bind:value="key" >{{value}}</option>
            </select>
          </fieldset>
          <div class="grid_12 alpha omega">
            <form @submit.prevent="saveConfig" id="edit-group-form" class="grid_12 alpha omega background" action="" method="POST">
              <div class="grid_2 alpha">
                <input v-model="groupName" type="text" name="groupName" id="groupName" class="new-group-name" :placeholder="$t('message.enterGroupName')">
              </div>
              <div class="grid_1 alpha omega mt0px">
                <select v-model="priceSign" name="priceSign" id="group-sign">
                  <option value="plus">+</option>
                  <option value="minus">-</option>
                </select>
              </div>
              <div class="grid_2 mt0px">
                <input v-model="priceValue" type="text" name="priceValue" id="priceValue" class="new-group-price-value">
              </div>
              <div class="grid_1 alpha omega mt0px">
                <select v-model="priceType" name="priceType" id="group-price-type">
                  <option value="percent">%</option>
                  <option value="unit">{{additionalInfo.currencyAbbr}}</option>
                </select>
              </div>
              <div class="grid_2 omega mt5px">
                <label>
                  {{$t('message.nonTaxable')}}
                  <input v-model="nonTaxable" type="checkbox" name="nonTaxable" :true-value="1" :false-value="0">
                </label>
              </div>

              <button class="btn grid_4 omega mt0px ticon-plus" type="submit">{{$t('message.createGroup')}}</button>
            </form>
          </div>

          <div class="grid_12 alpha omega mt15px">
                <table id="group-table" class="customer-groups-config-table responsive table-striped table-hover small mb10px">
                    <thead class="header-inner">
                    <tr>
                        <th>{{$t('message.groupName')}}</th>
                        <th>{{$t('message.defaultPriceModifier')}}</th>
                        <th>{{$t('message.nonTaxable')}}</th>
                        <th class="w10 text-center">{{$t('message.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="configData in configDataInfo">
                            <td>{{configData.groupName}}</td>
                            <td>
                              <span v-if="configData.priceSign === 'plus'">+</span>
                              <span v-if="configData.priceSign === 'minus'">-</span>
                               {{configData.priceValue}}
                              <span v-if="configData.priceType === 'percent'">%</span>
                              <span v-if="configData.priceType === 'unit'">{{additionalInfo.currencyAbbr}}</span>
                            </td>
                            <td v-if="parseInt(configData.nonTaxable) === 1">{{$t('message.yes')}}</td>
                            <td v-if="parseInt(configData.nonTaxable) === 0">{{$t('message.no')}}</td>
                            <td class="text-right">
                                <a @click="ruleDetails(configData.id)" class="ticon-pencil icon14" href="javascript:;"></a>
                                <a @click="deleteConfigItem(configData.id)" class="ticon-remove error icon14" href="javascript:;"></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <pagination sectionName="generalConfig" @paginationHandler="$store.dispatch('getConfigSavedData', {})"></pagination>
            </div>
        </div>
        <div id="customer-groups-details" v-if="loadedDetails === true">
            <div class="back-link grid_12 mb10px" id="back-link">
                <a @click="backToMainGrid" href="javascript:;" class="btn" id="back-to-main-link"><span class="icon-arrow-left">{{$t('message.back')}}</span></a>
            </div>

            <form @submit.prevent="updateConfig(configId)" class="grid_12 alpha omega" action="" method="POST">
              <fieldset class="background">
                  <legend class="background p5px">{{$t('message.editGroup')}}</legend>
                  <div class="grid_2 alpha mt0px">
                    <input v-model="groupName" type="text" name="groupName" id="group-name-edit" class="new-group-name" :placeholder="$t('message.enterGroupName')">
                  </div>
                  <div class="grid_1 alpha omega mt0px">
                    <select v-model="priceSign" name="priceSign" id="group-sign-edit">
                      <option value="plus">+</option>
                      <option value="minus">-</option>
                    </select>
                  </div>
                  <div class="grid_2 mt0px">
                    <input v-model="priceValue" type="text" name="priceValue" id="price-value-edit" class="new-group-price-value">
                  </div>
                  <div class="grid_1 alpha omega mt0px">
                    <select v-model="priceType" name="priceType" id="group-price-type-edit">
                      <option value="percent">%</option>
                      <option value="unit">{{additionalInfo.currencyAbbr}}</option>
                    </select>
                  </div>
                  <div class="grid_2 omega mt5px">
                    <label>
                      {{$t('message.nonTaxable')}}
                      <input v-model="nonTaxable" type="checkbox" name="nonTaxable" :true-value="1" :false-value="0">
                    </label>
                  </div>

                  <button class="btn grid_4 omega mt0px ticon-plus" type="submit">{{$t('message.updateGroup')}}</button>
              </fieldset>
            </form>
        </div>
    </div>
</template>

<script src="./controller/general-config.js"/>




