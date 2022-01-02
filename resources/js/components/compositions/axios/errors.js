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
                case 401: message = 'Authentification requise'; break;
                case 403: message = 'Accès refusé'; break;
                case 404: message = 'Page introuvable'; break;
                case 405: message = 'Method not allowed'; break;
                case 419: message = 'Token CSRF invalide'; break;
                default:
                    message = 'Erreur ' + error.response.status;
            }
            errors = { message: message };
        } else {
            errors = { message: 'Erreur de chargement' };
        }
    } else if (error.request) {
        // The request was made but no response was received
        // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
        // http.ClientRequest in node.js
        console.error('Error', error.request);
        errors = { message: "La requête a échoué !" };
    } else {
        // Something happened in setting up the request that triggered an Error
        console.error('Error', error.message);
        errors = { message: error.message };
    }
    console.error(error.config);
    return errors;
};
