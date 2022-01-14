import {reactive, toRefs,} from '@vue/composition-api';
import axios from 'axios';
import CryptoJS from 'crypto-js';

export function httpRequester() {
    const _loading = {};
    const state = reactive({
        loaded: false,
        loading: false,
        errors: null,
    })

    const get = (url) => { return request({'url': url}) };
    const request = async (config) => {
        state.loading = true;
        state.loaded = false;
        state.errors = null;

        let response = null;
        let hash = CryptoJS.SHA256(JSON.stringify(config));
        _loading[hash] = true;
        try {
            response = await axios.request(config);
            state.loaded = true;
        } catch(error) {
            state.errors = getErrorsFromAxiosException(error);
        }
        delete _loading[hash];
        if(Object.keys(_loading).length === 0) {
            state.loading = false;
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

export default function getErrorsFromAxiosException(error)
{
    let errors = null;
    if (error.response) {
        // The request was made and the server responded with a status code
        // that falls out of the range of 2xx
        console.log(error.response);
        if (error.response.status === 500) {
            errors = { message: 'Erreur 500' };
            if (error.response.data.message) {
                errors = { message: error.response.data.message };
            }
        } else if (error.response.status === 422) {
            errors = error.response.data;
        } else if (error.response.message) {
            errors = { message: error.response.message };
        } else if (error.response.data.message) {
            errors = { message: error.response.data.message };
        } else if (error.response.status) {
            let message = '';
            switch (error.response.status) {
                case 401: message = 'Authentication rquired'; break;
                case 403: message = 'Access refused'; break;
                case 404: message = 'Page not found'; break;
                case 405: message = 'Method not allowed'; break;
                case 419: message = 'Invalid CSRF token'; break;
                default:
                    message = 'Error ' + error.response.status;
            }
            errors = { message: message };
        } else {
            errors = { message: 'Loading error' };
        }
    } else if (error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error('Error', error.request);
        errors = { message: "Request failed !" };
    } else {
        // Something happened in setting up the request that triggered an Error
        console.error('Error', error.message);
        errors = { message: error.message };
    }
    console.error(error.config);
    return errors;
};
