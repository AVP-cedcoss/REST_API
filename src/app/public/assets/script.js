var fields = 0;
var variation = 0;
var subvariation = [];
$(document).ready(function () {
  //On Load Orders Page Lists the selected Products Variant
    // listVariantsOrder(productDetail($("#product_id_order").val()));
    // calculatePrice(productDetail($("#product_id_order").val()));

  /**
   * Close Pop Up Modal View
   */
  $("#popupview").click(function (e) {
    e.preventDefault();
    $("#popupview").hide();
  });

  /**********************************************Additional Field Trigger Start************************************** */
  /**
   * On click Add Field
   */
  $("#addField").click(function (e) {
    e.preventDefault();
    addField();
  });

  /**
   * On click Delete Field
   */
  $("#removeField").click(function (e) {
    e.preventDefault();
    removeField();
  });

  /**
   * On click deleteEntry
   */
  $("#fieldsDiv").on("click", ".deleteEntry", function (e) {
    e.preventDefault();
    deleteEntry($(this).data("id"));
  });
  /**********************************************Additional Field Trigger End************************************** */

  /**********************************************Variation Field Trigger Start************************************** */
  /**
   * On click Add Variation
   */
  $("#addVariation").click(function (e) {
    e.preventDefault();
    addVariation();
  });

  /**
   * On click Add Variation Field
   */
  $("#variationFieldsDiv").on("click", "#addVariationField", function (e) {
    e.preventDefault();
    addVariationField($(this).data("id"));
  });

  /**
   * On click Delete Variation Field
   */
  $("#variationFieldsDiv").on("click", "#deleteVariationField", function (e) {
    e.preventDefault();
    deleteVariationField($(this).data("id"));
  });

  /**
   * On click Delete Variation Entry
   */
  $("#variationFieldsDiv").on(
    "click",
    ".deleteVariationEntryField",
    function (e) {
      e.preventDefault();
      $(this).parent().remove();
      subvariation[id] = -1;
    }
  );
  /**********************************************Variation Field Trigger End************************************** */

  /**
   * on Click quickPeek Button in Listing
   */
  $(".table-responsive").on("click", ".quickPeek", function (e) {
    e.preventDefault();
    displayPopup(productDetail($(this).data("id")));
  });

  /**
   * On keyup Event in Search Box Listing of Products
   */
  $("#search").keyup(function (e) {
    e.preventDefault();
    searchProduct($(this).val());
  });

  /***************************************************************ORDER************************************************ */

  $("#product_id_order").change(function (e) {
    e.preventDefault();
    listVariantsOrder(productDetail($(this).val()));
    calculatePrice(productDetail($(this).val()));
  });

  $("#tbody").on("click", "#statusUpdateOrder", function (e) {
    e.preventDefault();
    updateStatusOrder($(this).data("id"), $(this).val());
  });

  $("#filterDate").click(function (e) {
    if ($("#filterDate").val() == 'Custom') {
        e.preventDefault();
        filterDate();
    }
  });

  /***************************************************************ORDER************************************************ */
});

/*---------------------------------------------------ORDER PAGE START----------------------------------------------------*/

/**
 * Returns Product Detail
 */
function productDetail(id) {
  let Data;
  $.ajax({
    method: "POST",
    async: false,
    url: "/public/admin/product/productDetail",
    data: {
      id: id,
    },
    datatype: "JSON",
  }).done(function (data) {
    data = JSON.parse(data);
    Data = data;
  });
  return Data;
}

/**
 * Lists Selected Product's Variants
 */
function listVariantsOrder(Data) {
  if (typeof Data["variant"] != "undefined") {
    var html = `
            <div class='container'>`;
    for (let key in Data["variant"]) {
      html +=
        `<br>
            <div class='border border-1'>
                <input type='radio' name='variant' value='` +
        key +
        `'>`;

      for (let value in Data["variant"][key]) {
        html +=
          `
                    <h6>` +
          value +
          ` : ` +
          Data["variant"][key][value] +
          `</h6>
                `;
      }
      html += `</div>`;
    }
    html += `</div>`;
    $("#variantsOrderDiv").html(html);
  }
}

function calculatePrice(Data) {
  $("#product_price").val(Data["product_price"]);
}

function updateStatusOrder(id, status) {
  $.ajax({
    method: "POST",
    async: false,
    url: "/public/admin/order/updateStatus",
    data: {
      id: id,
      status: status,
    },
    datatype: "JSON",
  }).done(function () {
    $("#status" + id).html(status);
  });
}

function filterDate()
{
    let html=`
        <input type='text' class='form-control' name='startDate' placeholder='Start Date (Y-M-D)'>
        <br>
        <input type='text' class='form-control' name='endDate' placeholder='End Date (Y-M-D)'>
    `;
    $('#customDateField').html(html);
}

/*---------------------------------------------------ORDER PAGE END----------------------------------------------------*/

/****************************************************Additional Field Start**************************************** */
/**
 * Add Field on Click
 */
function addField() {
  var html =
    "\
    <div class='input-group' id='meta"+fields +"'>\
        <input type='text' name='additionalKey[]' class='my-2 form-control' placeholder='Additonal Key' required>\
        <input type='text' name='additionalvalue[]' class='m-2 form-control' placeholder='Additional Value' required>\
        <a class='deleteEntry' data-id='" +
    fields +
    "'>\
            <svg xmlns='http://www.w3.org/2000/svg' width='30' height='30' fill='currentColor' class='m-2 bi bi-trash3-fill' viewBox='0 0 16 16'><path d='M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z'/></svg>\
        </a>\
    </div>";
  $("#fieldsDiv").append(html);
  fields += 1;
}

/**
 * Remove Field on Click
 */
function removeField() {
  if (fields > 1) {
    fields -= 1;
    $("#meta" + fields).remove();
  }
}

/**
 * Delete Entry on Click
 */
function deleteEntry(id) {
  $("#variation" + id).remove();
}
/****************************************************Additional Field End**************************************** */

/****************************************************Variation Field Start**************************************** */
/**
 * Add Variations Field on Click
 */
function addVariation() {
  subvariation[variation] = [];
  subvariation[variation] = 0;
  var html =
    "\
    <div class='m-2 p-2 border border-2' id='variationMainDiv" +
    variation +
    "'>\
        <a class='p-2 btn btn-primary rounded text-light' data-id='" +
    variation +
    "', id='addVariationField'>Add Variation Field</a>\
        <a class='p-2 btn btn-danger rounded text-light' data-id='" +
    variation +
    "' id='deleteVariationField'>Delete Variation Field</a>\
        <div id='variation" +
    variation +
    "'>\
            <div class='input-group' id='varField[" +
    variation +
    "][0]'>\
                <input type='text' name='variationKey[" +
    variation +
    "][]' class='my-2 form-control' placeholder='Key' required>\
                <input type='text' name='variationValue[" +
    variation +
    "][]' class='m-2 form-control' placeholder='Variation' >\
                <a class='deleteVariationEntryField' data-id='[" +
    variation +
    "][0]'>\
                    <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' fill='currentColor' class='m-2 bi bi-trash3-fill' viewBox='0 0 16 16'><path d='M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z'/></svg>\
                </a>\
            </div>\
        </div>\
        <label>Variant Price</label>\
        <input type='text' name='variationPrice" +
    variation +
    "' class='form-control' placeholder='Price' required>\
    </div>";

  $("#variationFieldsDiv").append(html);
  variation += 1;
}

/**
 * Delete variation Entry on Click
 */
function deleteVariationField(id) {
  $("#variationMainDiv" + id).remove();
  subvariation[id] = 0;
}

/**
 * Add Variation Field
 */
function addVariationField(id) {
  subvariation[id] += 1;
  let sub = subvariation[id];
  var html =
    "\
    <div class='input-group' id='varField[" +
    id +
    "][" +
    sub +
    "]'>\
        <input type='text' name='variationKey[" +
    id +
    "][]' class='my-2 form-control' placeholder='Key' required>\
        <input type='text' name='variationValue[" +
    id +
    "][]' class='m-2 form-control' placeholder='Variation' >\
        <a class='deleteVariationEntryField' data-id='[" +
    id +
    "][" +
    sub +
    "]'>\
            <svg xmlns='http://www.w3.org/2000/svg' width='25' height='25' fill='currentColor' class='m-2 bi bi-trash3-fill' viewBox='0 0 16 16'><path d='M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z'/></svg>\
        </a>\
    </div>";
  $("#variation" + id).append(html);
}

/****************************************************Variation Field End**************************************** */

/**
 * Searches for product based on name
 * @param {Product Name} name
 */
function searchProduct(name) {
  let Data;
  $.ajax({
    method: "POST",
    async: false,
    url: "/public/admin/product/searchProducts",
    data: {
      name: name,
    },
    datatype: "JSON",
  }).done(function (data) {
    data = JSON.parse(data);
    Data = data;
  });
  let html =
    `
        <tr>
            <td>
                ` +
    Data["product_name"] +
    `
            </td>
            <td>
                ` +
    Data["product_category"] +
    `
            </td>
            <td>
                ` +
    Data["product_price"] +
    `
            </td>
            <td>
                ` +
    Data["product_stock"] +
    `
            </td>
            <td>
                <a href="/product/delete?id=` +
    Data["_id"]["$oid"] +
    `" class="btn btn-danger">Delete</a>
            </td>
            <td>
                <a data-id="` +
    Data["_id"]["$oid"] +
    `" class="quickPeek btn btn-primary text-light" data-toggle="modal" data-target="#exampleModal">
                    Quick Peek
                </a>
            </td>
        </tr>
    `;
  $("#tbody").html(html);
}

/*-----------------------------------------------------------------MODAL DISPLAY-------------------------------------------------*/
/**
 * Display Modal
 */
function displayPopup(Data) {
  let html =
    `
    <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">` +
    Data["product_name"] +
    `</h5>
        </div>
        <div class="modal-body">
        <h6>Category: ` +
    Data["product_category"] +
    `</h6>
        <h6>₹ ` +
    Data["product_price"] +
    `/-</h6>
        <h6>Stock: ` +
    Data["product_stock"] +
    `</h6>`;
  if (typeof Data["meta"] != "undefined") {
    for (var key in Data["meta"]) {
      html += `<h6>` + key + ` : ` + Data["meta"][key] + `</h6>`;
    }
  }
  if (typeof Data["variant"] != "undefined") {
    for (var key in Data["variant"]) {
      html += `<h6>Variant ` + (parseInt(key) + 1) + ` :`;
      for (var value in Data["variant"][key]) {
        html += `<br>` + value + ` : ` + Data["variant"][key][value];
      }
      html += `</h6>`;
    }
  }
  html += `</div>
      <!--<div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary">Save changes</button>
      </div>-->
    </div>
  </div>`;
  $("#popupview").html(html);
  $("#popupview").show();
}
