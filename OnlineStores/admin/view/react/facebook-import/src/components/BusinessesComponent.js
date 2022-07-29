import React from "react";
import { FormattedMessage } from "react-intl";

const BusinessesComponent = props => {
  
  const {items,onChange,value} = props;
  
  const onChangeValue = (event) => {
    onChange(event.target.value);
  }

  return (
    <div className="col-md-6">
      <div className="form-group">
        <label className="control-label"><FormattedMessage id="app.selectBusinessLbl" /></label>
        <select
          id="business_id"
          className="form-control"
          onChange={onChangeValue}
        >
          <option></option>
          {items.map(item => (
            <option selected={item.id===value} value={item.id} key={item.name}>
              {item.name}
            </option>
          ))}
        </select>
      </div>
    </div>
  );
};

export default BusinessesComponent;