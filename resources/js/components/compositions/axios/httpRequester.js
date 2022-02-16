import { reactive, toRefs } from 'vue';
import axios from 'axios';
import CryptoJS from 'crypto-js';

export function httpRequester() {
    const _loading = {};
    const state = reactive({
        loaded: false,
        loading: false,
        errors: null,
    });

    const get = (url) => { return request({ 'url': url }) };
    const request = async (config) => {
        state.loading = true;
        state.loaded = false;

        let hasError = false;
        let response = null;
        let hash = CryptoJS.SHA256(JSON.stringify(config));
        _loading[hash] = true;
        try {
            response = await axios.request(config);
            state.loaded = true;
            if (state.errors) {
                delete state.errors[hash];
            }
        } catch (err) {
            if (state.errors === null) {
                state.errors = {};
            }

            state.errors[hash] = getErrorsFromAxiosException(err);
            hasError = true;
        }
        delete _loading[hash];
        if (Object.keys(_loading).length === 0) {
            state.loading = false;
        }

        if (hasError) {
            return;
        }
        if (response) {
            if (response.hasOwnProperty('data')) {
                return response.data;
            }
            return response
        }
        return {};
    }

    return { ...toRefs(state), request, get };
}

export default function getErrorsFromAxiosException(err) {
    let error = err;
    if (err.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        console.log(err.response);
        if (err.response.status === 500) {
            error.message = 'Erreur 500';
            if (err.response.data.message) {
                error.message = err.response.data.message;
            }
        } else if (err.response.status === 422) {
            error.message = err.response.data;
        } else if (err.response.message) {
            error.message = err.response.message;
        } else if (err.response.data.message) {
            error.message = err.response.data.message;
        } else if (err.response.status) {
            let message = '';
            switch (err.response.status) {
                case 401: message = 'Authentication rquired'; break;
                case 403: message = 'Access refused'; break;
                case 404: message = 'Page not found'; break;
                case 405: message = 'Method not allowed'; break;
                case 419: message = 'Invalid CSRF token'; break;
                default:
                    message = 'Error ' + err.response.status;
            }
            error.message = message;
        } else {
            error.message = 'Loading error';
        }
    } else if (err.request) {
        // The request was made but no response was received
        // `err.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error('Error', err.request);
        error.message = 'Request failed !';
    } else {
        // Something happened in setting up the request that triggered an Error
        console.error('Error', err.message);
        error.message = err.message;
    }
    console.error(err.config);
    return error;
};
