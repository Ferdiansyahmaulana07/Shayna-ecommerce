import axios from "axios";

const BASE_URL= import.meta.env.VITE_REACT_API_URL;//domain endpoint api ktia

const apiClient = axios.create({
    baseURL: BASE_URL,
});
//untuk get data, send data agar lebih mudah ddipakai berkali kali 

export const isAxiosError = axios.isAxiosError;

export default apiClient;