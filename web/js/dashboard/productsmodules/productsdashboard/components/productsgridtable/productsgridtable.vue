<template>
  <div v-if="loadedScreen" id="products-grid-table-block" class="flex_12">
    <div class="products-table mb0px grid-table-block">
      <div id="leads-grid-table-dashboard-table-scroll" class="dashboard-table-scroll">
        <table id="leads-grid-table" class="dynamic-columns lead-grid-table">
          <thead class="header-inner">
          <tr>
            <th>
              <label for="grid-membership-line-select-all">
                <input id="grid-membership-line-select-all" :checked="isAllChecked()"
                       @change="checkRemoveAllItemsOnPage($event)" type="checkbox" name="grid-membership-line-select-all">
              </label>
            </th>
            <th>Name</th>
            <th>Stock</th>
            <th>Brand</th>
            <th>Suppliers</th>
            <th>Sku</th>
            <th>Mpn</th>
            <th>Free shipping</th>
            <th>Weight</th>
            <th>Price</th>
            <th>Sales count</th>
          </tr>
          </thead>
          <tbody id="lead-grid-table-body">
            <tr v-if="Object.keys(ProductsGridInfoData).length > 0" :key="leadGridInfo.id" class="lead-row" v-for="(leadGridInfo, index) in ProductsGridInfoData">
              <td>
                <span>
                  <label :for="'grid-lead-line-'+leadGridInfo.id">
                    <input :checked="isCheckedItem(leadGridInfo.id)" :id="'grid-lead-line-'+leadGridInfo.id"
                           @change="addRemoveItem($event, leadGridInfo.id)" type="checkbox"
                           :name="'grid-item-number-'+leadGridInfo.id">
                  </label>
                </span>
              </td>
              <td>
                <span>
                  <a href="javascript:;" title="Click to open editor" class="tpopup" :data-url="websiteUrl+'plugin/shopping/run/product/id/'+leadGridInfo.id">{{leadGridInfo.name}}</a>
                </span>
              </td>
              <td>
                <span v-if="leadGridInfo.inventory === null || leadGridInfo.inventory == ''">{{$t('message.unlimited')}}</span>
                <span v-else>{{leadGridInfo.inventory}}</span>
              </td>
              <td>
                <span>
                  {{leadGridInfo.brandName}}
                </span>
              </td>
              <td>
                suppliersCompanies
              </td>
              <td>
                {{leadGridInfo.sku}}
              </td>
              <td>
                {{leadGridInfo.mpn}}
              </td>
              <td>
                <span v-if="leadGridInfo.free_shipping === null || leadGridInfo.free_shipping == '' || leadGridInfo.free_shipping == 0">{{$t('message.no')}}</span>
                <span v-else-if="leadGridInfo.free_shipping == 1">{{$t('message.yes')}}</span>
              </td>
              <td>
                {{leadGridInfo.prod_length}}
              </td>
              <td>
                {{leadGridInfo.price}}
              </td>
              <td>
                SalesCount
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div class="white-box omega p15px mb10px alpha tfoot-block">
      <div class="grid_3 leads-mass-action-block f-alpha">
        <span class="mass-action-title">{{$t('message.withSelectedDo')}}</span>
        <select name="leads-mass-action-selection" class="mass-action-selection">
          <option :selected="activeMassAction === 0" value="0">{{$t('message.noMassActions')}}</option>
        </select>
      </div>
      <pagination sectionName="productsGrid" :perPageDropdown="perPageDataValues"
                  @paginationHandler="$store.dispatch('getProductsGridData', {'searchData':filterData, 'updateOppStats':1})"></pagination>
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




