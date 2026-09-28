<template>
    <div id="general-seosambapos-config-block" class="grid_12">
      <div v-show="showLocationsBlock">
        <div class="grid_12 mt10px">
          <span class="grid_5 alpha omega text-center">{{$t('message.location')}}</span>
          <span class="grid_2 alpha omega text-center">{{$t('message.inventory')}}</span>
          <span class="grid_3 alpha omega text-center">{{$t('message.defaultLocation')}}</span>
          <span class="grid_2 alpha text-center">{{$t('message.action')}}</span>
        </div>
        <div class="h300px grid_12 mt10px">
          <div class="f-scroll" style="height: 100%;">
            <div class="saved-locations" v-for="(savedLocation, index) in savedLocations">
              <div class="grid_12 alpha omega mt10px">
                <VueMultiselect class="grid_5 alpha" v-model="selectedLocations[index]" @update:model-value="changeLocation($event, savedLocation.id)" :clear-on-select="false" :show-labels="false" :allowEmpty="false" :options="locationsData" label="label" track-by="id" :placeholder="$t('message.selectLocation')">
                  <template #noResult>{{$t('message.rowNotFound')}}</template>
                </VueMultiselect>
                <input autocomplete="off" class="grid_2 alpha mt5px" type="text" name="inv-label" @change="changeInventory($event, savedLocation.id, savedLocation.inventory)" :value="savedLocation.inventory"/>
                <input :checked="(savedLocation.is_default_location == '1')" @change="changeAppAccessStatus($event, savedLocation.id)" type="checkbox"
                       class="grid_3 alpha mt5px text-center default-location switcher"/>
                <div class="grid_2 omega text-center">
                  <a @click="deleteLocation(savedLocation.id)" class="ticon-remove error icon14" href="javascript:;"></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <form @submit.prevent="addNewLocation" class="grid_12 alpha omega" action="" method="POST">
            <fieldset class="background grid_12 alpha omega mt10px">
              <p class="grid_5 alpha omega mt0px">
                <label>{{$t('message.location')}}:</label>
                <VueMultiselect v-model="newLocationSelected" @update:model-value="setNewLocation($event)" :clear-on-select="false" :show-labels="false" :allowEmpty="false" :options="locationsData" label="label" track-by="id" :placeholder="$t('message.selectLocation')">
                  <template #noResult>{{$t('message.rowNotFound')}}</template>
                </VueMultiselect>
              </p>
              <p class="grid_5 omega mt0px">
                <label>{{$t('message.inventory')}}:</label>
                <input v-model="newInventory" class="mt5px" autocomplete="off" type="text" name="inv-label"/>
              </p>
              <p class="grid_2 alpha omega mt0px">
                <input class="btn mt30px" type="submit" name="save-new" :value="[[$t('message.saveNew')]]"/>
              </p>
            </fieldset>
          </form>
        </div>
      </div>
      <div v-if="!showLocationsBlock">
        <div class="message error mt2em">
          <div v-if="isDefaultProduct">
            <h1 class="error">{{ $t('message.defaultProduct') }}</h1>
          </div>
          <div v-else-if="isDefaultTipProduct">
            <h1 class="error">{{ $t('message.tipProduct') }}</h1>
          </div>
          <div v-else>
            <h1 class="error">{{ $t('message.productNotSaved') }}</h1>
            <p>{{ $t('message.clickToSaveButton') }}</p>
          </div>
        </div>
      </div>
    </div>
</template>

<script src="./controller/product-location-config.js"/>
<style src="vue-multiselect/dist/vue-multiselect.css"></style>




