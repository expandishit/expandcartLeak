import React from "react";

const ProductsTable = props => {
  const { items } = props;

  const checkForProducts = () => {
    console.log(items);
  };

  return (
    <div className="products-table">
      <div class="col-md-12">
        <table class="table datatable-show-all">
          <thead>
            <tr>
              <th>Select</th>
              <th>Image</th>
              <th>Name</th>
              <th>Price</th>
              <th>Currency</th>
              <th>Url</th>
              <th>Brand</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            {items.map(product => {
              <tr className={'product-'+product.id}>
                <td>
                  <input type="checkbox" value={product.id} />
                </td>
                <td>
                  <img src={product.image_url} class="img-md" />
                </td>
                <td>link</td>
                <td>link</td>
                <td>link</td>
                <td>
                  link
                </td>
                <td>{product.brand}</td>
                <td class="text-center">
                  <ul class="icons-list">
                    <li class="dropdown">
                      <a
                        href="#"
                        class="dropdown-toggle"
                        data-toggle="dropdown"
                      >
                        <i class="icon-menu9"></i>
                      </a>

                      <ul class="dropdown-menu dropdown-menu-right">
                        <li>
                          <a href="#">
                            <i class="icon-file-pdf"></i> Export to .pdf
                          </a>
                        </li>
                        <li>
                          <a href="#">
                            <i class="icon-file-excel"></i> Export to .csv
                          </a>
                        </li>
                        <li>
                          <a href="#">
                            <i class="icon-file-word"></i> Export to .doc
                          </a>
                        </li>
                      </ul>
                    </li>
                  </ul>
                </td>
              </tr>;
            })}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default ProductsTable;
