import axios from 'axios';

const API_URL = 'http://localhost/invoice-management/backend/api';

const getInvoices = async() => {
    try {
        const response = await axios.get(`${API_URL}/invoices.php`);
        return response.data;
    } catch (error) {
        console.error('Error fetching invoices:', error);
        throw error;
    }
};

const getInvoice = async(id) => {
    try {
        const response = await axios.get(`${API_URL}/invoices.php?id=${id}`);
        return response.data;
    } catch (error) {
        console.error(`Error fetching invoice ${id}:`, error);
        throw error;
    }
};

const getInvoicesByClient = async(clientId) => {
    try {
        const response = await axios.get(`${API_URL}/invoices.php?client_id=${clientId}`);
        return response.data;
    } catch (error) {
        console.error(`Error fetching invoices for client ${clientId}:`, error);
        throw error;
    }
};

const createInvoice = async(invoiceData) => {
    try {
        const response = await axios.post(`${API_URL}/invoices.php`, invoiceData);
        return response.data;
    } catch (error) {
        console.error('Error creating invoice:', error);
        throw error;
    }
};

const updateInvoice = async(invoiceData) => {
    try {
        const response = await axios.put(`${API_URL}/invoices.php`, invoiceData);
        return response.data;
    } catch (error) {
        console.error(`Error updating invoice ${invoiceData.id}:`, error);
        throw error;
    }
};

const deleteInvoice = async(id) => {
    try {
        const response = await axios.delete(`${API_URL}/invoices.php`, {
            data: { id }
        });
        return response.data;
    } catch (error) {
        console.error(`Error deleting invoice ${id}:`, error);
        throw error;
    }
};

const getInvoiceItems = async(invoiceId) => {
    try {
        const response = await axios.get(`${API_URL}/invoice_items.php?invoice_id=${invoiceId}`);
        return response.data;
    } catch (error) {
        console.error(`Error fetching items for invoice ${invoiceId}:`, error);
        throw error;
    }
};

export {
    getInvoices,
    getInvoice,
    getInvoicesByClient,
    createInvoice,
    updateInvoice,
    deleteInvoice,
    getInvoiceItems
};