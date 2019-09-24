import {createStore, combineReducers, applyMiddleware} from "redux";
import promise from "redux-promise-middleware";
import api from "./reducers";

export default createStore(
    combineReducers({
        api
    }),
    window.__REDUX_DEVTOOLS_EXTENSION__ && window.__REDUX_DEVTOOLS_EXTENSION__(),
    applyMiddleware(
        promise()
    )
);