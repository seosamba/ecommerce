<template>
  <div v-if="loadedScreen" id="products-grid-table-block" class="flex_12">
    <div class="products-table mb0px grid-table-block">
      <div id="products-grid-table-dashboard-table-scroll" class="dashboard-table-scroll">
        <table id="products-grid-table" class="product-grid-table">
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
            <th>{{$t('message.productTemplate')}}</th>
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
            <tr v-if="Object.keys(ProductsGridInfoData).length > 0" :key="productsGridInfo.id" class="product-row" v-for="(productsGridInfo, index) in ProductsGridInfoData">
              <td>
                <span>
                  <label :for="'grid-product-line-'+productsGridInfo.id">
                    <input :checked="isCheckedItem(productsGridInfo.id)" :id="'grid-product-line-'+productsGridInfo.id"
                           @change="addRemoveItem($event, productsGridInfo.id)" type="checkbox"
                           :name="'grid-item-number-'+productsGridInfo.id">
                  </label>
                </span>
              </td>
              <td>
                <span>
                  <a href="javascript:;" title="Click to open editor" class="tpopup" :data-url="websiteUrl+'plugin/shopping/run/product/id/'+productsGridInfo.id">{{productsGridInfo.name}}</a>
                </span>
              </td>
              <td class="product-inventory-col" width="80px">
                <input @blur="updateProp($event, index, 'inventory')" :placeholder="(productsGridInfo.inventory === null || productsGridInfo.inventory === '') ? 'unlimited':''" type="text" :value="(productsGridInfo.inventory === null || productsGridInfo.inventory === '') ? '' : productsGridInfo.inventory">
              </td>
              <td>
                <span>
                  {{productsGridInfo.brandName}}
                </span>
              </td>
              <td>
                <span>
                  <select @change="updateProp($event, index, 'pageTemplate')" name="product-list-templates">
                    <option v-for="prodTemplate in gridInfo.productTemplatesList" :selected="prodTemplate === productsGridInfo.pageTemplate" :value="prodTemplate">{{prodTemplate}}</option>
                  </select>
                </span>
              </td>
              <td>
                <div v-if="typeof suppliersCompanies !== 'undefined' && suppliersSpinner === false">
                  <p v-for="(supCompany, ind) in suppliersCompanies">
                    <strong v-if="productsGridInfo.id == supCompany.product_id">{{supCompany.company_name}}</strong>
                  </p>
                </div>
                <img v-if="suppliersSpinner === true" :src="websiteUrl+'plugins/shopping/web/images/spinner_16.gif'" alt="loading" />
              </td>
              <td>
                <input @blur="updateProp($event, index, 'sku')" type="text" :value="productsGridInfo.sku">
              </td>
              <td>
                <input @blur="updateProp($event, index, 'mpn')" type="text" :value="productsGridInfo.mpn">
              </td>
              <td>
                <span v-if="productsGridInfo.free_shipping === null || productsGridInfo.free_shipping == '' || productsGridInfo.free_shipping == 0">{{$t('message.no')}}</span>
                <span v-else-if="productsGridInfo.free_shipping == 1">{{$t('message.yes')}}</span>
              </td>
              <td class="product-weight-col" width="85px">
                <input @blur="updateProp($event, index, 'weight')" type="text" :value="productsGridInfo.weight">
              </td>
              <td class="product-price-col" width="110px">
                {{currencyOnly()}}<input @blur="updateProp($event, index, 'price')" type="text" :value="toCurrency(productsGridInfo.price, 2).replace(/[^0-9.]+/g, '').trim()">
              </td>
              <td class="textcentered">
                <template v-if="salesStats && salesStats.length && salesSpinner === false">
                  {{getTotal(productsGridInfo.id)}}
                  <ul>
                    <template v-for="(sStats, index) in salesStats" :key="index">
                       <li v-if="parseInt(productsGridInfo.id) === parseInt(sStats.product_id)">
                         <strong>{{$t('message.cs_' + sStats.status)}}</strong>: {{sStats.count}}
                       </li>
                    </template>
                  </ul>
                </template>
                <img v-if="salesSpinner === true" :src="websiteUrl+'plugins/shopping/web/images/spinner_16.gif'" alt="loading" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div id="products-mass-actions-block" :class="[{ hidden: (massActionActive === false)}, 'mass-actions-block-content']">
      <massactiongridassignbrand v-if="activeMassAction === 'assignBrand'"></massactiongridassignbrand>
      <massactiongridassigntemplate v-if="activeMassAction === 'assignTemplate'"></massactiongridassigntemplate>
      <massactiongridassigntax v-if="activeMassAction === 'assignTax'"></massactiongridassigntax>
      <massactiongridassignshipping v-if="activeMassAction === 'assignShipping'"></massactiongridassignshipping>
      <massactiongridassigntag v-if="activeMassAction === 'assignTag'"></massactiongridassigntag>
      <massactiongridassigncompany v-if="activeMassAction === 'assignCompany'"></massactiongridassigncompany>
      <massactiongriddeleteproduct v-if="activeMassAction === 'deleteProduct'"></massactiongriddeleteproduct>
      <massactiongridtoggleproduct v-if="activeMassAction === 'toggleProduct'"></massactiongridtoggleproduct>
      <massactiongridproductquantity v-if="activeMassAction === 'quantityProduct'"></massactiongridproductquantity>
      <massactiongridassignnegativestock v-if="activeMassAction === 'negativeStock'"></massactiongridassignnegativestock>
      <massactiongridassignpromo v-if="activeMassAction === 'assignPromo'"></massactiongridassignpromo>
      <massactiongridproductoptionprice v-if="activeMassAction === 'assignProductOptionPrice'"></massactiongridproductoptionprice>

    </div>

    <div class="white-box omega p15px mb10px alpha tfoot-block">
      <div class="grid_3 leads-mass-action-block f-alpha">
        <span class="mass-action-title">{{$t('message.withSelectedDo')}}</span>
        <select @change="changeMassAction" name="products-mass-action-selection" class="mass-action-selection">
          <option :selected="activeMassAction === 0" value="0">{{$t('message.noMassActions')}}</option>
          <option :selected="activeMassAction === 'assignBrand'" value="assignBrand">{{$t('message.massActionChangeProductBrand')}}</option>
          <option :selected="activeMassAction === 'assignTemplate'" value="assignTemplate">{{$t('message.massActionChangeProductTemplate')}}</option>
          <option :selected="activeMassAction === 'assignTax'" value="assignTax">{{$t('message.massActionChangeProductTax')}}</option>
          <option :selected="activeMassAction === 'assignShipping'" value="assignShipping">{{$t('message.massActionChangeProductShipping')}}</option>
          <option :selected="activeMassAction === 'assignTag'" value="assignTag">{{$t('message.massActionChangeProductTag')}}</option>
          <option :selected="activeMassAction === 'assignCompany'" value="assignCompany">{{$t('message.massActionChangeProductCompany')}}</option>
          <option :selected="activeMassAction === 'deleteProduct'" value="deleteProduct">{{$t('message.massActionDeleteProduct')}}</option>
          <option :selected="activeMassAction === 'toggleProduct'" value="toggleProduct">{{$t('message.massActionToggleProduct')}}</option>
          <option :selected="activeMassAction === 'quantityProduct'" value="quantityProduct">{{$t('message.massActionProductQuantity')}}</option>
          <option :selected="activeMassAction === 'negativeStock'" value="negativeStock">{{$t('message.massActionProductNegativeStock')}}</option>
          <option :selected="activeMassAction === 'assignPromo'" value="assignPromo">{{$t('message.massActionAssignPromo')}}</option>
          <option :selected="activeMassAction === 'assignProductOptionPrice'" value="assignProductOptionPrice">{{$t('message.massActionAssignProductOptionPrice')}}</option>

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




