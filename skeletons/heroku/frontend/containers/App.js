import React from "react";
import {connect} from "react-redux";

import { loadExample } from "../actions";


class App extends React.Component {
    // componentWillMount() {
    // }

    render() {
        return (
            <div className="container">
                <p>Some app this is</p>
            </div>
        );
    }
}

const mapStateToProps = (state) => {
  return {
      example: state.api.example,
  };
};

const mapDispatchToProps = (dispatch) => {
    return {
        loadExample: () => { dispatch(loadExample()); },
    };
};

export default connect(mapStateToProps, mapDispatchToProps)(App);
