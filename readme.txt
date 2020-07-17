E-commerce store plugin

Plugin widgets.

1. Product widgets (works on product pages)
    {$product:photourl[:_product_|small|medium|large|original]} - Displays URL for the preview image of the product.
        product|small|medium|large|original - size of the product pre-loaded image output
        product - default size (80x80 pixels)
    {$product:name} - Displays the product name.
    {$product:price[:nocurrency|realtimeupdate|currency]} - Displays the product price.
        nocurrency - displays price without currency
        realtimeupdate - update price according to chosen product options
    {$product:price:currency:newcurrency} - Displays the price in specified currency
        newcurrency - currency type
    {$product:weight} - Displays the weight of the product.
    {$product:brand} - Displays the product brand name.
    {$product:mpn} - Displays the product mnp. MPN = Manufacturer Part Number is the product code assigned by the manufacturer.
    {$product:url} - Displays the product page URL.
    {$product:tags} - Displays selected product tags.
    {$product:qty} - Displays quantity of product.
    {$product:options} - Displays product options as a drop-down list, text field, a set of check boxes or a set of radio buttons.
                         Can be displayed both on the product page and in the products list.
    {$product:description[:_short_|full]} - Displays a short or a full description of the product.
                        _short_|full - short or full description output type
    {$product:related[:img:addtocart:template=some_template]} - Displays a list of relevant products on a specific product page.
                       img - displays a list of relevant products with image.
                       addtocart - displays add to cart button.
                       template - template name.

     Allowed dictionary widgets in template for related widget :
        {$product:name}
        {$product:url}
        {$product:brand}
        {$product:weight}
        {$product:mpn}
        {$product:sku}
        {$product:id}
        {$product:description}
        {$product:description:short}
        {$product:description:full}
        {$store:addtocart}
        {$store:addtocart:(related prod id)}
        {$store:addtocart:checkbox}
        {$product:inventory}
        {$product:qty}
        {$product:wishlistqty}
        {$product:price}
        {$product:price:nocurrency}
        {$product:photourl}
        {$product:photourl:small}
        {$product:photourl:medium}
        {$product:photourl:large}
        {$product:photourl:original}

    {$product:editproduct} - This widget displays a link to the edit page of the product that is visible only to users with a copywriter or administrator rights.
    {$product:id} - Displays product id
    {$product:inventory} - Displays "In stock" or "Out of stock" message
    {$product:freeshipping[:sometext]} - Displays <span class="product-free-shipping">sometext</span> if free shipping enabled for this product
        sometext - custom text inside span element
    {$product:allowance} - Displays the product allowance end date.
    {$product:wishlistqty} - Displays the product Wishlist qty.

2. Product list widget.
    {$productlist:template_name[:tagnames-tag1,tag2,...,tagN[:brands-brand1,brand2,...,brandN[:order-name,price,brand,date,sku]]]:desc:unwrap:5} - Creates a list of products using the same tags.
    template_name - template name for the product list
    tagnames-tag1,tag2,...,tagN - filtering by product tags
    brands-brand1,brand2,...,brandN - filtering by product brands
    order-name,price,brand,date,sku - sorting of the list by: name, price, brand, date and sku
    desc - when option order-* is set the sorting of the list by desc. By default sorting by asc
    unwrap - remowed the <div class="product-list"></div> HTML element
    5 - add limit for productlist, where 5 is count of products limit. By default is 50 (must be last option in "product list" widget)
    additionalfilters-somename,somename2,.. - Special filter for {$filter... widget where somename* is unique filter name
3. Store widgets:
    {$store:cartblock} - Displays information about the state of the cart (the amount of goods, price, etc.) and link to the shopping cart page for the current user.

    {$store:labelGenerationGrid:15} - Displays grid for orders label generation with shipping.
    15 - orders limit on page.

4. Add to cart widgets:
    {$store:addtocart:{$product:id}[:gotocart]} - Displays add to cart button
    gotocart - prevent automatically redirect to the cart

5. Customer widgets:
    {$customer:name} - Adding a custom field for a storing additional customer information

6. User widgets: This type of widgets you can use at the page with page option (Store client landing page)
    {$user:name} - user full name
    {$user:registration} - registration date
    {$user:lastlogin} - last login date
    {$user:email} - user email
    {$user:account} - form for editing user's data
    {$user:tabs:tab1,tab2,...,tabN} - tabs with containers
    {$user:grid} - grid with information about purchases for current logged user
    {$user:grid:recurring} - grid with information about recurring payments(subscriptions)
    {$user:grid:recurring:without_period_cycle} - this option allows to hide 'payment period cycle' column and to block 'next billing date' changing.

7. Post purchase widgets:
    This type of widgets you can use at the post purchase page(page with option Post purchase "Thank you" page)
    Also you can use this widgets inside email templates (action email trigger "new order is placed")

    You can use 'clean' param if you want to receive result without html, currency etc..
    You can use 'withoutax' param if want to receive result without tax (even if display with tax enabled)
    {$postpurchase:ipAddress} -> ip address
    {$postpurchase:userId} -> system user id
    {$postpurchase:status} -> status of purchase
    {$postpurchase:gateway} -> payment gateway name
    {$postpurchase:shippingPrice[:clean[:withouttax]]} -> shipping price (with tax if tax enabled)
    {$postpurchase:shippingService} -> shipping service name
    {$postpurchase:subTotal[:clean[:withouttax]]} -> subtotal price (with tax if tax enabled)
    {$postpurchase:totalTax[:clean]} -> total tax
    {$postpurchase:total[:clean]} ->  cart total
    {$postpurchase:referer} -> referer link
    {$postpurchase:createdAt} -> date when purchase created in d-M-Y format
    {$postpurchase:updatedAt} -> date when purchase updated in d-M-Y format
    {$postpurchase:notes} -> customer notes
    {$postpurchase:additionalInfo} -> additional info of purchase
    {$postpurchase:discount[:clean[:withouttax]]} -> purchase discount (with tax if tax enabled)
    {$postpurchase:shippingTax[:clean]} -> shipping tax
    {$postpurchase:discountTax[:clean]} -> discount tax
    {$postpurchase:subTotalTax[:clean]} -> subtotal tax
    {$postpurchase:id} -> cart id
    {$postpurchase:coupon} -> if coupon was used, show coupon name.
    {$postpurchase:quotenote} -> show quote disclaimer.
    {$postpurchase:isGift:some text here} -> Is a gift message will be returned
    {$postpurchase:giftEmail} -> return receiver gift email

    ######### Billing information #############
    {$postpurchase:billing:prefix} -> billing prefix
    {$postpurchase:billing:lastname} -> billing lastname
    {$postpurchase:billing:firstname} -> billing firstname
    {$postpurchase:billing:address1} -> billing address
    {$postpurchase:billing:address2} -> billing address
    {$postpurchase:billing:city}     -> billing address city
    {$postpurchase:billing:state}   -> billing address state
    {$postpurchase:billing:zip} -> billing address zip
    {$postpurchase:billing:country} -> billing address country
    {$postpurchase:billing:phone} -> billing address phone
    {$postpurchase:billing:mobile} -> billing address mobile
    {$postpurchase:billing:email} -> billing address email
    {$postpurchase:billing:customer_notes} -> billing customer notes

    ######### Shipping information #############
    {$postpurchase:shipping:prefix} -> shipping prefix
    {$postpurchase:shipping:lastname} -> shipping lastname
    {$postpurchase:shipping:firstname} -> shipping firstname
    {$postpurchase:shipping:address1} -> shipping address
    {$postpurchase:shipping:address2} -> shipping address
    {$postpurchase:shipping:city}     -> shipping address city
    {$postpurchase:shipping:state}   -> shipping address state
    {$postpurchase:shipping:zip} -> shipping address zip
    {$postpurchase:shipping:country} -> shipping address country
    {$postpurchase:shipping:phone} -> shipping address phone
    {$postpurchase:shipping:mobile} -> shipping address mobile
    {$postpurchase:shipping:customer_notes} -> shipping customer notes
    {$postpurchase:shipping:email} -> shipping address email

    This type of widgets you can use inside 'postpurchasecartcontent' magic space
    It will return result for each product inside your cart

    {$postpurchase:cartitem:photo[:small|medium|large|original|product]} -> product photo (by default from product folder)
    {$postpurchase:cartitem:price[:clean]} -> product price without tax (if product freebies return text 'freebies')
    {$postpurchase:cartitem:tax[:clean]} -> product tax
    {$postpurchase:cartitem:taxprice[:clean]} -> product price with tax
    {$postpurchase:cartitem:sku} -> product sku
    {$postpurchase:cartitem:mpn} -> product mpn
    {$postpurchase:cartitem:name} -> product name
    {$postpurchase:cartitem:qty} -> product quantity
    {$postpurchase:cartitem:cartId} -> cart id
    {$postpurchase:cartitem:total[:clean]} -> total price with tax
    {$postpurchase:cartitem:options[:email[:cleanOptionPrice]} -> <div class="options">some options info</div>
    {$postpurchase:cartitem:producturl} -> product url
    {$postpurchase:cartitem:brand} -> product brand

Magic spaces:
    MAGICSPACE: freebies
    {freebies} ... {/freebies} - Freebies magicspace is processing all products inside magic space and makes them as free.
    Ex: {freebies} {$product:3:product list}{/freebies}

    MAGICSPACE: customeronly
    {customeronly} ... {/customeronly} - Customeronly magicspace displays content for customers

    MAGICSPACE: productset
    {productset} {$product:3:product list} {/productset} - creating set of products

    MAGICSPACE: productset
    {related} {$product:3:product list} {/related} - assigning related products to the product

    MAGICSPACE: postpurchasecode
    {postpurchasecode} ... {/postpurchasecode} - Postpurchasecartcontent magic space is used to specify a place where will
                                                     be displayed information about purchase.
    MAGICSPACE: postpurchasecartcontent
    {postpurchasecartcontent[:somename]} ... {/postpurchasecartcontent} - Postpurchasecartcontent magic space is used to specify a place where will
    be displayed information about each element of purchase. You should provide optional name if you want use several magicspaces on one page.
    If you want to use it with action email system add param 'email' for magic space {postpurchasecartcontent:email}
    somename - You should provide optional name if you want use several magicspaces on one page.

8. Product params widgets:
      {$productparams:titleoption:{$product:id}:SIZE} - Displays option title selected by default. Where "SIZE" - option name. Used only for dropdown and radio options.


9. Product filters widget:
{$filter:tagnames-tag1,tag2,...,tagN[:brands-brand1,brand2,...,brandN[:order-name,price,brand,date,sku[:productsqft]]]}
 tagnames-tag1,tag2,...,tagN - filtering by product tags
 brands-brand1,brand2,...,brandN - filtering by product brands
 order-name,price,brand,date,sku - sorting of the list by: name, price, brand, date and sku
 productsqft - special option (special option for surfacecalc plugin)
 allitems - Show all filter values without All others group

10. Wishlist widget:
a. {$storewishlist:addtowishlist:{$product:id}[:htmlclass:class class2 class3[:btnname:sometext[:profile]]]]}
   htmlclass:class class2 class3 - added html classes, where classX is name of html class.
   btnname:sometext - where sometext is custom text for button name.
   profile - redirected user on profile after add product to Wishlist.

b. {$storewishlist:wishList:_products wishlist list[:limit[:10]]}
   Option "_products wishlist list" template - used for show product on page.
   Into this template you can use any product and store widgets and magicspases.

   Option limit - you can set custom limit before pagination "show more" for product list ex. {$storewishlist:_products wishlist list:limit:10}
   where 10 - is count of products. By default limit is 20.
c. {$storewishlist:removeproduct:{$product:id}[htmlclass:class class2 class3[:btnname:sometext]]}
   htmlclass:class class2 class3 - added html classes, where classX is name of html class.
   btnname:sometext - where sometext is custom text for button name.

d. {$storewishlist:lastaddeduserwishlist:{$product:id}} - Display user full name who last added product to Wishlist.

11. Inventory notification widget:
 a. {$notifyme:addTonotificationlist:{$product:id}[:htmlclass:class class2 class3[:btnname:sometext[:profile]]]]}
    htmlclass:class class2 class3 - added html classes, where classX is name of html class.
    btnname:sometext - where sometext is custom text for button name.
    profile - redirected user on profile after add product to notification list.
 b. {$notifyme:notifylist:_products notification list[:limit[:10]]}
    Option "_products notification list" template - used for show product on page.
    Into this template you can use any product and store widgets and magicspases.

    Option limit - you can set custom limit before pagination "show more" for product list ex. {$notifyme:_products notification list:limit:10}
    where 10 - is count of products. By default limit is 20.

 c. {$notifyme:removeproduct:{$product:id}[htmlclass:class class2 class3[:btnname:sometext]]}
    htmlclass:class class2 class3 - added html classes, where classX is name of html class.
    btnname:sometext - where sometext is custom text for button name.
 d. {$notifyme:isnotified:{$product:id}}
    Display if customer already have the notification by email.

 e. Action emails lexems:
    {notify:productname} - Display product name
    {notify:productdescription} - Display product short description
    {customer:fullname} - Display customer full name
    {notify:producturl} - Display link to product page

12. Product custom params widget
{$productcustomparam:[:text|select[:name[:readonly]]]} - on the product page
{$productcustomparam:[:prodid[:text|select[:name[:readonly]]]]} - in the product list

allowed types
a) "text" - display values for text custom params
b) "select" - display values for dropdown custom params

readonly - return just a text result

Example:

{$productcustomparam:select:dogs:readonly} - on the product page
{$productcustomparam:{$product:id}:select:dogs:readonly} - in the product list

