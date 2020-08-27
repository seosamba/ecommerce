<template>
    <div v-show="loadedForm">
        <div class="grid_12 alpha omega">
            <form id="add-custom-tab-form" @submit.prevent="addRule" class="grid_12 alpha omega" action="" method="POST">
                <fieldset class="background">
                    <legend class="background p5px">{{$t('message.groupConfigLegend')}}</legend>
                    <div class="grid_6 mt0px">
                        <label class="grid_12 t-grid_3 text-left">{{$t('message.conditionLabel')}}</label>
                    </div>
                    <div class="grid_6 mt0px">
                        <label class="grid_12 t-grid_3 text-right">{{$t('message.selectGroupLabel')}}</label>
                    </div>
                    <div class="grid_3 mt20px">
                        <select @change="addProperty(chosenProperty)" class="grid_12 omega" name="rule"
                                v-model="chosenProperty">
                            <option value="0">{{$t('message.selectCustomParam')}}</option>
                            <option v-bind:value="name" v-for="(value, name) in configScreenInfo.customParams">
                                {{name}}
                            </option>
                        </select>
                    </div>
                    <div class="grid_6 mt20px">
                        <p class="grid_12 mb10px" v-for="(propertyData, index) in propertyDataEl">
                            <label class="grid_4 alpha mt5px">{{propertyData.label}}</label>
                            <select class="grid_3 omega alpha"  v-model="propertyData.operator">
                                <option v-for="(name, value) in propertyData.operators" v-bind:value="value">{{name}}</option>
                            </select>
                            <input class="grid_4 alpha" v-model.trim="propertyData.value"
                                   :name="propertyData.name" :placeholder="propertyData.placeholder">
                            <a class="text-center ticon-close error icon grid_1" @click="deletePropertyData(index)"></a>
                        </p>
                    </div>
                    <div class="grid_3 mt20px">
                        <select v-model="selectedGroup" name="form-action-assign-customer-group" class="grid_12 omega">
                            <option value="0">{{$t('message.selectGroup')}}</option>
                            <option v-bind:value="customerGroup[0]"  v-for="(customerGroup, index) in customerGroups">{{customerGroup[1]}}</option>
                        </select>
                    </div>
                    <p class="grid_12 omega mt10px">
                        <input class="prefix_7 grid_2 alpha mt15px omega" type="text" name="ruleName" v-model.trim="ruleName" placeholder="Rule name"/>
                        <button id="custom-tab-form-save" class="btn grid_3 fl-right" type="submit">Add rule</button>
                    </p>
                </fieldset>
            </form>
        </div>
        <div id="tab-grid" v-show="loadedGrid">
            <div class="grid_12 alpha omega h360px mt10px">
                <table class="manage-saved-config-table responsive">
                    <thead>
                    <tr>
                        <th>Rule name</th>
                        <th>Action taken</th>
                        <th>Last updated</th>
                        <th class="text-right">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="configData in configDataInfo">
                            <td><a @click="goToRuleDetailScreen(configData.id)">{{configData.rule_name}}</a></td>
                            <td>{{configData.actionTypes}}</td>
                            <td>{{prepareDate(configData.createdAt)}}</td>
                            <td class="text-right">
                                <a @click="deleteConfigItem(configData.id)" class="ticon-remove error icon14" href="javascript:;"></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <pagination sectionName="rulesConfig" @paginationHandler="$store.dispatch('getConfigSavedData', {})"></pagination>
            </div>
        </div>
    </div>
</template>

<script src="./controller/general-config.js"/>




