import React from "react";
import { FormattedMessage } from "react-intl";

const ProductsComponent = props => {
  const { items, onChange, allIsChecked} = props;

  const handleOnChangeLocal = (event) => {
    onChange(event);
  }

  return (
    <div className="col-md-12">
      <table className="table datatable-show-all">
        <thead>
          <tr>
            <th><input type="checkbox" name="allChecked" checked={allIsChecked} onChange={handleOnChangeLocal} /></th>
            <th><FormattedMessage id="table.image" /></th>
            <th><FormattedMessage id="table.name" /></th>
            <th><FormattedMessage id="table.price" /></th>
            <th><FormattedMessage id="table.currency" /></th>
            <th><FormattedMessage id="table.brand" /></th>
            <th className="text-center"><FormattedMessage id="table.inStore" /></th>
          </tr>
          {items.map(item => {
            return (
              <tr key={item.id}>
                <td>
                  <input type="checkbox" name={item.name} checked={item.isChecked} onChange={handleOnChangeLocal} value={item.id} />
                </td>
                <td><img className="img-md" src={item.image_url} /></td>
                <td>{item.name}</td>
                <td>{item.price}</td>
                <td>{item.currency}</td>
                <td>{item.brand}</td>
                <td className="text-center">
                  {item.is_imported ? <i className="fa fa-lg fa-check" style={{color: "green"}}></i> : <i className="fa fa-lg fa-times" style={{color: "red"}}></i>}
                </td>
              </tr>
            );
          })}
        </thead>
        <tbody></tbody>
      </table>
    </div>
  );
};

export default ProductsComponent;
