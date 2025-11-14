<template>
  <div v-if="loadedScreen" id="products-grid-table-block" class="flex_12">
    <div class="products-table mb0px grid-table-block">
      <div id="products-grid-table-dashboard-table-scroll" class="dashboard-table-scroll">
        <table id="products-grid-table" class="dynamic-columns product-grid-table">
          <thead class="header-inner">
          <tr>
            <th>
              <label for="grid-membership-line-select-all">
                <input id="grid-membership-line-select-all" :checked="isAllChecked()"
                       @change="checkRemoveAllItemsOnPage($event)" type="checkbox" name="grid-membership-line-select-all">
              </label>
            </th>
            <th>{{$t('message.name')}}</th>
            <th>{{$t('message.stock')}}</th>
            <th>{{$t('message.brand')}}</th>
            <th>{{$t('message.suppliers')}}</th>
            <th>{{$t('message.sku')}}</th>
            <th>{{$t('message.mpn')}}</th>
            <th>{{$t('message.freeShipping')}}</th>
            <th>{{$t('message.weight')}}</th>
            <th>{{$t('message.price')}}</th>
            <th>{{$t('message.salesCount')}}</th>
          </tr>
          </thead>
          <tbody id="product-grid-table-body">
            <tr v-if="Object.keys(ProductsGridInfoData).length > 0" :key="produuctsGridInfo.id" class="product-row" v-for="(produuctsGridInfo, index) in ProductsGridInfoData">
              <td>
                <span>
                  <label :for="'grid-product-line-'+produuctsGridInfo.id">
                    <input :checked="isCheckedItem(produuctsGridInfo.id)" :id="'grid-product-line-'+produuctsGridInfo.id"
                           @change="addRemoveItem($event, produuctsGridInfo.id)" type="checkbox"
                           :name="'grid-item-number-'+produuctsGridInfo.id">
                  </label>
                </span>
              </td>
              <td>
                <span>
                  <a href="javascript:;" title="Click to open editor" class="tpopup" :data-url="websiteUrl+'plugin/shopping/run/product/id/'+produuctsGridInfo.id">{{produuctsGridInfo.name}}</a>
                </span>
              </td>
              <td>
                <input @blur="updateProp($event, index, 'inventory')" type="text" :value="(produuctsGridInfo.inventory === null || produuctsGridInfo.inventory == '') ? 'unlimited' : produuctsGridInfo.inventory">
              </td>
              <td>
                <span>
                  {{produuctsGridInfo.brandName}}
                </span>
              </td>
              <td>
                <div v-if="typeof suppliersCompanies !== 'undefined'">
                  <p v-for="(supCompany, ind) in suppliersCompanies">
                    <strong v-if="produuctsGridInfo.id == supCompany.product_id">{{supCompany.company_name}}</strong>
                  </p>
                </div>
              </td>
              <td>
                <input @blur="updateProp($event, index, 'sku')" type="text" :value="produuctsGridInfo.sku">
              </td>
              <td>
                <input @blur="updateProp($event, index, 'mpn')" type="text" :value="produuctsGridInfo.mpn">
              </td>
              <td>
                <span v-if="produuctsGridInfo.free_shipping === null || produuctsGridInfo.free_shipping == '' || produuctsGridInfo.free_shipping == 0">{{$t('message.no')}}</span>
                <span v-else-if="produuctsGridInfo.free_shipping == 1">{{$t('message.yes')}}</span>
              </td>
              <td>
                <input @blur="updateProp($event, index, 'weight')" type="text" :value="produuctsGridInfo.weight">
              </td>
              <td>
                <input @blur="updateProp($event, index, 'price')" type="text" :value="toCurrency(produuctsGridInfo.price, 2)">
              </td>
              <td>
                <div v-if="salesStats && salesStats.length">
                  <p>{{getTotal(produuctsGridInfo.id)}}</p>
                  <div v-for="(sStats, index) in salesStats" :key="index">
                    <div v-if="produuctsGridInfo.id == sStats.product_id">
                      <p><strong>{{$t('message.cs_' + sStats.status)}}</strong>: {{sStats.count}}</p>
                    </div>
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div id="leads-mass-actions-block" :class="[{ hidden: (massActionActive === false)}, 'mass-actions-block-content']">
      <massactiongridassignbrand v-if="activeMassAction === 'assignBrand'"></massactiongridassignbrand>

    </div>

    <div class="white-box omega p15px mb10px alpha tfoot-block">
      <div class="grid_3 produsts-mass-action-block f-alpha">
        <span class="mass-action-title">{{$t('message.withSelectedDo')}}</span>
        <select @change="changeMassAction" name="produucts-mass-action-selection" class="mass-action-selection">
          <option :selected="activeMassAction === 0" value="0">{{$t('message.noMassActions')}}</option>
          <option :selected="activeMassAction === 'assignBrand'" value="assignBrand">{{$t('message.massActionChangeProductBrand')}}</option>
        </select>
      </div>
      <pagination sectionName="productsGrid" :perPageDropdown="perPageDataValues"
                  @paginationHandler="$store.dispatch('getProductsGridData', {'searchData':filterData, 'updateGridStats':1})"></pagination>
    </div>
  </div>
</template>
<style>
.multiselect__tags {
  min-height: 40px;
  display: block;
  padding: 8px 8px 0 8px !important;
  border-radius: 5px;
  border: 1px solid #e8e8e8;
  background: #fff;
  font-size: 14px;
}
.autocomplete-input {
  background-image:none !important;
  border:none !important;
  background-color: transparent !important;
}
.autocomplete-result{
  background-image:none !important;
}
</style>

<script src="./controller/productsgridtable.js"/>




