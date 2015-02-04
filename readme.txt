E-commerce store plugin

More info you can find here http://www.seotoaster.com/ecommerce-cms.html and http://seotoaster-documentation.seotoaster.com/

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
    {$product:options} - Displays product options as a drop-down list, text field, a set of check boxes or a set of radio buttons.
                         Can be displayed both on the product page and in the products list.
    {$product:description[:_short_|full]} - Displays a short or a full description of the product.
                        _short_|full - short or full description output type
    {$product:related[:img]} - Displays a list of relevant products on a specific product page.
                       img - displays a list of relevant products with image
    {$product:editproduct} - This widget displays a link to the edit page of the product that is visible only to users with a copywriter or administrator rights.
    {$product:id} - Displays product id
    {$product:inventory} - Displays "In stock" or "Out of stock" message
    {$product:freeshipping[:sometext]} - Displays <span class="product-free-shipping">sometext</span> if free shipping enabled for this product
        sometext - custom text inside span element

2. Product list widget.
    {$productlist:template_name[:tagnames-tag1,tag2,...,tagN[:brands-brand1,brand2,...,brandN[:order-name,price,brand,date]]]} - Creates a list of products using the same tags.
    template_name - template name for the product list
    tagnames-tag1,tag2,...,tagN - filtering by product tags
    brands-brand1,brand2,...,brandN - filtering by product brands
    order-name,price,brand,date - sorting of the list by: name, price, brand and date
3. Store widgets:
    {$store:cartblock} - Displays information about the state of the cart (the amount of goods, price, etc.) and link to the shopping cart page for the current user.

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
    {$postpurchase:discount[:clean[:withouttax]]} -> purchase discount (with tax if tax enabled)
    {$postpurchase:shippingTax[:clean]} -> shipping tax
    {$postpurchase:discountTax[:clean]} -> discount tax
    {$postpurchase:subTotalTax[:clean]} -> subtotal tax
    {$postpurchase:id} -> cart id

    ######### Billing information #############
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

    ######### Shipping information #############
    {$postpurchase:shipping:lastname} -> billing lastname
    {$postpurchase:shipping:firstname} -> billing firstname
    {$postpurchase:shipping:address1} -> billing address
    {$postpurchase:shipping:address2} -> billing address
    {$postpurchase:shipping:city}     -> billing address city
    {$postpurchase:shipping:state}   -> billing address state
    {$postpurchase:shipping:zip} -> billing address zip
    {$postpurchase:shipping:country} -> billing address country
    {$postpurchase:shipping:phone} -> billing address phone
    {$postpurchase:shipping:mobile} -> billing address mobile
    {$postpurchase:shipping:email} -> billing address email

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


