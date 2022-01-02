import {ref} from '@vue/composition-api'
import getErrorsFromAxiosException from './errors';

export function useDeleteRequest() {
    const deleting = ref(false);
    const deleted = ref(false);
    const errorsDeleting = ref(null);

    const doDelete = async (url, data) => {
        deleting.value = true;
        deleted.value = false;
        errorsDeleting.value = null;

        let response = null;
        try {
            response = await axios.delete(url, data);
            deleted.value = true;
        } catch(error) {
            errorsDeleting.value = getErrorsFromAxiosException(error);
        }
        deleting.value = false;

        return (response && response.data) || response;
    };

    return { deleting, deleted, errorsDeleting, doDelete };
}
