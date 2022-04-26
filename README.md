API Contains 1000 Products

            -> api/products/get?access_token={generated Token}
                %{GET}%
                # Provides only 20 Initial Products 

            -> api/products/get/{limit}/{page}?access_token={generated Token}
                %{GET}%
                # Limit no of products
                # Page No

            -> api/products/search/{keyword}?access_token={generated Token}
                %{GET}%
                # returns similar name Products

            -> api/get/product?access_token={generated Token}
                %{POST}%
                # "product_id"

            -> api/order/create?access_token={generated Token}
                %{POST}%
                # "customer_name" = {name}
                # "product_id" = {id}
                # "product_quantity" = {quantity}

            -> api/order/update?access_token={generated Token}
                %{PUT}%
                # "order_id" = {order id}
                # "order_status" = {order status}

            -> api/product/getSample
                %{GET}%
                # returns Sample Product for reference
