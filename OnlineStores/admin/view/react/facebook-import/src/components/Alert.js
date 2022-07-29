import React from 'react';

const Alert = (props) => {
  const {message} = props;

  return(
    <div className="col-md-12">
      <div className="alert alert-danger">
        {message}
      </div>
    </div>
  );
}

export default Alert;