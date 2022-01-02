import {ref} from '@vue/composition-api'
import getErrorsFromAxiosException from './errors';

export function usePatchRequest() {
    const loading = ref(false);
    const loaded = ref(false);
    const errors = ref(null);

    const patch = async (url, data) => {
        loading.value = true;
        loaded.value = false;
        errors.value = null;

        let response = null;
        try {
            response = await axios.patch(url, data);
            loaded.value = true;
        } catch(error) {
            errors.value = getErrorsFromAxiosException(error);
        }
        loading.value = false;

        if (response) {
            if (response.hasOwnProperty('data')) {
                return response.data;
            }
            return response
        }
        return {};
    };

    return { loading, loaded, errors, patch };
}
