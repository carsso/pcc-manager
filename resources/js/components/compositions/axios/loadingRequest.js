import {reactive, toRefs,} from '@vue/composition-api';
import getErrorsFromAxiosException from './errors';

export function useGetLoader() {
    const state = reactive({
        loaded: false,
        loading: false,
        errors: null,
    })

    const load = async (url) => {
        state.loading = true;
        state.loaded = false;
        state.errors = null;

        let response = null;
        try {
            response = await axios.get(url);
            state.loaded = true;
        } catch(error) {
            state.errors = getErrorsFromAxiosException(error);
        }
        state.loading = false;

        if (response) {
            if (response.hasOwnProperty('data')) {
                return response.data;
            }
            return response
        }
        return {};
    }

    return { ...toRefs(state), load };
}
