@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header font-weight-bold">Create a product</div>

                <div class="card-body">
                    @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                    @endif

                    <form id="createProductForm">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" class="form-control" name="name" id="name" aria-describedby="" required>
                            <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity In Stock</label>
                            <input type="text" name="quantity" class="form-control" id="quantity" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price Per Item</label>
                            <input type="text" name="price" class="form-control" id="price" required>
                        </div>
                        <button type="submit" id="createBtn" class="btn btn-outline-primary">Create</button>
                    </form>
                </div>
            </div>

            <div class="card mt-5">
                <div class="font-weight-bold card-header">
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">&nbsp;</th>
                                    <th scope="col">Product Name</th>
                                    <th scope="col">Quantity In Stock</th>
                                    <th scope="col">Price Per Item</th>
                                    <th scope="col">Datetime Submitted</th>
                                    <th scope="col">Total Value</th>
                                    <th scope="col">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody id="products">
                                @isset($products)
                                @foreach($products as $key => $product)
                                <tr>
                                    <td>{{ $key + 1 }} <input type="hidden" id="product-id" value="{{ $key }}"> </td>
                                    <td id="product-name{{ $key }}">{{ $product->name }}</td>
                                    <td id="product-quantity{{ $key }}">{{ $product->quantity }}</td>
                                    <td id="product-price{{ $key }}">${{ $product->price }}</td>
                                    <td>{{ $product->created_at }}</td>
                                    <td id="product-total{{ $key }}">${{ $product->total }} </td>
                                    <td scope="col" class="pointer-cursor" data-toggle="modal" data-target="#editProductModal" onclick="editProduct({{ $key}})"><i class="bi bi-pencil-fill"></i></td>
                                </tr>
                                @endforeach
                                <tr>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>&nbsp;</td>
                                    <td>
                                        <div class="font-weight-bold">SUM TOTAL:</div>
                                    </td>
                                    <td>$<span id="gross-total">{{ $gross_total }}</span></td>
                                    <td>&nbsp;</td>
                                </tr>
                                @endisset
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="updateProductForm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" class="form-control" name="name" id="edit-name" aria-describedby="" required>
                        <input type="hidden" value="" id="product-id">
                        <!-- <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone else.</small> -->
                    </div>
                    <div class="form-group">
                        <label for="quantity">Quantity In Stock</label>
                        <input type="text" name="quantity" class="form-control" id="edit-quantity" required>
                    </div>
                    <div class="form-group">
                        <label for="price">Price Per Item</label>
                        <input type="text" name="price" class="form-control" id="edit-price" required>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" onclick="updateProduct(event)" id="updateBtn" class="btn btn-outline-primary float-left">Update</button>
                    <button type="button" class="btn btn-danger float-right" data-dismiss="modal">Close</button>

                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript">
    let createProductForm = document.querySelector('#createProductForm');
    let updateProductForm = document.querySelector('#updateProductForm');

    /**
     * @param {String} HTML representing a single element
     * @return {Element}
     * Solution taken from the stackoverflow answer page
     * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
     */
    function htmlToElement(html) {
        var template = document.createElement('template');
        html = html.trim(); 
        template.innerHTML = html;
        return template.content.firstChild;
    }

    createProductForm.addEventListener('submit', function(e) {
        e.preventDefault();

        let lastRow = document.querySelector('#products tr:last-child');
        let name = document.querySelector('#name');
        let quantity = document.querySelector('#quantity');
        let price = document.querySelector('#price');
        let createBtn = document.querySelector('#createBtn');
        let grossTotal = document.querySelector('#gross-total');

        createBtn.innerHTML = 'Saving...';
        axios.post("/create", {
                "_token": "{{ csrf_token() }}",
                name: name.value,
                quantity: quantity.value,
                price: price.value
            })
            .then(function(response) {
                if (response.data) {
                    let products = document.querySelector('#products');
                    let productRow = document.querySelectorAll('#products > tr');

                    //Create table row and the columns
                    let tr = document.createElement('tr');
                    let keyColumn = document.createElement('td');
                    let nameColumn = document.createElement('td');
                    let quantityColumn = document.createElement('td');
                    let priceColumn = document.createElement('td');
                    let dateTimeColumn = document.createElement('td');
                    let totalColumn = document.createElement('td');
                    let editIcon = htmlToElement(`<td scope="col" class="pointer-cursor" data-toggle="modal" data-target="#editProductModal" onclick="editProduct(${response.data.id})"><i class="bi bi-pencil-fill"></i></td>`);


                    //Populate the columns with data from DB
                    keyColumn.innerHTML = productRow.length;
                    nameColumn.innerHTML = response.data.name;
                    quantityColumn.innerHTML = response.data.quantity;
                    priceColumn.innerHTML = `$${response.data.price}`;
                    dateTimeColumn.innerHTML = response.data.created_at;
                    totalColumn.innerHTML = `$${response.data.total}`;
                    grossTotal.innerHTML = parseInt(grossTotal.innerHTML) + parseInt(response.data.total);

                    // Set IDs on the columns
                    nameColumn.setAttribute('id', `product-name${response.data.id}`);
                    quantityColumn.setAttribute('id', `product-quantity${response.data.id}`);
                    priceColumn.setAttribute('id', `product-price${response.data.id}`);
                    totalColumn.setAttribute('id', `product-total${response.data.id}`);

                    // Append columns to table row and append table row to table body
                    tr.appendChild(keyColumn);
                    tr.appendChild(nameColumn);
                    tr.appendChild(quantityColumn);
                    tr.appendChild(priceColumn);
                    tr.appendChild(dateTimeColumn);
                    tr.appendChild(totalColumn);
                    tr.appendChild(editIcon);
                    products.insertBefore(tr, lastRow);
                }
                createBtn.innerHTML = 'Create';
                name.value = '';
                quantity.value = '';
                price.value = '';

            })
            .catch(function(error) {
                console.log(error);
            });


    });

    function updateProduct(e) {
        e.preventDefault();

        let name = document.querySelector('#edit-name');
        let quantity = document.querySelector('#edit-quantity');
        let price = document.querySelector('#edit-price');
        let productId = document.querySelector('#product-id');

        let updateBtn = document.querySelector('#updateBtn');
        let grossTotal = document.querySelector('#gross-total');

        updateBtn.innerHTML = 'Updating...';
        axios.post("/update", {
                "_token": "{{ csrf_token() }}",
                id: productId.value,
                name: name.value,
                quantity: quantity.value,
                price: price.value
            })
            .then(function(response) {
                console.log(response.data);
                if (response.data) {
                    let products = document.querySelector('#products');

                    document.querySelector(`#product-name${productId.value}`).innerHTML = response.data.name;
                    document.querySelector(`#product-quantity${productId.value}`).innerHTML = response.data.quantity;
                    document.querySelector(`#product-price${productId.value}`).innerHTML = `$${response.data.price}`;
                    document.querySelector(`#product-total${productId.value}`).innerHTML = `$${response.data.total}`;

                    updateBtn.innerHTML = 'Updated';
                    updateBtn.classList.remove('btn-outline-primary');
                    updateBtn.classList.add('btn-outline-success');
                    name.value = '';
                    quantity.value = '';
                    price.value = '';

                    setTimeout(() => {
                        updateBtn.classList.remove('btn-outline-success');
                        updateBtn.classList.add('btn-outline-primary');
                        updateBtn.innerHTML = 'Update';
                        $('#editProductModal').modal('hide');
                    }, 2000);

                    let allProductsTotal = document.querySelectorAll('#products td[id*="product-total"]');
                    let total = 0;
                    allProductsTotal.forEach(element => {
                        total = total + parseInt(element.innerHTML.substring(1));
                    });
                    grossTotal.innerHTML = total;
                }

            })
            .catch(function(error) {
                console.log(error);
            });
    }

    function editProduct(id) {
        let name = document.querySelector('#edit-name');
        let quantity = document.querySelector('#edit-quantity');
        let price = document.querySelector('#edit-price');
        let productId = document.querySelector('#product-id');

        name.value = document.querySelector(`#product-name${id}`).innerHTML;
        quantity.value = parseInt(document.querySelector(`#product-quantity${id}`).innerHTML);
        price.value = parseInt(document.querySelector(`#product-price${id}`).innerHTML.substring(1));
        productId.value = id;
    }
</script>
@endsection