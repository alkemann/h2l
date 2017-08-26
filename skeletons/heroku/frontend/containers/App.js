import React from "react";
import {connect} from "react-redux";

import { loadExample } from "../actions";


class App extends React.Component {
    componentWillMount() {
        this.props.loadExample();
    }

    render() {
        return (
            <div className="container">
                <h1>Home</h1>
                <h4>Word of the day: <span>{this.props.example.word}</span>!</h4>
                <p>{this.props.example.meaning}</p>
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
