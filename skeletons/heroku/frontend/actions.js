import api from './utils/Api';

export function loadExample() {
    return {
      type: "EXAMPLE_TYPE",
      payload: api('example/something')
    };
}
