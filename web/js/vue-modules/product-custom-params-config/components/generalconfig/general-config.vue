<template>
    <div v-show="loadedForm">
        <div id="tab-grid" v-show="loadedGrid">
            <div class="grid_12 alpha omega h360px">
                <table class="manage-saved-config-table responsive">
                    <thead>
                    <tr>
                        <th>{{$t('message.fieldType')}}</th>
                        <th>{{$t('message.fieldName')}}</th>
                        <th>{{$t('message.fieldLabel')}}</th>
                        <th class="text-right">{{$t('message.fieldAction')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="configData in configDataInfo">
                            <td>{{configData.param_type}}</td>
                            <td>{{configData.param_name}}</td>
                            <td>{{configData.label}}</td>
                            <td class="text-right">
                                <a @click="deleteConfigItem(configData.id)" class="ticon-remove error icon14" href="javascript:;"></a>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <pagination sectionName="customFieldsConfig" @paginationHandler="$store.dispatch('getProductConfigSavedData', {})"></pagination>
            </div>
        </div>
        <div>
            <form id="product-custom-params-form" @submit.prevent="addCustomField" class="grid_12 alpha omega"
                  action="" method="POST">
                <fieldset class="background">
                    <legend class="background p5px">{{$t('message.addFieldToScreen')}}</legend>
                    <p class="grid_3 alpha omega mt0px">
                        <label>{{$t('message.customFeieldType')}}:</label>
                        <select id="product-field-type" class="required" name="param_type" v-model="param_type">
                            <option value="text">{{$t('message.textField')}}</option>
                            <option value="select">{{$t('message.dropdownField')}}</option>
                        </select>
                    </p>
                    <p class="grid_3 omega mt0px">
                        <label>{{$t('message.customFieldName')}}:</label>
                        <input class="required param_name" type="text" name="param_name" v-model.trim="param_name" value=""/>
                    </p>
                    <p class="grid_3 omega mt0px">
                        <label>{{$t('message.customFieldLabel')}}:</label>
                        <input class="required param_label" type="text" name="label" v-model.trim="label" value=""/>
                    </p>
                    <p class="grid_3 omega mt0px">
                        <input id="product-custom-params-form-save" class="btn" type="submit" name="product-custom-params-form-save"
                               value="Add custom field"/>
                    </p>
                </fieldset>
            </form>
        </div>
    </div>
</template>

<script src="./controller/general-config.js"/>




