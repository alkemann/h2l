const api = (state = {
    example: null
}, action) => {
    switch (action.type) {
        case "EXAMPLE_TYPE":
            state = {
                ...state,
                example: action.payload.response
            }
            break;

    }
    return state;
};

export default api;