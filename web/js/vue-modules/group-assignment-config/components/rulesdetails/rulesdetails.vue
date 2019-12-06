<template>
    <main class="grid_12" v-show="loaded">
        <a class="back ticon-arrow-left mt10px inline-block" v-on:click="goToRulesScreen" href="javascript:;">Rules list</a>
        <h2 class="mt10px">Rules details</h2>
        <form id="add-custom-tab-form" @submit.prevent="updateRule" class="grid_12 alpha omega" action="" method="POST">
            <fieldset class="background">
                <legend class="background p5px">{{$t('message.groupConfigLegend')}}n</legend>
                <select @change="addProperty(chosenProperty)" class="grid_3 mt10px" name="rule" v-model="chosenProperty">
                    <option value="0">{{$t('message.selectCustomParam')}}</option>
                    <option v-bind:value="name" v-for="(value, name) in configScreenInfo.customParams">{{name}}</option>
                </select>
                <div class="grid_5">
                    <p class="grid_12 mb10px" v-for="(propertyData, index) in propertyDataEl">
                        <label class="grid_4 alpha">{{propertyData.label}}</label>
                        <select class="grid_3 omega alpha"  v-model="propertyData.operator">
                            <option v-for="(name, value) in propertyData.operators" v-bind:value="value">{{name}}</option>
                        </select>
                        <input class="grid_4 alpha" v-model.trim="propertyData.value"
                               :name="propertyData.name" :placeholder="propertyData.placeholder">
                        <a class="text-center ticon-close error icon grid_1" @click="deletePropertyData(index)"></a>
                    </p>
                </div>
                <div class="grid_4">
                    <label class="grid_5 t-grid_3 t-alpha">{{$t('message.selectGroup')}}</label>
                    <select v-model="selectedGroup" name="form-action-assign-customer-group" class="grid_12 omega">
                        <option value="0">{{$t('message.selectGroup')}}</option>
                        <option v-bind:value="customerGroup[0]"  v-for="(customerGroup, index) in customerGroups">{{customerGroup[1]}}</option>
                    </select>
                </div>
                <p class="grid_12 omega">
                    <input class="prefix_7 grid_2 alpha mt15px omega" type="text" name="ruleName" v-model.trim="ruleName" placeholder="Rule name"/>
                    <button id="custom-tab-form-save" class="btn grid_3 fl-right" type="submit">{{$t('message.groupConfigLegend')}}</button>
                </p>
            </fieldset>
        </form>
    </main>
</template>

<script src="./controller/rulesdetails.js"/>
