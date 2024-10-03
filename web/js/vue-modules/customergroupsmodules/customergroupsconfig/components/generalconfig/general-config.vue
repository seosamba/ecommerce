<template>
    <div>
        <div id="customer-groups-grid" v-show="loadedGrid">
          <div class="grid_12 alpha omega">
            <form @submit.prevent="saveConfig" class="grid_12 alpha omega" action="" method="POST">

            </form>
          </div>

          <div class="grid_12 alpha omega h360px">
                <table id="group-table" class="customer-groups-config-table responsive table-striped table-hover small mb10px">
                    <thead class="header-inner">
                    <tr>
                        <th>{{$t('message.groupName')}}</th>
                        <th>{{$t('message.defaultPriceModifier')}}</th>
                        <th>{{$t('message.nonTaxable')}}</th>
                        <th class="text-right">{{$t('message.action')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr v-for="configData in configDataInfo">
                            <td><a href="javascript:;"></a></td>
                            <td></td>
                            <td></td>
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
        <div id="customer-groups-details" v-show="loadedDetails">
            <div class="back-link grid_12 mb10px" id="back-link">
                <a @click="backToMainGrid" href="javascript:;" class="btn" id="back-to-main-link"><span class="icon-arrow-left">{{$t('message.back')}}</span></a>
            </div>

            <form @submit.prevent="updateConfig(configId)" class="grid_12 alpha omega" action="" method="POST">

            </form>
        </div>
    </div>
</template>

<script src="./controller/general-config.js"/>




