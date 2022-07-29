import React from "react";
import { BrowserRouter as Router, Switch, Route, Link } from "react-router-dom";
import { FormattedMessage } from "react-intl";

import axios from "axios";
import _ from "lodash";
import qs from "qs";

import Loader from "./components/Loader";
import BusinessesComponent from "./components/BusinessesComponent";
import CatalogsComponent from "./components/CatalogsComponent";
import ProductsComponent from "./components/ProductsComponent";
import Alert from "./components/Alert";

class App extends React.Component {
  state = {
    isLoaded: false,
    language: null,
    error: null,
    success: null,
    next_cursor: null,
    previous_cursor: null,
    businesses: [],
    catalogs: [],
    products: [],
    selectedBusiness: {},
    selectedCatalog: {},
    selectedProducts: [],
    allChecked: false,
    productCount: 0,
    current_page: 0,
    max_pages: 0
  };

  onPageChanged = data => {};

  handleChange = e => {
    let itemName = e.target.name;
    let itemId = e.target.value;
    let checked = e.target.checked;
    let { products, allChecked } = this.state;

    if (itemName === "allChecked") {
      allChecked = checked;
      products = products.map(item => ({ ...item, isChecked: checked }));
    } else {
      products = products.map(item =>
        item.id === itemId ? { ...item, isChecked: checked } : item
      );
      allChecked = products.every(item => item.isChecked);
    }

    this.setState({
      products: products,
      allChecked: allChecked
    });
  };

  componentDidMount() {
    //Get businesses
    fetch(`/admin/module/facebook_import/getBusinesses`)
      .then(res => {
        return res.json();
      })
      .then(data => {
        if (data.error) {
          this.setState({
            isLoaded: true,
            error: data.error
          });
        } else {
          this.setState({
            isLoaded: true,
            businesses: data.items
          });
        }
      });
  }

  onBusinessChange = value => {
    this.setState({
      catalogs: [],
      products: [],
      selectedBusiness: value,
      selectedCatalog: null,
      isLoaded: false,
      error: null,
      success: null,
      next_cursor: null,
      previous_cursor: null,
      productCount: 0
    });

    if (!_.isEmpty(value)) {
      fetch("/admin/module/facebook_import/getCatalogs?business_id=" + value)
        .then(res => {
          return res.json();
        })
        .then(data => {
          if (data.error) {
            this.setState({
              isLoaded: true,
              error: data.error
            });
          } else {
            //check if the response has catalogs
            if (_.has(data, "owned_product_catalogs")) {
              this.setState({
                isLoaded: true,
                catalogs: data.owned_product_catalogs.data
              });
            } else {
              this.setState({
                isLoaded: true,
                catalogs: []
              });
            }
          }
        })
        .catch(error => {
          console.log(error);
        });
    } else {
      this.setState({
        isLoaded: true,
        catalogs: []
      });
    }
  };

  onCatalogChange = value => {
    this.setState({
      products: [],
      selectedCatalog: value,
      isLoaded: false,
      error: null,
      success: null,
      current_page: 1
    });

    if (!_.isEmpty(value)) {
      this.setState({
        selectedCatalog: value,
        products: []
      });

      fetch("/admin/module/facebook_import/getProducts?catalog_id=" + value)
        .then(res => {
          return res.json();
        })
        .then(data => {
          if (data.error) {
            this.setState({
              isLoaded: true,
              error: data.error,
              products: []
            });
          } else if (_.isEmpty(data.products) || data.products.length < 1) {
            this.setState({
              isLoaded: true,
              products: []
            });
          } else {
            console.log("data:");
            console.log(data);
            let new_products = data.products.map(item => {
              return { ...item, isChecked: false };
            });

            console.log("new_data:");
            console.log(new_products);

            this.setState({
              isLoaded: true,
              products: new_products,
              totalPages: data.total_pages,
              productCount: data.productCount,
              next_cursor: data.next_cursor,
              previous_cursor: data.previous_cursor,
              max_pages: Math.ceil(data.productCount / 100)
            });
          }
        })
        .catch(error => {
          console.log(error);
        });
    } else {
      this.setState({
        isLoaded: true,
        products: []
      });
    }
  };

  handleImport = value => {
    this.setState({
      isLoaded: false,
      error: null,
      success: null
    });

    const d = qs.stringify({
      products: _.filter(this.state.products, ["isChecked", true])
    });

    axios({
      method: "post",
      url: "/admin/module/facebook_import/handleImport",
      data: d
    })
      .then(res => {
        if (res.data.status == "error") {
          this.setState({
            error: res.data.message
          });
        }

        if (res.data.status == "success") {
          this.setState(state => ({
            ...state,
            error: null,
            success: res.data.message,
            allChecked: false,
            products: this.state.products.map(product => ({
              ...product,
              isChecked: false
            }))
          }));
        }

        this.setState({
          isLoaded: true
        });
      })
      .catch(err => {
        console.log(err);
      });
  };

  handleImportAll = value => {
    this.setState({
      isLoaded: false,
      error: null,
      success: null
    });

    const d = qs.stringify({
      catalog_id: this.state.selectedCatalog
    });

    axios({
      method: "post",
      url: "/admin/module/facebook_import/handleImportAll",
      data: d
    })
      .then(res => {
        if (res.data.status == "error") {
          this.setState({
            error: res.data.message
          });
        }

        if (res.data.status == "success") {
          this.setState(state => ({
            ...state,
            error: null,
            success: res.data.message,
          }));
        }

        this.setState({
          isLoaded: true
        });
      })
      .catch(err => {
        console.log(err);
      });
  };

  handleNext = () => {
    this.setState({
      products: [],
      isLoaded: false,
      error: null,
      success: null,
      allChecked: false
    });

    if (!_.isEmpty(this.state.next_cursor)) {
      this.setState({
        products: []
      });

      fetch(
        "/admin/module/facebook_import/getProducts?catalog_id=" +
          this.state.selectedCatalog +
          "&next_cursor=" +
          this.state.next_cursor
      )
        .then(res => {
          return res.json();
        })
        .then(data => {
          if (data.error) {
            this.setState({
              isLoaded: true,
              error: data.error,
              products: []
            });
          } else if (data.length < 1) {
            this.setState({
              isLoaded: true,
              products: []
            });
          } else {
            console.log("next data:");
            console.log(data);

            let new_products = data.products.map(item => {
              return { ...item, isChecked: false };
            });

            console.log("next new_data:");
            console.log(new_products);

            this.setState({
              isLoaded: true,
              products: new_products,
              totalPages: data.total_pages,
              productCount: data.productCount,
              next_cursor: data.next_cursor,
              previous_cursor: data.previous_cursor,
              current_page: this.state.current_page + 1
            });
          }
        })
        .catch(error => {
          console.log(error);
        });
    } else {
      this.setState({
        isLoaded: true,
        products: []
      });
    }
  };

  handlePrevious = () => {
    this.setState({
      products: [],
      isLoaded: false,
      error: null,
      success: null,
      allChecked: false
    });

    if (!_.isEmpty(this.state.previous_cursor)) {
      this.setState({
        products: []
      });

      fetch(
        "/admin/module/facebook_import/getProducts?catalog_id=" +
          this.state.selectedCatalog +
          "&previous_cursor=" +
          this.state.previous_cursor
      )
        .then(res => {
          return res.json();
        })
        .then(data => {
          if (data.error) {
            this.setState({
              isLoaded: true,
              error: data.error,
              products: []
            });
          } else if (data.length < 1) {
            this.setState({
              isLoaded: true,
              products: []
            });
          } else {
            console.log("next data:");
            console.log(data);

            let new_products = data.products.map(item => {
              return { ...item, isChecked: false };
            });

            console.log("next new_data:");
            console.log(new_products);

            this.setState({
              isLoaded: true,
              products: new_products,
              totalPages: data.total_pages,
              productCount: data.productCount,
              next_cursor: data.next_cursor,
              previous_cursor: data.previous_cursor,
              current_page: this.state.current_page - 1
            });
          }
        })
        .catch(error => {
          console.log(error);
        });
    } else {
      this.setState({
        isLoaded: true,
        products: []
      });
    }
  };

  render() {


    const businessRender =
      !Array.isArray(this.state.businesses) ||
      this.state.businesses.length < 1 ? (
        <Alert message={<FormattedMessage id="app.noBusiness" />} />
      ) : (
        <BusinessesComponent
          items={this.state.businesses}
          onChange={this.onBusinessChange}
          value={this.state.selectedBusiness}
        />
      );

    const catalogsRender = _.isEmpty(this.state.selectedBusiness) ? (
      <div className="row">
        <div className="col-md-12">
          <div className="alert alert-warning">
            <p>{<FormattedMessage id="app.selectBusiness" />}</p>
          </div>
        </div>
      </div>
    ) : !_.isArray(this.state.catalogs) || this.state.catalogs.length < 1 ? (
      <div className="row">
        <div className="col-md-12">
          <div className="alert alert-warning">
            <p>{<FormattedMessage id="app.noCatalogs" />}</p>
          </div>
        </div>
      </div>
    ) : (
      <CatalogsComponent
        items={this.state.catalogs}
        onChange={this.onCatalogChange}
        value={this.state.selectedCatalog}
      />
    );

    const productsRender = _.isEmpty(this.state.selectedCatalog) ? (
      <div className="row">
        <div className="col-md-12">
          <div className="alert alert-warning">
            <p>{<FormattedMessage id="app.selectCatalog" />}</p>
          </div>
        </div>
      </div>
    ) : !_.isArray(this.state.products) || this.state.products.length < 1 ? (
      <div className="row">
        <div className="col-md-12">
          <div className="alert alert-warning">
            <p>{<FormattedMessage id="app.noProducts" />}</p>
          </div>
        </div>
      </div>
    ) : (
      <ProductsComponent
        allIsChecked={this.state.allChecked}
        onChange={this.handleChange}
        items={this.state.products}
      />
    );

    const selectedProducts = _.filter(this.state.products, ["isChecked", true]);

    const selectedProductsRender =
      selectedProducts.length > 0 ? (
        <p>
          <span>{<FormattedMessage id="app.selectedProducts" values={{count: selectedProducts.length}} />}</span>
        </p>
      ) : (
        <p>
          <span><FormattedMessage id="app.noProductsSelected" /></span>
        </p>
      );

    const importButtonRender =
      selectedProducts.length > 0 ? (
        <button
          onClick={this.handleImport}
          className="btn btn-success"
          style={{ margin: "0 10px" }}
        >
          {<FormattedMessage id="app.importSelectedProducts" values={{count: selectedProducts.length}} />}
        </button>
      ) : (
        <button
          onClick={this.handleImport}
          className="btn btn-success"
          style={{ margin: "0 10px" }}
          disabled
        >
          <FormattedMessage id="app.importProducts" />
        </button>
      );

    const importAllButtonRender = this.state.products.length > 0 && (
      <button
        onClick={this.handleImportAll}
        className="btn btn-success"
        style={{ margin: "0 10px" }}
      >
        <FormattedMessage id="app.importAllProducts" />
      </button>
    );

    const fetchButtonRender = !_.isEmpty(this.state.selectedCatalog) && (
      <div className="col-md-6">
        <div className="form-group">
          {importButtonRender}
          {importAllButtonRender}
        </div>
        {selectedProductsRender}
      </div>
    );

    const successMessageRender = this.state.success != null && (
      <div className="col-md-12">
        <div className="alert alert-success">{this.state.success}</div>
      </div>
    );

    const disable_previous = this.state.current_page <= 1 ? true : false;
    const disable_next =
      this.state.current_page == this.state.max_pages ? true : false;

    const paginationRender = (
      <div className="col-md-12">
        <ul class="pager text-right">
          <li>
            <button
              className="btn btn-primary"
              onClick={this.handlePrevious}
              disabled={disable_previous}
            >
              <FormattedMessage id="app.previousBtn" />
            </button>
          </li>
          <li>
            <button
              className="btn btn-primary"
              onClick={this.handleNext}
              disabled={disable_next}
            >
              <FormattedMessage id="app.nextBtn" />
            </button>
          </li>
        </ul>
      </div>
    );

    return (
      <Router>
        {!this.state.isLoaded ? (
          <Loader />
        ) : (
          <div>
            {successMessageRender}
            {this.state.error && <Alert message={this.state.error} />}

            { businessRender}

            {!_.isEmpty(this.state.selectedBusiness) && catalogsRender}
            
            {fetchButtonRender}

            {!_.isEmpty(this.state.products) && this.state.productCount > 100
              ? paginationRender
              : ""}

            {!_.isEmpty(this.state.catalogs) && productsRender}

            {!_.isEmpty(this.state.products) && this.state.productCount > 100
              ? paginationRender
              : ""}
          </div>
        )}
      </Router>
    );
  }
}

export default App;
