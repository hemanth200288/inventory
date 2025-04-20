import axios from 'axios';

const API_URL = 'http://localhost/backend/api/clients.php';

export const getClients = async() => {
    const response = await axios.get(API_URL);
    return response.data;
};

export const getClient = async(id) => {
    const response = await axios.get(`${API_URL}?id=${id}`);
    return response.data;
};

export const createClient = async(clientData) => {
    const response = await axios.post(API_URL, clientData);
    return response.data;
};

export const updateClient = async(clientData) => {
    const response = await axios.put(API_URL, clientData);
    return response.data;
};

export const deleteClient = async(id) => {
    const response = await axios.delete(`${API_URL}?id=${id}`);
    return response.data;
};