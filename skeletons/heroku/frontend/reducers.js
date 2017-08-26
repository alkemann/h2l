const api = (state = {
    example: {word: "Nothing", meaning: "-"}
}, action) => {
    switch (action.type) {
        case "EXAMPLE_TYPE_FULFILLED":
            state = {
                ...state,
                example: action.payload.response
            }
            break;

    }
    return state;
};

export default api;